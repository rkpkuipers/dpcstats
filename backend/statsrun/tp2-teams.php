#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

# Gather data from sengent

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'tsc');

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$tscteams = array();

for($i=6;$i<count($teams);$i+=5)
{
	if ( ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 1000000 ) ) )
	{
	#	$teams[$teams[$i+1]] = $teams[$i];
#		$tscteams[count($tscteams)] = new Member($teams[$i], $teams[$i+1]);
		$tscteams[$teams[$i]] = $teams[$i+1];
	}
}

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);
$rang = 1;

$score = 0;

for($i=10;$i<count($teams);$i+=5)
{
        if ( ( strstr($teams[$i], 'Unassigned') ) && ( $teams[$i-3] > 0 ) )
        {
		$score += $teams[$i-3];
 #               $tscmembers[count($tscmembers)] = new Member($teams[$i-4], $teams[$i-3]);
        }
}

#echo count($tscteams) . ' ' . count($teams) . "\n";
#$teams[$score] = 'Unassigned';
$tscteams['Unassigned'] = $score;

arsort($tscteams, SORT_NUMERIC);
#echo count($tscteams) . ' ' . count($teams);
foreach($tscteams as $tName => $tScore)
{
	$teamList[] = new Member($tName, $tScore);
}

addStatsrun($teamList, 'tsc_teamoffset');
?>
