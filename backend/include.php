<?php

include('/var/www/tstats/classes/config.php');

$db = new miDataBase($dbuser, $dbpass, $dbhost, $dbport, $dbname);
$db->connect();

# Globals

$listsize = 30;

function getCurrentDate($prefix)
{
	return date("Y-m-d");
	
        switch($prefix)
        {
	case 'fah':
		return date("Y-m-d", strtotime("+30 minutes"));
		break;
	default:
		return date("Y-m-d");
        }
}

function getPrevDate($datum = '')
{
	if ( $datum == '' )$datum = date("Y-m-d");
        return date("Y-m-d", strtotime($datum) - 1);
}

function getTwoDaysPrev($datum = '')
{
	if ( $datum == '' )$datum = date("Y-m-d");
	return date("Y-m-d", strtotime("-2 day"));
}

function getPrevWeek($datum = '')
{
	if ( $datum == '' )$datum = date("Y-m-d");
	return date("Y-m-d", strtotime("-1 week" ));
}

$memberList = array();
function inArray($value, $array)
{
	global $memberList;

	if ( count($memberList) == 0 )
	{
		for($i=0;$i<count($array);$i++)
	        {
			$memberList[$array[$i]->getName()] = 0;
		}
        }

	if ( in_array($value, $memberList) )
		return true;
	else
	        return false;
}

function getSubteamListFromArray($array)
{
	$members = array();

	for($i=0;$i<count($array);$i++)
		$members[$array[$i]->getTeam()][$array[$i]->getName()] = 0;
	
	return $members;
}

function getMemberListFromArray($array)
{
	$members = array();

	for($i=0;$i<count($array);$i++)
		$members[] = $array[$i]->getName();
	
	return $members;
}

function addStatsRun($array, $tabel)
{
        global $datum, $db;

	if ( count($array) == 0 )die('Lege array tijdens statsrun ' . date('Y-m-d:H:i') . ' voor tabel ' . $tabel);

        # Check for retirements
        $query = 'SELECT naam, (cands+daily) as totaal, cands, currrank FROM ' . $tabel . ' WHERE dag = \'' . $datum . '\'';

        $result = $db->selectQuery($query);

	$currentUsers = array();
	$currentScore = array();
	$currentRanks = array();
        while ( $line = $db->fetchArray($result) )
        {
		$currentUsers[] = $line['naam'];
		$currentScore[$line['naam']] = $line['cands'];
		$currentRanks[$line['naam']] = $line['currrank'];
	}

	$missing = array_diff($currentUsers, getMemberListFromArray($array));
	sort($missing);

	if ( count($missing) > 0 )
	{
		for($i=0;$i<count($missing);$i++)
                {
			$naam = str_replace('\\', '\\\\', $missing[$i]);
			$naam = str_replace('/', '\/', $naam);
			$naam = str_replace('\'', '\\\'', $naam);

			$remQuery = 'DELETE FROM ' . $tabel . ' WHERE naam = \'' . $naam . '\' AND dag = \'' . $datum . '\';';
			#echo $remQuery;
			$db->deleteQuery($remQuery);
			 
			
			$insQuery = 'INSERT INTO
					movement
				( naam, datum, direction, candidates, tabel )
				SELECT
					naam,
					dag,
					0,
					(cands+daily),
					\'' . $tabel . '\'
				FROM
					' . $tabel . '
				WHERE
					naam = \'' . $naam . '\'
				AND	dag = \'' . $datum . '\'';

			$db->insertQuery($insQuery);
                }
        }

        for($i=0;$i<count($array);$i++)
        {
		$naam = $array[$i]->getName();

		$naam = str_replace('\\', '\\\\', $naam);
		$naam = str_replace('/', '\/', $naam);
                $naam = str_replace('\'', '\\\'', $naam);
		#echo $array[$i]->getName() . ' ' . $naam . "\n";
                $score = $array[$i]->getCredits();

		if ( ( $currentScore[$naam] != $score ) || ( $currentRanks[$naam] != ($i+1) ) ) # If no change in score there's no need for an update
		{
                	$query = 'SELECT cands FROM ' . $tabel . ' WHERE naam = \'' . $naam . '\' AND dag = \'' . $datum . '\';';
			#echo $query;
        	        $result = $db->selectQuery($query);# or die("Error fetching offset\n" . $query);

                	if ( $line = $db->fetchArray($result) )
	#		if ( in_array($array[$i]->getNaam(), $currentUsers) )
        	        {
                	        $updateQuery = 'UPDATE
                        	                        ' . $tabel . '
                                	        SET     daily = ' . ($score - $line['cands'] ) . ',
							currrank = ' . ( $i + 1 ) . '
        	                                WHERE   naam = \'' . $naam . '\'
                	                        AND     dag = \'' . $datum . '\'';
	                        #echo $updateQuery . ";" . ' ';
				$updateResult = $db->updateQuery($updateQuery);
                	}
	                else if ( ( $score > 0 ) || ( substr($tabel, 0, 4) == 'rah_' ) )
  #			else if ( $currentScore[$array[$i]->getNaam()] > 0 )
                	{
                        	$maxIdQry = 'SELECT max(id) as tops FROM ' . $tabel . ' WHERE dag = \'' . $datum . '\'';
	                        $maxIdResult = $db->selectQuery($maxIdQry);
        	                if ( $maxIdLine = $db->fetchArray($maxIdResult) )
                	                $nwId = $maxIdLine['tops'] + 1;
                        	else
	                                $nwId = 0;
        	                #echo $nwId;
                	        $insQuery = 'INSERT INTO
                        	                ' . $tabel . '
                                	                (naam, dag, cands, daily, id)
                                        	VALUES
                                                	(\'' . $naam . '\',\'' . $datum . '\', ' . $score . ', 0, ' . $nwId . ')';
	                        #echo $insQuery . "\n";
        	                $db->insertQuery($insQuery);
	
				$insQuery = 'INSERT INTO movement VALUES ( \'' . $naam . '\', \'' . $datum . '\', 1, ' . $score . ',\'' . $tabel . '\')';
				#echo $query;
				$db->insertQuery($insQuery);
	                }
		}
		#echo $i . "\n";
        }

        $query = 'SELECT naam, daily FROM ' . $tabel . ' WHERE dag=\'' . $datum . '\' ORDER BY daily DESC';
        $result = $db->selectQuery($query);

        $pos = 1;
        while( $line = $db->fetchArray($result, MYSQL_ASSOC) )
        {
		$naam = str_replace('\\', '\\\\', $line['naam']);
                $naam = str_replace('\'', '\\\'', $naam);
                $updateQuery = 'UPDATE
                                        ' . $tabel . '
                                SET     dailypos = ' . $pos . '
                                WHERE   naam = \'' . $naam . '\'
                                AND     dag = \'' . $datum . '\'';

                #echo $updateQuery;
                $db->updateQuery($updateQuery);
                $pos++;
        }

	$info = explode('_', $tabel);

	$query = 'UPDATE
			updates
		SET
			tijd = \'' . date("Y-m-d H:i:s") . '\'
		WHERE
			project = \'' . $info[0] . '\'
		AND	tabel = \'' . $info[1] . '\'';

	$db->insertQuery($query);
}

function addSubteamStatsrun($array, $tabel)
{
        global $datum, $db;

	if ( count($array) == 0 )die('Lege array tijdens statsrun ' . date('Y-m-d:H:i') . ' voor tabel ' . $tabel);

	$query = 'SELECT
			naam,
			subteam,
			( cands + daily )AS score
		FROM
			' . $tabel . '
		WHERE
			dag = \'' . $datum . '\'';
	
	$result = $db->selectQuery($query);

	while ( $line = $db->fetchArray($result) )
	{
		$naam = $line['naam'];
		$currentuser[$line['subteam']][$naam] = $line['score'];
	}
	$userarray = getSubteamListFromArray($array);

	foreach($currentuser as $team => $members)
	{
		foreach($members as $membername => $score)
		{
			if ( ! isset($userarray[$team][$membername]) )
			{
				/*
				$db->insertQuery('INSERT INTO 
						movement 
						( 
							naam, 
							datum, 
							direction, 
							candidates, 
							tabel 
						)
						VALUES
						(
							\'' . $membername . '\',
							\'' . $datum . '\',
							0,
							' . ( $currentuser[$team][$membername] ) . ',
							\'' . $tabel . '\'
						)');*/
				
				$db->deleteQuery('DELETE FROM ' . $tabel . ' WHERE naam = \'' . pg_escape_string($membername) . '\' 
					AND subteam = \'' . $team . '\' AND dag = \'' . $datum . '\'');
			}
		}
	}

	$subteamCounter = array();
	for($i=0;$i<count($array);$i++)
	{
		$naam = $array[$i]->getName();
		
		$score = $array[$i]->getCredits();
		$subteam = $array[$i]->getTeam();

		if ( ! isset($subteamCounter[$subteam]) )
			$subteamCounter[$subteam] = 1;
		else
			$subteamCounter[$subteam]++;
		
		$query = 'SELECT 
				cands 
			FROM 
				' . $tabel . ' 
			WHERE 
				naam = \'' . pg_escape_string($naam) . '\' 
			AND dag = \'' . $datum . '\'
			AND	subteam = \'' . $subteam . '\'';
			#echo $query;

		$result = $db->selectQuery($query) or die("Error fetching offset\n" . $query);

		if ( $line = $db->fetchArray($result) )
		{
			$updateQuery = 'UPDATE
							' . $tabel . '
						SET     
							daily = ' . ($score - $line['cands'] ) . ',
							currrank = ' . ( $subteamCounter[$subteam] ) . '
						WHERE   
							naam = \'' . pg_escape_string($naam) . '\'
						AND	dag = \'' . $datum . '\'
						AND	subteam = \'' . $subteam . '\'';
#			echo $updateQuery . ";" . ' ';
			$updateResult = $db->updateQuery($updateQuery);
		}
		else if ( ( $score > 0 ) || ( substr($tabel, 0, 4) == 'sp5_' ) )
		{
			$maxIdQry = 'SELECT max(id) as tops FROM ' . $tabel . ' WHERE dag = \'' . $datum . '\' AND subteam = \'' . $subteam . '\'';
			$maxIdResult = $db->selectQuery($maxIdQry);
			if ( $maxIdLine = $db->fetchArray($maxIdResult) )
				$nwId = $maxIdLine['tops'] + 1;
			else
				$nwId = 0;
			#echo $nwId;
			$insQuery = 'INSERT INTO
					' . $tabel . '
						(naam, subteam, dag, cands, daily, id)
					VALUES
						(\'' . pg_escape_string($naam) . '\', \'' . $subteam . '\', \'' . $datum . '\', ' . $score . ', 0, ' . $nwId . ')';
			#echo $insQuery . "\n";
			$db->insertQuery($insQuery);

			$insQuery = 'INSERT INTO movement VALUES ( \'' . pg_escape_string($naam) . '\', \'' . $datum . '\', 1, ' . $score . ',\'' . substr($tabel, 0, strpos($tabel, '_')) . '_memberoffset\')';
			#echo $insQuery;
			$db->insertQuery($insQuery);
		}
		#echo $i . "\n";
	}

	$query = 'SELECT naam, daily, subteam FROM ' . $tabel . ' WHERE dag=\'' . $datum . '\' ORDER BY subteam, daily DESC';
	#echo $query;
	$result = $db->selectQuery($query);

	$currSubteam = '';
	$pos = 1;
	while( $line = $db->fetchArray($result, MYSQL_ASSOC) )
	{
		if ( $currSubteam != $line['subteam'] )
		{
			$pos = 1;
			$currSubteam = $line['subteam'];
		}

		$updateQuery = 'UPDATE
					' . $tabel . '
				SET     dailypos = ' . $pos . '
				WHERE   naam = \'' . pg_escape_string($line['naam']) . '\'
				AND	subteam = \'' . $currSubteam . '\'
				AND     dag = \'' . $datum . '\'';

		#echo $updateQuery;
		$db->updateQuery($updateQuery);
		$pos++;
	}

	$info = explode('_', $tabel);

	$query = 'UPDATE
			updates
		SET
			tijd = \'' . date("Y-m-d H:i:s") . '\'
		WHERE
			project = \'' . $info[0] . '\'
		AND	tabel = \'' . $info[1] . '\'';

	$db->insertQuery($query);
}

function checkTeamMember($tabel, $team, $member)
{
	global $db;
	
	$query = 'SELECT
			member,
			name
		FROM
			' . $tabel . '
		WHERE
			name = \'' . $team . '\'
		AND	member = \'' . $member . '\'';
	
	$result = $db->selectQuery($query);

	if ( $line = $db->fetchArray($result) )
		return true;
	else
		return false;
}

function addTeamMember($tabel, $team, $member)
{
	global $db;

	$query = 'INSERT INTO
			' . $tabel . '
		(
			member,
			name
		)
		VALUES
		(
			\'' . $member . '\',
			\'' . $team . '\'
		)';
	
	$result = $db->selectQuery($query);
}

function individualStatsrun($project)
{
	global $db, $datum;
	$datum = getCurrentDate($project);
	
	$query = '(
			SELECT 
				naam,
				( cands + daily ) as total
			FROM 
				' . $project . '_memberoffset 
			WHERE 
				dag = \'' . $datum . '\' 
			AND 	naam NOT IN 
				(
					SELECT 
						DISTINCT(subteam) 
					FROM 
						' . $project . '_subteamoffset 
					WHERE 
						dag = \'' . $datum . '\'
					)
		) 
		UNION 
		(
			SELECT 
				' . ($db->getType()=='mysql'?'CONCAT( subteam, \' - \', naam ) as naam':'subteam || \' - \' || naam as naam') . ',
				( cands + daily ) as total
			FROM
				' . $project . '_subteamoffset 
			WHERE 
				dag = \'' . $datum . '\'
		) 
		ORDER BY
			total DESC';
	
	$result = $db->selectQuery($query);

	$members = array();
	while ( $line = $db->fetchArray($result) )
	{
		$members[] = new Member($line['naam'], $line['total']);
	}

	addStatsrun($members, $project . '_individualoffset');
}

function setDailyOffset($prefix, $tabel, $datum)
{
	global $db;
	
	if ( $tabel == 'subteamoffset' )
		$selectFields = ' subteam, ';
	else
		$selectFields;
	
	$query = 'INSERT INTO 
			' . $prefix . '_' . $tabel . '
				SELECT 
					naam, 
					(cands+daily),
					CASE WHEN currrank=0 THEN id ELSE currrank END,
					\'' . $datum . '\', 
					0, 
					dailypos, 
					' . $selectFields . '
					id 
				FROM 
					' . $prefix . '_' . $tabel . '
				WHERE
					dag = \'' . date("Y-m-d", strtotime("-1 days", strtotime($datum))) . '\'';
	$db->insertQuery($query);
}

function dailyOffset($tabel, $project)
{
	global $db;
	$datum = getCurrentDate($project);

	$query = 'SELECT 
			COUNT(naam) 
		FROM 
			' . $project . '_' . $tabel . ' 
		WHERE 
			dag = \'' . $datum . '\'';
	$result = $db->selectQuery($query);

	if ( $line = $db->fetchArray($result) )
	{
		if ( $line[0] <= 0 )
			setDailyOffset($project, $tabel, $datum);
	}
	else
		setDailyOffset($project, $tabel, $datum);
}

function updateStats($members, $table)
{
	global $datum, $db;

	# Retrieve a list of members currently in the database
	$query = 'SELECT
			naam,
			cands,
			daily,
			(cands + daily)AS total
		FROM
			' . $table . '
		WHERE
			dag = \'' . $datum . '\'
		ORDER BY
			id';
	
	$result = $db->selectQuery($query);

	$currMembers = array();
	$currMemberInfo = array();
	while ( $line = $db->fetchArray($result) )
	{
		$currMembers[$line['naam']] = $line['total'];
		$currMemberInfo[$line['naam']]['cands'] = $line['cands'];
		$currMemberInfo[$line['naam']]['daily'] = $line['daily'];
	}

	# Using array_diff obtain all members in the new array compared to the database contents
	# this effectively yields an array with new members
	$newMembers = array_diff(array_keys($members), array_keys($currMembers));

	# Insert the new members one by one into the database
	foreach($newMembers as $origID => $newMemberName)
	{
		$newMemberScore = $members[$newMemberName];

		$insertQuery = 'INSERT INTO
					' . $table . '
				( 
					naam, cands, id, dag, daily, currrank 
				)
				VALUES
				(
					\'' . $newMemberName . '\',
					' . $newMemberScore . ',
					' . ( count($currMembers) + 1 ) . ',
					\'' . $datum . '\',
					0,
					' . ( count($surrMembers) + 1 ) . '
				)';
		$db->insertQuery($insertQuery);
		unset($insertQuery);

		$currMembers[$newMemberName] = $newMemberScore;
		$currMemberInfo[$newMemberName]['cands'] = $newMemberScore;
		$currMemberInfo[$newMemberName]['daily'] = 0;

		# Register the new member in the movement table for listing as new member
		addMovementRecord($newMemberName, $newMemberScore, $datum, 1, $table);

		unset($newMemberScore);
	}

	unset($newMembers);

	# Doing the reverse yields an array with all retired members
	$retMembers = array_diff(array_keys($currMembers), array_keys($members));

	foreach($retMembers as $origId => $retMemberName)
	{
		$retMemberScore = $members[$retMemberName];

		$deleteQuery = 'DELETE FROM
					' . $table . '
				WHERE
					naam = \'' . $retMemberName . '\'
				AND	datum = \'' . $datum . '\'';
		$db->deleteQuery($deleteQuery);

		unset($deleteQuery);

		addMovementRecord($retMemberName, $retMemberScore, $datum, 0, $table);
		unset($retMemberScore);

		unset($currMembers[$retMemberName]);
		unset($currMemberInfo[$retMemberName]);
	}
	unset($retMembers);

	# Now any differing records are caused by increased total scores thus
	# a third array_diff yields all members who have flushed since the
	# previous statsrun
	$flushingMembers = array_diff($members, $currMembers);

	foreach($flushingMembers as $flushMemberName => $flushTotalScore)
	{
		$updateQuery = 'UPDATE
					' . $table . '
				SET
					daily = ' . ( $members[$flushMemberName] - $currMemberInfo[$flushMemberName]['cands'] ) . '
				WHERE
					naam = \'' . $flushMemberName . '\'
				AND	dag = \'' . $datum . '\'';

		$db->updateQuery($updateQuery);
		unset($updateQuery);

		$currMemberInfo[$flushMemberName]['daily'] = ( $members[$flushMemberName] - $currMemberInfo[$flushMemberName]['cands'] );
		$currMemberInfo[$flushMemberName]['cands'] = $flushTotalScore;
		$currMembers[$flushMemberName] = $flushTotalScore;
	}
	unset($flushingMembers);

	#Set the flush rank for the members who have moved since the last run
	updateDailyRanks($table);

	#Set the curr rank for the members who have moved in the overall list since the last run
	updateCurrRanks($table);

	#Set the current time as last update time in the database
	updateStatsrunTime($table);
}

function updateStatsrunTime($table)
{
	global $db;

	$info = explode('_', $table);
	$project = $info[0];
	$tablename = $info[1];

	$query = 'REPLACE INTO
			updates
		( project, tabel, tijd )
		VALUES
		(
			\'' . $project . '\',
			\'' . $tablename . '\',
			NOW()
		)';
	
	$db->insertQuery($query);
}

function updateCurrRanks($table)
{
	global $db, $datum;

	$query = 'SELECT
			naam,
			(cands + daily) as total,
			currrank
		FROM
			' . $table . '
		WHERE
			dag = \'' . $datum . '\'
		ORDER BY
			( cands + daily ) DESC,
			naam';
	
	$result = $db->selectQuery($query);

	$newTotalRank = 1;
	while ( $line = $db->fetchArray($result) )
	{
		if ( $line['currrank'] != $newTotalRank )
		{
			$updQuery = 'UPDATE
					' . $table . '
				SET
					currrank = ' . $newTotalRank . '
				WHERE
					naam = \'' . $line['naam'] . '\'
				AND	currrank = ' . $line['currrank'] . '
				AND	dag = \'' . $datum . '\'';

			$db->updateQuery($updQuery);
		}

		$newTotalRank++;
	}
}

function updateDailyRanks($table)
{
	global $db, $datum;

	$query = 'SELECT
			naam,
			daily,
			dailypos
		FROM
			' . $table . '
		WHERE
			dag = \'' . $datum . '\'
		AND NOT	daily = 0
		ORDER BY
			daily DESC,
			naam';
	
	$result = $db->selectQuery($query);

	$newDailyRank = 1;
	while ( $line = $db->fetchArray($result) )
	{
		if ( $line['dailypos'] != $newDailyRank )
		{
			$updQuery = 'UPDATE
					' . $table . '
				SET
					dailypos = ' . $newDailyRank . '
				WHERE
					naam = \'' . $line['naam'] . '\'
				AND	dag = \'' . $datum . '\'
				AND	daily = ' . $line['daily'];

			$db->updateQuery($updQuery);
			unset($updQuery);
		}
		$newDailyRank++;
	}
}

function addMovementRecord($name, $credits, $datum, $direction, $table)
# Adds a new record to the movement table in case of a join/leave
{
	global $db;

	$insertQuery = 'INSERT INTO
				movement
			(
				naam, candidates, datum, direction, tabel 
			)
			VALUES
			(
				\'' . $name . '\',
				' . $credits . ',
				\'' . $datum . '\',
				' . $direction . ',
				\'' . $table . '\'
			)';
	
	$db->insertQuery($insertQuery);

	unset($insertQuery);
}

?>
