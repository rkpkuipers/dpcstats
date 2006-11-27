#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

# Gather data from sengent

$datum = getCurrentDate('d2ol');

dailyOffset('memberoffset', 'dp1');

$html = implode('', file ('http://app.d2ol.com/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
#$teams = explode("|\n", $html);
$teams = split("[\n|]", $html);
$rang = 1;

$d2olmembers = array();

for($i=10;$i<count($teams);$i+=6)
{
	if ( $teams[$i+4] == 'Dutch Power Cows' )
		$d2olmembers[count($d2olmembers)] = new Member($teams[$i], $teams[$i+1]);
}

addStatsrun($d2olmembers, 'dp1_memberoffset');
?>
