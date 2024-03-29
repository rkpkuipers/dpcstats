#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('fah');

dailyOffset('memberoffset', 'fah');
dailyOffset('subteamoffset', 'fah');
dailyOffset('individualoffset', 'fah');

$members = array();
$subteams = array();

$page = file('http://fah-web.stanford.edu/daily_user_summary.txt');

$customsubteam = array(	'New-Folder0twisted' => 'New_Folder',
			'Team_Elteor-borislavj0Missy' => 'Team_Elteor_Borislavj');

for($i=0;$i<count($page);$i++)
{
	$data = explode("\t", $page[$i]);
	
	# Skip entries without a team number
	if ( ! isset($data[3]) )
		continue;

	if ($data[3] == 92 )
	{
		$user = preg_replace("/<a[^>]*?>(.*)<\/a>/i", "$1", $data[0]);
		$score = $data[1];

		$user = str_replace('[DPC]_Team_Coldfusion', '[DPC]_Team_ColdFusion', $user);

		if ( ( is_numeric($score) ) && ( $score > 0 ) )
		{
			addMember(	$members, 
					$subteams, 
					(isset($customsubteam[$user])?$customsubteam[$user].'0'.$user:$user), 
					$score, 
					'0');
		}
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

$db->disconnect();

?>
