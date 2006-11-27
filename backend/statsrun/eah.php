#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'eah');
dailyOffset('memberoffset', 'eah');
dailyOffset('subteamoffset', 'eah');
dailyOffset('individualoffset', 'eah');

$url = 'http://einstein.phys.uwm.edu/stats/team_id.gz';

#$xmldata = simplexml_load_file(rawurlencode($url));

system('wget -q '. $url . ' -O /home/rkuipers/stats/statsrun/files/eah.team.gz');

$action = 'gunzip /home/rkuipers/stats/statsrun/files/eah.team.gz';

system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/eah.team');

$team = array();
foreach($xmldata->team as $xmlteam)
{
	#echo  $xmlteam->name . ' ' . $xmlteam->total_credit . "\n";
	$name = '' . $xmlteam->name;
	$score = 0 + $xmlteam->total_credit;
	$team[$name] = $score;
}
unlink('/home/rkuipers/stats/statsrun/files/eah.team');

arsort($team, SORT_NUMERIC);

foreach($team as $name => $score)
	$teamlist[] = new Member($name, $score);

addStatsrun($teamlist, 'eah_teamoffset');

$url = 'http://einstein.phys.uwm.edu/stats/user_id.gz';

system('wget -q -O /home/rkuipers/stats/statsrun/files/eah.user.gz ' . $url);

$action = 'gunzip /home/rkuipers/stats/statsrun/files/eah.user.gz';

system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/eah.user');

unlink('/home/rkuipers/stats/statsrun/files/eah.user');

$member = array();
$subteamlist = array();
$subteamMembers = array();
foreach($xmldata->user as $xmluser)
{
	$user = '' . $xmluser->name;
	$score = 0 + $xmluser->total_credit;
	$team = 0 + $xmluser->teamid;

	if ( $team == 822 )
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
			$member[$user] = $score;
	}
}

foreach ( $subteamlist as $name => $score )
{
        $member[$name] = $score;
}

arsort($member, SORT_NUMERIC);

foreach($member as $name => $score)
	$memberlist[] = new Member($name, $score);

addStatsrun($memberlist, 'eah_memberoffset');

foreach ( $subteamMembers as $subTeamName => $member )
{
        arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
        {
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'eah_subteamoffset');
?>
