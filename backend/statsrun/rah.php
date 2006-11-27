#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('rah');

dailyOffset('memberoffset', 'rah');
dailyOffset('teamoffset', 'rah');
dailyOffset('subteamoffset', 'rah');
dailyOffset('individualoffset', 'rah');

$url = 'http://boinc.bakerlab.org/rosetta/stats/team.gz';

#$xmldata = simplexml_load_file(rawurlencode($url));

system('wget -q '. $url . ' -O /home/rkuipers/stats/statsrun/files/rah.team.gz');

$action = 'gunzip /home/rkuipers/stats/statsrun/files/rah.team.gz';

system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/rah.team');

$team = array();
foreach($xmldata->team as $xmlteam)
{
	#echo  $xmlteam->name . ' ' . $xmlteam->total_credit . "\n";
	$name = '' . $xmlteam->name;
	$score = 0 + $xmlteam->total_credit;
	$team[$name] = $score;
}
unlink('/home/rkuipers/stats/statsrun/files/rah.team');

arsort($team, SORT_NUMERIC);

foreach($team as $name => $score)
	$teamlist[] = new Member($name, $score);

addStatsrun($teamlist, 'rah_teamoffset');

$url = 'http://boinc.bakerlab.org/rosetta/stats/user.gz';

system('wget -q -O /home/rkuipers/stats/statsrun/files/rah.user.gz ' . $url);

$action = 'gunzip /home/rkuipers/stats/statsrun/files/rah.user.gz';

system($action);

$xmldata = simplexml_load_file('/home/rkuipers/stats/statsrun/files/rah.user');

unlink('/home/rkuipers/stats/statsrun/files/rah.user');

$member = array();
$subteamlist = array();
$subteamMembers = array();
foreach($xmldata->user as $xmluser)
{
	$user = '' . $xmluser->name;
	$score = 0 + $xmluser->total_credit;
	$team = 0 + $xmluser->teamid;

	if ( $team == 78 )
	{
		$tildepos = strpos($user, '~');
		if ( $tildepos > 0 )
		{
			$teamName = substr($user, 0, $tildepos);
			$user = substr($user, ( $tildepos + 1 ));
	#		echo $user . ' ' . $teamName . ' ' . $score . "\n";

			$set = 0;
			foreach($subteamlist as $tName => $tScore)
			{
	#			echo $tName . ' ' . $teamName . "\n";
				if ( strtoupper($tName) == strtoupper($teamName) )
				{
	#				echo 't ' . $teamName . ' ' . $tName . ' - ' . $user . "\n";
					$subteamlist[$tName] += $score;
					if ( isset($subteamMembers[$tName][$user]) )
						$subteamMembers[$tName][$user] += $score;
					else
						$subteamMembers[$tName][$user] = $score;
					$set = 1;
				}
			}

			if ( $set == 0 )
			{
	#			echo $teamName . ' ' . $user . ' ' . $score . "\n";
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

				if ( isset($subteamMembers[$teamName][$user]) )
				{
					$subteamMembers[$teamName][$user] += $score;
					echo $teamName . ' ' . $user . "\n";
				}
				else
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

addStatsrun($memberlist, 'rah_memberoffset');

foreach ( $subteamMembers as $subTeamName => $member )
{
        arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
        {
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'rah_subteamoffset');
?>
