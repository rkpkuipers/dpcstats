#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('fah');

dailyOffset('memberoffset', 'fah');
dailyOffset('subteamoffset', 'fah');
dailyOffset('individualoffset', 'fah');

$members = array();
$subteams = array();

$page = file('http://fah-web.stanford.edu/teamstats/team92.txt');

$customsubteam = array(	'New-Folder0twisted' => 'New_Folder',
			'Team_Elteor-borislavj0Missy' => 'Team_Elteor_Borislavj');

for($i=0;$i<count($page);$i++)
{
	$data = explode("\t", $page[$i]);
	$user = preg_replace("/<a[^>]*?>(.*)<\/a>/i", "$1", $data[2]);
	$score = $data[3];

	if ( ( is_numeric($score) ) && ( $score > 0 ) )
	{
		addMember(	$members, 
				$subteams, 
				(isset($customsubteam[$user])?$customsubteam[$user].'0'.$user:$user), 
				$score, 
				'0');
	}
}

fixLists($members, $subteams, '0');

foreach ( $subteams as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
	foreach ( $member as $memberName => $memberScore )
	{
		$subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

updateStats($members, 'fah_memberoffset');

addSubTeamStatsRun($subteammembers, 'fah_subteamoffset');

individualStatsrun('fah');

?>
