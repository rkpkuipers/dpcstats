<?php

# Check if a sorting method was specified
if ( ( isset($_REQUEST['sort']) ) && ( in_array($_REQUEST['sort'], array('dag', 'daily')) ) )
	$sort = $_REQUEST['sort'];
# Default to sorting by day
else
	$sort = 'dag';

# Center the content
echo '<div style="width:100%; text-align:center">';

# Header
echo '<h2>' . $naam . '</h2>';

# Flush history of this week
echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) . 
	'&amp;prefix=' . $project->getPrefix() . '&amp;timespan=7&amp;team=' . rawurlencode($team) . '">';

# Spacer
echo '<div><br></div>';

# Flush history of the month
echo '<img src="graphs/flushHistoryGraph.php?tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) . 
	'&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($team) . '&amp;timespan=31&amp;labelInterval=2">';

# Spacer
echo '<div><br><div>';

# Retrieve the flush history
$query = 'SELECT 
		( cands + daily ) AS day_total, 
		daily, 
		dag,
		dailypos
	FROM 
		' . $project->getPrefix() . '_' . $tabel . ' 
	WHERE 
		naam = \'' . $db->real_escape_string($naam) . '\' ' .
	($tabel=='subteamoffset'?'AND subteam = \'' . $db->real_escape_string($team) . '\'':'') . ' 
	ORDER BY 
		' . $sort . ' DESC';

# Execute the query
$result = $db->selectQuery($query);

# Table for the results
echo '<table class="colorbox" style="margin-left:auto; margin-right:auto;">';
echo '<tr>';

# Link the column headers to allow sorting of the table
echo '<td><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;sort=dag&amp;prefix=' . $project->getPrefix() . '">Dag</td>';
echo '<td align="center"><a href="index.php?mode=history&amp;tabel=' . $tabel . '&amp;naam=' . $naam . '&amp;sort=daily&amp;prefix=' . $project->getPrefix() . '">Flush</a></td>';

echo '<td align="center">Total</td>';
echo '</tr>';

# Counter
$pos = 0;

# Loop through the results
while($line = $db->fetchAssocArray($result))
{
	echo trBackground($pos++);
	echo '<td width="110">' . $line['dag'] . '</td>';
	echo '<td align="right" width="80px">' . number_format($line['daily'], 0, ',', '.') . ' (' . $line['dailypos'] . ')</td>';
	echo '<td align="right" width="85px">' . number_format($line['day_total'], 0, ',' ,'.') . '</td>';
	echo '</tr>';
}

# Close the data table
echo '</table>';

# Close the centration div
echo '</div>';

# Spacer
echo '<div><br></div>';

?>