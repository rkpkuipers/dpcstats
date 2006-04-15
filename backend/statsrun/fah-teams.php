#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

# Gather data from sengent

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'fah');

$html = implode('', file ('http://vspx27.stanford.edu/daily_team_summary.txt')) or die("Error retrieving information");
$data = explode("\t", $html);

$teams = array();
for($i=10;$i<count($data);$i+=3)
{
	$name = $data[$i];
	$name = str_replace('"', '\"', $name);
	$name = str_replace('/', '\/', $name);
	$name = str_replace('\'', '\\\'', $name);
	$score = $data[$i+1];

	if ( $score > 0 )
	{
		$teams[] = new Member($name, $score);
	}
}

addStatsRun($teams, 'fah_teamoffset');
?>
