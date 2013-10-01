#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('ud');

dailyOffset('teamoffset', 'ud');
$tempdir = '/home/rkuipers/stats/statsrun/files/';

for($i=1;$i<=20;$i++)
{
	if ( is_file($tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT)) )
		unlink($tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT));

	system('wget --quiet --tries 5 -O ' . $tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
}

for($ctrl=0;$ctrl<5;$ctrl++)
{
        for($i=1;$i<=15;$i++)
	{
		if ( file_exists($tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) ) )
		{
			if ( filesize($tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT)) < 70000 )
				system('wget --quiet --tries 5 -O ' . $tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
		}
		else
		{
			system('wget --quiet --tries 5 -O ' . $tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
		}
	}
}

system('cat ' . $tempdir . '/udt-* | grep -e \<td\ align=\"right\"\> -e \/services\/teams\/team\.htm | grep -v -e nowrap -e generated > ' . $tempdir . '/ud-teams');

function getMembersFromPage($file)
{
	$teams = array();
	
	$raw = implode('', file($file));

	$data = preg_replace(array('@<b>@si', '@</b>@si', '@<a[^>]*?>@si', '@</a>@si', '@<td[^>]*?>@si', '@</td>@si'), "||", $raw);
	$info = explode('||', $data);
	$info = str_replace('&nbsp;', ' ', $info);

	for($i=5;$i<count($info);$i+=10)
	{
		if ( $info[$i] != '' )
		{
			$teams[trim(html_entity_decode($info[$i]))] = str_replace(',', '', $info[$i+4]);
		#	echo html_entity_decode($info[$i]) . "\n";;
		}
	}

	return $teams;
}

$teams = getMembersFromPage($tempdir . '/ud-teams');

arsort($teams, SORT_NUMERIC);

$teamList = array();
foreach($teams as $team => $score)
{
	#echo $team . ' ' . $score . "\n";
	$teamList[] = new Member(str_replace(chr(160), ' ', $team), $score);
}

updateStats($teams, 'ud_teamoffset');

for($i=1;$i<=20;$i++)
{
	unlink($tempdir . '/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT));
}
unlink($tempdir . '/ud-teams');
?>
