#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

function getSubteam($name)
{
	$team = '';
	
	$query = 'SELECT
			name
		FROM
			fad_subteam
		WHERE
			member = \'' . $name . '\'';
	$result = mysql_query($query);

	if ( $line = mysql_fetch_array($result) )
	{
		$team = $line['name'];
	}
	
	return $team;
}

$datum = getCurrentDate('tsc');

$html = implode('', file ('http://stats.find-a-drug.com/stats1.php?Page=1&Team=41&Period=1&Order=Points&Size=5000')) or die("Error retrieving information");
$content = ereg_replace("<[^>]*>", "|", $html);
$teams = explode('|', $content);

$fadmembers = array();

$offset = 0;
for($i=0;$i<count($teams);$i++)
{
	if ( $teams[$i] == 'Explain' )
	{
		$offset = $i;
		break;
	}
}
if ( $offset == 0 )
	die('Error determining offset for fad-members');

#echo $offset;
#die();
$subTeamArray = array();

for($i=($offset+11);$i<count($teams);$i+=23)
{
	if ( $teams[$i] == '' ) break;
	if ( $teams[$i+11] > 0 )
	{
		$teamName = getSubteam($teams[$i]);
		if ( $teamName != '' )
		{
			if ( ! isset($subteams[$teamName]) )
				$subteams[$teamName] = $teams[$i+5];
			else
				$subteams[$teamName] += $teams[$i+5];

			if ( ! isset($subTeamArray[$teamName]) )
				$subTeamArray[$teamName] = array();

			$subTeamArray[$teamName][$teams[$i]] = $teams[$i+5];
				#echo $teamName . ' ' . $teams[$i] . ' ' . $teams[$i+5] . "\n";
		}
		else
		{
			$members[$teams[$i]] = $teams[$i+5];
		}
	}
}
foreach ( $subteams as $name => $score )
{
	$members[$name] = $score;
}

arsort($members, SORT_NUMERIC);
foreach($members as $name => $score)
{
	# De html_entity_decode is nodig omdat de teams van een webpagina worden gehaald
	# ' & en dergelijke karakters zijn gecodeerd
	$fadmembers[] = new Member(html_entity_decode($name), $score);
}

addStatsrun($fadmembers, 'fad_memberoffset');


foreach ( $subTeamArray as $subTeamName => $member )
{
	arsort($member, SORT_NUMERIC);
	foreach ( $member as $memberName => $memberScore )
	{
		$fadsubteammembers[] = new TeamMember($memberName, $memberScore, $subTeamName);
	}
}

addSubTeamStatsRun($fadsubteammembers, 'fad_subteamoffset');
?>
