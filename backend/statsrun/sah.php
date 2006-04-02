#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

$datum = getCurrentDate('tsc');

dailyOffset('memberOffset', 'sah');
dailyOffset('teamOffset', 'sah');
dailyOffset('subteamOffset', 'sah');
dailyOffset('individualOffset', 'sah');

$url = 'http://setiathome.berkeley.edu/stats/team.gz';
system('wget -q '. $url . ' -O /home/rkuipers/stats/statsrun/files/sah.team.gz');
$action = 'gunzip /home/rkuipers/stats/statsrun/files/sah.team.gz';
system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/sah.team');
$team = array();
foreach($xmldata->team as $xmlteam)
{
#	echo  $xmlteam->name . ' ' . $xmlteam->total_credit . "\n";
	$name = '' . $xmlteam->name;
	$score = 0 + $xmlteam->total_credit;

	$team[$name] = $score;
}
unlink('/home/rkuipers/stats/statsrun/files/sah.team');
arsort($team, SORT_NUMERIC);

foreach($team as $name => $score)
	$teamlist[] = new Member($name, $score);

addStatsrun($teamlist, 'sah_teamOffset');

$url = 'http://setiathome.berkeley.edu/stats/user.gz';

system('wget -q -O /home/rkuipers/stats/statsrun/files/sah.user.gz ' . $url);

$action = 'gunzip /home/rkuipers/stats/statsrun/files/sah.user.gz';

system($action);

unset($xmldata);
$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/sah.user');

unlink('/home/rkuipers/stats/statsrun/files/sah.user');

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
			$member[$user] = $score;
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

addStatsrun($memberlist, 'sah_memberOffset');

foreach ( $subteamMembers as $subTeamName => $member )
{
        arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
        {
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'sah_subteamOffset');
?>
