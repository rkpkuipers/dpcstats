<br>
<center>
<table width="100%">
<tr>
 <td width="25%" align="center">
  <form action="index.php">
  <input type="hidden" name="mode" value="avgProd">
  <input type="hidden" name="tabel" value="memberOffset">
  <input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
  <input type="Submit" value="DPC Members" class="TextField">
  </form>
 </td>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="avgProd">
   <input type="hidden" name="tabel" value="teamOffset">
   <input type="Submit" value="Teams" class="TextField">
   <input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
   </form>
 </td>
<?
if ( in_array($project->getPrefix(), array('fah', 'rah', 'sah', 'smp', 'sob', 'ufl')) )
{
?>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="avgProd">
   <input type="hidden" name="tabel" value="subteamOffset">
   <input type="Submit" value="Subteam Members" class="TextField">
   <input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
   </form>
 </td>
<?
}
?>
</tr>
</table>
<hr>
</center>
<br>
<br>
<center>
<?
echo openColorTable();
?>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><a href="index.php?mode=avgProd&amp;prefix=<? echo $project->getPrefix(); ?>&amp;tabel=<? echo $tabel; ?>&amp;sort=avgDaily">WeekAvg</a></td>
<td><a href="index.php?mode=avgProd&amp;prefix=<? echo $project->getPrefix(); ?>&amp;tabel=<? echo $tabel; ?>&amp;sort=avgMonthly">MonthAvg</a></td>
</tr>
<?
$query = 'SELECT 
		' . ($tabel=='subteamOffset'?'CONCAT(m.subteam,"~",a.naam)AS naam':'a.naam') . ',
		a.avgDaily, 
		a.avgMonthly
	FROM 
		averageProduction a, 
		' . $project->getPrefix() . '_' . $tabel . ' m 
	WHERE 
		tabel = \'' . $project->getPrefix() . '_' . $tabel . '\' 
	AND 	a.naam = m.naam 
	AND	m.dag = \'' . $datum . '\' 
	AND NOT 
		avgMonthly=0 
	ORDER BY 
		' . $sort . ' DESC 
	LIMIT 
		100';
$result = $db->selectQuery($query);
$i=0;
while ( $line = $db->fetchArray($result) )
{
	echo trBackground($i);
	echo '<td align="right" width="25">' . ( $i + 1 ) . '.</td>';
	echo '<td align="left" width="200"><a href="index.php?mode=detail&amp;naam=' . $line['naam'] . '&amp;tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '">' . $line['naam'] . '</a></td>';
	echo '<td align="right" width="55"><font color="red">' . number_format( /*$ap[$i]->getFlush() */ $line['avgDaily'], 0, ',', '.') . '</font></td>';
	echo '<td align="right" width="55"><font color="red">' . number_format( $line['avgMonthly'], 0, ',' , '.') . '</font></td>';
	echo '</tr>';
	$i++;
}
?>
</table>
</td></tr></table>
</center>
