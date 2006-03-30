<?

include ('../classes.php');

$prefix = 'sp5';

if ( isset($_GET['datum']) )
	$datum = $_GET['datum'];
else
{
	$datum = getYesterday($prefix);
}

if ( $datum > date("Y-m-d", strtotime("-3 day")) )
	$tableSuffix = 'Daily';
else
	$tableSuffix = '';

$project = new Project($db, $prefix, 'memberOffset', $datum);
$listLength = 5;

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

$ts = new TableStatistics($project->getPrefix() . '_memberOffset' . $tableSuffix, $datum, $db);
$ts->gather();

$page .= '<div style="font-weight: bold">DPC ' . $project->getDpchTitle() . ' hitparade van ' . strftime('%e %B %Y', strtotime($datum)) . '</div>';
$page .= '<div class="dpch">';
$page .= '<p>Daily</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">daily</th>';
$page .= '<th>member</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th></th>';
$page .= '</tr>';

$ml = new MemberList($project->getPrefix() . '_memberOffset' . $tableSuffix, $datum, 0, 30, $db);

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
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
		$page .= '<td class="ri">(' . $mbs[$i]->getCurrRank() . ')</td>';
	$page .= '</tr>';

	if ( ! isset($teammembers[$mbs[$i]->getName()]) )
		$subteam[$mbs[$i]->getName()] = new MemberList(	$project->getPrefix() . '_subteamOffset', 
						$datum, 
						0, 
						$listsize, 
						$db, 
						$mbs[$i]->getName());
						
	$subteam[$mbs[$i]->getName()]->generateFlushList();
	$stMembers = $subteam[$mbs[$i]->getName()]->getMembers();
	if ( count($stMembers) < $subteamCount ) $cCount = ( count($stMembers) );
	else $cCount = $listLength;

	$page .= '<tr>';
	$page .= '<td width="10px"></td>';
	$page .= '<td colspan="8">';
	for($j=0;$j<$cCount;$j++)
	{
		$page .= '<table width="100%">';
		$page .= '<tr>';
		$page .= '<td width="3%" class="ri">' . ( $j + 1 ) . '.</td>';
		$page .= '<td width="10%">' . getDPCHChangeImage( $stMembers[$j]->getYesterday() - ( $j + 1 ) , $ts) . '</td>';
		$page .= '<td class="da" width="12%">' . number_format($stMembers[$j]->getFlush(), 0, ',', '.') . '</td>';
		$page .= '<td width="55%"><a href="' . $baseUrl . '/index.php?mode=detail&amp;naam=' . rawurlencode($stMembers[$j]->getName()) . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=subteamOffset&amp;datum=' . $datum . '&amp;team=' . rawurlencode($mbs[$i]->getName()) . '">' . $stMembers[$j]->getName() . '</a></td>';
		$page .= '<td class="ov" width="12%">' . number_format($stMembers[$j]->getCredits(), 0, ',', '.') . '</td>';
		$page .= '<td width="7%" class="ri">(' . $stMembers[$j]->getCurrRank() . ')</td>';
		$page .= '</tr>';
		$page .= '</table>';
	}
	$page .= '</td></tr>';

	if ( $i < ( count($mbs) - 1 ) )
		$page .= '<tr><td colspan="9">&nbsp;</td></tr>';
}

#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberOffset' . $tableSuffix . '">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Overall</p>';
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
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	$page .= '<td class="ri">(' . $mbs[$i]->getFlushRank() . ')</td>';
	$page .= '</tr>';
	
	if ( ! isset($subteam[$mbs[$i]->getName()]) ) $subteam[$mbs[$i]->getName()] = new MemberList( 	$project->getPrefix() . '_subteamOffset',
													$datum,
								                                        0,
													$listsize,
													$db,
													$mbs[$i]->getName());
	$subteam[$mbs[$i]->getName()]->generateRankList();
	$stMembers = $subteam[$mbs[$i]->getName()]->getMembers();
																																							        if ( count($stMembers) < $subteamCount ) $cCount = ( count($stMembers) );
	else $cCount = $listLength;

	$page .= '<tr>';
	$page .= '<td width="10px"></td>';
	$page .= '<td colspan="8">';
	for($j=0;$j<$cCount;$j++)
	{
		$page .= '<table width="100%">';
		$page .= '<tr>';
		$page .= '<td width="3%" class="ri">' . ( $j + 1 ) . '.</td>';
		$page .= '<td width="10%">' . getDPCHChangeImage( $stMembers[$j]->getYesterday() - ( $j + 1 ) , $ts) . '</td>';
		$page .= '<td width="12%" class="ov">' . number_format($stMembers[$j]->getCredits(), 0, ',', '.') . '</td>';
		$page .= '<td width="55%"><a href="' . $baseUrl . '/index.php?mode=detail&amp;naam=' . rawurlencode($stMembers[$j]->getName()) . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=subteamOffset&amp;datum=' . $datum . '&amp;team=' . rawurlencode($mbs[$i]->getName()) . '">' . $stMembers[$j]->getName() . '</a></td>';
		$page .= '<td width="12%" class="da">' . number_format($stMembers[$j]->getFlush(), 0, ',', '.') . '</td>';
		$page .= '<td width="7%" class="ri">(' . $stMembers[$j]->getCurrRank() . ')</td>';
		$page .= '</tr>';
		$page .= '</table>';
	}
	$page .= '</td>';
	$page .= '</tr>';

	if ( $i < ( count($mbs) - 1 ) )
		 $page .= '<tr><td colspan="9">&nbsp;</td></tr>';
}

#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberOffset' . $tableSuffix . '">More...</a></td></tr>';
$page .= '</table>';

/*
$page .= '<p>Teams Daily Top 3</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">daily</th>';
$page .= '<th>team</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th></th>';
$page .= '</tr>';

$ml = new MemberList('rah_teamOffset' . $tableSuffix, $datum, 0, 3, $db);

$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $project->getTeamDaily() > 15 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, ( $project->getTeamDaily() - 8 ), 15, $db);
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
        $page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamOffset' . $tableSuffix . '&amp;datum=' . $datum . 
'&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
        $page .= '<td class="ri">(' . $mbs[$i]->getCurrRank() . ')</td>';
        $page .= '</tr>';
}
$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamOffset' . $tableSuffix . '">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Teams Overall Top 5</p>';
$page .= '<table>';
$page .= '    <tr>';
$page .= '     <th colspan="2">pos</th>';
$page .= '     <th class="ri">total</th>';
$page .= '     <th>team</th>';
$page .= '     <th class="ri">daily</th>';
$page .= '     <th></th>';
$page .= '    </tr>';

$ml = new MemberList('rah_teamOffset' . $tableSuffix, $datum, 0, 3, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $project->getTeamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, ( $project->getTeamRank() - 8 ), 15, $db);
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
        $page .= '    <td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '    <td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
        $page .= '    <td class="ri">(' . $mbs[$i]->getFlushRank() . ')</td>';
        $page .= '   </tr>';
}

$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamOffset' . $tableSuffix . '">More...</a></td></tr>';
$page .= '  </table>';
/*/
$page .= '<p>' . $project->getDpchTitle() . ' Links</p>';
#$page .= '<a href="' . $project->getWebsite() . '">' . $project->getDpchTitle() . ' webpage</a><br />';
#$page .= '<a href="' . $project->getForum() . '">' . $project->getDpchTitle() . ' forum</a><br />';
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
/*
$ts = new TableStatistics($project->getPrefix() . '_memberOffset' . $tableSuffix, $datum, $db);
$ts->gather();

$rmlpage .= '[b]DPC ' . $project->getDpchTitle() . ' hitparade van ' . strftime('%e %B %Y', strtotime(($project->getPrefix()=='ud'?getPrevDate($datum):$datum))) . '[/b]' . "\n";
$rmlpage .= '[table bgcolor=transparent width="100%"]';
$rmlpage .= '[tr][td colspan="6"][img=450,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="6"][b]Daily Stampede Teams[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td colspan="2"][b]pos[/b][/td]';
$rmlpage .= '[td align="right"][b]daily[/b][/td]';
$rmlpage .= '[td][b]member[/b][/td]';
$rmlpage .= '[td align="right"][b]total[/b][/td]';
$rmlpage .= '[td][/td]';
$rmlpage .= '[/tr]';

$ml = new MemberList($project->getPrefix() . '_memberOffset' . $tableSuffix, $datum, 0, 30, $db);

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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
	$rmlpage .= '[/tr]';
	
	if ( ! isset($teammembers[$mbs[$i]->getName()]) )
		$subteam[$mbs[$i]->getName()] = new MemberList(	$project->getPrefix() . '_subteamOffset', 
						$datum, 
						0, 
						$listsize, 
						$db, 
						$mbs[$i]->getName());
						
	$subteam[$mbs[$i]->getName()]->generateFlushList();
	$stMembers = $subteam[$mbs[$i]->getName()]->getMembers();
	if ( count($stMembers) < $subteamCount ) $cCount = ( count($stMembers) );
	else $cCount = $listLength;

	$rmlpage .= '[tr]';
	$rmlpage .= '[td][/td]';
	$rmlpage .= '[td colspan="5"]';
	for($j=0;$j<$cCount;$j++)
	{
		$rmlpage .= '[table width="100%" cellspacing="1" cellpadding="1" bgcolor="transparent"]';

		$rmlpage .= '[tr]';
		$rmlpage .= '[td][img=5,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=14,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=45,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=54,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=248,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=54,1]http://tadah.mine.nu/images/blank.gif[/img][/td]';
		$rmlpage .= '[td][img=32,1]http://tadah.mine.nu/images/blank.gif[/img][/td][/tr]';
		
		$rmlpage .= '[tr]';
		$rmlpage .= '[td width="1%"][/td]';
		$rmlpage .= '[td width="3%" align="right"]' . ( $j + 1 ) . '.[/td]';
		$rmlpage .= '[td width="10%" align="center"]' . getRMLDPCHChangeImage( $stMembers[$j]->getYesterday() - ( $j + 1 ) , $ts) . '[/td]';
		$rmlpage .= '[td align="right" width="12%"][red]' . number_format($stMembers[$j]->getFlush(), 0, ',', '.') . '[/red][/td]';
		$rmlpage .= '[td width="55%"][url="' . $baseUrl . '/index.php?mode=detail&amp;naam=' . rawurlencode($stMembers[$j]->getName()) . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=subteamOffset&amp;datum=' . $datum . '&amp;team=' . rawurlencode($mbs[$i]->getName()) . '"]' . $stMembers[$j]->getName() . '[/url][/td]';
		$rmlpage .= '[td align="right" width="12%"][blue]' . number_format($stMembers[$j]->getCredits(), 0, ',', '.') . '[/blue][/td]';
		$rmlpage .= '[td width="7%" align="right"](' . $stMembers[$j]->getCurrRank() . ')[/td]';
		$rmlpage .= '[/tr]';
		$rmlpage .= '[/table]';
	}
	$rmlpage .= '[/td][/tr]';

	if ( $i < ( count($mbs) - 1 ) )
		$rmlpage .= '[tr][td colspan="9">&nbsp;[/td][/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberOffset' . $tableSuffix . '"]More...[/url][/td][/tr]' . "\n\n";
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
	$rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
	$rmlpage .= '[td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
	$rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=memberOffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
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

$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, 0, 15, $db);
$ml->generateFlushList();
$mbs = $ml->getMembers();

if ( $project->getTeamDaily() > 15 )
{
	if ( isset($mbs[0]) )
		$tmpMember = array($mbs[0]);
	else
		$tmpMember = array();

	$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, ( $project->getTeamDaily() - 8 ), 15, $db);
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
        $rmlpage .= '[td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '[td align="right"][blue]' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '[td align="right"](' . $mbs[$i]->getCurrRank() . ')[/td]';
        $rmlpage .= '[/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamOffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
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

$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, 0, 15, $db);
$ml->generateRankList();
$mbs = $ml->getMembers();

if ( $project->getTeamRank() > 15 )
{
	$tmpMember = array($mbs[0]);
	
	$ml = new MemberList($project->getPrefix() . '_teamOffset' . $tableSuffix, $datum, ( $project->getTeamRank() - 8 ), 15, $db);
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
        $rmlpage .= '    [td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=teamOffset' . $tableSuffix . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '"]' . $mbs[$i]->getName() . '[/url][/td]';
        $rmlpage .= '    [td align="right"][blue]' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '[/blue][/td]';
        $rmlpage .= '    [td align="right"](' . $mbs[$i]->getFlushRank() . ')[/td]';
        $rmlpage .= '   [/tr]';
}

$rmlpage .= '[tr][td][/td][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;tabel=teamOffset' . $tableSuffix . '"]More...[/url][/td][/tr]';
$rmlpage .= '  [/table]' . "\n";

# Code wordt voor de HTML uitvoer al aangeroepen
$fmc = new FlushList($project->getPrefix() . '_memberOffset', $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

$rmlpage .= '[table width="350px" bgcolor=transparent]';
$rmlpage .= '[tr][td colspan="6"][img=400,1]http://gathering.tweakers.net/global/templates/got/images/layout/pixel.gif[/img][/td][/tr]';
$rmlpage .= '[tr][td colspan="4"][b]Megaflush Top 5[/b][/td][/tr]';
$rmlpage .= '[tr]';
$rmlpage .= '[td width="10%"][b]pos[/b][/td]';
$rmlpage .= '[td width="45%"][b]Team[/b][/td]';
$rmlpage .= '[td width="20%" align="right"][b]Flush[/b][/td]';
$rmlpage .= '[td width="25%" align="right"][b]Date[/b][/td]';
$rmlpage .= '[/tr]';
for($i=0;$i<$top;$i++)
{
	$rmlpage .= '[tr]';
	$rmlpage .= '[td align="right"]' . ( $i + 1 ) . '.[/td]';
	$rmlpage .= '[td][url="' . $baseUrl . '/index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=memberOffset&amp;naam=' . rawurlencode($fl[$i]->getName()) . '"]' . $fl[$i]->getName() . '[/url][/td]';
	$rmlpage .= '[td align="right"]' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '[/td]';
	$rmlpage .= '[td align="right" width="65"]' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '[/td]';
	$rmlpage .= '[/tr]';
}
$rmlpage .= '[tr][td][url="' . $baseUrl . '/?prefix=' . $project->getPrefix() . '&amp;mode=Flush"]More...[/url][/td][/tr]';
$rmlpage .= '[/table]' . "\n";

$mp = new MijlPalen($project->getPrefix() . '_memberOffset', $datum, $project->getPrefix(), $db);
$mpl = $mp->getMijlpalen();

if ( count($mpl) > 0 )
{
	$rmlpage .= '  [b]Mijlpalen[/b]';
	$rmlpage .= '  [table width="300px" bgcolor=transparent]';
	for($i=0;$i<count($mpl);$i++)
	{
		$rmlpage .= '   [tr]';
		$rmlpage .= '    [td][url="' . $baseUrl . '/?mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($mpl[$i]->getName()) . '&amp;datum=' . $datum . '"]' . $mpl[$i]->getName() . '[/url][/td]';
		$rmlpage .= '    [td align="right"]' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '[/td]';
		$rmlpage .= '   [/tr]';
	}
	$rmlpage .= '  [/table]' . "\n";
}

$mi = new MemberInfo($db, $project->getTeamName(), $project->getPrefix() . '_teamOffset', $datum, $project->getPrefix(), 'teamOffset' . $tableSuffix, $project->getTeamName());

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgMonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'' . $project->getTeamName() . '\' 
		AND 	tabel = \'' . $project->getPrefix() . '_teamOffset\'';
		
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

        $t = new TOThreats($db, $project->getPrefix() . '_teamOffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $project->getTeamName());
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
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=teamOffset' . $tableSuffix . '&amp;naam=' . rawurlencode($tl[$i]['name']) . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '"]' . $tl[$i]['name'] . '[/url][/td]';
                        $rmlpage .= '[td width="50" align="right"]' . number_format($tl[$i]['average'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[td align="right" width="50"]' . number_format($tl[$i]['days'], 0, ',', '.') . '[/td]';
                        $rmlpage .= '[/tr]';
                }
                $rmlpage .= '[/table]' . "\n";
        }

        $o = new Opertunities($db, $project->getPrefix() . '_teamOffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly', $project->getTeamName());
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
                        $rmlpage .= '[td width="190" align="left"][url="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamOffset' . $tableSuffix . '&amp;naam=' . rawurlencode($ol[$i]['name']) . '&amp;datum=' . $datum . '"]' . $ol[$i]['name'] . '[/url][/td]';
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
                $rmlpage .= '[td align="left" width="70%"][url="' . $baseUrl . '/index.php?mode=detail&amp;tabel=memberOffset' . $tableSuffix . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '&amp;naam=' . rawurlencode($joins[$i]['name']) . '"]' . $joins[$i]['name'] . '[/url][/td]';
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
*/?>
</body>
</html>
