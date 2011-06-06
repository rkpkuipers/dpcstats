<?php

# Search function
function searchTabel($db, $tabel, $prefix, $string, $tabelNaam)
{
	$query = 'SELECT 
			DISTINCT(naam) ' .
			($tabel=='subteamoffset'?',subteam':'') . '
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			upper(naam) LIKE \'%' . strtoupper($string) . '%\'
		AND	dag = \'' . date("Y-m-d") . '\'';
	
	$result = $db->selectQuery($query);
	
	if ( $db->getNumAffectedRows($result) > 0 )
	{
		echo '<div class="colorbox">';
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
		echo '</div>';
		echo '<br>';
	}
}

# Fetch the search string
if ( ( ! isset($_REQUEST['searchString']) ) || ( ! preg_match('/^[a-zA-Z0-9\[\]_\-\.]+$/', $_REQUEST['searchString']) ) )
{
	# Throw a warning
	echo '<b>Empty searchstring provided</b>';
	
	# Abort
	return;
}

# Load the search string
$searchstring = $_REQUEST['searchString'];

# Center the content
echo '<center>';

# Header
echo '<h2>Search Results</h2>';

# Retrieve the projects from the database
$projects = $db->getRecordsByCondition("project", array("project", "description"), array(), "project");

# Loop through the projects
foreach($projects as $projectdata)
{
	# Search through the members
	searchTabel($db, 'memberoffset', $projectdata['project'], $searchstring, $projectdata['description'] . ' Members');
	
	# Search through the teams
	searchTabel($db, 'teamoffset', $projectdata['project'], $searchstring, $projectdata['description'] . ' Teams');
	
	searchTabel($db, 'subteamoffset', $projectdata['project'], $searchstring, $projectdata['description'] . ' Subteams Members');
}

# Close the centration tag
echo '</center>';

?>