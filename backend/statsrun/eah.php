#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'eah');
dailyOffset('memberoffset', 'eah');
dailyOffset('subteamoffset', 'eah');
dailyOffset('individualoffset', 'eah');

$url = 'http://einstein.phys.uwm.edu/stats/team_id.gz';

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
	addTeam($team, $name, $score);
}
unlink('/home/rkuipers/stats/statsrun/files/eah.team');

arsort($team, SORT_NUMERIC);

updateStats($team, 'eah_teamoffset');

$url = 'http://einstein.phys.uwm.edu/stats/user_id.gz';

system('wget -q -O /home/rkuipers/stats/statsrun/files/eah.user.gz ' . $url);

$action = 'gunzip /home/rkuipers/stats/statsrun/files/eah.user.gz';

system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/eah.user');

unlink('/home/rkuipers/stats/statsrun/files/eah.user');

$member = array();
$subteamMembers = array();

foreach($xmldata->user as $xmluser)
{
	$user = '' . $xmluser->name;
	$score = 0 + $xmluser->total_credit;
	$team = 0 + $xmluser->teamid;

	if ( $team == 822 )
	{
		addMember($member, $subteamMembers, $user, $score, '~');
	}
}

fixLists($member, $subteamMembers, '~');
updateStats($member, 'eah_memberoffset');

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
