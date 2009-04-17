<?php

$webroot = '/home/rkuipers/public_html/';
# Configuration
include($webroot . '/classes/config.php');
# Required for support of the member() class used by the soon to be deprecated addStatsRun functions
include($webroot . '/classes/members.php');
# Contains the database code
include($webroot . '/classes/database.php');

$db = new miDataBase($dbuser, $dbpass, $dbhost, $dbport, $dbname);
$db->connect();


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
				
				$db->deleteQuery('DELETE FROM ' . $tabel . ' WHERE naam = \'' . $db->real_escape_string($membername) . '\' 
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
				naam = \'' . $db->real_escape_string($naam) . '\' 
			AND dag = \'' . $datum . '\'
			AND	subteam = \'' . $db->real_escape_string($subteam) . '\'';

		$result = $db->selectQuery($query) or die("Error fetching offset\n" . $query);

		if ( $line = $db->fetchArray($result) )
		{
			$updateQuery = 'UPDATE
							' . $tabel . '
						SET     
							daily = ' . ($score - $line['cands'] ) . ',
							currrank = ' . ( $subteamCounter[$subteam] ) . '
						WHERE   
							naam = \'' . $db->real_escape_string($naam) . '\'
						AND	dag = \'' . $datum . '\'
						AND	subteam = \'' . $db->real_escape_string($subteam) . '\'';
			#echo $updateQuery . ";" . ' ';
			$updateResult = $db->updateQuery($updateQuery);
		}
		else if ( ( $score > 0 ) || ( substr($tabel, 0, 2) == 'sp' ) )
		{
			#echo $score;
			$maxIdQry = 'SELECT max(id) as tops FROM ' . $tabel . ' WHERE dag = \'' . $datum . '\' AND subteam = \'' . $db->real_escape_string($subteam) . '\'';
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
						(\'' . $db->real_escape_string($naam) . '\', \'' . $db->real_escape_string($subteam) . '\', \'' . $datum . '\', ' . $score . ', 0, ' . $nwId . ')';
			#echo $insQuery . "\n";
			$db->insertQuery($insQuery);

			$insQuery = 'INSERT INTO movement VALUES ( \'' . $db->real_escape_string($naam) . '\', \'' . $datum . '\', 1, ' . $score . ',\'' . substr($tabel, 0, strpos($tabel, '_')) . '_memberoffset\')';
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
				WHERE   naam = \'' . $db->real_escape_string($line['naam']) . '\'
				AND	subteam = \'' . $db->real_escape_string($currSubteam) . '\'
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

	$seperator = getSeperator($project);
	
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
				' . ($db->getType()=='mysql'?'CONCAT( subteam, \'' . $seperator . '\', naam ) as naam':'subteam || \' - \' || naam as naam') . ',
				( cands + daily ) as total
			FROM
				' . $project . '_subteamoffset 
			WHERE 
				dag = \'' . $datum . '\'
		) 
		ORDER BY
			total DESC';
	
	$result = $db->selectQuery($query);

	$member = array();
	while ( $line = $db->fetchArray($result) )
	{
		$member[$line['naam']] = $line['total'];
	}

	updateStats($member, $project . '_individualoffset');
}

function setDailyOffset($prefix, $tabel, $datum)
{
	global $db, $datum;
	
	#echo 'Running setDailyOffset for ' . $prefix . '_' . $tabel . ' (' . $datum  . ')' . "\n";
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
	global $db, $datum;
	if ( $datum == '' )
		die('Error: Trying to run dailyOffset without $datum set');

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
		{
			setDailyOffset($project, $tabel, $datum);
		}
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
					\'' . $db->real_escape_string($newMemberName) . '\',
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
					naam = \'' . $db->real_escape_string($retMemberName) . '\'
				AND	dag = \'' . $datum . '\'';
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
	$flushingMembers = array_diff_assoc($members, $currMembers);

	foreach($flushingMembers as $flushMemberName => $flushTotalScore)
	{
		$updateQuery = 'UPDATE
					' . $table . '
				SET
					daily = ' . ( $members[$flushMemberName] - $currMemberInfo[$flushMemberName]['cands'] ) . '
				WHERE
					naam = \'' . $db->real_escape_string($flushMemberName) . '\'
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
					naam = \'' . $db->real_escape_string($line['naam']) . '\'
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
					naam = \'' . $db->real_escape_string($line['naam']) . '\'
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
				\'' . $db->real_escape_string($name) . '\',
				' . $credits . ',
				\'' . $datum . '\',
				' . $direction . ',
				\'' . $table . '\'
			)';
	
	$db->insertQuery($insertQuery);

	unset($insertQuery);
}

function addTeam(&$teams, $rawname, $score)
{
	foreach($teams as $teamname => $teamscore)
	{
		if ( strtolower($teamname) == strtolower($rawname) )
		{
			$teams[$teamname] += $score;
			$score = 0;
		}
	}

	if ( $score != 0 )
	{
		$teams[$rawname] = $score;
	}
}

function addMember(&$members, &$subteams, $rawname, $score, $seperator)
{
	# Determine the position of the seperator in the name
	$seperatorPosition = strpos($rawname, $seperator);

	# If the first character is the seperator consider the member not to be part of a team
	if ( substr($rawname, 0, 1) == $seperator )
	{
		$members[$rawname] = $score;
	}
	# Else if the seperator is found in the name we have a subteammember
	elseif ( is_numeric($seperatorPosition) )
	{
		# Get the team and name from the rawname
		$team = substr($rawname, 0, $seperatorPosition);
		$name = substr($rawname, ( $seperatorPosition + 1 ));

		# Loop through the existing members to check if the member allready exists with the same name in a different case
		# This way user12 and USeR12 are merged into one member with the scores added up
		$teamset = 0;
		foreach($members as $membername => $memberscore)
		{
			if ( strtolower($membername) == strtolower($team) )
			{
				# If the subteam array for the current team doesn't exist but there is a member with the same name as the subteam
				# add this member to the subteam with the subteamname as the membername
				if ( ! isset($subteams[$team]) )
					$subteams[$team][$team] = $members[$team];

				# Add the score to the total teamscore
				$members[$membername] += $score;
				$teamset = 1;
				$team = $membername;
			}
		}

		# If the team hasn't been found insert it into the member array as a single new entity
		if ( $teamset == 0 )
			$members[$team] = $score;

		unset($teamset);

		# If the subteam array hasn't been set, create it
		if ( ! isset($subteams[$team]) )
		{
			$subteams[$team] = array();
		}

		# Loop through the subteam members to verify is the subteammember doesn't exist with the name in a different case
		foreach($subteams[$team] as $stname => $stscore)
		{
			if ( strtolower($stname) == strtolower($name) )
			{
				# If it does add the score to the allready existing member
				$subteams[$team][$stname] += $score;
				$score = 0;
			}
		}

		# If the member wasn't found add it to the subteammembers list as a new subteammember
		if ( $score != 0 )
			$subteams[$team][$name] = $score;
	}
	# Otherwise we have a non-subteam member
	else
	{
		# Loop through the existing members to check if the member allready exists with the same name in a different case
		foreach($members as $membername => $memberscore)
		{
			if ( strtolower($membername) == strtolower($rawname) )
			{
				# If a user has the same name as a subteam, add it to the subteam as <user><seperator><user>
				if ( isset($subteams[$membername]) )
					$subteams[$membername][$membername] = $score;

				# Add the score to the allready existing member
				$members[$membername] += $score;
				$score = 0;
			}
		}

		# If the member wasn't found add it as a new entity
		if ( $score != 0 )
		{
			$members[$rawname] = $score;
		}
	}
}

function fixLists(&$member, &$subteam, $seperator)
{
	# Remove subteams with only one member, consider those as members with the seperator in their name
	foreach($subteam as $teamname => $subteammembers)
	{
		if ( count($subteammembers) == 1 )
		{
			$member[$teamname . $seperator . key($subteammembers)] = current($subteammembers);
			unset($subteam[$teamname]);
			unset($member[$teamname]);
		}
		else
		{
			arsort($subteam[$teamname], SORT_NUMERIC);

			foreach($subteammembers as $stmembername => $stmemberscore)
			{
				if ( isset($member[$teamname . $seperator . $stmembername]) )
				{
					unset($member[$teamname . $seperator . $stmembername]);
				}
			}
		}
	}

	arsort($member, SORT_NUMERIC);
}

function getSeperator($project)
{
	global $db;

	$query = 'SELECT
			seperator
		FROM
			project
		WHERE
			project = \'' . $project . '\'';
	
	$result = $db->selectQuery($query);

	if ( $line = $db->fetchArray($result) )
		$seperator =  $line['seperator'];
	
	if ( $seperator != '' )
		return $seperator;
	else
		return ' - ';
}

?>
