#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

# Gather data from sengent

function getSubteam($name)
{
	global $db;
	
	$team = '';
	
	$name = str_replace('\'', '\\\'', $name);
	
	$query = 'SELECT
			name
		FROM
			fah_subteam
		WHERE
			member = \'' . $name . '\'';

	$result = $db->selectQuery($query) or die('');

	if ( $line = $db->fetchArray($result) )
	{
		$team = $line['name'];
	}
	
	return $team;
}

$datum = getCurrentDate('fah');

dailyOffset('memberoffset', 'fah');
dailyOffset('subteamoffset', 'fah');
dailyOffset('individualoffset', 'fah');

$members = array();
$subteamMembers = array();

function getMembers()
{
	global $datum, $members, $subteamMembers;
	
	$tempDir = '/home/rkuipers/stats/statsrun/files/';
	system('wget "http://fah-web.stanford.edu/cgi-bin/main.py?qtype=teampage&teamnum=92" -q -O ' . $tempDir . '/fah-members.html');
	system('cat ' . $tempDir . '/fah-members.html | grep -e fah-web.stanford.edu\/awards\/cert.php -e qtype=userpage | grep -v t=wus > ' . $tempDir . '/fah-members.txt');
	unlink($tempDir . '/fah-members.html');

	$raw = implode('', file($tempDir . '/fah-members.txt'));
	$data = preg_replace(array('@<a[^>]*?>@si', '@</a>@si', '@<td[^>]*?>@si', '@</td>@si', '@<TD[^>]*?>@si', '@</TD>@si'), '||', $raw);

	unlink($tempDir . '/fah-members.txt');

	$info = explode('||', $data);

	$members = array();
	$subteams = array();
	$subteamMembers = array();

	for($i=2;$i<count($info);$i+=8)
	{
		#echo 'user: ' . trim($info[$i]) . ' score: ' . trim($info[$i+4]) . "\n";

		$user = trim($info[$i]);
		$score = trim($info[$i+4]);

		if ( $score == 0 ) break;
	
		$team = getSubteam($user);
		if ( $team != '' )
		{
			$subteams[$team] += $score;
			$subteamMembers[$team][$user] = $score;
		}
		elseif ( is_numeric(strpos($user, '0')) )
		{
			$pos = strpos($user, '0');
			$team = substr($user, 0, $pos);
			$user = substr($user, ( $pos + 1 ) );
			#echo $user . ' - ' . $team . "\n";
		
			$set = 0;
			foreach($subteams as $stName => $stScore)
			{
				if ( strtoupper($stName) == strtoupper($team) )
				{
					$set = 1;
					$subteams[$stName] += $score;
				
					if ( ! isset($subteamMembers[$stName][$user]) )
						$subteamMembers[$stName][$user] = $score;
					else
						$subteamMembers[$stName][$user] += $score;
				}
			}

			if ( $set == 0 )
			{
				$subteams[$team] = $score;
				
				$subteamMembers[$team] = array();

				if ( ! isset($subteamMembers[$team][$user]) )
					$subteamMembers[$team][$user] = $score;
				else
					$subteamMembers[$team][$user] += $score;
			}
		}
		else
		{
			$set = 0;
			foreach($members as $mName => $mScore)
			{
				if ( strtoupper($mName) == strtoupper($user) )
				{
					$members[$mName] += $score;
					$set++;
				}
			}
		
			if ( $set == 0 )
				$members[$user] = $score;
		}
	}

	foreach($members as $mName => $mScore)
	{
		foreach($subteams as $stName => $stScore)
		{
			if ( strtoupper($mName) == strtoupper($stName) )
			{
				#$stScore += $mScore;
				$subteams[$stName] += $mScore;
				if ( ! isset($subteamMembers[$stName]) )
					$subteamMembers[$stName] = $mScore;
				else
					$subteamMembers[$stName][$mName] += $mScore;
				#echo $mName . "\n";
			}
		}
	}

	foreach ( $subteams as $name => $score )
	{
		if ( count($subteamMembers[$name] ) > 1 )
		{
			#$members[$name] = $score;
			#echo $name . ' ' . $score . "\n";
			if ( $name != '' )
				$members[$name] = $score;
		}
		else
		{
			foreach($subteamMembers[$name] as $temp =>  $tmpname)
				$members[$name . '0' . $temp] = $score;
		}
	}
}

$itt = 0;
do
{
	if ( $itt > 0 )
	{
		echo "No members retrieved, restarting loop\n";
		sleep(10);
	}
	$itt++;
	
	getMembers();
} while ( ( count($members) == 0 ) || ( count($subteamMembers) == 0 ) );

arsort($members, SORT_NUMERIC);
foreach($members as $name => $score)
	$fahmembers[] = new Member($name, $score);

addStatsrun($fahmembers, 'fah_memberoffset');

$fahsubteammembers = array();
foreach ( $subteamMembers as $subTeamName => $member )
{
	if ( ( ! $subTeamName == '0' ) && (  count($subteamMembers[$subTeamName]) > 1 ) )
	{
		arsort($member, SORT_NUMERIC);
		
		foreach ( $member as $memberName => $memberScore )
		{
			$fahsubteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
		}
	}
}

addSubTeamStatsRun($fahsubteammembers, 'fah_subteamoffset');

individualStatsrun('fah');

?>
