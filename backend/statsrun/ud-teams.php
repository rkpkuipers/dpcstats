#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

$datum = getCurrentDate('ud');

dailyOffset('teamOffset', 'ud');

for($i=1;$i<=20;$i++)
{
	unlink('/home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT));
	system('wget --quiet --tries 5 -O /home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
}

for($ctrl=0;$ctrl<5;$ctrl++)
{
        for($i=1;$i<=15;$i++)
	{
		if ( file_exists('/home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) ) )
		{
			if ( filesize('/home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT)) < 70000 )
				system('wget --quiet --tries 5 -O /home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
		}
		else
		{
			system('wget --quiet --tries 5 -O /home/rkuipers/stats/statsrun/ud/udt-' . str_pad($i, 2, 0, STR_PAD_LEFT) . ' "http://www.grid.org/stats/teams/points.htm?rsps=250&rscp=' . $i . '"');
		}
	}
}

system('cat /home/rkuipers/stats/statsrun/ud/udt-* | grep -e \<td\ align=\"right\"\> -e \/services\/teams\/team\.htm | grep -v -e nowrap -e generated > /home/rkuipers/stats/statsrun/ud/teams');

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

$teams = getMembersFromPage('/home/rkuipers/stats/statsrun/ud/teams');

arsort($teams, SORT_NUMERIC);

$teamList = array();
foreach($teams as $team => $score)
{
	#echo $team . ' ' . $score . "\n";
	$teamList[] = new Member(str_replace(chr(160), ' ', $team), $score);
}

#echo count($teamList);
addStatsrun($teamList, 'ud_teamOffset');
?>
