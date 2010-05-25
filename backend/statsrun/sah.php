#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('sah');

dailyOffset('memberoffset', 'sah');
dailyOffset('teamoffset', 'sah');
dailyOffset('subteamoffset', 'sah');
dailyOffset('individualoffset', 'sah');

$tempdir = '/home/rkuipers/stats/statsrun/files/';

$url = 'http://setiathome.berkeley.edu/stats/team.gz';
system('wget -q '. $url . ' -O ' . $tempdir . '/sah.team.gz');

if ( filesize($tempdir . '/sah.team.gz') > 0 )
{
	$action = 'gunzip ' . $tempdir . '/sah.team.gz';
	system($action);
	$action = 'cat ' . $tempdir . '/sah.team | grep -v -e create_time -e description -e country -e expavg_credit -e expavg_time -e type -e nusers -e founder_name -e url -e name_html -e userid -e \<id\> > ' . $tempdir . '/sah.team.2';
	system($action);
	unlink($tempdir . '/sah.team');

	$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/sah.team.2');
	$team = array();
	foreach($xmldata->team as $xmlteam)
	{
	#	echo  $xmlteam->name . ' ' . $xmlteam->total_credit . "\n";
		$name = '' . $xmlteam->name;
		$score = 0 + $xmlteam->total_credit;
	
		$team[$name] = $score;
	}
	#unlink('/home/rkuipers/stats/statsrun/files/sah.team');
	arsort($team, SORT_NUMERIC);
	
	foreach($team as $name => $score)
		$teamlist[] = new Member($name, $score);
	
	updateStats($team, 'sah_teamoffset');
	unlink($tempdir . '/sah.team.2');
}

$url = 'http://setiathome.berkeley.edu/stats/user.gz';

system('wget -q -O ' . $tempdir . '/sah.user.gz ' . $url);

$action = 'gunzip ' . $tempdir . '/sah.user.gz';
system($action);

$action = 'cat ' . $tempdir . '/sah.user | grep -v -e country -e \<id\> -e expavg_time -e expavg_credit -e cpid -e create_time > ' . $tempdir . '/sah.user.2';
system($action);

unlink($tempdir . '/sah.user');

unset($xmldata);
$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/sah.user.2');

unlink('/home/rkuipers/stats/statsrun/files/sah.user.2');

$member = array();
$subteamlist = array();
$subteamMembers = array();
foreach($xmldata->user as $xmluser)
{
	$user = '' . $xmluser->name;
	$score = 0 + $xmluser->total_credit;
	$team = 0 + $xmluser->teamid;

	if ( $team == 30206 )
	{
		$tildepos = strpos($user, '~');
		if ( $tildepos > 0 )
		{
			$teamName = substr($user, 0, $tildepos);
			$user = substr($user, ( $tildepos + 1 ));
			if ( ! isset($subteamlist[$teamName]) )
				$subteamlist[$teamName] = $score;
			else
				$subteamlist[$teamName] += $score;

			if ( ! isset($subteamMembers[$teamName]) )
				$subteamMembers[$teamName] = array();

			$subteamMembers[$teamName][$user] = $score;
		}
		else
		{
			if ( isset($member[$user]) )
				$member[$user] += $score;
			else
				$member[$user] = $score;
		}
	}
}

unset($xmldata);
foreach ( $subteamlist as $name => $score )
{
        $member[$name] = $score;
	#echo $name . "\n";
}

arsort($member, SORT_NUMERIC);

foreach($member as $name => $score)
	$memberlist[] = new Member($name, $score);

updateStats($member, 'sah_memberoffset');

foreach ( $subteamMembers as $subTeamName => $member )
{
        arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
        {
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'sah_subteamoffset');

individualStatsrun('sah');

$db->disconnect();
?>
