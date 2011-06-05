<?php
if ( isset($HTTP_GET_VARS['maand']) )
	$maand = $HTTP_GET_VARS['maand'];
else
	$maand = $datum;
?>
<br>
<center>
<table width="100%">
<tr>
 <td width="25%" align="center">
  <form action="index.php">
  <input type="hidden" name="mode" value="monthlyStats">
  <input type="hidden" name="tabel" value="memberoffset">
  <input type="Submit" value="DPC Members" class="TextField">
  <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
  </form>
 </td>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="monthlyStats">
   <input type="hidden" name="tabel" value="teamoffset">
   <input type="Submit" value="Teams" class="TextField">
   <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
   </form>
 </td>
</tr>
</table>
<hr>
</center>
<br>
<?php
#echo $datum;

error_reporting(E_ALL);

checkTable($tabel);

$ts = new TableStatisticsMonthly($project->getPrefix() . '_' . $tabel, $maand, $db);
$ts->gather();

{
	$query = 'SELECT distinct(dag) FROM ' . $project->getPrefix() . '_' . $tabel . ' WHERE dag LIKE \'%-01\' ORDER BY dag';
	$result = $db->selectQuery($query);

	echo '<br>';
	echo '<form name="maandselectie" action="index.php">';
	echo '<input type="hidden" name="mode" value="monthlyStats">';
	echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
	echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '&nbsp;<select name="maand" class="Textfield">';
	while ($line = $db->fetchArray($result) )
	{
		echo '<option value=';
		if 	( 
			 (date("m", strtotime($line['dag']))) != 
			( date("m", strtotime($project->getCurrentDate())))
			)
		{
			switch(date("m", strtotime($line['dag'])))
			{
				case 1: case 3: case 5: case 7:case 8: case 10: case 12: $mnddat = date("Y-m-31", strtotime($line['dag']));
											 break;
				case 4: case 6: case 9: case 11: $mnddat = date("Y-m-30", strtotime($line['dag']));
								 break;
				case 2: default: $mnddat = date("Y-m-28", strtotime($line['dag']));
			}
		}
		else
			$mnddat = date("Y-m-d", strtotime($project->getCurrentDate()));
		echo $mnddat;
		if ( $maand == $mnddat )echo ' selected';
		echo '>' . date("F Y", strtotime($line['dag'])) . '</option>';
	}
	echo '</select>';
	echo '<br>';
	echo '&nbsp;<input type="submit" class="Textfield" value="Show">';
	echo '</form>';

	echo '<br><hr><br>';
}
?>
<center>
<br>
<?php
echo openColorTable(); 
?>
<b>Month</b>
<hr>
<table width="100%">
<tr>
<td align="left">Flushers Today</td>
<?php
if ( $ts->getDailyFlushers() != 0 )
        echo '<td align="right">' . $ts->getDailyFlushers() . '/' . $ts->getTotalMembers() . ' (' . number_format($ts->getDailyFlushers() / ( $ts->getTotalMembers() / 100 ), 0, ',', '.') . ' %)</td>';
else
        echo '<td align="right">0/' . $ts->getTotalMembers() . ' (0%)</td>';

echo '</tr>';
echo '<tr>';
echo '<td align="left">Current output</td>';
echo '<td align="right">' . number_format($ts->getDailyOutput(), 0, ',', '.') . ' ' . $project->getWuName() . '</td>';
?>
</tr>
</table>
<hr>
<table width="100%"><tr><td align="left">
<?php
if ( $ts->getDailyFlushers() > $listsize )
{
        echo '<table><tr>';
        echo '<td>Page: </td>';
        echo '<td><form action="index.php" method="get">';
        echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
        echo '<input type="hidden" name="low" value="' . $low . '">';
        echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '<input type="hidden" name="mode" value="monthlyStats">';
	echo '<input type="hidden" name="maand" value="' . $maand . '">';
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
        echo '<input type="submit" value="Go" class="textfield">';
        echo '</form>';
        echo '</tr></table>';
}
?>
</td>
<form name="Daily" action="graphs/dailyBars.php" method="post">
<input type="hidden" name="tabel" value="<?php echo $tabel ?>">
<input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
<?php

echo '<td align=right><INPUT TYPE="image" SRC="images/graph.jpg" value="Graph"></td>';
echo '<input type="hidden" name="timespan" value="' . date("d") . '">';
echo '</tr>';
echo '</table>';
echo '<hr>';

$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, $dlow, $listsize, $db);
$ml->generateMonthlyFlushList(date("Y-m", strtotime($maand)), $maand);
$mbs = $ml->getMembers();
echo "<table>";

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i+1;
        echo trBackground($i);
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
		if ( $change > $ts->getDailyFlushers() )
			$change = "";
                $image = '<img src="images/green.gif" alt="green">';
	}

#        echo '<td align="center" width="30">' . $image . $change . '</td>';
	echo '<td align="right" width="65"><font color=red>' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</font></td>';
	echo '<td align="right" width="75">(' . number_format($mbs[$i]->getFlush() / ( $ts->getDailyOutput() / 100 ), 2, ',', '.') . ' %)</td>';
	echo '<td align="left"  width="300">';

	$naam = $mbs[$i]->getName();
	echo '<a href="index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . $mbs[$i]->getName() . '&amp;frame=m">' . $naam . '</a>';
	echo '</td>';

        echo '<td align="right" width="70"><font color="blue">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</font></td>';
	echo '<td align="right" width="35">(' . $mbs[$i]->getRank() . ')</td>';
	echo '<td><input class="TextField" type="checkbox" name="teams[]" value="' . $mbs[$i]->getName() . '"></td>';
        echo '</tr>';
	$pos++;
}

echo '</table>';
closeTable(2);
?>
</form>
<br>
<?php
echo openColorTable(); 
?>
<b><a name="Ranking">Ranking</a></b>
<hr>
<table width="100%">
<?php
$ts = new TableStatistics($project->getPrefix() . '_' . $tabel, $maand, $db);
$ts->gather();
echo '<tr><td align="left">Total Output</td><td align="right">' . number_format($ts->getTotalOutput(), 0, ',', '.') . ' ' . $project->getWuName() . '</td></tr>';
?>
</table>
<hr>
<table width="100%">
<tr>
<td align="left">
<?php
if ( $ts->getTotalMembers() > $listsize )
{
        echo '<table><tr>';
        echo '<td>Page: </td>';
        echo '<td><form action="index.php" method="get">';
        echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
        echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
        echo '<input type="hidden" name="dlow" value="' . $dlow . '">';
	echo '<input type="hidden" name="mode" value="' . $mode . '">';
	echo '<input type="hidden" name="maand" value="' . $maand . '">';
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
        echo '<input type="submit" value="Go" class="textfield">';
	/*
        echo '</form></td>';
        echo '<td><form action="index.php" method="get">';
        echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
        echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
        echo '<input type="hidden" name="low" value="0">';
	echo '<input type="hidden" name="mode" value="' . $mode . '">';a
        echo '<input type="hidden" name="dlow" value="0">';
        echo '<input type="hidden" name="fl" value="1">';
        echo '<input type="hidden" name="datum" value="' . $datum . '">';
        echo '<input type="submit" value="List" class="textfield">';
        echo '</form></td>';*/
        echo '</tr></table>';
}

?>
</td>
<form name="progress" method="post" action="graphs/progress.php">
<input type="hidden" name="tabel" value="<?php echo $tabel?>">
<input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
<td align="right"><INPUT TYPE="image" SRC="images/graph.jpg" value="Graph"></td>
<input type="hidden" name="timespan" value="<?php echo date("d"); ?>">
</tr>
</table>
<hr>

<table border="0">

<?php
$ml = new MemberList($project->getPrefix() . '_' . $tabel, $datum, $low, $listsize, $db);
$ml->generateMonthlyRankList(date("Y-m", strtotime($maand)), $maand);

$mbs = $ml->getMembers();

for($i=0;$i<count($mbs);$i++)
{
	$pos = $i + 1;
        $change = $mbs[$i]->getRank() - ( $pos + $low );

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

        echo trBackground($i);
        echo '<td align="right">' . ( $pos + $low ) . '.</td>';
	echo '<td align="center" width="30">' . $image . $change . '</td>';
	echo '<td align="right" width="75"><font color="red">' . number_format($mbs[$i]->getCredits(), 0, ',', '.') . '</font></td>';
	echo '<td align="right" width="75">(' . number_format($mbs[$i]->getCredits() / ( $ts->getTotalOutput() / 100 ), 2, ',', '.') . ' %)</td>';

        $naam = $mbs[$i]->getName();
        echo '<td align="left"  width="300"><a href="index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $mbs[$i]->getName() . '&amp;frame=m">' . $naam . '</a></td>';
	echo '<td align="right" width="65"><font color="blue">' . number_format($mbs[$i]->getFlush(), 0, ',', '.') . '</font></td>';
	
	$flushRank = $mbs[$i]->getFlushRank();
	if ( $flushRank > $ts->getDailyFlushers() )
		$flushRank = 0;
	echo '<td align="right" width="35">(' . $flushRank . ')</td>';
	echo '<td><input class="TextField" type="checkbox" name="teams[]" value="' . $mbs[$i]->getName() . '"></td>';
        echo '</tr>';
}

?>
</form>
</table>
</td></tr></table>
</td></tr></table>
</center>
