<?

$changelog = new ChangeLog($db);
$changelog->createChangelog();

$changes = $changelog->getChanges();

echo '<center>';
echo '<table width="98%">';

$datstr = '';

for($i=0;$i<count($changes);$i++)
{
	if ( $datstr != $changes[$i]->getDatum() )
	{
		echo '</table>';
		echo '<br>';
		echo '<table width="98%">';
		echo trBackground(0);
		echo '<td>' . date("j F Y", strtotime($changes[$i]->getDatum())) . '</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table width="98%">';
		$datstr = $changes[$i]->getDatum();
	}
	echo trBackground($pos++);

	echo '<td width="14%" valign="top" align="left">' . $changes[$i]->getAuthor() . '</td>';
	echo '<td align="left">' . $changes[$i]->getEntry() . '</td>';
	echo '</tr>';
}

echo '</table>';
echo '</center>';
?>
