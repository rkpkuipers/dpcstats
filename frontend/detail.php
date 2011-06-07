<?php

# Strip slashes from the name to enable db lookup of the member
$naam = stripslashes($naam);

# Check if a naam was specified
if ( ( ! isset($naam) ) || ( empty($naam) ) )
{
	echo '<h3>&nbsp;ERROR: Geen member opgegeven</h3>';
	return;
}

# Gather member info
$mi = new MemberInfo($db, $naam, $project->getPrefix() . '_' . $tabel, $datum, $project->getPrefix(), $tabel, $team);

# Check if the member exists
if ( ! $mi->exists() )
{
	echo '<h3>&nbsp;ERROR: Member ' . $naam . ' bestaat niet</h3>';
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

# Center the content
echo '<center>';

# Determine the page of the flushlist the member is on
$rankPage = ( ( floor((($mi->getRank())-1)/30) ) * 30 );

# Data table
echo '<table style="width:400px" class="colorbox">';
echo '<tr><td width=300 align="left">' . $project->getWuName() . '</td><td width=90 align=right>' . number_format($mi->getCredits(), 0, ',', '.') . '</td></tr>';
echo '<tr><td colspan="2"><hr></td></tr>';
echo '<tr><td align="left">Todays output</td><td align=right>' . number_format($mi->getFlush(), 0, ',', '.') . '</td></tr>';
echo '<tr><td align="left">Team Rank</td><td align=right><a href="index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;dlow=0&amp;datum=' . $datum . '&amp;team=' . rawurlencode($team) . '&amp;hl=' . rawurlencode($naam) . '&amp;low=' . $rankPage . '#Ranking" title="Overall ranking hightlighting ' . $naam . '">' . $mi->getRank() . '</a></td></tr>';
echo '<tr><td align="left">Flush rank</td><td align=right>' . $mi->getDailyRank() . '</td></tr>';
echo '<tr><td align="left">Average Daily Pos</td><td align=right>' . number_format($mi->getAvgDailyPos(), 1, ',', '.') . '</td></tr>';
echo '<tr><td align="left">Increase</td>';
echo '<td align=right>' . number_format($mi->getIncrease(), 2, ',', '.') . ' %</td></tr>';

# Show additional data for SoB
if ( $project->getPrefix() == 'sob' )
	echo '<tr><td align="left">' . $project->getAdditional() . '</td><td align=right>' . number_format($mi->getNodes(), 0, ',', '.') . '</td></tr>';

# Data table
echo '<tr><td colspan="2"><hr></td></tr>';
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
echo '<tr><td colspan="2"><hr></td></tr>';
echo '<tr><td align="left">Largest Flush</td><td align=right>' . number_format($mi->getLargestFlush(), 0, ',', '.') . '</td></tr>';
echo '<tr><td align="left">Flush Date</td><td align=right><a href="index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $mi->getLargestFlushDate() . '&amp;team=' . rawurlencode($team) . '" title="Flush list for ' . $mi->getLargestFlushDate() . '">' . $mi->getLargestFlushDate() . '</a></td></tr>';

# Close the data table
echo '</table>';

# Spacer
echo '<br>';

# Gather flush history
$flushHistory = $mi->getFlushHistory(7);

# Display a graphical overview of the production
echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($naam) . '&amp;team=' . rawurlencode($team) . '" alt="History">';

# Spacer
echo '<div><br></div>';

# Table for the flush history
echo '<table class="colorbox" width="400px">';
echo '<tr><td colspan="3" style="text-align:center"><b>Flush History</b></td></tr>';
echo '<tr><td width="33%">Date</td><td width="33%">Output</td><td width="33%">Daily Rank</td></tr>';

# Counter
$pos = 0;

# Loop through the flush history
foreach($flushHistory as $flush)
{
	echo trBackground($pos++);
	echo '<td width="60%">' . date("d-m-Y", strtotime($flush['date'])) . '</td>';
	echo '<td align="right" width="40%">' . number_format($flush['flush'], 0, ',', '.') . '</td>';
	echo '<td>' . $flush['flushrank'] . '</td>';
	echo '</tr>';
}

# More link
echo '<tr><td colspan="3" style="text-align:right"><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . 
	'&amp;team=' . rawurlencode($team) . '&amp;naam=' . $naam . '" title="Complete flush history">More...</a></td></tr>';
echo '</table>';

# Spacer
echo '<br>';

if ( $frame == 'm' )
{
	echo '<br>';
	echo '<img src="graphs/monthlyOutput.php?naam=' . $naam . '&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '">';
	echo '<br><br>';
	echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;naam=' . rawurlencode($naam) . 
		'&amp;team=' . rawurlencode($team) . '&amp;timespan=31" alt="History">';
	
	# Spacer
	echo '<div><br></div>';
}

$opp = new Oppertunities($naam, $team, $project, $db);

$info = $opp->getOppList();

echo '<div class="colorbox" style="width:400px">';
echo '<center><b>Average output</b></center>';
echo '<table width="100%">';
echo '<tr><td align="left">Last week</td><td align="right">' . number_format($info['average'], 0, ',', '.') . '&nbsp;' . $project->getWuName() . '</td></tr>';
echo '<tr><td align="left">Last month</td><td align="right">' . number_format($info['maverage'], 0, ',', '.') . '&nbsp;' . $project->getWuName() . '</td></tr>';
echo '</table>';
if ( count($info['opp']) > 0 )
{
	echo '<hr width="75%">';
	echo '<center><b>Opportunities</b></center>';
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
if ( ( isset($info['thr']) ) && ( count($info['thr']) > 0 ) )
{
	echo '<hr width="75%">';
	echo '<center><b>Threats</b></center>';
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
echo '</div>';

# Close the centration tag
echo '</center>';

?>