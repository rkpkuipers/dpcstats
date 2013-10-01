#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$tempDir = '/home/rkuipers/stats/statsrun/files/';

$datum = getCurrentDate('ud');

dailyOffset('memberoffset', 'ud');

for($i=1;$i<=15;$i++)
{
	if ( is_file($tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT)) )
		unlink($tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT));

	system('wget --quiet --tries 5 -O ' . $tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/services/teams/team_members.htm?id=F715D0C5-C270-4F6F-86D8-11A9D85EF01E&ord=POINTS&rsps=250&rscp=' . $i . '"');
}

for($ctrl=0;$ctrl<5;$ctrl++)
{
	for($i=1;$i<=15;$i++)
	{
		if ( file_exists($tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT) ) )
		{
			if ( filesize($tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT)) < 70000 )
				system('wget --quiet --tries 5 -O ' . $tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/services/teams/team_members.htm?id=F715D0C5-C270-4F6F-86D8-11A9D85EF01E&ord=POINTS&rsps=250&rscp=' . $i . '"');
		}
		else
		{
			system('wget --quiet --tries 5 -O ' . $tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/services/teams/team_members.htm?id=F715D0C5-C270-4F6F-86D8-11A9D85EF01E&ord=POINTS&rsps=250&rscp=' . $i . '"');
		}
	}
}

system('cat ' . $tempDir . '/udm-* | grep -e \<td\ align=\"right\"\>[0-9] -e \/members\/compare\.htm | grep -v -e nowrap -e generated -e bnr_teams\.gif > ' . $tempDir . '/ud-members');

function getMembersFromPage($file)
{
	$teams = array();
	
	$raw = implode('', file($file));

	$data = preg_replace(array('@<b>@si', '@</b>@si', '@<a[^>]*?>@si', '@</a>@si', '@<td[^>]*?>@si', '@</td>@si'), '||', $raw);

	$data = str_replace('&nbsp;', ' ', $data);
	$info = explode('||', $data);

	for($i=3;$i<count($info);$i+=12)
	{
		if ( $info[$i+6] == 0 )
			break;

		if ( $info[$i] != '' )
		{
			$naam = html_entity_decode($info[$i]);
			$naam = str_replace('\\', '\\\\', $naam);
			$naam = str_replace('/', '\/', $naam);
			$teams[trim($naam)] = str_replace(',', '', $info[$i+6]);
		}
	}

	return $teams;
}

$teams = getMembersFromPage($tempDir . '/ud-members');

arsort($teams, SORT_NUMERIC);

$teamList = array();

updateStats($teams, 'ud_memberoffset');

for($i=1;$i<=15;$i++)
{
        unlink($tempDir . '/udm-' . str_pad($i, 2, 0, STR_PAD_LEFT));
}
unlink($tempDir . '/ud-members');
?>
