<?php

if ( ! isset($naam) )
{
	echo '<h3>Geen member opgegeven, kies een member uit de lijst of ga terug naar de <a href="#" onclick="history.go(-1)">vorige pagina</a></h3>';
	echo '<br>';
	getMemberList($project->getPrefix(), $tabel, $datum);
	return;
}

$mi = new MemberInfo($db, $naam, $project->getPrefix() . '_' . $tabel, $datum, $project->getPrefix(), $tabel, $team);

if ( $mi->getCredits() == 0 )
{
	echo '<h3>Member: ' . $naam . ' komt niet voor in de database, kies een member uit de lijst of ga terug naar de <a href="#" onclick="history.go(-1)">vorige pagina</a></h3>';
	getMemberList($project->getPrefix(), $tabel, $datum);
	return;
}

?>
<br>
<center><h2><? echo str_replace('\\', '', $mi->getNaam()) ?></h2></center>
<hr>
<?
        if ( $mi->isSubteam() )
        {
                echo '<br>';
		echo '<center>';
                echo '<form action="index.php" method="get">';
                echo '<input type="hidden" name="mode" value="Subteam">';
                echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
                echo '<input type="hidden" name="team" value="' . $naam . '">';
                echo '<input type="hidden" name="tabel" value="subteamoffset">';
                echo '<input type="submit" class="TextField" value="Members">';
                echo '</form>';
                echo '<br>';
                echo '<hr>';
		echo '</center>';
        }

?>
<center>
<?
echo openColorTable();
?>	
<table border="0">
<?php
echo '<tr><td width=200 align="left">' . $project->getWuName() . '</td><td width=90 align=right>' . number_format($mi->getCredits(), 0, ',', '.') . '</td></tr>';
echo '</table><hr><table border="0" width="100%">';
echo '<tr><td align="left">Todays output</td><td align=right>' . number_format($mi->getFlush(), 0, ',', '.') . '</td></tr>';

$rankPage = ( ( floor((($mi->getRank())-1)/30) ) * 30 );

echo '<tr><td align="left">Team Rank</td><td align=right><a href="index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;dlow=0&amp;datum=' . $datum . '&amp;team=' . rawurlencode($team) . '&amp;hl=' . rawurlencode($naam) . '&amp;low=' . $rankPage . '#Ranking" title="Overall ranking hightlighting ' . $naam . '">' . $mi->getRank() . '</a></td></tr>';
echo '<tr><td align="left">Flush rank</td><td align=right>' . $mi->getDailyRank() . '</td></tr>';
echo '<tr><td align="left">Average Daily Pos</td><td align=right>' . number_format($mi->getAvgDailyPos(), 1, ',', '.') . '</td></tr>';
echo '<tr><td align="left">Increase</td>';
echo '<td align=right>' . number_format($mi->getIncrease(), 2, ',', '.') . ' %</td></tr>';
echo '</table><hr><table border="0" width="100%">';
if ( ( ( $project->getPrefix() == 'tsc' ) || ( $project->getPrefix() == 'd2ol' ) ) && ( $tabel != 'nodeoffset' ) )
{
	echo '<tr><td align="left">Nodes</td><td align=right>' . number_format($mi->getNodes(), 0, ',', '.') . '</td></tr>';
	echo '<tr><td align="left">Average node output overall</td><td align=right>' . number_format($mi->getANOOverall(), 2, ',', '.') . '</td></tr>';
	echo '<tr><td align="left">Average node output today</td><td align=right>' . number_format($mi->getANOToday(), 2, ',', '.') . '</td></tr>';
	echo '</table><hr><table border="0" width="100%">';
}

echo '<tr><td align="left"istances</td></tr>';

echo '<tr><td align="left">To next member</td><td align=right>';

if ( $mi->getNaamNext() == '' )
	echo number_format( $mi->getDistanceNext(), 0, ',', '.');
else
	echo '<a href="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;team=' . rawurlencode($team) . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . $mi->getNaamNext() . '" title="Details for ' . $mi->getNaamNext() . '">' . number_format( $mi->getDistanceNext(), 0, ',', '.') . '</a>';
echo '</td></tr>';

echo '<tr><td align="left">From previous member</td><td align=right>';
if ( $mi->getNaamPrev() == '' )
	echo number_format($mi->getDistancePrev(), 0, ',', '.');
else
	echo '<a href="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;team=' . rawurlencode($team) . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . $mi->getNaamPrev() . '" title="Details for ' . $mi->getNaamPrev() . '">' . number_format($mi->getDistancePrev(), 0, ',', '.') . '</a>';

echo '</td></tr>';
echo '</table><hr><table border="0" width="100%">';
echo '<tr><td align="left">Largest Flush</td><td align=right>' . number_format($mi->getLargestFlush(), 0, ',', '.') . '</td></tr>';
echo '<tr><td align="left">Flush Date</td><td align=right><a href="index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $mi->getLargestFlushDate() . '&amp;team=' . rawurlencode($team) . '" title="Flush list for ' . $mi->getLargestFlushDate() . '">' . $mi->getLargestFlushDate() . '</a></td></tr>';

?>
</table>
</td></tr></table>
</td></tr></table>
<br>
<?php
echo openColorTable();

$flushHistory = $mi->getFlushHistory(7);

echo '<b>Flush History</b>';
echo '<hr>';

$output = "";
$output .= '<table width="100%">';
$output .= '<tr><td width="33%">Date</td><td width="33%">Output</td><td width="33%">Daily Rank</td></tr>';
$pos = 0;
foreach($flushHistory as $flush)
{
	$output .= trBackground($pos++);
	$output .= '<td width="60%">' . date("d-m-Y", strtotime($flush['date'])) . '</td>';
	$output .= '<td align="right" width="40%">' . number_format($flush['flush'], 0, ',', '.') . '</td>';
	$output .= '<td>' . $flush['flushrank'] . '</td>';
	$output .= '</tr>';
}
unset($pos);

$output .= '</table>';
$output .= '<hr>';
$output .= '<div align="right"><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($team) . '&amp;naam=' . $naam . '" title="Complete flush history">More...</a></div>';
$query = 'select avgdaily from averageproduction where naam=\'' . $naam . '\'';
$result = $db->selectQuery($query);
$avgDaily = 0;
if ( $line = $db->fetchArray($result) )
{
        $avgDaily += $line['avgdaily'];
}

echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($naam) . '&amp;team=' . rawurlencode($team) . '" alt="History">';
echo '<hr>';
echo $output;

closeTable(2);
echo '<br>';

if ( $frame == 'm' )
{
	echo openColorTable();
	echo '<b>Monthly Graphs</b>';
	echo '<center><hr></center>';
	echo '<br>';
	echo '<img src="graphs/monthlyOutput.php?naam=' . $naam . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '">';
	echo '<br><br>';
	echo '<img src="graphs/monthlyProgress.php?naam=' . $naam . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '">';
	echo '<br>';
	closeTable(2);
}

#if ( $mi->getFlush() > 0 )
{
$query = 'SELECT 
		avgdaily, 
		avgmonthly 
	FROM 
		averageproduction 
	WHERE 
		naam = \'' . $mi->getRealNaam() .'\' 
	AND 	tabel = \'' . $project->getPrefix() . '_' . $tabel . '\'';
$result = $db->selectQuery($query);
if ( $line = $db->fetchArray($result) )
{
	if ( $line['avgdaily'] == $line['avgmonthly'] )
		$lineArray = array($line['avgdaily']);
	else
		$lineArray = array($line['avgdaily'], $line['avgmonthly']);
}
else
	$lineArray = array(0, 0);

$charArray = array('avgdaily', 'avgmonthly');
$headArray = array('weekly', 'monthly');

for($j=0;$j<count($lineArray);$j++)
{
	$t = new TOThreats($db, $project->getPrefix() . '_' . $tabel, $lineArray[$j], $mi, $datum, $listsize, $charArray[$j], $team);
	$tl = $t->getThreatList();;

	echo '<br>';
	echo openColorTable();
	echo '<b><div align="left">Based on an average ' . $headArray[$j] . ' output of ' . number_format($t->getAverageProduction(), 0, ',' ,'.') . '</div></b>';
	echo '<hr>';
	if ( count($tl) > 0 )
	{
		echo '<b>When do they get you</b>';
		echo '<hr>';
		echo '<table width="100%">';
		echo '<tr><td align="center">Name</td><td align="center">Avg.</td><td align="center">Days</td></tr>';
		for($i=0;$i<count($tl);$i++)
		{
			echo trBackground($i);
			echo '<td width="190" align="left"><a href="index.php?mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $tl[$i]['name'] . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum . '" title="Details for ' . $tl[$i]['name'] . '">' . $tl[$i]['name'] . '</a></td>';
			echo '<td width="50" align="right">' . number_format($tl[$i]['average'], 0, ',', '.') . '</td>';
			echo '<td align="right" width="50">' . number_format($tl[$i]['days'], 0, ',', '.') . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		echo '<hr>';
	}

	$o = new Opertunities($db, $project->getPrefix() . '_' . $tabel, $lineArray[$j], $mi, $datum, $listsize, $charArray[$j], $team);
	$ol = $o->getOpertunityList();;
	if ( count($ol) > 0 )
        {
                echo '<b>When do you get them</b>';
                echo '<hr>';
                echo '<table width="100%">';
		echo '<tr><td align="center">Name</td><td align="center">Avg.</td><td align="center">Days</td></tr>';
                for($i=0;$i<count($ol);$i++)
                {
                        echo trBackground($i);
                        echo '<td width="190" align="left"><a href="index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . $ol[$i]['name'] . '" title="Details for ' . $ol[$i]['name'] . '">' . $ol[$i]['name'] . '</a></td>';
			echo '<td width="50" align="right">' . number_format($ol[$i]['average'], 0, ',', '.') . '</td>';
                        echo '<td align="right" width="50">' . number_format($ol[$i]['days'], 0, ',', '.') . '</td>';
                        echo '</tr>';
                }
                echo '</table>';
		echo '<hr>';
        }
        closeTable(2);
}
}
?>
</center>
