#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

# Gather data from sengent

$datum = getCurrentDate('d2ol');

dailyOffset('teamoffset', 'd2ol');

$html = implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$d2olteams = array();

for($i=6;$i<count($teams);$i+=5)
{
	if ( ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 1000000 ) ) )
		$d2olteams[count($d2olteams)] = new Member($teams[$i], $teams[$i+1]);
}

#$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
#$teams = explode('|', $html);
#$rang = 1;

$score = 0;

for($i=10;$i<count($teams);$i+=5)
{
        if ( ( strstr($teams[$i], 'Unassigned') ) && ( $teams[$i-3] > 0 ) )
        {
		$score += $teams[$i-3];
 #               $d2olmembers[count($d2olmembers)] = new Member($teams[$i-4], $teams[$i-3]);
        }
}

$d2olteams[count($d2olteams)] = new Member('Unassigned', $score);

#echo count($d2olteams);

addStatsrun($d2olteams, 'd2ol_teamoffset');
?>
