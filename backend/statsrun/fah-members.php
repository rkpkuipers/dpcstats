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
$subteams = array();

function getMembers()
{
	global $datum, $members, $subteams;

	# Three members are placed in a subteam even though the name doesn't match
	$customsubteam = array(	'New-Folder0twisted' => 'New_Folder', 
				'Team_Elteor-borislavj0Missy' => 'Team_Elteor_Borislavj');
	
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

	for($i=2;$i<count($info);$i+=8)
	{
		#echo 'user: ' . trim($info[$i]) . ' score: ' . trim($info[$i+4]) . "\n";

		$user = trim($info[$i]);
		$score = trim($info[$i+4]);

		if ( $score == 0 ) break;

		addMember($members, $subteams, (isset($customsubteam[$user])?$customsubteam[$user].'0'.$user:$user), $score, '0');
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
} while ( ( ( count($members) == 0 ) || ( count($subteams) == 0 ) ) && ( $itt < 25 ) );

fixLists($members, $subteams, '0');

updateStats($members, 'fah_memberoffset');

$fahsubteammembers = array();
foreach ( $subteams as $subTeamName => $member )
{
	if ( ! $subTeamName == '0' )
	{
		arsort($member, SORT_NUMERIC);
		
		foreach ( $member as $memberName => $memberScore )
		{
			$fahsubteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
		}
	}
	else
	{
		echo 'hit';
	}
}
addSubTeamStatsRun($fahsubteammembers, 'fah_subteamoffset');

individualStatsrun('fah');

?>
