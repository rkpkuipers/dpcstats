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
<center>
<?
$avgProduction = new AverageList($db, $project->getPrefix() . '_' . $tabel, $datum);
$avgProduction->gather();

$avgProductionList = $avgProduction->getList();

echo openColorTable(87);
echo '<tr><td></td><td></td><td><a href="index.php?mode=avgProd&amp;prefix=<? echo $project->getPrefix(); ?>&amp;tabel=<? echo $tabel; ?>&amp;sort=avgDaily">WeekAvg</a></td><td><a href="index.php?mode=avgProd&amp;prefix=<? echo $project->getPrefix(); ?>&amp;tabel=<? echo $tabel; ?>&amp;sort=avgMonthly">MonthAvg</a></td></tr>';

for($member=0;$member<count($avgProductionList);$member++)
{
	echo trBackground($member);
	echo '<td align="right" width="4%">' . ( $member + 1 ) . '.</td>';
	echo '<td align="left" width="80%"><a href="index.php?mode=detail&amp;naam=' . rawurlencode($avgProductionList[$member]['realname']) . '&amp;tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($avgProductionList[$member]['team']) . '">' . $avgProductionList[$member]['name'] . '</a></td>';
	echo '<td align="right" width="8%" style="color:#FF0000">' . $avgProductionList[$member]['daily'] . '</td>';
	echo '<td align="right" width="8%" style="color:#FF0000">' . $avgProductionList[$member]['monthly'] . '</td>';
	echo '</tr>';
}
?>
</table>
</td></tr></table>
</center>
