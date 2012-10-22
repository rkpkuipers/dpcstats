#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

#system('killall -q fah-teams.php');

$datum = getCurrentDate('fah');

dailyOffset('teamoffset', 'fah');

# Download the stats file, unzip, skip the first two lines, and load into a buffer
exec("wget \"http://fah-web.stanford.edu/daily_team_summary.txt.bz2\" -O - | bunzip2 -c | tail -n +2", $html);

# Variable to store the teams
$teams = array();

# Loop through the data
foreach($html as $rawteam) {
	# Split the data into fields
	$data = explode("\t", $rawteam);

	# Fetch the name
	$name = $data[1];

	# Strip slahes
	$name = str_replace('"', '\"', $name);
	$name = str_replace('/', '\/', $name);
	$name = str_replace('\'', '\\\'', $name);
	
	# Fetch the score
	$score = $data[2];

	if ( ( $score > 0 ) && ( $name != 'Google' ) )
	{
		$teams[] = new Member($name, $score);
		$teamlist[$name] = $score;
	}
}

updateStats($teamlist, 'fah_teamoffset');

$db->disconnect();
?>
