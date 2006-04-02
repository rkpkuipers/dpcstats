#!/usr/bin/php
<?php

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

# Gather data from sengent

$datum = getCurrentDate('d2ol');

dailyOffset('teamOffset', 'dp1');

$temp = '/home/rkuipers/stats/statsrun/files/';

system('wget http://app.d2ol.com/stats/teams.100.htm -O ' . $temp . 'dp1-teams.html -q');
system('cat ' . $temp . 'dp1-teams.html | grep -e TD\ align=left\ width=180 -e TD\ align=middle\ width=65 > ' . $temp . 'dp1-teams.txt');
unlink($temp . 'dp1-teams.html');

$raw = implode('', file($temp . 'dp1-teams.txt'));

$data = preg_replace(array('@<TD[^>]*?>@si', '@</TD>@si'), '||', $raw);

#unlink($tempDir . '/fah-members.txt');

$info = explode('||', $data);

$team = array();
for($i=5;$i<count($info);$i+=6)
	$team[$info[$i]] = trim($info[$i+2]);

$set = 0;

$html = implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) or die("Error retrieving information");
$teams = explode('|', $html);

$d2olteams = array();

for($i=6;$i<count($teams);$i+=5)
{
	if ( isset($team[$teams[$i]]) )
	{
#		$d2olteams[] = new Member($teams[$i], ($team[$teams[$i]]+$teams[$i+1]));
		$team[$teams[$i]] += $teams[$i+1];
		$set++;
	}

	if ( $set == 100 ) break;
}
arsort($team);

foreach($team as $name => $score)
	$d2olteams[] = new Member($name, $score);

addStatsrun($d2olteams, 'dp1_teamOffset');
?>
