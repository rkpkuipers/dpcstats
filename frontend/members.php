<?php
if ( $tabel == 'subteamoffset' )
	$ts = new STTableStatistics($project->getPrefix() . '_' . $tabel, $datum, $db, $team);
else
	$ts = new TableStatistics($project->getPrefix() . '_' . $tabel, $datum, $db);
$ts->gather();

if ( $mode == 'Subteam' )
	echo '<center><h3>' . $team . '</h3></center>';
else
	echo '<br>';

if ( $ts->getDailyFlushers() > 0 )
{
echo '<div class="colorbox">';
?>
<h4>&nbsp;Today</h4>
<hr>
<table width="100%">
<tr>
<td align="left">Flushers Today</td>
<?php
echo '<td align="right">' . number_format($ts->getDailyFlushers(), 0, ',', '.') . '/' . number_format($ts->getTotalMembers(), 0, ',', '.') . ' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 0, ',', '.') . ' %)</td>';

echo '</tr>';
echo '<tr>';
echo '<td align="left">Current Output</td>';
echo '<td align="right">' . number_format($ts->getDailyOutput(), 0, ',', '.') . ' ' . $project->getWuName() . '</td>';
?>
</tr>
</table>
<hr>
<table width="100%"><tr>
<?php

if ( $ts->getDailyFlushers() > $listsize )
{
	echo '<td align="left">';
	echo 'Page: ';
	echo '<form action="index.php" method="get">';
	echo '<p>';
	echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
	echo '<input type="hidden" name="low" value="' . $low . '">';
	echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '<input type="hidden" name="datum" value="' . $datum . '">';
	if ( $mode == 'Subteam' )
	{
		echo '<input type="hidden" name="mode" value="Subteam">';
		echo '<input type="hidden" name="team" value="' . $team . '">';
	}
	echo '<select name="dlow" class="TextField">';
	
	for($i=0;($i*$listsize)<$ts->getDailyFlushers();$i++)
	{
		echo '<option value="' . ( $i * $listsize ) . '"';
		if ( ( $flushlist == 0 ) && ( $dlow > 0 ) && ( ($i) == ($dlow/$listsize) ) )
			echo ' selected';
		
		echo '>' . ( $i + 1 ) . '</option>';
	}
	echo '</select>';
	echo '&nbsp;';
	echo '<input type="submit" value="Go" class="TextField">';
	echo '</p>';
	echo '</form>';
        echo '<form action="index.php" method="get">';
        echo '<p>';
        echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
        echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
        echo '<input type="hidden" name="low" value="0">';
	echo '<input type="hidden" name="team" value="' . $team . '">';
        echo '<input type="hidden" name="dlow" value="0">';
        echo '<input type="hidden" name="flushlist" value="2">';
        echo '<input type="hidden" name="datum" value="' . $datum . '">';
        echo '<input type="submit" value="List" class="TextField">';
        echo '</p>';
        echo '</form>';
	echo '</td>';
}
?>
<td align="right">
<form name="Daily" action="graphs/dailyBars.php" method="post">
<p>
<input type="hidden" name="tabel" value="<?php echo $tabel ?>">
<input type="hidden" name="prefix" value="<?php echo $project->getPrefix() ?>">
<input type="hidden" name="team" value="<?php echo $team; ?>">
</p>
<?php
#echo '<td align="right">
echo '<INPUT TYPE="image" SRC="images/graph.jpg" value="Graph"></td>';
echo '</tr></table>';
echo '<hr>';

if ( $flushlist == 2 )
	$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, 0, $ts->getDailyFlushers(), $db, $team);
else
	$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, $dlow, $listsize, $db, $team);

$ml->generateFlushList();
$mbs = $ml->getMembers();
echo "<table style=\"white-space:nowrap;\">";

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;

	if ( $hl != $mbs[$i]->getName() )
	{
		if ( ( $i == 0 ) && ( $hl != '' ) )
			echo trBackground(2);
		else
			echo trBackground($i);
	}
	else
		echo trBackground(0);

        echo '<td align="right" width="30">' . ( $pos + $dlow ) . '.</td>';

        $change = $mbs[$i]->getYesterday() - ( $pos + $dlow );

        if ( $change == 0 )
        {
                $image = '<img src="images/yellow.gif" alt="yellow">';
                $change = '';
        }
        elseif ( $change < 0 )
        {
                $image = '<img src="images/red.gif" alt="red">';
                $change = $change - ( $change * 2 );
        }
        elseif ( $change > 0 )
	{
		if ( ( $change + ( $pos + $dlow ) ) > $ts->getPrevDayFlushCount() )
			$change = "";
                $image = '<img src="images/green.gif" alt="green">';
	}

        echo '<td align="center" width="30">' . $image . $change . '</td>';
	echo '<td align="right" width="65" class="score">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	echo '<td align="right" width="65" style="font-size:10px;">(' . number_format($mbs[$i]->getFlush() / ( $ts->getDailyOutput() / 100 ), 2, ',', '.') . ' %)</td>';
	
	if ( $ml->getSubteamCount() > 0 )
	{
		echo '<td>';
		if ( $mbs[$i]->isSubteam() )
		{
			echo getURL(array('link' => '<img src="images/members.gif" alt="subteam">',
					'mode' => 'Subteam', 'team' => $mbs[$i]->getName(), 'table' => 'subteamoffset',
					'date' => $datum, 'prefix' => $project->getPrefix(),
					'title' => 'Subteam stats for ' . $mbs[$i]->getName()));
		}
		else
			echo '<img src="images/blank.gif" height=11 width="10" alt="">';

		echo '</td>';
	}
	echo '<td align="left" width="230">';
	echo getURL(array('link' => $mbs[$i]->getName(), 'name' => $mbs[$i]->getName(), 'mode' => 'detail', 'title' => 'Details for ' . $mbs[$i]->getName()));
	echo '</td>';

	echo '<td align="right" width="70" class="altScore">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';

	$rankPage = ( ( floor((($mbs[$i]->getCurrRank())-1)/30) ) * 30 );
	echo '<td align="right" width="35">(';
	
	if ( $low == $rankPage )
		echo $mbs[$i]->getCurrRank();
	else
		echo '<a title="Overall ranking highlighting ' . $mbs[$i]->getName() . '" href="index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;dlow=0&amp;datum=' . $datum . '&amp;low=' . $rankPage . '&amp;hl=' . $mbs[$i]->getName() . '#Ranking">' . $mbs[$i]->getCurrRank() . '</a>';
	
	echo ')</td>';
	
	echo '<td><input class="TextField" type="checkbox" name="teams[]" value="' . $mbs[$i]->getName() . '"></td>';
        echo '</tr>';
	$pos++;
}
echo '</form>';
echo '</table>';
echo '</div>';
}
?>
<br>
<?php
echo '<div class="colorbox">';
?>
<h4><a name="Ranking">&nbsp;Ranking</a></h4>
<hr>
<table width="100%">
<?php
echo '<tr><td>Total Output</td><td align="right">' . number_format($ts->getTotalOutput(), 0, ',', '.') . ' ' . $project->getWuName() . '</td></tr>';
?>
</table>
<hr>
<table width="100%">
<tr>
<td align="left" valign="middle">
<?php
if ( $ts->getTotalMembers() > $listsize )
{
	echo '<td align="left">';
        echo 'Page:';
	echo '<form action="index.php" method="get">';
	echo '<p>';
	echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
	echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '<input type="hidden" name="dlow" value="' . $dlow . '">';
	echo '<input type="hidden" name="datum" value="' . $datum . '">';
	echo '<input type="hidden" name="team" value="' . $team . '">';
	if ( $mode == 'Subteam' )
		echo '<input type="hidden" name="mode" value="Subteam">';
	echo '<select name="low" class="TextField">';

        for($i=0;($i*$listsize)<$ts->getTotalMembers();$i++)
        {
		echo '<option value="' . ( $i * $listsize ) . '"';
                if ( ( $flushlist == 0 ) && ( ($i) == ($low/$listsize) ) )
                        echo ' selected';

		echo '>' . ( $i + 1 ) . '</option>';
	}
 	echo '</select>';
	echo '&nbsp;';
	echo '<input type="submit" value="Go" class="TextField">';
	echo '</p>';
	echo '</form>';
 	echo '<form action="index.php" method="get">';
	echo '<p>';
	echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
	echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '<input type="hidden" name="low" value="0">';
	echo '<input type="hidden" name="team" value="' . $team . '">';
	echo '<input type="hidden" name="dlow" value="0">';
	echo '<input type="hidden" name="flushlist" value="1">';
	echo '<input type="hidden" name="datum" value="' . $datum . '">';
	echo '<input type="submit" value="List" class="TextField">';
	echo '</p>';
	echo '</form>';
}

?>
<form name="progress" method="post" action="index.php">
<input type="hidden" name="tabel" value="<?php echo $tabel?>">
<input type="hidden" name="prefix" value="<?php echo $project->getPrefix() ?>">
<input type="hidden" name="mode" value="Graph">
<input type="hidden" name="team" value="<?php echo $team; ?>">
</td>
<td align="right"><INPUT TYPE="image" SRC="images/graph.jpg" value="Graph"></td>
</tr>
</table>
<hr>

<table border="0">

<?php
if ( $flushlist == 1 )
	$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, 0, $ts->getTotalMembers(), $db, $team);
else
	$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, $low, $listsize, $db, $team);
$ml->generateRankList();

$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
#	echo trBackground($i);
        if ( $hl != $mbs[$i]->getName() )
        {
                if ( ( $i == 0 ) && ( $hl != '' ) )
                        echo trBackground(2);
                else
                        echo trBackground($i);
        }
        else
                echo trBackground(0);

	$pos = $i + 1;
        $change = $mbs[$i]->getRank() - ( $pos + $low );
#	echo '<td>'.$mbs[$i]->getRank().'</td><td>'.($pos+$low).'</td>';

        if ( $change == 0 )
        {
                $image = '<img src="images/yellow.gif" alt="yellow">';
                $change = '';
        }
        elseif ( $change < 0 )
        {
                $image = '<img src="images/red.gif" alt="red">';
                $change = $change - ( $change * 2 );
        }
        elseif ( $change > 0 )
                $image = '<img src="images/green.gif" alt="green">';

        echo '<td align="right" width="30">' . ( $pos + $low ) . '.</td>';
	echo '<td align="center" width="30">' . $image . $change . '</td>';
	echo '<td align="right" width="75" class="score">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</td>';
	echo '<td align="right" width="75" style="font-size:10px">(' . number_format($mbs[$i]->getCredits() / ( $ts->getTotalOutput() / 100 ), 2, ',', '.') . ' %)</td>';
	if ( $ml->getSubteamCount() > 0 )
	{
		echo '<td>';
		if ( $mbs[$i]->isSubteam() )
		{
			echo getURL(array('link' => '<img src="images/members.gif" alt="subteam">',
				'mode' => 'Subteam', 'team' => $mbs[$i]->getName(), 'date' => $datum,
				'prefix' => $project->getPrefix(), 'table' => 'subteamoffset',
				'title' => 'Subteam stats for ' . $mbs[$i]->getName()));
		}
		else
			echo '<img src="images/blank.gif" height="11" width="10" alt="blank">';
		echo '</td>';
	}

	echo '<td align="left" width="300">';
	echo getURL(array('link' => $mbs[$i]->getName(), 'prefix' => $project->getPrefix(), 
			'table' => $tabel, 'date' => $datum, 'name' => $mbs[$i]->getName(), 'team' => $team,
			'mode' => 'detail', 'title' => 'Details for ' . $mbs[$i]->getName()));
	echo '<td align="right" width="65" class="altScore">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</td>';
	echo '<td align="right" width="35">(' . $mbs[$i]->getFlushRank() . ')</td>';
	echo '<td><input class="TextField" type="checkbox" name="teams[]" value="' . $mbs[$i]->getName() . '"></td>';
        echo '</tr>';
}

?>
</form>
</table>
</div>

<?php
$mp = new MijlPalen($project->getPrefix() . '_' . $tabel, $datum, $project->getPrefix(), $db, $team);
$mpl = $mp->getMijlpalen();
if ( count($mpl) > 0 )
{
	echo '<br>';
	echo '<div class="colorbox" style="width:350px; margin-left:auto; margin-right:auto;">';
	echo '<h4>&nbsp;' . count($mpl) . ' Milestone';
	if ( count($mpl) != 1 )
		echo 's';
	echo '</h4>';
	echo '<hr>';
	echo '<table width="100%">';
	for($i=0;$i<count($mpl);$i++)
	{
		echo trBackground($i);
		echo '<td align="left" width="70%">';
		echo getURL(array('link' => $mpl[$i]->getName(), 'mode' => 'detail', 
			'name' => $mpl[$i]->getName(), 'title' => 'Details for ' . $mpl[$i]->getName()));
		echo '</td>';
		echo '<td align="right" width="30%">' . number_format($mpl[$i]->getCredits(), 0, ',', '.') . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</div>';
	echo '<div><br></div>';
}

# Initialize Joins/Leaves class
$movement = new Movement($db, $project->getPrefix() . '_' . $tabel, $datum);

$leaves = $movement->getMembers(0);

if ( count($leaves) > 0 )
{
        echo '<br>';
        echo '<div class="colorbox" style="width:350px; margin-left:auto; margin-right:auto;">';
        echo '<h4>&nbsp;' . count($leaves) . ' Retired Member';
        if ( count($leaves) != 1 )
                echo 's';
        echo ' ( ' . number_format($movement->getTotalCredits(0), 0, ',', '.') . ' ' . $project->getWuName() . ' )</h4>';
        echo '<hr>';
        echo '<table width="100%">';
        for($i=0;$i<count($leaves);$i++)
        {
                echo trBackground($i+1);
                echo '<td align="left" width="70%">' . $leaves[$i]['name'] . '</td>';
                echo '<td align="right" width="30%">' . number_format($leaves[$i]['credits'], 0, ',', '.') . '</td>';
                echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '<div><br></div>';
}

# Get joins
$joins = $movement->getMembers(1);

if ( count($joins) > 0 )
{
        echo '<br>';
        echo '<div class="colorbox" style="width:350px; margin-left:auto; margin-right:auto;">';
        echo '<h4>&nbsp;' . count($joins) . ' New Member';
        if ( count($joins) != 1 )
                echo 's';
        echo ' ( ' . number_format($movement->getTotalCredits(1), 0, ',', '.') . ' ' . $project->getWuName() . ' )</h4>';
        echo '<hr>';
        echo '<table width="100%">';
        for($i=0;$i<count($joins);$i++)
        {
                echo trBackground($i+1);
		echo '<td align="left" width="70%">';
		echo getURL(array('link' => $joins[$i]['name'], 'date' => $datum, 'team' => $team, 
			'table' => $tabel, 'prefix' => $project->getPrefix(), 'mode' => 'detail',
			'name' => $joins[$i]['name'], 'title' => 'Details for ' . $joins[$i]['name']));
		echo '</td>';
                echo '<td align="right" width="30%">' . number_format($joins[$i]['credits'], 0, ',', '.') . '</td>';
                echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '<div><br></div>';
}

unset($leaves, $joins, $movement);

?>