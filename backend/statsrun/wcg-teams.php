#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('wcg');

dailyOffset('teamoffset', 'wcg');
$tempdir = '/home/rkuipers/stats/statsrun/files/';

for($i=1;$i<=20;$i++)
{
	if ( is_file($tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT)) )
		unlink($tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT));

	system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/stat/viewStatsByTeamAT.do?sort=points&numRecordsPerPage=250&pageNum=' . $i . '"');
}

for($ctrl=0;$ctrl<5;$ctrl++)
{
        for($i=1;$i<=15;$i++)
	{
		if ( file_exists($tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT) ) )
		{
			if ( filesize($tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT)) < 70000 )
				system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/stat/viewStatsByTeamAT.do?sort=points&numRecordsPerPage=250&pageNum=' . $i . '"');
		}
		else
		{
			system('wget --quiet --tries 5 -O ' . $tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.worldcommunitygrid.org/stat/viewStatsByTeamAT.do?sort=points&numRecordsPerPage=250&pageNum=' . $i . '"');
		}
	}
}

system('cat ' . $tempdir . '/wcgt-* | grep -A 3 \<\/a\>\<\/span\>\<\/td\> | grep -v -e \<td\ \>\<span\ class=\"contentText\"\> --  > ' . $tempdir . '/wcg-teams');
system('dos2unix -q ' . $tempdir . '/wcg-teams');

function getMembersFromPage($file)
{
	$teams = array();
	
	$raw = implode('', file($file));
	$lines = file($file);

	for($i=0;$i<count($lines);$i+=4)
	{
		$data = preg_replace(array('@<b>@si', '@</b>@si', '@<a[^>]*?>@si', '@</a>@si', '@<td[^>]*?>@si', '@</td>@si', '@</span>@si'), "", $lines[$i]);
#		echo $i . ' ' . trim($data) . ' ' . str_replace(',', '', trim($lines[$i+2])) . "\n";
		$teams[trim($data)] = str_replace(',', '', trim($lines[$i+2]));
	}

	return $teams;
}

$teams = getMembersFromPage($tempdir . '/wcg-teams');

updateStats($teams, 'wcg_teamoffset');

for($i=1;$i<=20;$i++)
{
	unlink($tempdir . '/wcgt-' . str_pad($i, 2, 0, STR_PAD_LEFT));
}
unlink($tempdir . '/wcg-teams');
?>
