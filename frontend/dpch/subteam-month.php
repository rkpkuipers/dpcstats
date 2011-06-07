<?php

include ('../classes.php');

# Retrieve the project
if ( ( isset($_REQUEST['project']) ) && ( preg_match('/^[a-z0-9]+$/', $_REQUEST['project']) ) )
	$prefix = $_REQUEST['project'];
else
	die("ERROR: No project specified");

if ( isset($_REQUEST['datum']) )
	$datum = $_REQUEST['datum'];
else
	$datum = getPrevDate();

$prevMonth = date("Y-m", strtotime(date("Y-m-d", strtotime("-1 month"))));

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];
else
	die('No subteam name given');

$query = 'SELECT 
		MAX(dag) 
	FROM 
		' . $prefix . '_subteamoffset 
	WHERE 
		dag LIKE \'' . $prevMonth . '%\'
	AND	subteam = \'' . $team . '\'';
	
$result = $db->selectQuery($query);

if ( $line = $db->fetchArray($result) )
	$maand = $line['0'];
else
	die('Error determining month');

$project = new Project($db, $prefix, 'subteamoffset', $maand, $team);

# Set locale to provide dutch names for days, months and such
setLocale(LC_ALL, 'nl_NL');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title><?php echo $project->getDPCHTitle();?> DPCH</title>
<link rel="stylesheet" type="text/css" href="http://gathering.tweakers.net/global/templates/tweakers/css/nightlife.css?v=193h" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php

$ts = new STTableStatisticsMonthly($project->getPrefix() . '_subteamoffset', $maand, $db, $team);
$ts->gather();

$page = '[b]DPC ' . $project->getDPCHTitle() . ' maand-hitparade van ' . strftime('%B %Y', strtotime($prevMonth . '-01')) . ' voor ' . $team . '[/b][br][br]';
$page .= '[table bgcolor="transparent" width="450px"]';
$page .= '[tr][td colspan="5"][b]Monthly Top 30[/b][/td][/tr]';
$page .= '[tr][td colspan="5"][small]Flushers: ' . $ts->getDailyFlushers() . ' / ' . number_format($ts->getTotalMembers(), 0, ',', '.') . 
	' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 1, ',', '.') . ' %)[/small][/td][/tr]';
$page .= '[tr]';
$page .= '[td colspan="1"][b]pos[/b][/td]';
$page .= '[td align="right"][b]daily[/b][/td]';
$page .= '[td colspan="1"][b]member[/b][/td]';
$page .= '[td align="right"][b]total[/b][/td]';
$page .= '[td][/td]';
$page .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_subteamoffset', $datum, 0, 30, $db, $team);

$ml->generateMonthlyFlushList(date("Y-m", strtotime($maand)), $maand);
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	$page .= '[tr]';
	$page .= '[td align="right"]' . ( $pos ) . '.[/td]';

	$page .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
	$page .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=subteamoffset&amp;team=' . $team . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$page .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$page .= '[td align="right"](' . $mbs[$i]->getRank() . ')[/td]';
	$page .= '[/tr]';
}

$page .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $maand . '&amp;tabel=subteamoffset&amp;team=' . $team . '"]More...[/url][/td][/tr]';
$page .= '[/table]';
$page .= '[br]';

$page .= '[table bgcolor="transparent" width="450px"]';
$page .= '[tr][td colspan="6"][b]Overall Top 30[/b][/td][/tr]';
$page .= '[tr]';
$page .= '[td colspan="2"][b]pos[/b][/td]';
$page .= '[td align="right"][b]total[/b][/td]';
$page .= '[td][b]member[/b][/td]';
$page .= '[td align="right"][b]monthly[/b][/td]';
$page .= '[/tr]';

$ml->generateMonthlyRankList(date("Y-m", strtotime($maand)), $maand);
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	
	$page .= '[tr]';
	$page .= '[td align="right"]' . $pos . '.[/td]';

	$change = $mbs[$i]->getRank() - $pos;

	if ( $change > 0 )
		$page .= '[td]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
	elseif ( $change == 0 )
		$page .= '[td]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
	elseif ( $change < 0 )
		$page .= '[td]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';
	
	$page .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$page .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=subteamoffset&amp;datum=' . $maand . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '&amp;team=' . $team . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$page .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
	$page .= '[td align="right"]'./*(' . $mbs[$i]->getFlushRank() . ')*/'[/td]';
	$page .= '[/tr]';
}

$page .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset"]More...[/url][/td][/tr]';
$page .= '[/table]';
$page .= '[br]';

$page .= '[table width="450px"]';
$page .= '[tr][td colspan="5"][b]Teams Monthly Top 15[/b][/td][/tr]';
$page .= '[tr]';
$page .= '[td colspan="1"][b]pos[/b][/td]';
$page .= '[td align="right"][b]daily[/b][/td]';
$page .= '[td][b]team[/b][/td]';
$page .= '[td align="right"][b]total[/b][/td]';
$page .= '[td][/td]';
$page .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, 0, 15, $db);

$ml->generateMonthlyFlushList(date("Y-m", strtotime($maand)), $maand);
$mbs = $ml->getMembers();

if ( $project->getTeamDaily(1) > 15 )
{
        if ( isset($mbs[0]) )
                $tmpMember = array($mbs[0]);
        else
                $tmpMember = array();

        $ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, ( $project->getTeamDaily(1) - 8 ), 15, $db);
        $ml->generateMonthlyFlushList(date("Y-m", strtotime($maand)), $maand);
        $mbs = array_merge($tmpMember, $ml->getMembers());
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamDaily(1) > 15 ) )
		$pos = $i + ( $project->getTeamDaily(1) - 8 );
	else
                $pos = $i + 1;

        $page .= '[tr]';
        $page .= '[td align="right"]' . ( $pos ) . '.[/td]';

        $page .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
        $page .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $maand . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $page .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $page .= '[td align="right"](' . $mbs[$i]->getRank() . ')[/td]';
        $page .= '[/tr]';
}
$page .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $maand . '&amp;tabel=memberoffset"]More...[/url][/td][/tr]';
$page .= '[/table]';
$page .= '[br]';

$page .= '[table width="450px"]';
$page .= '[tr][td colspan="6"][b]Teams Overall Top 15[/b][/td][/tr]';
$page .= '[tr]';
$page .= '[td colspan="2"][b]pos[/b][/td]';
$page .= '[td align="right"][b]total[/b][/td]';
$page .= '[td][b]team[/b][/td]';
$page .= '[td align="right"][b]daily[/b][/td]';
$page .= '[td][/td]';
$page .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, 0, 15, $db);
$ml->generateMonthlyRankList(date("Y-m", strtotime($maand)), $maand);
$mbs = $ml->getMembers();

if ( $project->getTeamRank(1) > 15 )
{
        $tmpMember = array($mbs[0]);

        $ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, ( $project->getTeamRank() - 8 ), 15, $db);
        $ml->generateMonthlyRankList(date("Y-m", strtotime($maand)), $maand);
        $mbs = $ml->getMembers();

        $mbs = array_merge($tmpMember, $ml->getMembers());
        #echo $tmpMember;
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamRank(1) > 15 ) )
        	$pos = $i + ( $project->getTeamRank(1) - 8 );
        else
	        $pos = $i + 1;

        $page .= '[tr]';
        $page .= '    [td align="right"]' . $pos . '.[/td]';

        $change = $mbs[$i]->getRank() - $pos;

        if ( $change > 0 )
                $page .= '[td]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
        elseif ( $change == 0 )
                $page .= '[td]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
        elseif ( $change < 0 )
                $page .= '[td]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';

        $page .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $page .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $maand . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $page .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
        $page .= '[td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
        $page .= '[/tr]';
}

$page .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $maand . '&amp;tabel=memberoffset"]More...[/url][/td][/tr]';
$page .= '[/table]';
$page .= '[br]';

$fmc = new FlushList($project->getPrefix() . '_memberoffset', $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

$page .= '[table width="350px"]';
$page .= '[tr][td colspan="3"][b]Megaflush Top 5[/b][/td][/tr]';

for($i=0;$i<5;$i++)
{
	$page .= '[tr]';
	$page .= '[td]' . ( $i + 1 ) . '.[/td]';
	$page .= '[td][url="' . $baseUrl . '/index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($fl[$i]->getName()) . '"]' . $fl[$i]->getName() . '[/url][/td]';
	$page .= '[td]' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '[/td]';
	$page .= '[/tr]';
}
$page .= '[tr][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=Flush"]More...[/url][/td][/tr]';
$page .= '[/table]';

$mi = new MemberInfo($db, $project->getTeamName(), $project->getPrefix() . '_teamoffset', $datum, $project->getPrefix(), 'teamoffset', $project->getTeamName());

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgmonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'Dutch Power Cows\' 
		AND 	tabel = \'' . $project->getPrefix() . '_teamoffset\'';
		
	$result = $db->selectQuery($query);
	
	if ( $line = $db->fetchArray($result) )
	{
                $lineArray = array($line['avgmonthly']);
	}
	else
	{
        	$lineArray = array(0, 0);
	}

	$charArray = array('avgmonthly');
	$headArray = array('monthly');

        $t = new TOThreats($db, $project->getPrefix() . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgmonthly', $project->getTeamName());
	$tl = $t->getThreatList();;

        if ( count($tl) > 0 )
        {
		$page .= '[br]';
                $page .= '[table width="350px"]';
		$page .= '[tr][td colspan="3"][b]When do they get you[/b][/td][/tr]';
                $page .= '[tr][td align="left"][b]Name[/b][/td][td align="right"][b]Average[/b][/td][td align="right"][b]Days[/b][/td][/tr]';
                for($i=0;$i<count($tl);$i++)
                {
                        $page .= '[tr]';
                        $page .= '[td width="190px" align="left"][url="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($tl[$i]['name']) . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '"]' . $tl[$i]['name'] . '[/url][/td]';
			$page .= '[td width="50px" align="right"]' . number_format($tl[$i]['average'], 0, ',', '.') . '[/td]';
			$page .= '[td align="right" width="50px"]' . number_format($tl[$i]['days'], 0, ',', '.') . '[/td]';
                        $page .= '[/tr]';
                }
                $page .= '[/table]';
        }

        $o = new Opertunities($db, $project->getPrefix() . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgmonthly', $project->getTeamName());
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
		$page .= '[br]';
                $page .= '[table width="350px"]';
		$page .= '[tr][td colspan="3"][b]When do you get them[/b][/td][/tr]';
                $page .= '[tr][td align="left"][b]Name[/b][/td][td align="right"][b]Avg.[/b][/td][td align="right"][b]Days[/b][/td][/tr]';
                for($i=0;$i<count($ol);$i++)
                {
                        $page .= '[tr]';
                        $page .= '[td width="190px" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($ol[$i]['name']) . '&amp;datum=' . $datum . '"]' . $ol[$i]['name'] . '[/url][/td]';
			$page .= '[td width="50px" align="right"]' . number_format($ol[$i]['average'], 0, ',', '.') . '[/td]';
                        $page .= '[td align="right" width="50px"]' . number_format($ol[$i]['days'], 0, ',', '.') . '[/td]';
                        $page .= '[/tr]';
                }
                $page .= '[/table]';
        }
}
$page .= '[br]';

$page .= '[b]' . $project->getDPCHTitle() . ' Links[/b][br]';
$page .= '[url="' . $project->getWebsite() . '"]' . $project->getDPCHTitle() . ' webpage[/url][br]';
$page .= '[url="' . $project->getForum() . '"]' . $project->getDPCHTitle() . ' forum[/url][br]';
$page .= '[url="http://www.dutchpowercows.org/doc.php?id=316"]DPCH Suggestiepagina[/url][br]';
$page .= '[url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '"]Bron[/url][br]';

echo str_replace('bgcolor="transparent"', '', parseRML($page));

echo '<br /><hr />';

echo 'Voor de statsposters:<br /><br /><input type="text" style="width:700px" value="[' . $project->getDPCHTitle() . '] maand-hitparade van ' . strftime('%B', strtotime($prevMonth . '-01')) . '" />';

?>
<br /><br /><textarea style="width:700px" rows="12" cols="85"><?php echo htmlentities($page); ?></textarea>
<br /><br /><textarea style="width:700px" rows="12" cols="85"><?php echo htmlentities(parseRML($page)); ?></textarea>
<br>
</body>
</html>
