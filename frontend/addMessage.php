<?

include ('classes.php');

function parseCode($text)
{
// Array of tags with opening and closing
	$tagArray['img'] = array('open'=>'<img src="','close'=>'">');
	$tagArray['b'] = array('open'=>'<b>','close'=>'</b>');
	$tagArray['i'] = array('open'=>'<i>','close'=>'</i>');
	$tagArray['u'] = array('open'=>'<u>','close'=>'</u>');
	$tagarray['br'] = array('open'=>'<br>','close'=>'');
	#$tagArray['url'] = array('open'=>'<a href="','close'=>'">\\1</a>');
	#$tagArray['email'] = array('open'=>'<a href="mailto:','close'=>'">\\1</a>');
	$tagArray['url=(.*)'] = array('open'=>'<a href="','close'=>'">\\2</a>');
	$tagArray['email=(.*)'] = array('open'=>'<a href="mailto:','close'=>'">\\2</a>');
	$tagArray['color=(.*)'] = array('open'=>'<font color="','close'=>'">\\2</font>');
	$tagArray['size=(.*)'] = array('open'=>'<font size="','close'=>'">\\2</font>');
	$tagArray['font=(.*)'] = array('open'=>'<font face="','close'=>'">\\2</font>');

// Array of tags with only one part
	$sTagArray['br'] = array('tag'=>'<br>');
	$sTagArray['hr'] = array('tag'=>'<hr>');

	foreach($tagArray as $tagName=>$replace)
	{
		$tagEnd=preg_replace('/\W/Ui','',$tagName);
		$text = preg_replace("|\[$tagName\](.*)\[/$tagEnd\]|Ui","$replace[open]\\1$replace[close]",$text);
	}

	foreach($sTagArray as $tagName=>$replace)
	{
		$text= preg_replace("|\[$tagName\]|Ui","$replace[tag]",$text);
	}

return $text;
}

if ( isset($HTTP_GET_VARS['bericht']) )
	$bericht = $HTTP_GET_VARS['bericht'];
else
	$bericht = '';

if ( isset($HTTP_GET_VARS['poster']) )
	$poster = $HTTP_GET_VARS['poster'];
else
	$poster = '';

if ( isset($HTTP_GET_VARS['email']) )
	$email = $HTTP_GET_VARS['email'];
else
	$email = '';

if ( isset($_GET['tabel']) )
	$tabel = $_GET['tabel'];

if ( isset($_GET['prefix']) )
	$prefix = $_GET['prefix'];

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

if ( ( $poster != '' ) && ( $bericht != '' ) )
{
	$query = 'INSERT INTO 
			shoutbox 
		VALUES 
		(
			\'' . $poster . '\', 
			\'' . parseCode(htmlspecialchars($bericht, ENT_QUOTES)) . '\', 
			\''. date("Y-m-d H:i:s") . '\',
			\'' . $email . '\')';
 	$db->selectQuery($query);

	$recipient = 'Remko Kuipers <rkpkuipers@planet.nl>';
	$subject = 'Shoutbox post by ' . $poster;
	$message = $poster . ' (' . $email . ') posted a message on ' . date("Y-m-d H:i:s") .
			"\n\n" . parseCode(htmlspecialchars($bericht));
	
	mail($recipient, $subject, $message);
 }

 // page ending rejumping 2 index.
 header("Location: index.php?prefix=" . $prefix . '&tabel=' . $tabel . '&team=' . rawurlencode($team));
 ?>
