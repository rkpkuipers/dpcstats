<center>
<br>
<?php
function searchTabel($tabel, $prefix, $string, $tabelNaam)
{
	global $db;

        $query = 'SELECT 
			DISTINCT(naam) ' .
			($tabel=='subteamoffset'?',subteam':'') . '
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			naam ILIKE \'%' . $string . '%\'
		AND	dag = \'' . date("Y-m-d") . '\'';

        $result = $db->selectQuery($query);

	if ( $db->getNumAffectedRows($result) > 0 )
	{
		echo openColorTable(63);
		echo '<b>' . $db->getNumAffectedRows($result) . ' matching ' .$tabelNaam . ' found</b>';
        	echo '<hr>';
		echo '<table width="100%">';
		$pos = 1;
	        while ($line = $db->fetchArray($result))
		{
			echo trBackground($pos++);
        	        echo '<td><a href="index.php?mode=detail&tabel=' . $tabel . '&amp;prefix=' . $prefix . 
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
	searchTabel('memberoffset', 'tsc', $searchString, 'TSC Members');
	searchTabel('teamoffset', 'tsc', $searchString, 'TSC Teams');
	searchTabel('memberoffset', 'd2ol', $searchString, 'D2OL Members');
	searchTabel('teamoffset', 'd2ol', $searchString, 'D2OL Teams');
	searchTabel('memberoffset', 'sob', $searchString, 'SoB Members');
	searchTabel('teamoffset', 'sob', $searchString, 'SoB Teams');
	searchTabel('subteamoffset', 'sob', $searchString, 'SoB Subteams Members');
	searchTabel('memberoffset', 'fad', $searchString, 'FAD Members');
	searchTabel('teamoffset', 'fad', $searchString, 'FAD Teams');
	searchTabel('subteamoffset', 'fad', $searchString, 'FAD Subteam Members');
	searchTabel('teamoffset', 'rah', $searchString, 'R@H Teams');
	searchTabel('memberoffset', 'rah', $searchString, 'R@H Members');
	searchTabel('subteamoffset', 'rah', $searchString, 'R@H Subteam Members');
	searchTabel('memberoffset', 'sah', $searchString, 'S@H Members');
	searchTabel('subteamoffset', 'sah', $searchString, 'S@H Subteam Members');
	searchTabel('teamoffset', 'sah', $searchString, 'S@H Teams');
	searchTabel('memberoffset', 'ufl', $searchString, '&micro;Fluid Members');
	searchTabel('teamoffset', 'ufl', $searchString, '&micro;Fluid Teams');
	searchTabel('subteamoffset', 'ufl', $searchString, '&micro;Fluid Subteam Members');
	searchTabel('teamoffset', 'fah', $searchString, 'F@H Teams');
	searchTabel('memberoffset', 'fah', $searchString, 'F@H Members');
	searchTabel('subteamoffset', 'fah', $searchString, 'F@H Subteam Members');
	searchTabel('memberoffset', 'smp', $searchString, 'Simap Members');
	searchTabel('subteamoffset', 'smp', $searchString, 'Simap Subteam Members');
	searchTabel('teamoffset', 'smp', $searchString, 'Simap Teams');
}

?>
</center>
