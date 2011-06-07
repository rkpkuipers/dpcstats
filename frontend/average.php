<br>
<table width="100%">
<tr>
 <td width="25%" align="center">
  <form action="index.php">
  <input type="hidden" name="mode" value="avgProd">
  <input type="hidden" name="tabel" value="memberoffset">
  <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
  <input type="Submit" value="DPC Members" class="TextField">
  </form>
 </td>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="avgProd">
   <input type="hidden" name="tabel" value="teamoffset">
   <input type="Submit" value="Teams" class="TextField">
   <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
   </form>
 </td>
<?php
if ( in_array($project->getPrefix(), array('fah', 'rah', 'sah', 'smp', 'sob', 'ufl')) )
{
?>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="avgProd">
   <input type="hidden" name="tabel" value="subteamoffset">
   <input type="Submit" value="Subteam Members" class="TextField">
   <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
   </form>
 </td>
<?php
}
?>
</tr>
</table>
<hr>
<div><br></div>
<div style="width:100%; text-align:center;">
<?php
$avgProduction = new AverageList($db, $project->getPrefix() . '_' . $tabel, $datum, $project);
$avgProduction->gather();

$avgProductionList = $avgProduction->getList();

echo '<table class="colorbox" style="width:470px; margin-left:auto; margin-right:auto;">';
echo '<tr><td colspan="4"><h3>Average Output</h3></td></tr>';
echo '<tr>';
echo '<td></td>';
echo '<td></td>';
echo '<td><a href="index.php?mode=avgProd&amp;prefix=<?php echo $project->getPrefix(); ?>&amp;tabel=<?php echo $tabel; ?>&amp;sort=avgDaily">WeekAvg</a></td>';
echo '<td><a href="index.php?mode=avgProd&amp;prefix=<?php echo $project->getPrefix(); ?>&amp;tabel=<?php echo $tabel; ?>&amp;sort=avgMonthly">MonthAvg</a></td>';
echo '</tr>';

for($member=0;$member<count($avgProductionList);$member++)
{
	echo trBackground($member);
	echo '<td align="right" width="4%">' . ( $member + 1 ) . '.</td>';
	echo '<td align="left" width="80%"><a href="index.php?mode=detail&amp;naam=' . rawurlencode($avgProductionList[$member]['realname']) . '&amp;tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($avgProductionList[$member]['team']) . '">' . $avgProductionList[$member]['name'] . '</a></td>';
	echo '<td align="right" width="8%" style="color:#FF0000">' . number_format($avgProductionList[$member]['daily'], 0, ',', '.') . '</td>';
	echo '<td align="right" width="8%" style="color:#FF0000">' . number_format($avgProductionList[$member]['monthly'], 0, ',', '.') . '</td>';
	echo '</tr>';
}
?>
</table>

</center>
