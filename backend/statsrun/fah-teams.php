#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

#system('killall -q fah-teams.php');

$datum = getCurrentDate('fah');

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
		$teamlist[$name] = $score;
	}
}

updateStats($teamlist, 'fah_teamoffset');
?>
