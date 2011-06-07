<br>
<center>
<table width="100%">
<tr>
 <td width="25%" align="center">
  <form action="index.php">
  <input type="hidden" name="mode" value="Flush">
  <input type="hidden" name="tabel" value="memberoffset">
  <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
  <input type="Submit" value="DPC Members" class="TextField">
  </form>
 </td>
 <td width="25%" align="center">
   <form action="index.php">
   <input type="hidden" name="mode" value="Flush">
   <input type="hidden" name="tabel" value="teamoffset">
   <input type="Submit" value="Teams" class="TextField">
   <input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
   </form>
 </td>
</tr>
</table>
<hr>
<div><br></div>
<?php
if ( is_numeric(strpos($tabel, 'daily') ) )
	$strippedTabel = substr($tabel, 0, strpos($tabel, 'daily'));
else
	$strippedTabel = $tabel;
	
$fmc = new FlushList($project->getPrefix() . '_' . $strippedTabel, $db);

$fmc->createMFList();
$fl = $fmc->getMFList();

# Div for the box
echo '<div style="width:475px" class="colorbox">';

# Header
echo '<h3>Member Flushes</h3>';

# Table for the list
echo '<table>';

# Loop through the results
for($i=0;$i<count($fl);$i++)
{
	echo trBackground($i);
	echo '<td align=right width=10>' . ( $i + 1 ) . '.</td>';
	echo '<td align=right width=55><font color=red>' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</font></td>';
	echo '<td align=left width="300px">';
	echo getURL(array('link' => $fl[$i]->getName(), 'mode' => 'detail', 'tabel' => $tabel, 
				'name' => $fl[$i]->getName(), 'prefix' => $project->getPrefix(),
				'date' => date("Y-m-d", strtotime($fl[$i]->getDate())),
				'title' => 'Details for ' . $fl[$i]->getName()));
	echo '</td>';
	echo '<td align="right" width="75">' . date("d-m-Y", strtotime($fl[$i]->getDate())) . '</td>';
	echo '</tr>';
}

# Close the table and the color box
echo '</table>';
echo '</div>';

# Spacer
echo '<div><br></div>';

$fmc->createFlushList();

$fl = $fmc->getFlushList();

# Colorbox
echo '<div style="width:470px" class="colorbox">';

# Header
echo '<h3>Overall Flushes</h3>';

# Table for the results
echo '<table>';

# Loop through the results
for($i=0;$i<count($fl);$i++)
{
        echo trBackground($i);
        echo '<td align="right" width="10px">' . ( $i + 1 ) . '.</td>';
        echo '<td align="right" width="55px"><font color=red>' . number_format($fl[$i]->getCredits(), 0, ',', '.') . '</font></td>';
        echo '<td align="left" width="300">';
	echo getURL(array('name' => $fl[$i]->getName(), 'prefix' => $project->getPrefix(), 'mode' => 'detail',
				'tabel' => $tabel, 'title' => 'Details for ' . $fl[$i]->getName(), 
				'link' => $fl[$i]->getName(), 'date' => date("Y-m-d", strtotime($fl[$i]->getDate()))));
	echo '</td>';
        echo '<td align="right" width="75px">';
	echo getURL(array('prefix' => $project->getPrefix(), 'mode' => 'Members', 'tabel' => $strippedTabel,
			'date' => $fl[$i]->getDate(), 'link' => date("d-m-Y", strtotime($fl[$i]->getDate())),
			'title' => 'Flust list for ' . date("d-m-Y", strtotime($fl[$i]->getDate()))));
	echo '</td>';
        echo '</tr>';
}
# Close the results table and color box
echo '</table>';
echo '</div>';

# Close the centration tag
echo '</center>';

# Spacer
echo '<div><br></div>';

?>