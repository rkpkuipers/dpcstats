<?

include ('../classes.php');

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

$project = new Project($db, $prefix, 'memberoffset', $datum);

# Set locale to provide dutch names for days, months and such
setLocale(LC_ALL, 'nl_NL.utf8');

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

$page = '<style type="text/css">';
$page .= 'div.dpch { font-size: 11px; font-family: Verdana,Arial,helvetica,sans-serif; }';
$page .= 'div.dpch table { width: 450px; }';
$page .= 'div.dpch th { font-size: 11px; font-family: Verdana,Arial,helvetica,sans-serif; text-align: left; font-weight: bold; }';
$page .= 'div.dpch td { font-size: 11px; font-family: Verdana,Arial,helvetica,sans-serif; }';
$page .= 'div.dpch td.da { text-align: right; color: red; }';
$page .= 'div.dpch td.ov { text-align: right; color: blue; }';
$page .= 'div.dpch td.le { white-space: nowrap; }';
$page .= 'div.dpch td.ri { text-align: right; white-space: nowrap; }';
$page .= 'div.dpch th.ri { text-align: right; white-space: nowrap; }';
$page .= 'div.dpch p { font-weight: bold; }';
$page .= 'div.dpch img { width: 10px; height: 8px; }';
$page .= 'div.dpch div.links { font-size: 11px; font-family: Verdana,Arial,helvetica,sans-serif; }';
$page .= 'div.dpch div.links a { text-decoration: underline; }';
$page .= '</style>';

$ts = new TableStatistics($project->getPrefix() . '_memberoffset', $datum, $db);
$ts->gather();

$page .= '<div style="font-weight: bold">DPC ' . $project->getDpchTitle() . ' hitparade van ' . strftime('%e %B %Y', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum))) . '</div>';
$page .= '<div class="dpch">';
$page .= '<p>Daily Top 30</p>';
$page .= '<span style="color: #000000">Flushers: ' . $ts->getDailyFlushers() . ' / ' . number_format($ts->getTotalMembers(), 0, ',', '.') . ' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 1, ',', '.') . ' %)</span><br /><br />';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">daily</th>';
$page .= '<th>member</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th></th>';
$page .= '</tr>';

$ml = new MemberList($project->getPrefix() . '_memberoffset', $datum, 0, 30, $db);

$ml->generateFlushList();
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	$page .= '<tr>';
	$page .= '<td class="ri">' . ( $pos ) . '.</td>';

	$change = $mbs[$i]->getYesterday() - ( $pos );

	if ( $change > 0 )
	{
		if ( ( $change + $pos ) > $ts->getPrevDayFlushCount() )
			$change = '';
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change ) . ')</td>';
	}
	elseif ( $change == 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
	elseif ( $change < 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';
		
	$page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
		$page .= '<td class="ri">(' . $mbs[$i]->getCurrRank() . ')</td>';
	$page .= '</tr>';
}

$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Overall Top 30</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th>member</th>';
$page .= '<th class="ri">daily</th>';
$page .= '<th></th>';
$page .= '</tr>';

$ml->generateRankList();
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	
	$page .= '<tr>';
	$page .= '<td class="ri">' . $pos . '.</td>';

	$change = $mbs[$i]->getRank() - $pos;

	if ( $change > 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change ) . ')</td>';
	elseif ( $change == 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
	elseif ( $change < 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';
	
	$page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	$page .= '<td class="ri">(' . $mbs[$i]->getFlushRank() . ')</td>';
	$page .= '</tr>';
}

$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset">More...</a></td></tr>';
$page .= '</table>';
$page .= '<p>Teams Daily Top 15</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">daily</th>';
$page .= '<th>team</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th></th>';
$page .= '</tr>';

$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, 0, 15, $db);

$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $project->getTeamDaily() > 15 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, ( $project->getTeamDaily() - 8 ), 15, $db);
	$ml->generateFlushList();
	$mbs = array_merge($tmpMember, $ml->getMembers());
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamDaily() > 15 ) )
		$pos = $i + ( $project->getTeamDaily() - 8 );
	else
	        $pos = $i + 1;

        $page .= '<tr>';
        $page .= '<td class="ri">' . ( $pos ) . '.</td>';

        $change = $mbs[$i]->getYesterday() - ( $pos );

        if ( $change > 0 )
	{
		if ( ( $change + $pos ) > $ts->getPrevDayFlushCount() )
			$change = '';
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change ) . ')</td>';
	}
        elseif ( $change == 0 )
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
        elseif ( $change < 0 )
	{
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';
	}

        $page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
        $page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . 
'&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
        $page .= '<td class="ri">(' . $mbs[$i]->getCurrRank() . ')</td>';
        $page .= '</tr>';
}
$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Teams Overall Top 15</p>';
$page .= '<table>';
$page .= '    <tr>';
$page .= '     <th colspan="2">pos</th>';
$page .= '     <th class="ri">total</th>';
$page .= '     <th>team</th>';
$page .= '     <th class="ri">daily</th>';
$page .= '     <th></th>';
$page .= '    </tr>';

$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, 0, 15, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $project->getTeamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, ( $project->getTeamRank() - 8 ), 15, $db);
	$ml->generateRankList();
	$mbs = $ml->getMembers();

	$mbs = array_merge($tmpMember, $ml->getMembers());
	#echo $tmpMember;
}
	
for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamRank() > 15 ) )
		$pos = $i + ( $project->getTeamRank() - 8 );
	else
		$pos = $i + 1;

       $page .= '   <tr>';
        $page .= '    <td class="ri">' . $pos . '.</td>';

        $change = $mbs[$i]->getRank() - $pos;

        if ( $change > 0 )
                $page .= '    <td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change )
                . ')</td>';
        elseif ( $change == 0 )
                $page .= '    <td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
        elseif ( $change < 0 )
                $page .= '    <td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';

        $page .= '    <td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
        $page .= '    <td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '    <td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
        $page .= '    <td class="ri">(' . $mbs[$i]->getFlushRank() . ')</td>';
        $page .= '   </tr>';
}

$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset">More...</a></td></tr>';
$page .= '  </table>';

$fmc = new FlushList($project->getPrefix() . '_memberoffset', $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

$page .= '<p>Megaflush Top 5</p>';

$page .= '<table style="width:375px">';
$page .= '<tr>';
$page .= '<th width="37">pos</th>';
$page .= '<th width="169">Team</th>';
$page .= '<th width="75" class="ri">Flush</th>';
$page .= '<th width="94" class="ri">Date</th>';
$page .= '</tr>';
if ( count($fl) < 5 )
	$top = count($fl);
else
	$top = 5;
for($i=0;$i<$top;$i++)
{
	$page .= '<tr>';
	$page .= '<td class="ri">' . ( $i + 1 ) . '.</td>';
	$page .= '<td><a href="' . $baseUrl . '/index.php?prefix=' . $project->getprefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;naam=' . $fl[$i]->getName() . '">' . $fl[$i]->getName() . '</a></td>';
	$page .= '<td class="ri">' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</td>';
	$page .= '<td align="right">' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '</td>';
	$page .= '</tr>';
}
$page .= '<tr><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=Flush">More...</a></td></tr>';
$page .= '</table>';

$mp = new MijlPalen($project->getPrefix() . '_memberoffset', $datum, $project->getPrefix(), $db);
$mpl = $mp->getMijlpalen();

if ( count($mpl) > 0 )
{
	$page .= '  <p>Mijlpalen</p>';
	$page .= '  <table style="width:300px">';
	for($i=0;$i<count($mpl);$i++)
	{
		$page .= '   <tr>';
		$page .= '    <td><a href="' . $baseUrl . '/?mode=detail&amp;tabel=memberoffset&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . $mpl[$i]->getName() . '&amp;datum=' . $datum . '" rel="external">' . $mpl[$i]->getName() . '</a></td>';
		$page .= '    <td class="ri">' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '</td>';
		$page .= '   </tr>';
	}
	$page .= '  </table>';
}

$mi = new MemberInfo($db, $project->getTeamName(), $project->getPrefix() . '_teamoffset', $datum, $project->getPrefix(), 'teamoffset', $project->getTeamName());

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgmonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'' . $project->getTeamName() . '\' 
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
                $page .= '<p>When do they get you</p>';
                $page .= '<table>';
                $page .= '<tr>';
		$page .= '<th>Team</th>';
		$page .= '<th class="ri">Average</th>';
		$page .= '<th class="ri">Days</th>';
		$page .= '</tr>';
                for($i=0;$i<count($tl);$i++)
                {
                        $page .= '<tr>';
                        $page .= '<td width="190" align="left"><a href="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $tl[$i]['name'] . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '">' . $tl[$i]['name'] . '</a></td>';
                        $page .= '<td width="50" align="right">' . number_format($tl[$i]['average'], 0, ',', '.') . '</td>';
                        $page .= '<td align="right" width="50">' . number_format($tl[$i]['days'], 0, ',', '.') . '</td>';
                        $page .= '</tr>';
                }
                $page .= '</table>';
        }

        $o = new Opertunities($db, $project->getPrefix() . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgmonthly', $project->getTeamName());
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
                $page .= '<p>When do you get them</p>';
                $page .= '<table width="100%">';
                $page .= '<tr>';
		$page .= '<th>Team</th>';
		$page .= '<th class="ri">Average</th>';
		$page .= '<th class="ri">Days</th>';
		$page .= '</tr>';
                for($i=0;$i<count($ol);$i++)
                {
                        $page .= '<tr>';
                        $page .= '<td width="190" align="left"><a href="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . $ol[$i]['name'] . '&amp;datum=' . $datum . '">' . $ol[$i]['name'] . '</a></td>';
                        $page .= '<td width="50" align="right">' . number_format($ol[$i]['average'], 0, ',', '.') . '</td>';
                        $page .= '<td align="right" width="50">' . number_format($ol[$i]['days'], 0, ',', '.') . '</td>';
                        $page .= '</tr>';
                }
                $page .= '</table>';
        }
}

$movement = new Movement($db, $project->getPrefix() . '_memberoffset', $datum);
$joins = $movement->getMembers(1);

if ( count($joins) > 0 )
{
	$page .= '  <p>Nieuwe Leden</p>';
        $page .= '<table>';
        for($i=0;$i<count($joins);$i++)
        {
                $page .= '<tr>';
                $page .= '<td align="left" width="70%"><a href="' . $baseUrl . '/index.php?mode=detail&amp;tabel=memberoffset&amp;prefix=' . $project->getprefix() . '&amp;datum=' . $datum . '&amp;naam=' . $joins[$i]['name'] . '">' . $joins[$i]['name'] . '</a></td>';
                $page .= '<td align="right" width="30%">' . number_format($joins[$i]['credits'], 0, ',', '.') . '</td>';
                $page .= '</tr>';
        }
        $page .= '</table>';
}

$leaves = $movement->getMembers(0);

if ( count($leaves) > 0 )
{
	$page .= '  <p>Leaves</p>'; 
        $page .= '<table>';
        for($i=0;$i<count($leaves);$i++)
        {
                $page .= '<tr>';
                $page .= '<td align="left" width="70%">' . $leaves[$i]['name'] . '</td>';
                $page .= '<td align="right" width="30%">' . number_format($leaves[$i]['credits'], 0, ',', '.') . '</td>';
                $page .= '</tr>';
        }
        $page .= '</table>';
}

$page .= '<p>' . $project->getDpchTitle() . ' Links</p>';
$page .= '<a href="' . $project->getWebsite() . '">' . $project->getDpchTitle() . ' webpage</a><br />';
$page .= '<a href="' . $project->getForum() . '">' . $project->getDpchTitle() . ' forum</a><br />';
$page .= '<a href="http://www.dutchpowercows.org/doc.php?id=316">DPCH Suggestiepagina</a><br />';

$query = 'SELECT link, name FROM links WHERE prefix = \'' . $project->getPrefix() . '-dpch\'';
$result = $db->selectQuery($query);
$dpchLinks = array();
while ( $line = $db->fetchArray($result) )
{
	$page .= '<a href="' . $line['link'] . '">' . $line['name'] . '</a><br />';
	$dpchLinks[$line['name']] = $line['link'];
}

$page .= '<a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '">Bron</a>';
$page .= '</div>';

#echo strlen($page);
echo $page;

echo '<br /><hr />';

echo 'Voor de statsposters:';
echo '<br /><br />';
echo '<input type="text" style="width:700px" value="[' . $project->getDpchTitle() . '] hitparade van ' . trim(strftime('%e %B', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum)))) . '" />';
echo '<br /><br /><b>HTML</b><br />';
?>
<textarea style="width:700px" rows="12" cols="85">{verhaal}[norml]<?php echo htmlentities($page); ?>[/norml]</textarea>

<?php

$ts = new TableStatistics($project->getPrefix() . '_memberoffset', $datum, $db);
$ts->gather();

$rmlpage .= '[b]DPC ' . $project->getDpchTitle() . ' hitparade van ' . strftime('%e %B %Y', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum))) . '[/b]' . "\n";
$rmlpage .= '[table bgcolor=transparent]';
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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset"]More...[/url][/td][/tr]' . "\n\n";
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table bgcolor=transparent]';
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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberoffset"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";
$rmlpage .= '[table bgcolor=transparent]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Daily Top 15[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, 0, 15, $db);
$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $project->getTeamDaily() > 15 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, ( $project->getTeamDaily() - 8 ), 15, $db);
	$ml->generateFlushList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
}

for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamDaily() > 15 ) )
		$pos = $i + ( $project->getTeamDaily() - 8 );
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
        $rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
        $rmlpage .= '[/tr]';
}
$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$rmlpage .= '[table bgcolor=transparent]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Teams Overall Top 15[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][b]team[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, 0, 15, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $project->getTeamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($project->getPrefix() . '_teamoffset', $datum, ( $project->getTeamRank() - 8 ), 15, $db);
	$ml->generateRankList();

	$mbs = array_merge($tmpMember, $ml->getMembers());
	#echo $tmpMember;
}
	
for($i=0;$i<count($mbs);$i++)
{
	if ( ( $i >= 1 ) && ( $project->getTeamRank() > 15 ) )
		$pos = $i + ( $project->getTeamRank() - 8 );
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
        $rmlpage .= '    [td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '    [td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '    [td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
        $rmlpage .= '   [/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamoffset"]More...[/url][/td][/tr]';
$rmlpage .= '  [/table]' . "\n";

# Code wordt voor de HTML uitvoer al aangeroepen
#$fmc = new FlushList($project . '_memberoffset', $db);

#$fmc->createMFList();
$fl = $fmc->getMFList();

$rmlpage .= '[table width="350px" bgcolor=transparent]';
$rmlpage .= '[tr][td colspan="6"][img=400,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="4"][b]Megaflush Top 5[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td][b]pos[/b][/td]';
$rmlpage .= '[td][b]Team[/b][/td]';
$rmlpage .= '[td align="right"][b]Flush[/b][/td]';
$rmlpage .= '[td align="right"][b]Date[/b][/td]';
$rmlpage .= '[/tr]';
for($i=0;$i<$top;$i++)
{
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . ( $i + 1 ) . '.[/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberoffset&amp;naam=' . rawurlencode($fl[$i]->getName()) . '"]' . $fl[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"]' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '[/td]';
	$rmlpage .= '[td align="right"]' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '[/td]';
	$rmlpage .= '[/tr]';
}
$rmlpage .= '[tr][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=Flush"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$mp = new MijlPalen($project->getPrefix() . '_memberoffset', $datum, $project->getPrefix(), $db);
$mpl = $mp->getMijlpalen();

if ( count($mpl) > 0 )
{
	$rmlpage .= '  [b]Mijlpalen[/b]';
	$rmlpage .= '  [table width="300px" bgcolor=transparent]';
	for($i=0;$i<count($mpl);$i++)
	{
		$rmlpage .= '   [tr]';
		$rmlpage .= '    [td][url="' . $baseUrl . '/?mode=detail&amp;tabel=memberoffset&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($mpl[$i]->getName()) . '&amp;datum=' . $datum . '"]' . $mpl[$i]->getName() . '[/url][/td]';
		$rmlpage .= '    [td align="right"]' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '[/td]';
		$rmlpage .= '   [/tr]';
	}
	$rmlpage .= '  [/table]' . "\n";
}

$mi = new MemberInfo($db, $project->getTeamName(), $project->getPrefix() . '_teamoffset', $datum, $project->getPrefix(), 'teamoffset', $project->getTeamName());

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgmonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'' . $project->getTeamName() . '\' 
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
                $rmlpage .= '[b]When do they get you[/b]';
                $rmlpage .= '[table width="300" bgcolor=transparent]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($tl);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=teamoffset&amp;naam=' . rawurlencode($tl[$i]['name']) . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '"]' . $tl[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($tl[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($tl[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }

        $o = new Opertunities($db, $project->getPrefix() . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgmonthly', $project->getTeamName());
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
                $rmlpage .= '[b]When do you get them[/b]';
                $rmlpage .= '[table width="300px" bgcolor=transparent]';
                $rmlpage .= '[tr]';
		$rmlpage .= '[td][b]Team[/b][/td]';
		$rmlpage .= '[td align="right"][b]Average[/b][/td]';
		$rmlpage .= '[td align="right"][b]Days[/b][/td]';
		$rmlpage .= '[/tr]';
                for($i=0;$i<count($ol);$i++)
                {
                        $rmlpage .= '[tr]';
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamoffset&amp;naam=' . rawurlencode($ol[$i]['name']) . '&amp;datum=' . $datum . '"]' . $ol[$i]['name'] . '[/url][/td]';
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
        $rmlpage .= '[table bgcolor=transparent width="300"]';
        for($i=0;$i<count($joins);$i++)
        {
                $rmlpage .= '[tr]';
                $rmlpage .= '[td align="left" width="70%"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=memberoffset&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($joins[$i]['name']) . '"]' . $joins[$i]['name'] . '[/url][/td]';
                $rmlpage .= '[td align="right" width="30%"]' . number_format($joins[$i]['credits'], 0, ',', '.') . '[/td]';
                $rmlpage .= '[/tr]';
        }
        $rmlpage .= '[/table]' . "\n";
}

if ( count($leaves) > 0 )
{
	$rmlpage .= '  [b]Leaves[/b]'; 
        $rmlpage .= '[table bgcolor=transparent width="300"]';
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

foreach($dpchLinks as $name => $link)
	$rmlpage .= '[url="' . $link . '"]' . $name . '[/url]' . "\n";

$rmlpage .= '[url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '"]Bron[/url][/small]';

echo '<br /><br /><b>RML</b><br />';

?>
<textarea style="width:700px" rows="12" cols="85">{verhaal}<?php echo "\n" .  str_replace('8)', '[norml]8)[/norml]', htmlentities($rmlpage)); ?></textarea>
<br><br>
</body>
</html>
