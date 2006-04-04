#!/usr/bin/php
<?

include('/home/rkuipers/stats/database.php');
include('/home/rkuipers/stats/include.php');
include('/var/www/tstats/classes/members.php');

dailyOffset('memberOffset', 'sp5');
dailyOffset('subteamOffset', 'sp5');
dailyOffset('individualOffset', 'sp5');

$query = 'SELECT
		p.name,
		p.stampedeTeam,
		p.offset,
		(o.cands+o.daily)AS total
	FROM
		stampedeParticipants p,
		rah_individualOffset o
	WHERE
		o.dag = \'' . date("Y-m-d") . '\'
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

$datum = getCurrentDate('sp5');

addStatsrun($teamList, 'sp5_memberOffset');
addSubTeamStatsRun($memberList, 'sp5_subteamOffset');

$db->disconnect();
?>
