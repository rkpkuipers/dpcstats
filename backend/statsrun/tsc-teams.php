#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'tsc');

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$tscteams = array();

for($i=6;$i<count($teams);$i+=5)
{
	if ( ( $teams[$i+1] > 0 ) && ( ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 10000 ) ) ) )
	{
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
        }
}

$tscteams['Unassigned'] = $score;

arsort($tscteams, SORT_NUMERIC);

updateStats($tscteams, 'tsc_teamoffset');

?>
