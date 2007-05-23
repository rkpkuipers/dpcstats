#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('ogr');

dailyOffset('memberoffset', 'ogr');

# Gather data from dnet



$members = array();
for($i=1;$i<3000;$i+=100)
{
	$tempdir = '/home/rkuipers/stats/statsrun/files/';
	$filename = $tempdir . '/dnetc.' . $i . '.out';
	system('wget -q "http://stats.distributed.net/team/tmember.php?project_id=25&pass=&team=10313&source=o&low=' . $i . '&limit=100" -O ' . $filename);
	system('cat ' . $filename . ' |  grep -A 7 "participant/psummary" | grep -v -- ^-- > ' . $filename . '.2');
	$lines = file($filename . '.2');
	for($line=0;$line<count($lines);$line+=8)
	{
		$name = trim(str_replace(array('[DPC]', '[Dutch Power Cows]'), '', strip_tags($lines[$line])));
		$score = str_replace(',', '', trim(strip_tags($lines[$line+7])));
		$members[$name] = $score;
	}
	unlink($filename);
	unlink($filename . '.2');
}

#dailyOffset('teamoffset', 'sob');
#dailyOffset('subteamoffset', 'sob');
#dailyOffset('individualoffset', 'sob');

arsort($members, SORT_NUMERIC);
updateStats($members, 'ogr_memberoffset');

/*
updateStats($members, 'sob_memberoffset');
updateStats($teams, 'sob_teamoffset');

foreach ( $subTeamArray as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
	{
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'sob_subteamoffset');

individualStatsrun('sob');
*/
?>
