<?php

echo '<div style="margin-left:auto; margin-right:auto; text-align:center;"><h2>Shoutbox</h2></div>';

# Use a centered table
echo '<table style="width:98%; margin-left:auto; margin-right:auto;">';

# Retrieve the shoutbox elements from the database
$query = 'SELECT 
		naam,
		bericht,
		geplaatst,
		email
	FROM
		shoutbox
	ORDER BY
		geplaatst DESC';

# Execute the query
$result = $db->selectQuery($query);

# Loop through the results
while ( $line = $db->fetchArray($result) )
{
	# Start the table row and first cell
	echo trBackground(0);
	echo '<td>';
	
	# If an email adres was provided, link the postername
	if ( ! empty($line['email']) )
		echo '<a href="mailto:' . $line['email'] . '">' .  $line['naam'] . '</a>';
	else
		echo $line['naam'];
	
	# Show the time the message was places next to the name of the poster
	echo ' - ' . $line['geplaatst'] . '</td>';

	echo '</tr>';
	echo trBackground( ( $i + 1 ) );
	echo '<td>';
	
	# Replace complete URL's with an actual link
	echo preg_replace(	array('/http\:\/\/(.\*)(\b|$|\s|\n||\)|\z|\Z)/'),
				array('<a href="\0">link</a>'),
				$line['bericht']);
	echo '</td>';
	echo '</tr>';
	echo '<tr><td><br></td></tr>';
}
echo '</table>';

?>