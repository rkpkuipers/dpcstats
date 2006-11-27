#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

$html = implode('', file ('http://stats.find-a-drug.com/teams1.php?Order=Points')) or die("Error retrieving information");
$content = ereg_replace("<[^>]*>", "|", $html);
$teams = explode('|', $content);
$rang = 1;

$fadteams = array();
#for($i=218;$i<260;$i++)
#	echo $i . ' ' . $teams[$i] . "\n";
#die();
for($i=226;$i<count($teams);$i+=29)
{
	if ( $teams[$i] == '' ) break;
	if ( $teams[$i+8] > 0 )
		$fadteams[count($fadteams)] = new Member($teams[$i], $teams[$i+8]);
	
	#echo $i . ' ' . $teams[$i] . ' ' . $teams[$i+8] . "\n";
}

addStatsrun($fadteams, 'fad_teamoffset');
?>
