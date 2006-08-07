#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

dailyoffset('subteamoffset', 'tsc');

$datum = getCurrentDate('tsc');

$query = 'SELECT
		prefix,
		username,
		password
	FROM
		sengent_subteam
	ORDER BY
		prefix,
		username';

$result = $db->selectQuery($query);

error_reporting(E_ALL);

$tempdir = '/home/rkuipers/stats/statsrun/files/';

$userdata = array();
while ( $line = $db->fetchArray($result) )
{
	$file = fopen($tempdir . 'subteampage.txt', 'w');

	$ch = curl_init();
	
	switch($line['prefix'])
	{
	case 'tsc':
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 	"memberName=" . $line['username'] . 
							"&password=" . $line['password']);
		curl_setopt($ch, CURLOPT_URL, "http://d2ol.childhooddiseases.org/memberServices/myNodes.jsp");
		curl_setopt($ch, CURLOPT_FILE, $file);

		$data = curl_exec($ch);
		fclose($file);

		system('cat ' . $tempdir . '/subteampage.txt | grep nodePopUp -A 1 | grep -v -- -- > ' . $tempdir . 'subteampage2.txt');

		$data = file_get_contents($tempdir . 'subteampage2.txt');
		unlink($tempdir . 'subteampage2.txt');
		$info = preg_replace(array('@</a>@si', '@<a[^>]*?>@si', '@<td[^>]*?>@si', '@</td>@si'), '||', $data);

		$nodes = explode('||', $info);
		for($i=2;$i<count($nodes);$i+=6)
		{
#			echo $i . ' ' . $nodes[$i] . ' ' . $nodes[$i+3] . "\n";
			$userdata[$line['prefix']][$line['username']][$nodes[$i]] = number_format($nodes[$i+3], 0, '', '');
		}
		break;
	case 'd2ol':break;
	case 'ud':break;
	}

	unlink($tempdir . 'subteampage.txt');
}

$subteams = array();
foreach($userdata as $project => $subteam)
{
	foreach($subteam as $username => $members)
	{
		arsort($members, SORT_NUMERIC);
		foreach($members as $membername => $memberscore)
		{
			$subteams[$project][] = new TeamMember($membername, $memberscore, $username);
		}
	}
}

#call the addSubteamStatsRun() function
foreach($subteams as $project => $members)
{
	addSubteamStatsrun($members, $project . '_subteamoffset');
}

?>
