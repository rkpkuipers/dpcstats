<?

include ('/var/www/tstats/classes.php');

if ( isset($_GET['project']) )
	$prefix = $_GET['project'];
else
	$prefix = 'tsc';

if ( isset($_GET['datum']) )
	$datum = $_GET['datum'];
else
{
	if ( $prefix == 'ud' )
		$datum = getCurrentDate($prefix);
	else
		$datum = getYesterday($prefix);
}

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];
else
	die('No subteam name given');

if ( $datum > date("Y-m-d", strtotime("-3 day")) )
	$tableSuffix = 'daily';
else
	$tableSuffix = '';

$project = new Project($db, $prefix, 'memberoffset', $datum, $team);

# Set locale to provide dutch names for days, months and such
setLocale(LC_ALL, 'nl_NL');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title><?php echo $project->getDpchTitle();?> DPCH</title>
<link rel="stylesheet" type="text/css" href="http://gathering.tweakers.net/global/templates/tweakers/css/nightlife.css?v=193h" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php

$ts = new STTableStatistics($project->getPrefix() . '_subteamoffset' . $tableSuffix, $datum, $db, $team);
$ts->gather();

$rmlpage .= '[b]DPC ' . $project->getDpchTitle() . ' hitparade voor ' . $team . ' van ' . strftime('%e %B %Y', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum))) . '[/b]' . "\n";
$rmlpage .= '[table]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Daily Top 30[/b][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"]Flushers: ' . $ts->getDailyFlushers() . ' / ' . number_format($ts->getTotalMembers(), 0, ',', '.') . ' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 1, ',', '.') . ' %)[br][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]member[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_subteamoffset' . $tableSuffix, $datum, 0, 30, $db, $team);

$ml->generateFlushList();
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . ( $pos ) . '.[/td]';

	$change = $mbs[$i]->getYesterday() - ( $pos );

	if ( $change > 0 )
	{
		if ( ( $change + $pos ) > $ts->getPrevDayFlushCount() )
			$change = '';
		$rmlpage .= '[td valign=middle]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
	}
	elseif ( $change == 0 )
		$rmlpage .= '[td valign=middle]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
	elseif ( $change < 0 )
		$rmlpage .= '[td valign=middle]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';
		
	$rmlpage .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=subteamoffset' . $tableSuffix . '&amp;team=' . rawurlencode($team) . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=subteamoffset' . $tableSuffix . '&amp;team=' . rawurlencode($team) . '"]More...[/url][/td][/tr]' . "\n\n";
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Overall Top 30[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][b]member[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml->generateRankList();
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . $pos . '.[/td]';

	$change = $mbs[$i]->getRank() - $pos;

	if ( $change > 0 )
		$rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
	elseif ( $change == 0 )
		$rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
	elseif ( $change < 0 )
		$rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';
	
	$rmlpage .= '[td align="right"][red]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/red][/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=subteamoffset' . $tableSuffix . '&amp;team=' . rawurlencode($team) . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=subteamoffset' . $tableSuffix . '&amp;team=' . rawurlencode($team) . '"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";
$rmlpage .= '[table]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Daily Top 15[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberoffset' . $tableSuffix, $datum, 0, 15, $db);
$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $project->getSubteamDaily() > 15 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($project->getPrefix() . '_memberoffset' . $tableSuffix, $datum, ( $project->getSubteamDaily() - 8 ), 15, $db);
	$ml->generateFlushList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getSubteamDaily() > 15 ) )
		$pos = $i + ( $project->getSubteamDaily() - 8 );
	else
	        $pos = $i + 1;

        $rmlpage .= '[tr]';
        $rmlpage .= '[td align="right"]' . ( $pos ) . '.[/td]';

        $change = $mbs[$i]->getYesterday() - ( $pos );

        if ( $change > 0 )
	{
		if ( ( $change + $pos ) > $ts->getPrevDayFlushCount() )
			$change = '';
                $rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
	}
        elseif ( $change == 0 )
                $rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
        elseif ( $change < 0 )
                $rmlpage .= '[td]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';

        $rmlpage .= '[td align="right"][red]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/red][/td]';
        $rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
        $rmlpage .= '[/tr]';
}
$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Overall Top 15[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberoffset' . $tableSuffix, $datum, 0, 15, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $project->getSubteamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($project->getPrefix() . '_memberoffset' . $tableSuffix, $datum, ( $project->getSubteamRank() - 8 ), 15, $db);
	$ml->generateRankList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
	#echo $tmpMember;
}
	
for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getSubteamRank() > 15 ) )
		$pos = $i + ( $project->getSubteamRank() - 8 );
	else
		$pos = $i + 1;

        $rmlpage .= '   [tr]';
        $rmlpage .= '    [td align="right"]' . $pos . '.[/td]';

        $change = $mbs[$i]->getRank() - $pos;

        if ( $change > 0 )
                $rmlpage .= '    [td]([img]http://www.tweakers.net/g/dpc/up.gif[/img]' . ( $change ) . ')[/td]';
        elseif ( $change == 0 )
                $rmlpage .= '    [td]([img]http://www.tweakers.net/g/dpc/stil.gif[/img])[/td]';
        elseif ( $change < 0 )
                $rmlpage .= '    [td]([img]http://www.tweakers.net/g/dpc/down.gif[/img]' . ( $change - ( $change * 2 )) . ')[/td]';

        $rmlpage .= '    [td align="right"][red]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/red][/td]';
        $rmlpage .= '    [td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '    [td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '    [td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
        $rmlpage .= '   [/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
$rmlpage .= '  [/table]' . "\n";

$fmc = new FlushList($project->getPrefix() . '_subteamoffset', $db, $team);

$fmc->createMFList();
$fl = $fmc->getMFList();

$rmlpage .= '[table width="350px"]';
$rmlpage .= '[tr][td colspan="6"][img=400,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="4"][b]Megaflush Top 5[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td width="10%"][b]pos[/b][/td]';
$rmlpage .= '[td width="45%"][b]Team[/b][/td]';
$rmlpage .= '[td width="20%" align="right"][b]Flush[/b][/td]';
$rmlpage .= '[td width="25%" align="right"][b]Date[/b][/td]';
$rmlpage .= '[/tr]';
for($i=0;$i<(count($fl)>=5?5:count($fl));$i++)
{
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . ( $i + 1 ) . '.[/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=subteamoffset&amp;team=' . rawurlencode($team) . '&amp;naam=' . rawurlencode($fl[$i]->getName()) . '"]' . $fl[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"]' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '[/td]';
	$rmlpage .= '[td align="right" width="65"]' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '[/td]';
	$rmlpage .= '[/tr]';
}
$rmlpage .= '[tr][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=Flush"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$mp = new MijlPalen($project->getPrefix() . '_subteamoffset', $datum, $project->getPrefix(), $db, $team);
$mpl = $mp->getMijlpalen();

if ( count($mpl) > 0 )
{
	$rmlpage .= '  [b]Mijlpalen[/b]';
	$rmlpage .= '  [table width="300px"]';
	for($i=0;$i<count($mpl);$i++)
	{
		$rmlpage .= '   [tr]';
		$rmlpage .= '    [td][url="' . $baseUrl . '/?mode=detail&amp;tabel=subteamoffset' . $tableSuffix . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($team) . '&amp;naam=' . rawurlencode($mpl[$i]->getName()) . '&amp;datum=' . $datum . '"]' . $mpl[$i]->getName() . '[/url][/td]';
		$rmlpage .= '    [td align="right"]' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '[/td]';
		$rmlpage .= '   [/tr]';
	}
	$rmlpage .= '  [/table]' . "\n";
}

$mi = new MemberInfo($db, $team, $project->getPrefix() . '_memberoffset', $datum, $project->getPrefix(), 'memberoffset' . $tableSuffix, $team);

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgMonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'' . $team . '\' 
		AND 	tabel = \'' . $project->getPrefix() . '_memberoffset\'';
		
	$result = $db->selectQuery($query);
	
	if ( $line = $db->fetchArray($result) )
	{
                $lineArray = array($line['avgMonthly']);
	}
	else
	{
        	$lineArray = array(0, 0);
	}

	$charArray = array('avgMonthly');
	$headArray = array('monthly');

        $t = new TOThreats($db, $project->getPrefix() . '_memberoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $team);
	$tl = $t->getThreatList();;

        if ( count($tl) > 0 )
        {
                $rmlpage .= '[b]When do they get you[/b]';
                $rmlpage .= '[table width="300"]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($tl);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=memberoffset' . $tableSuffix . '&amp;naam=' . rawurlencode($tl[$i]['name']) . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '"]' . $tl[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($tl[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($tl[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }

        $o = new Opertunities($db, $project->getPrefix() . '_memberoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $team);
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
                $rmlpage .= '[b]When do you get them[/b]';
                $rmlpage .= '[table width="300px"]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($ol);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=memberoffset' . $tableSuffix . '&amp;naam=' . rawurlencode($ol[$i]['name']) . '&amp;datum=' . $datum . '"]' . $ol[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($ol[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($ol[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }
}

if ( count($joins) > 0 )
{
	$rmlpage .= '  [b]Nieuwe Leden[/b]';
        $rmlpage .= '[table width="300"]';
        for($i=0;$i<count($joins);$i++)
        {
                $rmlpage .= '[tr]';
                $rmlpage .= '[td align="left" width="70%"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=memberoffset' . $tableSuffix . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($joins[$i]['name']) . '"]' . $joins[$i]['name'] . '[/url][/td]';
                $rmlpage .= '[td align="right" width="30%"]' . number_format($joins[$i]['credits'], 0, ',', '.') . '[/td]';
                $rmlpage .= '[/tr]';
        }
        $rmlpage .= '[/table]' . "\n";
}

if ( count($leaves) > 0 )
{
	$rmlpage .= '  [b]Leaves[/b]'; 
        $rmlpage .= '[table width="300"]';
        for($i=0;$i<count($leaves);$i++)
        {
                $rmlpage .= '[tr]';
                $rmlpage .= '[td align="left" width="70%"]' . $leaves[$i]['name'] . '[/td]';
                $rmlpage .= '[td align="right" width="30%"]' . number_format($leaves[$i]['credits'], 0, ',', '.') . '[/td]';
                $rmlpage .= '[/tr]';
        }
        $rmlpage .= '[/table]' . "\n";
}

unset($movement, $joins, $leaves);

$rmlpage .= '[small][b]' . $project->getDpchTitle() . ' Links[/b]' . "\n";
$rmlpage .= '[url="' . $project->getWebsite() . '"]' . $project->getDpchTitle() . ' webpage[/url]' . "\n";
$rmlpage .= '[url="' . $project->getForum() . '"]' . $project->getDpchTitle() . ' forum[/url]' . "\n";
$rmlpage .= '[url="http://www.dutchpowercows.org/doc.php?id=316"]DPCH Suggestiepagina[/url]' . "\n";

#foreach($dpchLinks as $name => $link)
#	$rmlpage .= '[url="' . $link . '"]' . $name . '[/url]' . "\n";

$rmlpage .= '[url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '"]Bron[/url][/small]';

echo str_replace('bgcolor="transparent"', '', parseRML($rmlpage));

echo '<br /><hr />';

echo 'Voor de statsposters:';
echo '<br /><br />';
echo '<input type="text" style="width:700px" value="[' . $project->getDpchTitle() . '] hitparade van ' . trim(strftime('%e %B', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum)))) . '" />';
echo '<br /><br /><b>HTML</b><br />';
?>
<textarea style="width:700px" rows="12" cols="85">{verhaal}[norml]<?php echo parseRML($rmlpage); ?>[/norml]</textarea>
<br /><br /><b>RML</b><br />
<textarea style="width:700px" rows="12" cols="85">{verhaal}<?php echo "\n" .  str_replace('8)', '[norml]8)[/norml]', htmlentities($rmlpage)); ?></textarea>
<br><br>
</body>
</html>
