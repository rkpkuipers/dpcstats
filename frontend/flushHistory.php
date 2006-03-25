<center><h2><? echo $naam; ?></h2></center>
<hr>
<?
echo '<center>';
echo '<br>';
echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;prefix=' . $project->getPrefix() . '&amp;timespan=7&amp;team=' . rawurlencode($team) . '">';
echo '<br><br>';
echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($team) . '&amp;timespan=31&amp;labelInterval=2">';
echo '</center>';
echo '<br>';

$query = 'SELECT 
		( cands + daily ) AS day_total, 
		daily, 
		dag,
		dailypos
	FROM 
		' . $project->getPrefix() . '_' . $tabel . ' 
	WHERE 
		naam = \'' . $naam . '\' ' .
	($tabel=='subteamOffset'?'AND subteam = \'' . $team . '\'':'') . ' 
	ORDER BY 
		' . $sort . ' DESC';
$result = mysql_query($query);

echo '<center>';
echo openColorTable();
echo '<table>';
echo '<tr>';
echo '<td><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;sort=dag">Dag</td>';
echo '<td align="center"><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;sort=daily">Flush</a></td>';
echo '<td align="center">Total</td>';
echo '</tr>';
$pos = 0;
while($line = mysql_fetch_array($result))
{
	echo trBackground($pos++);
	echo '<td width="110">' . $line['dag'] . '</td>';
	echo '<td align="right" width="80">' . number_format($line['daily'], 0, ',', '.') . ' (' . $line['dailypos'] . ')</td>';
	echo '<td align="right" width="85">' . number_format($line['day_total'], 0, ',' ,'.') . '</td>';
	echo '</tr>';
}
echo '</table>';
echo '</center>';
closeTable(2);
