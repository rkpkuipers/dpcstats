<center>
<br>
<?php
function searchTabel($tabel, $prefix, $string, $tabelNaam)
{
	global $db;

        $query = 'SELECT 
			DISTINCT(naam) ' .
			($tabel=='subteamOffset'?',subteam':'') . '
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			naam LIKE \'%' . $string . '%\'
		AND	dag = \'' . date("Y-m-d") . '\'';

        $result = $db->selectQuery($query);

	if ( $db->getNumAffectedRows() > 0 )
	{
		echo openColorTable(63);
		echo '<b>' . $db->getNumAffectedRows() . ' matching ' .$tabelNaam . ' found</b>';
        	echo '<hr>';
		echo '<table width="100%">';
		$pos = 1;
	        while ($line = $db->fetchArray($result))
		{
			echo trBackground($pos++);
        	        echo '<td><a href="index.php?mode=detail&tabel=' . $tabel . 'Daily&amp;prefix=' . $prefix . 
				'&amp;naam=' . rawurlencode($line['naam']) . '&amp;team=' . rawurlencode($line['subteam']) . 
				'">' . $line['naam'] . '</a></td>';
			echo '</tr>';
		}
		echo '</table>';
		closeTable(2);
		echo '<br>';
	}
}

if ( trim($searchString) == '' )
	echo 'Empty searchstring';
else
{
	searchTabel('memberOffset', 'tsc', $searchString, 'TSC Members');
	searchTabel('teamOffset', 'tsc', $searchString, 'TSC Teams');
	searchTabel('memberOffset', 'd2ol', $searchString, 'D2OL Members');
	searchTabel('teamOffset', 'd2ol', $searchString, 'D2OL Teams');
	searchTabel('memberOffset', 'sob', $searchString, 'SoB Members');
	searchTabel('teamOffset', 'sob', $searchString, 'SoB Teams');
	searchTabel('subteamOffset', 'sob', $searchString, 'SoB Subteams Members');
	searchTabel('memberOffset', 'fad', $searchString, 'FAD Members');
	searchTabel('teamOffset', 'fad', $searchString, 'FAD Teams');
	searchTabel('subteamOffset', 'fad', $searchString, 'FAD Subteam Members');
	searchTabel('teamOffset', 'rah', $searchString, 'R@H Teams');
	searchTabel('memberOffset', 'rah', $searchString, 'R@H Members');
	searchTabel('subteamOffset', 'rah', $searchString, 'R@H Subteam Members');
	searchTabel('memberOffset', 'sah', $searchString, 'S@H Members');
	searchTabel('subteamOffset', 'sah', $searchString, 'S@H Subteam Members');
	searchTabel('teamOffset', 'sah', $searchString, 'S@H Teams');
	searchTabel('memberOffset', 'ufl', $searchString, '&micro;Fluid Members');
	searchTabel('teamOffset', 'ufl', $searchString, '&micro;Fluid Teams');
	searchTabel('subteamOffset', 'ufl', $searchString, '&micro;Fluid Subteam Members');
	searchTabel('teamOffset', 'fah', $searchString, 'F@H Teams');
	searchTabel('memberOffset', 'fah', $searchString, 'F@H Members');
	searchTabel('subteamOffset', 'fah', $searchString, 'F@H Subteam Members');
	searchTabel('memberOffset', 'smp', $searchString, 'Simap Members');
	searchTabel('subteamOffset', 'smp', $searchString, 'Simap Subteam Members');
	searchTabel('teamOffset', 'smp', $searchString, 'Simap Teams');
}

?>
</center>
