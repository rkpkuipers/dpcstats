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

#	$member[$line['name']] = $line['total'];
#	$stMem[$line['name']] = ( $line['total'] - $line['offset'] );
}

$teamscore;
$memberList;
foreach($team as $teamname => $members)
{
	#echo $teamname . ' ' . count($members) . "\n";
	arsort($members, SORT_NUMERIC);
#	print_r($members);
	foreach($members as $membername => $memberscore)
	{
	#	echo $membername . ' offset ' . $memberoffset . ' total ' . $member[$membername] . ' total - offset ' . ( $member[$membername] - $memberoffset ) . "\n";
		$teamscore[$teamname] += $memberscore;#($member[$membername]-$memberoffset);
		$memberList[] = new TeamMember($membername, $memberscore /*($member[$membername] - $memberoffset)*/, $teamname);
#		echo $membername . ' ' . $stMem[$membername] . ' ' . ($member[$membername]-$memberoffset) . "\n";
	}
}

$teamList;
foreach($teamscore as $tName => $tScore)
	$teamList[] = new Member($tName, $tScore);

$datum = getCurrentDate('sp5');

#print_r($memberList);
addStatsrun($teamList, 'sp5_memberOffset');
addSubTeamStatsRun($memberList, 'sp5_subteamOffset');

$db->disconnect();
?>
