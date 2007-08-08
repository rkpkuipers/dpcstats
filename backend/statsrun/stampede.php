#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$datum = getCurrentDate('sp6');

dailyOffset('memberoffset', 'sp6');
dailyOffset('subteamoffset', 'sp6');
dailyOffset('individualoffset', 'sp6');

$query = 'SELECT
		p.name,
		p.stampedeTeam,
		p.offset,
		(o.cands+o.daily)AS total
	FROM
		stampede6participants p
	LEFT JOIN
		fah_individualoffset o
	ON	o.dag = \'' . date("Y-m-d") . '\'
	AND	p.name = REPLACE(o.naam, \' - \', \'~\')
	ORDER BY
		stampedeTeam,
		name';

$result = $db->selectQuery($query);

$team;
while ( $line = $db->fetchArray($result) )
{
	if ( ! isset($team[$line['stampedeTeam']]) )
		$team[$line['stampedeTeam']];
	
	$team[$line['stampedeTeam']][$line['name']] = ( $line['total'] - $line['offset'] );
}

$teamscore;
$memberList;
foreach($team as $teamname => $members)
{
	arsort($members, SORT_NUMERIC);
	foreach($members as $membername => $memberscore)
	{
		$teamscore[$teamname] += $memberscore;#($member[$membername]-$memberoffset);
		$memberList[] = new TeamMember($membername, $memberscore /*($member[$membername] - $memberoffset)*/, $teamname);
	}
}

$teamList;
arsort($teamscore);
foreach($teamscore as $tName => $tScore)
	$teamList[] = new Member($tName, $tScore);

updateStats($teamscore, 'sp6_memberoffset');
addSubTeamStatsRun($memberList, 'sp6_subteamoffset');
individualStatsrun('sp6');
?>
