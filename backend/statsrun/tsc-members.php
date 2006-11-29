#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

dailyOffset('memberoffset', 'tsc');
dailyOffset('subteamoffset', 'tsc');

$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[\n|]", $html);
$rang = 1;

$tscmembers = array();

for($i=10;$i<count($teams);$i+=6)
{
	if ( ( $teams[$i+4] == 'Dutch Power Cows' ) && ( $teams[$i+1] > 0 ) )
	{
		$tscmembers[$teams[$i]] = $teams[$i+1];
	}
}

updateStats($tscmembers, 'tsc_memberoffset');

?>
