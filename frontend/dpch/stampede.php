<?

$prefix = 'sp6';
include ('../classes.php');


if ( isset($_GET['datum']) )
	$datum = $_GET['datum'];
else
{
	$datum = getYesterday($prefix);
}

?>
<html>
<head>
<link rel="stylesheet" type="text/css" href="http://gathering.tweakers.net/global/templates/tweakers/css/nightlife.css?v=193h" />

<body bgcolor="#DADADA">
<?php

$project = new Project($db, $prefix, 'memberoffset', $datum);
$listLength = 5;

# Set locale to provide dutch names for days, months and such
setLocale(LC_ALL, 'nl_NL');

$ts = new TableStatistics($project->getPrefix() . '_memberoffset', $datum, $db);
$ts->gather();

$rmlpage .= '[b]DPC ' . $project->getDpchTitle() . ' hitparade van ' . strftime('%e %B %Y', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum))) . '[/b]' . "\n";
$rmlpage .= '[table bgcolor="transparent" width="450px"]';
$rmlpage .= '[tr][td colspan="6"][b]Stampede Teams Daily[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]member[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, 0, 30, $db);

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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset' . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
	$rmlpage .= '[/tr]';
	
	if ( ! isset($teammembers[$mbs[$i]->getName()]) )
		$subteam[$mbs[$i]->getName()] = new MemberList(	$project->getPrefix() . '_subteamoffset', 
						$datum, 
						0, 
						$listsize, 
						$db, 
						$mbs[$i]->getName());
						
	$subteam[$mbs[$i]->getName()]->generateFlushList();
	$stMembers = $subteam[$mbs[$i]->getName()]->getMembers();
	if ( count($stMembers) < $listLength ) $cCount = ( count($stMembers) );
	else $cCount = $listLength;

	$rmlpage .= '[tr]';
	$rmlpage .= '[td][/td]';
	$rmlpage .= '[td colspan="5"]';
	$rmlpage .= '[table width="450px" bgcolor="transparent"]';
	for($j=0;$j<$cCount;$j++)
	{
		$rmlpage .= '[tr]';
		$rmlpage .= '[td][/td]';
		$rmlpage .= '[td align="right"]' . ( $j + 1 ) . '.[/td]';
		$rmlpage .= '[td align="center"]' . getRMLDPCHChangeImage( $stMembers[$j]->getYesterday() - ( $j + 1 ) , $ts) . '[/td]';
		$rmlpage .= '[td align="right" width="12%"][red]' . number_format($stMembers[$j]->getFlush(), 0, ',', '.') . '[/red][/td]';
		$rmlpage .= '[td][url="' . $baseUrl . '/index.php?mode=detail&amp;naam=' . rawurlencode($stMembers[$j]->getName()) . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=subteamoffset&amp;datum=' . $datum . '&amp;team=' . rawurlencode($mbs[$i]->getName()) . '"]' . $stMembers[$j]->getName() . '[/url][/td]';
		$rmlpage .= '[td align="right"][blue]' . number_format($stMembers[$j]->getCredits(), 0, ',', '.') . '[/blue][/td]';
		$rmlpage .= '[td align="right"](' . $stMembers[$j]->getCurrRank() . ')[/td]';
		$rmlpage .= '[/tr]';
	}
	$rmlpage .= '[tr][td colspan="6"][/td][/tr]';
	$rmlpage .= '[/table]';
	$rmlpage .= '[/td][/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;mode=Stampede&amp;tabel=memberoffset' . '"]More...[/url][/td][/tr]' . "\n\n";
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table bgcolor="transparent" width="450px"]';
$rmlpage .= '[tr][td colspan="6"][b]Stampede Teams Overall[/b][/td][/tr]';
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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset' . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
	$rmlpage .= '[/tr]';
	
	if ( ! isset($subteam[$mbs[$i]->getName()]) ) $subteam[$mbs[$i]->getName()] = new MemberList( 	$project->getPrefix() . '_subteamoffset',
													$datum,
								                                        0,
													$listsize,
													$db,
													$mbs[$i]->getName());
	$subteam[$mbs[$i]->getName()]->generateRankList();
	$stMembers = $subteam[$mbs[$i]->getName()]->getMembers();
	
	if ( count($stMembers) < $listLength ) $cCount = ( count($stMembers) );
	else $cCount = $listLength;

	$rmlpage .= '[tr]';
	$rmlpage .= '[td][/td]';
	$rmlpage .= '[td colspan="8"]';
	$rmlpage .= '[table width="450px"]';
	for($j=0;$j<$cCount;$j++)
	{
		$rmlpage .= '[tr]';
		$rmlpage .= '[td align="right"]' . ( $j + 1 ) . '.[/td]';
		$rmlpage .= '[td]' . getRMLDPCHChangeImage( $stMembers[$j]->getYesterday() - ( $j + 1 ) , $ts) . '[/td]';
		$rmlpage .= '[td align="right"][red]' . number_format($stMembers[$j]->getCredits(), 0, ',', '.') . '[/red][/td]';
		$rmlpage .= '[td][url="' . $baseUrl . '/index.php?mode=detail&amp;naam=' . rawurlencode($stMembers[$j]->getName()) . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=subteamoffset&amp;datum=' . $datum . '&amp;team=' . rawurlencode($mbs[$i]->getName()) . '"]' . $stMembers[$j]->getName() . '[/url][/td]';
		$rmlpage .= '[td align="right"][blue]' . number_format($stMembers[$j]->getFlush(), 0, ',', '.') . '[/blue][/td]';
		$rmlpage .= '[td align="right"](' . $stMembers[$j]->getCurrRank() . ')[/td]';
		$rmlpage .= '[/tr]';
	}
	$rmlpage .= '[tr][td colspan="6"][/td][/tr]';
	$rmlpage .= '[/table]';
	$rmlpage .= '[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;mode=Stampede&amp;tabel=memberoffset' . '"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table bgcolor="transparent" width="450px"]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Daily[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$pproject = new Project($db, 'fah', 'teamoffset', $datum);
$ml = new MemberList('fah_teamoffset', $datum, 0, 5, $db);
$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $pproject->getTeamDaily() > 8 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($pproject->getPrefix() . '_teamoffset', $datum, ( $pproject->getTeamDaily() - 4 ), 8, $db);
	$ml->generateFlushList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $pproject->getTeamDaily() > 8 ) )
		$pos = $i + ( $pproject->getTeamDaily() - 4 );
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
        $rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $pproject->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset' . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
        $rmlpage .= '[/tr]';
}

#$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table bgcolor="transparent" width="450px"]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Overall[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList('fah_teamoffset', $datum, 0, 5, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $pproject->getTeamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($pproject->getPrefix() . '_teamoffset', $datum, ( $pproject->getTeamRank() - 4 ), 8, $db);
	$ml->generateRankList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
	#echo $tmpMember;
}
	
for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $pproject->getTeamRank() > 15 ) )
		$pos = $i + ( $pproject->getTeamRank() - 4 );
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
        $rmlpage .= '    [td][url="' . $baseUrl . '/?prefix=' . $pproject->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset' . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '    [td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '    [td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
        $rmlpage .= '   [/tr]';
}

#$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
$rmlpage .= '  [/table]' . "\n";

# Code wordt voor de HTML uitvoer al aangeroepen
$fmc = new FlushList('sp6_memberoffset', $db);
$fmc->createMFList();
$fl = $fmc->getMFList();

$rmlpage .= '[table width="400px" bgcolor="transparent"]';
$rmlpage .= '[tr][td colspan="4"][b]Megaflush Top 5[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td width="10%"][b]pos[/b][/td]';
$rmlpage .= '[td width="45%"][b]Team[/b][/td]';
$rmlpage .= '[td width="20%" align="right"][b]Flush[/b][/td]';
$rmlpage .= '[td width="25%" align="right"][b]Date[/b][/td]';
$rmlpage .= '[/tr]';
for($i=0;$i<(count($fl)>4?5:count($fl));$i++)
{
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . ( $i + 1 ) . '.[/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;naam=' . rawurlencode($fl[$i]->getName()) . '"]' . $fl[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"]' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '[/td]';
	$rmlpage .= '[td align="right" width="65"]' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '[/td]';
	$rmlpage .= '[/tr]';
}
$rmlpage .= '[/table]' . "\n";

$mp = new MijlPalen($project->getPrefix() . '_memberoffset', $datum, $project->getPrefix(), $db);
$mpl = $mp->getMijlpalen();

if ( count($mpl) > 0 )
{
	$rmlpage .= '  [b]Mijlpalen[/b]';
	$rmlpage .= '  [table width="300px" bgcolor="transparent"]';
	for($i=0;$i<count($mpl);$i++)
	{
		$rmlpage .= '   [tr]';
		$rmlpage .= '    [td][url="' . $baseUrl . '/?mode=detail&amp;tabel=memberoffset' . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($mpl[$i]->getName()) . '&amp;datum=' . $datum . '"]' . $mpl[$i]->getName() . '[/url][/td]';
		$rmlpage .= '    [td align="right"]' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '[/td]';
		$rmlpage .= '   [/tr]';
	}
	$rmlpage .= '  [/table]' . "\n";
}

$mi = new MemberInfo($db, $project->getTeamName(), 'fah_teamoffset', $datum, 'fah', 'teamoffset', $project->getTeamName());

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgMonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'' . $project->getTeamName() . '\' 
		AND 	tabel = \'fah_teamoffset\'';
		
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

        $t = new TOThreats($db, 'fah_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $project->getTeamName());
	$tl = $t->getThreatList();;

        if ( count($tl) > 0 )
        {
                $rmlpage .= '[b]When do they get you[/b]';
                $rmlpage .= '[table width="300" bgcolor="transparent"]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($tl);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=teamoffset' . '&amp;naam=' . rawurlencode($tl[$i]['name']) . '&amp;prefix=fah&amp;datum=' . $datum . '"]' . $tl[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($tl[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($tl[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }

        $o = new Opertunities($db, 'fah_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $project->getTeamName());
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
                $rmlpage .= '[b]When do you get them[/b]';
                $rmlpage .= '[table width="300px" bgcolor="transparent"]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($ol);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;prefix=fah&amp;tabel=teamoffset' . '&amp;naam=' . rawurlencode($ol[$i]['name']) . '&amp;datum=' . $datum . '"]' . $ol[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($ol[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($ol[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }
}

unset($movement, $joins, $leaves);
$rmlpage .= '[small][b]' . $project->getDpchTitle() . ' Links[/b]' . "\n";
$rmlpage .= '[url="' . $project->getWebsite() . '"]' . $project->getDpchTitle() . ' webpage[/url]' . "\n";
$rmlpage .= '[url="' . $project->getForum() . '"]' . $project->getDpchTitle() . ' forum[/url]' . "\n";
$rmlpage .= '[url="http://www.dutchpowercows.org/doc.php?id=316"]DPCH Suggestiepagina[/url]' . "\n";

$rmlpage .= '[url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '"]Bron[/url][/small]';

echo str_replace('bgcolor="transparent"', '', parseRML($rmlpage));
echo '<br /><br /><b>RML</b><br />';
echo '<input type="text" style="width:700px" value="[' . $project->getDpchTitle() . '] hitparade van ' . trim(strftime('%e %B', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum)))) . '" />';

?>
<br><br>
<textarea style="width:700px" rows="12" cols="85">{verhaal}<?php echo "\n" . str_replace('8)', '[norml]8)[/norml]', htmlentities($rmlpage)); ?></textarea>
<br><br>
</body>
</html>
