#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');

# TSC Members
$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

$db->deleteQuery('DELETE FROM additional WHERE prefix LIKE \'d2ol%\' OR prefix LIKE \'tsc%\'');

$copyData = array();
for($i=10;$i<count($teams);$i+=6)
{
	if ( strstr($teams[$i+4], 'Dutch Power Cows') )
	{
		$copyData[] = $teams[$i] . "\t" . $teams[$i+3] . "\t" . 'tsc_memberoffset' . "\n";
	}
}

# TSC Teams
$html = implode('', file('http://d2ol.childhooddiseases.org/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving teams information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	$copyData[] = $teams[$i] . "\t" . $teams[$i+4] . "\t" . 'tsc_teamoffset' . "\n";
}

# D2OL Members
$html = implode('', file ('http://app.d2ol.com/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	if ( strstr($teams[$i+4], 'Dutch Power Cows') )
	{
		$copyData[] = $teams[$i] . "\t" . $teams[$i+3] . "\t" . 'd2ol_memberoffset' . "\n";
	}
}

# D2OL Teams
$html = implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	$copyData[] = $teams[$i] . "\t" . $teams[$i+4] . "\t" . 'd2ol_teamoffset' . "\n";
}

$db->copyData(array('additional', 'naam', 'aantal', 'prefix'), $copyData);
?>
