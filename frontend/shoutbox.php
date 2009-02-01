<?

function parseSmiley($bericht)
{
	$text = $bericht;
	
	$text = str_replace(':-)',  '<img src="images/smilies/smile.gif" alt=":)">',   $text);
	$text = str_replace(':)',   '<img src="images/smilies/smile.gif" alt=":)">',   $text);
	
	$text = str_replace(':-D',  '<img src="images/smilies/biggrin.gif" alt=":D">', $text);
	$text = str_replace(':D',   '<img src="images/smilies/biggrin.gif" alt=":D">', $text);
	
	$text = str_replace(':-+',  '<img src="images/smilies/clown.gif" alt=":+">',   $text);
	$text = str_replace(':+',   '<img src="images/smilies/clown.gif" alt=":+">',   $text);
	
	$text = str_replace(':\'(', '<img src="images/smilies/cry.gif" alt=":\'(">',     $text);
	
	$text = str_replace('>:)',  '<img src="images/smilies/devil.gif" alt=">:)">',   $text);
	
	$text = str_replace(' :(',   '<img src="images/smilies/frown.gif" alt=":(">',   $text);
	$text = str_replace(' :-(',  '<img src="images/smilies/frown.gif" alt=":(">',   $text);
	
	$text = str_replace('|:(',  '<img src="images/smilies/frusty.gif" alt="|:(">',  $text);
	
	$text = str_replace(':9~',  '<img src="images/smilies/kwijl.gif" alt=":9~">',   $text);
	
	$text = str_replace(':p',   '<img src="images/smilies/puh2.gif" alt=":p">',    $text);
	$text = str_replace(':P',   '<img src="images/smilies/puh2.gif" alt=":p">',    $text);
	
	$text = str_replace(':r',   '<img src="images/smilies/pukey.gif" alt=":r">',   $text);
	
	$text = str_replace(':o',   '<img src="images/smilies/redface.gif" alt=":o">', $text);
	$text = str_replace(':O',   '<img src="images/smilies/redface.gif" alt=":o">', $text);
	
	$text = str_replace(';)',   '<img src="images/smilies/wink.gif" alt=";)">',    $text);
	$text = str_replace(';-)',  '<img src="images/smilies/wink.gif" alt=";)">',    $text);
	
	$text = str_replace(':w',   '<img src="images/smilies/bye.gif" alt=":w">',      $text);
	
	return $text;
}

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