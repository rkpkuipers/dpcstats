#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

# Gather data from sengent
$datum = getCurrentDate('tsc');

dailyOffset('memberoffset', 'tsc');
dailyOffset('subteamoffset', 'tsc');

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
#$teams = explode("|\n", $html);
$teams = split("[\n|]", $html);
$rang = 1;

$tscmembers = array();

for($i=10;$i<count($teams);$i+=6)
{
	if ( ( $teams[$i+4] == 'Dutch Power Cows' ) && ( $teams[$i+1] > 0 ) )
		$tscmembers[count($tscmembers)] = new Member($teams[$i], $teams[$i+1]);
}

addStatsrun($tscmembers, 'tsc_memberoffset');
?>
