#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

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

$html = @implode('', file ('http://www.seventeenorbust.com/stats/textStats2.mhtml')) or die("Error retrieving information");
$lines = explode("\n", $html);

if ( count($lines) <= 1 )
	die('Error retrieving SOB stats page');

$dateinfo = explode(' ', $lines[0]);
$update = $dateinfo[1];

$datum = date("Y-m-d", $update);
unset($dateinfo, $update);

#$datum = getCurrentDate('sob');

dailyOffset('memberoffset', 'sob');
dailyOffset('teamoffset', 'sob');
dailyOffset('subteamoffset', 'sob');
dailyOffset('individualoffset', 'sob');

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
                }
                else
                {
			$members[$name] = $score;
                }
	}

}
arsort($teams, SORT_NUMERIC);

foreach ( $subteams as $name => $score )
{
        $members[$name] = $score;
}

arsort($members, SORT_NUMERIC);

updateStats($members, 'sob_memberoffset');
updateStats($teams, 'sob_teamoffset');

foreach ( $subTeamArray as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
	{
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($subteammembers, 'sob_subteamoffset');

individualStatsrun('sob');
?>
