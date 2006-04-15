#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

$datum = getCurrentDate('tsc');

dailyOffset('teamoffset', 'smp');
dailyOffset('memberoffset', 'smp');
dailyOffset('subteamoffset', 'smp');
dailyOffset('individualoffset', 'smp');

$url = 'http://boinc.bio.wzw.tum.de/boincsimap/stats/team_id.gz';
$basedir = '/home/rkuipers/stats/statsrun/files/';

#$xmldata = simplexml_load_file(rawurlencode($url));

system('wget -q '. $url . ' -O ' . $basedir . '/smp.team.gz');

$action = 'gunzip ' . $basedir . '/smp.team.gz';

system($action);

$xmldata = simplexml_load_file($basedir . '/smp.team');

$team = array();
foreach($xmldata->team as $xmlteam)
{
	#echo  $xmlteam->name . ' ' . $xmlteam->total_credit . "\n";
	$name = '' . $xmlteam->name;
	$score = 0 + $xmlteam->total_credit;
	$team[$name] = $score;
}
unlink($basedir . '/smp.team');

arsort($team, SORT_NUMERIC);

foreach($team as $name => $score)
	$teamlist[] = new Member($name, $score);

addStatsrun($teamlist, 'smp_teamoffset');

$url = 'http://boinc.bio.wzw.tum.de/boincsimap/stats/user_id.gz';

system('wget -q -O ' . $basedir . '/smp.user.gz ' . $url);

$action = 'gunzip ' . $basedir . '/smp.user.gz';

system($action);

$xmldata = simplexml_load_file($basedir . '/smp.user');

unlink($basedir . '/smp.user');

$member = array();
$subteamlist = array();
$subteamMembers = array();
foreach($xmldata->user as $xmluser)
{
	$user = '' . $xmluser->name;
	$score = 0 + $xmluser->total_credit;
	$team = 0 + $xmluser->teamid;

	if ( $team == 119 )
	{
		$tildepos = strpos($user, '~');
		if ( $tildepos > 0 )
		{
			$teamName = substr($user, 0, $tildepos);
			$user = substr($user, ( $tildepos + 1 ));

			$set = 0;
			foreach($subteamlist as $tName => $tScore)
			{
				#echo $tName . ' ' . $teamName . "\n";
				if ( strtoupper($tName) == strtoupper($teamName) )
				{
					#echo $teamName . ' ' . $tName . ' - ' . $user . "\n";
					$subteamlist[$tName] += $score;
					$subteamMembers[$tName][$user] = $score;
					$set = 1;
				}
			}

			if ( $set == 0 )
			{
				if ( isset($member[$teamName]) )
				{
					$subteamlist[$teamName] = $member[$teamName];
					$subteamMembers[$teamName][$teamName] = $member[$teamName];
				}
					
				if ( ! isset($subteamlist[$teamName]) )
					$subteamlist[$teamName] = $score;
				else
					$subteamlist[$teamName] += $score;

				if ( ! isset($subteamMembers[$teamName]) )
					$subteamMembers[$teamName] = array();

				$subteamMembers[$teamName][$user] = $score;
			}
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

addStatsrun($memberlist, 'smp_memberoffset');

foreach ( $subteamMembers as $subTeamName => $member )
{
        arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
        {
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

if ( count($subteammembers) > 0 )
	addSubTeamStatsRun($subteammembers, 'smp_subteamoffset');
?>
