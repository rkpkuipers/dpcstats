#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');

# TSC Members
$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

mysql_query('DELETE FROM additional WHERE prefix = \'d2ol\' OR prefix = \'tsc\'');

for($i=10;$i<count($teams);$i+=6)
{
	if ( strstr($teams[$i+4], 'Dutch Power Cows') )
	{
		$query = 'INSERT INTO additional VALUES ( \'' . $teams[$i] . '\', ' . $teams[$i+3] . ', \'tsc\')';
		#echo $query . "\n";
		mysql_query($query);
	}
}

# TSC Teams
$html = implode('', file('http://d2ol.childhooddiseases.org/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving teams information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	$query = 'INSERT INTO additional VALUES ( \'' . $teams[$i] . '\', ' . $teams[$i+4] . ', \'tsc\')';
	mysql_query($query);
}

# D2OL Members
$html = implode('', file ('http://app.d2ol.com/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	if ( strstr($teams[$i+4], 'Dutch Power Cows') )
	{
		$query = 'INSERT INTO additional VALUES ( \'' . $teams[$i] . '\', ' . $teams[$i+3] . ', \'d2ol\')';
		#echo $query . "\n";
		mysql_query($query);
	}
}

# D2OL Teams
$html = implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = split("[|\n]", $html);

for($i=10;$i<count($teams);$i+=6)
{
	$query = 'INSERT INTO additional VALUES ( \'' . $teams[$i] . '\', ' . $teams[$i+4] . ', \'d2ol\')';
	#echo $query;
	mysql_query($query);
}

?>
