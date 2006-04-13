#!/usr/bin/php
<?php

include ('/var/www/tstats/classes/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

function getSubteam($name)
{
	global $db;
        $team = '';

        $query = 'SELECT
                        name
                FROM
                        sob_subteam
                WHERE
                        member = \'' . $name . '\'';
        $result = $db->selectQuery($query);

        if ( $line = $db->fetchArray($result) )
        {
                $team = $line['name'];
        }

        return $team;
}

# Gather data from sengent

$datum = getCurrentDate('sob');

dailyOffset('memberOffset', 'sob');
dailyOffset('teamOffset', 'sob');
dailyOffset('subteamOffset', 'sob');
dailyOffset('individualOffset', 'sob');

$html = implode('', file ('http://www.seventeenorbust.com/stats/textStats.mhtml')) or die("Error retrieving information");
$lines = explode("\n", $html);

$members = array();
$teams = array();

for($line=2;$line<count($lines);$line++)
{
	$info = explode(' ', $lines[$line]);
	$type = $info[0];
	$name = $info[2];
	$score = number_format(round($info[8]/1000000), 0, '', '');

	if ( $type == 'User' )
		$teamID = $info[4];
	
#	echo $type . ': ' . $name . ' ' . $score . "\n";
	if ( ( $type == 'Team' ) && ( $score > 0 ) )
		$teams[$name] = $score;
	
	if ( ( $type == 'User' ) && ( $score > 0 ) && ( $teamID == 34 ) )
	{
                $teamName = getSubteam($name);
                if ( $teamName != '' )
                {
                        if ( ! isset($subteams[$teamName]) )
                                $subteams[$teamName] = $score;
                        else
                                $subteams[$teamName] += $score;

                        if ( ! isset($subTeamArray[$teamName]) )
                                $subTeamArray[$teamName] = array();

                        $subTeamArray[$teamName][$name] = $score;
                        #echo $teamName . ' ' . $teams[$i] . ' ' . $teams[$i+5] . "\n";
                }
                else
                {
			$members[$name] = $score;
                }
	}

}
#echo count($members) . ' ' . count($teams) . "\n";

arsort($teams, SORT_NUMERIC);

$teamList = array();
foreach($teams as $team => $score)
	$teamList[] = new Member($team, $score);

foreach ( $subteams as $name => $score )
{
        $members[$name] = $score;
}

arsort($members, SORT_NUMERIC);

$memberList = array();
foreach($members as $member => $score)
{
	$memberList[] = new Member($member, $score);
}

#for($i=0;$i<25;$i++)
#	echo $teamList[$i]->getNaam() . ' ' . $teamList[$i]->getCandidates() . "\n";

#die();

addStatsrun($memberList, 'sob_memberOffset');
addStatsrun($teamList, 'sob_teamOffset');

foreach ( $subTeamArray as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
	{
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}


addSubTeamStatsRun($subteammembers, 'sob_subteamOffset');
?>
