<?

include ('../classes.php');

if ( isset($_GET['project']) )
	$project = $_GET['project'];
else
	$project = 'tsc';

$datum = date("Y-01-01", strtotime(date("Y-m-d", strtotime("-1 year"))));

$yearStart = date("Y", strtotime($datum));
$yearEnd   = date("Y-12-31", strtotime(date("Y-m-d", strtotime("-1 year"))));

# Set locale to provide dutch names for days, months and such
setLocale(LC_ALL, 'nl_NL');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
<title><?php echo strtoupper($project);?> DPCH</title>
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


$ts = new TableStatisticsYearly($project . '_memberoffset', $datum, $db);
$ts->gather();

$page .= '<div style="font-weight: bold">DPC ' . strtoupper($project) . ' jaar-hitparade van ' . strftime('%Y', strtotime($datum)) . '</div>';
$page .= '<div class="dpch">';
$page .= '<p>Members Top 30</p>';
$page .= '<span style="color: #000000">Flushers: ' . $ts->getDailyFlushers() . ' / ' . number_format($ts->getTotalMembers(), 0, ',', '.') . ' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 1, ',', '.') . ' %)</span><br /><br />';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th>pos</th>';
$page .= '<th class="ri">score</th>';
$page .= '<th>member</th>';
$page .= '<th class="ri">total</th>';
$page .= '</tr>';

$ml = new MemberList($project . '_memberoffset', $datum, 0, 30, $db);

$ml->generateYearlyFlushList($yearStart, $yearEnd);
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
	$page .= '<tr>';
	$page .= '<td class="ri">' . ( $pos ) . '.</td>';

/*
	$change = $mbs[$i]->getYesterday() - ( $pos );

	if ( $change > 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change ) . ')</td>';
	elseif ( $change == 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
	elseif ( $change < 0 )
		$page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';
*/

	$page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
	$page .= '<td class="ri">(' . $mbs[$i]->getRank() . ')</td>';
	$page .= '</tr>';
}

#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;datum=' . $datum . '&amp;tabel=memberoffset">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Members Overall Top 30</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th class="ri">total</th>';
$page .= '<th>member</th>';
$page .= '<th class="ri">score</th>';
$page .= '</tr>';

$ml->generateYearlyRankList($yearStart, $yearEnd);
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
	$page .= '<td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;mode=detail&amp;tabel=memberoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
	$page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	$page .= '<td class="ri">'./*(' . $mbs[$i]->getFlushRank() . ')*/'</td>';
	$page .= '</tr>';
}

#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;datum=' . $datum . '&amp;tabel=memberoffset">More...</a></td></tr>';
$page .= '</table>';
$page .= '<p>Teams Top 15</p>';
$page .= '<table>';
$page .= '<tr>';
$page .= '<th colspan="2">pos</th>';
$page .= '<th>score</th>';
$page .= '<th>team</th>';
$page .= '<th colspan="2">total</th>';
$page .= '</tr>';

$ml = new MemberList($project . '_teamoffset', $datum, 0, 15, $db);

$ml->generateYearlyFlushList($yearStart, $yearEnd);
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
        $pos = $i + 1;
        $page .= '<tr>';
        $page .= '<td class="ri">' . ( $pos ) . '.</td>';

/*
        $change = $mbs[$i]->getYesterday() - ( $pos );

        if ( $change > 0 )
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/up.gif" alt="+" title="up" />' . ( $change ) . ')</td>';
        elseif ( $change == 0 )
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/stil.gif" alt="-" title="stil" />)</td>';
        elseif ( $change < 0 )
                $page .= '<td class="le">(<img src="http://www.tweakers.net/g/dpc/down.gif" alt="-" title="down" />' . ( $change - ( $change * 2 )) . ')</td>';
*/

        $page .= '<td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
        $page .= '<td><a href="' . $baseUrl . '?prefix=' . $project . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . 
'&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '<td class="ov">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
        $page .= '<td class="ri">(' . $mbs[$i]->getRank() . ')</td>';
        $page .= '</tr>';
}
#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;datum=' . $datum . '&amp;tabel=teamoffset">More...</a></td></tr>';
$page .= '</table>';

$page .= '<p>Teams Overall Top 15</p>';
$page .= '<table>';
$page .= '    <tr>';
$page .= '     <th colspan="2">pos</th>';
$page .= '     <th>total</th>';
$page .= '     <th>team</th>';
$page .= '     <th colspan="2">score</th>';
$page .= '    </tr>';

$ml->generateYearlyRankList($yearStart, $yearEnd);
$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
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
        $page .= '    <td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;mode=detail&amp;tabel=teamoffset&amp;datum=' . $datum . '&amp;naam=' . $mbs[$i]->getName() . '">' . $mbs[$i]->getName() . '</a></td>';
        $page .= '    <td class="da">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
        $page .= '    <td class="ri">(' . $mbs[$i]->getFlushRank() . ')</td>';
        $page .= '   </tr>';
}

#$page .= '<tr><td></td><td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;datum=' . $datum . '&amp;tabel=memberoffset">More...</a></td></tr>';
$page .= '  </table>';

$fmc = new FlushList($project . '_memberoffset', $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

$page .= '<p>Megaflush Top 5</p>';

$page .= '<table>';
for($i=0;$i<5;$i++)
{
	$page .= '<tr>';
	$page .= '<td>' . ( $i + 1 ) . '.</td>';
	$page .= '<td><a href="' . $baseUrl . '/index.php?prefix=' . $project . '&amp;mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $fl[$i]->getName() . '">' . $fl[$i]->getName() . '</a></td>';
	$page .= '<td>' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</td>';
	$page .= '</tr>';
}
$page .= '<tr><td><a href="' . $baseUrl . '/?prefix=' . $project . '&amp;mode=Flush">More...</a></td></tr>';
$page .= '</table>';

$mi = new MemberInfo($db, 'Dutch Power Cows', $project . '_teamoffset', $datum, $project, 'teamoffset', '');

if ( $mi->getFlush() > 0 )
{
	$query = 'SELECT 
			avgMonthly 
		FROM 
			averageproduction 
		WHERE 
			naam = \'Dutch Power Cows\' 
		AND 	tabel = \'' . $project . '_teamoffset\'';
		
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

        $t = new TOThreats($project . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly');
	$tl = $t->getThreatList();;

        if ( count($tl) > 0 )
        {
                $page .= '<p>When do they get you</p>';
                $page .= '<table>';
                $page .= '<tr><th align="left">Name</th><th class="ri">Average</th><th class="ri">Days</th></tr>';
                for($i=0;$i<count($tl);$i++)
                {
                        $page .= '<tr>';
                        $page .= '<td width="190" align="left"><a href="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $tl[$i]->getName() . '&amp;prefix=' . $project . '&amp;datum=' . $datum . '">' . $tl[$i]->getName() . '</a></td>';
                        $page .= '<td width="50" align="right">' . number_format($tl[$i]->getAverage(), 0, ',', '.') . '</td>';
                        $page .= '<td align="right" width="50">' . number_format($tl[$i]->getDagen(), 0, ',', '.') . '</td>';
                        $page .= '</tr>';
                }
                $page .= '</table>';
        }

        $o = new Opertunities($project . '_teamoffset', $lineArray[0], $mi, $datum, 10, 'avgMonthly');
        $ol = $o->getOpertunityList();;
        if ( count($ol) > 0 )
        {
                $page .= '<p>When do you get them</p>';
                $page .= '<table width="100%">';
                $page .= '<tr><th align="left">Name</th><th align="right">Avg.</th><th align="right">Days</th></tr>';
                for($i=0;$i<count($ol);$i++)
                {
                        $page .= '<tr>';
                        $page .= '<td width="190" align="left"><a href="' . $baseUrl . '/index.php?mode=detail&amp;prefix=' . $project . '&amp;tabel=' . $tabel . '&amp;naam=' . $ol[$i]->getName() . '&amp;datum=' . $datum . '">' . $ol[$i]->getName() . '</a></td>';
                        $page .= '<td width="50" align="right">' . number_format($ol[$i]->getAverage(), 0, ',', '.') . '</td>';
                        $page .= '<td align="right" width="50">' . number_format($ol[$i]->getDagen(), 0, ',', '.') . '</td>';
                        $page .= '</tr>';
                }
                $page .= '</table>';
        }
}


$prj = new Project($db, $project, 'memberoffset', $datum);

$page .= '<p>' . strtoupper($project) . ' Links</p>';
$page .= '<a href="' . $prj->getWebsite() . '">' . strtoupper($project) . ' webpage</a><br />';
$page .= '<a href="' . $prj->getForum() . '">' . strtoupper($project) . ' forum</a><br />';
$page .= '<a href="http://www.dutchpowercows.org/doc.php?id=316">DPCH Suggestiepagina</a><br />';
$page .= '<a href="' . $baseUrl . '/?prefix=' . $project . '">Bron</a>';

$page .= '</div>';

#echo strlen($page);
echo $page;

echo '<br /><hr />';

echo 'Voor de statsposters:<br /><br /><input type="text" style="width:700px" value="[' . strtoupper($project) . '] jaar-hitparade van ' . strftime('%Y', strtotime($datum)) . '" />';

?>
<br /><br /><textarea style="width:700px" rows="12" cols="85">{verhaal}[norml]<?php echo htmlentities($page); ?>[/norml]</textarea>

</body>
</html>
