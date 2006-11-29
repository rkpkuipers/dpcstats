#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('d2ol');

dailyOffset('teamoffset', 'd2ol');

$html = implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$d2olteams = array();

for($i=6;$i<count($teams);$i+=5)
{
	if ( ( $teams[$i+1] > 0 ) && (  ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 10000 ) ) ) )
		$d2olteams[$teams[$i]] = $teams[$i+1];
}

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$score = 0;
for($i=10;$i<count($teams);$i+=5)
{
        if ( ( strstr($teams[$i], 'Unassigned') ) && ( $teams[$i-3] > 0 ) )
        {
		$score += $teams[$i-3];
        }
}
$d2olteams['Unassigned'] = $score;

arsort($d2olteams);

updateStats($d2olteams, 'd2ol_teamoffset');
?>
