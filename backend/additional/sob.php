#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$query = 'DELETE FROM additional where prefix LIKE \'sob%\'';
$db->deleteQuery($query);

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

$html = implode('', file ('http://www.seventeenorbust.com/stats/textStats.mhtml')) or die("Error retrieving information");
$lines = explode("\n", $html);

$members = array();
$teams = array();

for($line=2;$line<count($lines);$line++)
{
	$info = explode(' ', $lines[$line]);

	# Verify the line contained anything
	if ( count($info) < 2 )
		continue;

	$type = $info[0];
	$name = $info[2];
	$name = str_replace('\\', '\\\\', $name);
	$name = str_replace('/', '\/', $name);
	$name = str_replace('\'', '\\\'', $name);
	$tests = $info[5];

	if ( $type == 'User' )
		$teamID = $info[4];
	
#	echo $type . ': ' . $name . ' ' . $score . "\n";
	if ( ( $type == 'Team' ) && ( $tests > 0 ) )
		$teams[$name] = $tests;
	
	if ( ( $type == 'User' ) && ( $tests > 0 ) && ( $teamID == 34 ) )
	{
                $teamName = getSubteam($name);
                if ( $teamName != '' )
                {
                        if ( ! isset($subteams[$teamName]) )
                                $subteams[$teamName] = $tests;
                        else
                                $subteams[$teamName] += $tests;

                        if ( ! isset($subTeamArray[$teamName]) )
                                $subTeamArray[$teamName] = array();

                        $subTeamArray[$teamName][$name] = $tests;
			$subteamMembers[$name] = $tests;
                        #echo $teamName . ' ' . $teams[$i] . ' ' . $teams[$i+5] . "\n";
                }
                else
                {
			$members[$name] = $tests;
                }
	}

}
#echo count($members) . ' ' . count($teams) . "\n";

foreach ( $subteams as $name => $score )
{
        $members[$name] = $score;
}

foreach ( $subTeamArray as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
        foreach ( $member as $memberName => $memberScore )
	{
                $subteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

if ( $db->getType() == 'postgres' )
{
	$copyData = array();
	foreach($members as $name => $tests)
		$copyData[] = "$name\t$tests\tsob_memberoffset\n";

	foreach($teams as $name => $tests)
		$copyData[] = "$name\t$tests\tsob_teamoffset\n";

	foreach($subteamMembers as $name => $tests)
		$copyData[] = "$name\t$tests\tsob_subteamoffset\n";

	$db->copyData(array('additional', 'naam', 'aantal', 'prefix'), $copyData);
}
elseif ( $db->getType() == 'mysql' )
{
	$insData = '';

	foreach($members as $name => $tests)
		$insData .= ($insData!=''?',':'') . '("' . $name . '", ' . $tests . ', "sob_memberoffset")';
	
	foreach($teams as $name => $tests)
		$insData .= ($insData!=''?',':'') . '("' . $name . '", ' . $tests . ', "sob_teamoffset")';
	
	foreach($subteamMembers as $name => $tests)
		$insData .= ($insData!=''?',':'') . '("' . $name . '", ' . $tests . ', "sob_subteamoffset")';
	
	$db->insertQuery('INSERT INTO additional (naam, aantal, prefix) VALUES ' . $insData . ';');
}
else
	die('Unknown database type encountered');
?>
