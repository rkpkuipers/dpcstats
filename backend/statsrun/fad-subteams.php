#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

$html = implode('', file ('http://fad.qik.nl/subteamusers.php')) or die("Error retrieving information");
$teams = explode("\n", $html);

$db->selectQuery('DELETE FROM fad_subteam');

$subteam = '';
for($i=0;$i<count($teams);$i++)
{
	$teams[$i] = trim($teams[$i]);
	if ( $teams[$i] == '<subteam>' )
	{
#		echo 'Subteam: ' . substr($teams[$i+1], 14, -12) . "\n";
		$subteam = substr($teams[$i+1], 14, -12);
	}
	
	if ( substr($teams[$i], 0, 10) == '<username>' )
	{
#		echo "\t" . substr($teams[$i], 10, -11) . "\n";
		$query = 'INSERT INTO fad_subteam (name, member) VALUES ( \'' . $subteam . '\', \'' . substr($teams[$i], 10, -11) . '\' );';
		$db->insertQuery($query);
	}
#	if ( $teams[$i] == '
#	echo $i . ' ' . $teams[$i] . "\n";
}

#$teams = split("[\n|]", $html);

/*
$tscmembers = array();

for($i=10;$i<count($teams);$i+=6)
{
	if ( $teams[$i+4] == 'Dutch Power Cows' )
		$tscmembers[count($tscmembers)] = new Member($teams[$i], $teams[$i+1]);
}

addStatsrun($tscmembers, 'tsc_memberoffset');
*/
?>
