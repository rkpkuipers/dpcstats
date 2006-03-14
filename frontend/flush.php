<br>
<center>
<table width="100%">
<tr>
 <td width="25%" align="center">
  <form action="index.php">
  <input type="hidden" name="mode" value="Flush">
  <input type="hidden" name="tabel" value="memberOffset">
  <input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
  <input type="Submit" value="DPC Members" class="TextField">
  </form>
 </td>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="Flush">
   <input type="hidden" name="tabel" value="teamOffset">
   <input type="Submit" value="Teams" class="TextField">
   <input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
   </form>
 </td>
</tr>
</table>
<hr>
</center>
<br>
<table width="100%">
<tr>
<td align=center valign=top>
<?
if ( is_numeric(strpos($tabel, 'Daily') ) )
	$strippedTabel = substr($tabel, 0, strpos($tabel, 'Daily'));
else
	$strippedTabel = $tabel;
	
$fmc = new FlushList($project->getPrefix() . '_' . $strippedTabel, $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

echo openColorTable();
?>
Member Flushes
<hr>
<table>
<?

for($i=0;$i<count($fl);$i++)
{
	echo trBackground($i);
	echo '<td align=right width=10>' . ( $i + 1 ) . '.</td>';
	echo '<td align=right width=55><font color=red>' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</font></td>';
	echo '<td align=left width=300><a href="index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $fl[$i]->getName() . '">' . $fl[$i]->getName() . '</a></td>';
	echo '<td align="right" width="75">' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '</td>';
	echo '</tr>';
}

echo '</table>';
closeTable(2);

?>
</td>
</tr><tr><td>&nbsp;</td></tr><tr>
<td align=center valign=top>
<?

$fmc->createFlushList();

$fl = $fmc->getFlushList();

echo openColorTable();
?>
Overall Flushes
<hr>
<table>
<?
for($i=0;$i<count($fl);$i++)
{
        echo trBackground($i);
        echo '<td align=right width=10>' . ( $i + 1 ) . '.</td>';
        echo '<td align=right width=55><font color=red>' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</font></td>';
        echo '<td align=left width=300><a href="index.php?prefix=' . $project->getPrefix() . '&amp;mode=detail&amp;tabel=' . $tabel . '&amp;naam=' . $fl[$i]->getName() . '">' . $fl[$i]->getName() . '</a></td>';
        echo '<td align=right width="75"><a href="index.php?prefix=' . $project->getPrefix() . '&amp;mode=Members&amp;tabel=' . $strippedTabel . '&amp;datum=' . $fl[$i]->getDate() . '">' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '</a></td>';
        echo '</tr>';
}
echo '</table>';
echo '</table>';
closeTable(2);

?>
