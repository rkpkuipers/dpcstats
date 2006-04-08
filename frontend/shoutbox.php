<?
$shoutbox = New ShoutBox($db);
$shoutbox->getMessages();
$messages = $shoutbox->getMessageList();

echo '<center>';
echo '<table width="98%">';

for($i=0;$i<count($messages);$i++)
{
	echo trBackground(0);
	echo '<td>';
	if ( $messages[$i]->getEmail() != '' )
		echo '<a href="mailto:' . $messages[$i]->getEmail() . '">' .  $messages[$i]->getPoster() . '</a>';
	else
		echo $messages[$i]->getPoster();
	echo ' - ' . $messages[$i]->getTijd() . '</td>';
	echo '</tr>';
	echo trBackground( ( $i + 1 ) );
	echo '<td>';
	
	# Replace complete URL's with an actual link
	echo preg_replace(	array('/http\:\/\/(.\*)(\b|$|\s|\n||\)|\z|\Z)/'),
				array('<a href="\0">link</a>'),
				$messages[$i]->getBericht());
	echo '</td>';
	echo '</tr>';
	echo '<tr><td><br></td></tr>';
}
echo '</table>';
echo '</center>';
?>
