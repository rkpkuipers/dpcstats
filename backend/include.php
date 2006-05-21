<?php

$db = new pgDataBase();
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
		$naam = str_replace('\\', '\\\\', $line['naam']);
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
				
				$db->deleteQuery('DELETE FROM ' . $tabel . ' WHERE naam = \'' . $membername . '\' 
					AND subteam = \'' . $team . '\' AND dag = \'' . $datum . '\'');
			}
		}
	}

	$subteamCounter = array();
	for($i=0;$i<count($array);$i++)
	{
		$naam = str_replace('\'', '\\\'', $array[$i]->getName());
		$score = $array[$i]->getCredits();
		$subteam = $array[$i]->getTeam();
		$subteamCounter[$subteam]++;
		
		$query = 'SELECT 
				cands 
			FROM 
				' . $tabel . ' 
			WHERE 
				naam = \'' . $naam . '\' 
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
							naam = \'' . $naam . '\'
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
						(\'' . $naam . '\', \'' . $subteam . '\', \'' . $datum . '\', ' . $score . ', 0, ' . $nwId . ')';
			#echo $insQuery . "\n";
			$db->insertQuery($insQuery);

			$insQuery = 'INSERT INTO movement VALUES ( \'' . $naam . '\', \'' . $datum . '\', 1, ' . $score . ',\'' . $tabel . '\')';
			#echo $query;
			//$db->insertQuery($insQuery);
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

		$naam = str_replace('\'', '\\\'', $line['naam']);
		$updateQuery = 'UPDATE
					' . $tabel . '
				SET     dailypos = ' . $pos . '
				WHERE   naam = \'' . $naam . '\'
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

?>
