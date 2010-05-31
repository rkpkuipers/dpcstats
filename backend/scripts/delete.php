#!/usr/bin/php
<?php

include (dirname($argv[0]) . '/../classes.php');

# Enddate
$finaldate = '2010-01-01';

# Table
$table = 'fah_teamoffset';

# Retrieve the oldest date from the database
$query = 'SELECT dag FROM ' . $table . ' ORDER BY dag LIMIT 1';

# Execute the query
$result = $db->selectQuery($query);

# Retrieve the result
if ( $line = $db->fetchArray($result) )
	$epoch = $line['dag'];
else
	die("ERROR: Unable to fetch epoch\n");

# Travel through time
while ( date("Y-m-d", strtotime($epoch)) < date("Y-m-d", strtotime($finaldate)) )
{
	# User notification
	echo "Deleting everything before " . $epoch . "\n";
	
	# Drop data
	$result = $db->deleteQuery('DELETE FROM ' . $table . ' WHERE dag <= \'' . $epoch . '\'');

	# Show results
	echo "Removed " . $db->getNumAffectedRows($result) . " rows\n";

	# Move the day one forward
	$epoch = date("Y-m-d", strtotime("+1 day", strtotime($epoch)));
}

?>
