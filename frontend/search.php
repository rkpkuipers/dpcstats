<center>
<br>
<?php
function searchTabel($tabel, $prefix, $string, $tabelNaam)
{
	global $db;

        $query = 'SELECT 
			DISTINCT(naam) 
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			naam LIKE \'%' . $string . '%\'
		AND	dag = \'' . date("Y-m-d") . '\'';
        $result = $db->selectQuery($query);

	if ( mysql_affected_rows() > 0 )
	{
		echo openColorTable(63);
		echo '<b>' . mysql_affected_rows() . ' matching ' .$tabelNaam . ' found</b>';
        	echo '<hr>';
		echo '<table width="100%">';
		$pos = 1;
	        while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			echo trBackground($pos++);
        	        echo '<td><a href="index.php?mode=detail&tabel=' . $tabel . 'Daily&amp;prefix=' . $prefix . '&amp;naam=' . $line['naam'] . '">' . $line['naam'] . '</a></td>';
			echo '</tr>';
		}
		echo '</table>';
		closeTable(2);
		echo '<br>';
	}
	/*
	else
	{
		echo '<b>No matching ' . $tabelNaam . ' found</b>';
		echo '<hr>';
	}
	*/
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
	searchTabel('subteamOffset', 'sob', $searchString, 'SoB SubTeams');
	searchTabel('memberOffset', 'fad', $searchString, 'FAD Members');
	searchTabel('teamOffset', 'fad', $searchString, 'FAD Teams');
	searchTabel('subteamOffset', 'fad', $searchString, 'FAD SubTeams');
	searchTabel('teamOffset', 'rah', $searchString, 'R@H Teams');
	searchTabel('memberOffset', 'rah', $searchString, 'R@H Members');
	searchTabel('memberOffset', 'sah', $searchString, 'S@H Members');
	searchTabel('subteamOffset', 'sah', $searchString, 'S@H SubTeam Members');
	searchTabel('memberOffset', 'ufl', $searchString, '&micro;Fluid Members');
	searchTabel('teamOffset', 'ufl', $searchString, '&micro;Fluid Teams');
	searchTabel('teamOffset', 'fah', $searchString, 'F@H Teams');
	searchTabel('memberOffset', 'fah', $searchString, 'F@H Members');
}

?>
</center>
