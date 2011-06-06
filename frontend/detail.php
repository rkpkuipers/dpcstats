<?php

# Strip slashes from the name to enable db lookup of the member
$naam = stripslashes($naam);

if ( ! isset($naam) )
{
	echo '<h3>Geen member opgegeven, kies een member uit de lijst of ga terug naar de <a href="#" onclick="history.go(-1)">vorige pagina</a></h3>';
	echo '<br>';
	getMemberList($project->getPrefix(), $tabel, $datum);
	return;
}

$mi = new MemberInfo($db, $naam, $project->getPrefix() . '_' . $tabel, $datum, $project->getPrefix(), $tabel, $team);

if ( ! $mi->exists() )
{
	echo '<h3>Member: ' . $naam . ' komt niet voor in de database, kies een member uit de lijst of ga terug naar de <a href="#" onclick="history.go(-1)">vorige pagina</a></h3>';
	getMemberList($project->getPrefix(), $tabel, $datum);
	return;
}

?>
<br>
<center><h2><?php echo str_replace('\\', '', $mi->getNaam()) ?></h2></center>
<hr>
<?php
        if ( $mi->isSubteam() )
        {
                echo '<br>';
		echo '<center>';
                echo '<form action="index.php" method="get">';
                echo '<input type="hidden" name="mode" value="Subteam">';
                echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
                echo '<input type="hidden" name="team" value="' . $naam . '">';
                echo '<input type="hidden" name="tabel" value="subteamoffset">';
                echo '<input type="submit" class="TextField" value="Members" title="Substeamstats for ' . $naam . '">';
                echo '</form>';
                echo '<br>';
                echo '<hr>';
		echo '</center>';
        }

?>
<center>
<?php
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
if ( in_array($project->getPrefix(), array('tsc', 'sob') ) )
{
	echo '<tr><td align="left">' . $project->getAdditional() . '</td><td align=right>' . number_format($mi->getNodes(), 0, ',', '.') . '</td></tr>';
	#echo '<tr><td align="left">Average node output overall</td><td align=right>' . number_format($mi->getANOOverall(), 2, ',', '.') . '</td></tr>';
	#echo '<tr><td align="left">Average node output today</td><td align=right>' . number_format($mi->getANOToday(), 2, ',', '.') . '</td></tr>';
}
echo '</table><hr><table border="0" width="100%">';

echo '<tr><td align="left">Distance</td></tr>';

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

$opp = new Oppertunities($naam, $team, $project, $db);

$info = $opp->getOppList();

echo openColorTable();
echo '<center><b>Average output</b></center>';
echo '<hr width="75%">';
echo '<table width="420px">';
echo '<tr><td align="left">Last week</td><td align="right">' . number_format($info['average'], 0, ',', '.') . '&nbsp;' . $project->getWuName() . '</td></tr>';
echo '<tr><td align="left">Last month</td><td align="right">' . number_format($info['maverage'], 0, ',', '.') . '&nbsp;' . $project->getWuName() . '</td></tr>';
echo '</table>';
if ( count($info['opp']) > 0 )
{
	echo '<hr width="75%">';
	echo '<center><b>Opportunities</b></center>';
	echo '<hr width="75%">';
	echo '<table>';
	echo '<tr><td width="200px"></td><td colspan="2">Weekly</td><td colspan="2">Monthly</td></tr>';
	echo '<tr><td>User</td><td>Avg.</td><td>Days</td><td>Avg.</td><td>Days</td></tr>';
	$cnt = 0;
	foreach($info['opp'] as $opp)
	{
		echo trBackground($cnt++);
		echo '<td align="left">' . getURL(array(	'name' => $opp['name'],
								'link' => $opp['name'],
								'title' => 'Details for ' . $opp['name'])) . '</td>';
		echo '<td align="right" width="50px">' . number_format($opp['average'], 0, ',', '.') . '</td>';
		echo '<td align="right" width="50px">' . round($opp['days']) . '</td>';
		echo '<td align="right" width="50px">' . number_format($opp['maverage'], 0, ',', '.') . '</td>';
		echo '<td align="right" width="50px">' . ($opp['mdays']<0?'-':number_format(round($opp['mdays']), 0, ',', '.')) . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
if ( count($info['thr']) > 0 )
{
	echo '<hr width="75%">';
	echo '<center><b>Threats</b></center>';
	echo '<hr width="75%">';
	echo '<table>';
	echo '<tr><td width="200px"></td><td colspan="2">Weekly</td><td colspan="2">Monthly</td></tr>';
	echo '<tr><td>User</td><td>Avg.</td><td>Days</td><td>Avg.</td><td>Days</td></tr>';
	$cnt = 0;
	foreach($info['thr'] as $thr)
	{
		echo trBackground($cnt++);
		echo '<td align="left">' . getUrl(array(	'name' => $thr['name'],
								'tabel' => $tabel,
								'link' => $thr['name'],
								'title' => 'Details for ' . $thr['name'])) . '</td>';
		echo '<td align="right" width="50px">' . number_format($thr['average'], 0, ',', '.') . '</td>';
		echo '<td align="right" width="50px">' . round($thr['days']) . '</td>';
		echo '<td align="right" width="50px">' . number_format($thr['maverage'], 0, ',', '.') . '</td>';
		echo '<td align="right" width="50px">' . number_format(round($thr['mdays']), 0, ',', '.') . '</td>';
		echo '</tr>';
	}
	echo '</table>';
}
echo '</td></tr></table>';
echo '</td></tr></table>';

unset($opp, $info);

?>
</center>
