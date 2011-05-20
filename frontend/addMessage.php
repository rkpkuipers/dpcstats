<?

include ('classes.php');

if ( ! isset($_SESSION['username']) )
	die('ERROR: Je moet ingelogd zijn om te kunnen posten');

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

if ( isset($_REQUEST['bericht']) )
	$bericht = $_REQUEST['bericht'];
else
	$bericht = '';

$poster = $_SESSION['username'];

if ( isset($_SESSION['email']) )
	$email = $_SESSION['email'];
else
	$email = '';

if ( isset($_GET['tabel']) )
	$tabel = $_GET['tabel'];

if ( isset($_GET['prefix']) )
	$prefix = $_GET['prefix'];

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

if ( ( ! empty($poster) ) && ( ! empty($bericht) ) )
{
	$file = file('banned');
	foreach($file as $rand => $value)
		$banlist[trim($value)] = 1;

	if ( isset($banlist[$_SERVER['REMOTE_ADDR']]) )
		$banned = true;
	else
		$banned = false;
	
	if ( substr_count($bericht, 'http://') > 2 )
		$banned = true;
	
	if ( substr_count($bericht, 'https://') > 2 )
		$banned = true;
	
	if ( strlen($bericht) > 400 )
		$banned = true;
	
	if ( substr($poster, 0, 16) == '<a href= http://' )
		$banned = true;

	if ( in_array($poster, array('none', 'None', 'Unknown', '<a href=  ></a>   [url=][/url]   ', 'phentermine') ) )
		$banned = true;

	if ( preg_match('/\@mail.com$/', $email) )
		$banned = true;

	if ( ( ! isset($_SERVER['HTTP_REFERER']) ) || ( substr_count($_SERVER['HTTP_REFERER'], 'tadah.mine.nu') == 0 ) )
		$banned = true;

	$query = 'INSERT INTO 
			shoutbox 
		VALUES 
		(
			\'' . $poster . '\', 
			\'' . parseCode(htmlspecialchars($bericht, ENT_QUOTES)) . '\', 
			\''. date("Y-m-d H:i:s") . '\',
			\'' . $email . '\')';
	
	
	if ( ! $banned )
	{
		# If the message is not banned, add it to the database and mail
	 	$db->selectQuery($query);

		$recipient = 'Remko Kuipers <rkpkuipers@planet.nl>';
		$subject = 'Shoutbox post by ' . $poster;
		$message = $poster . "\n" . 
				($banned?'MESSAGE NOT POSTED':'') . "\n" . 
				$email . "\n" . date("Y-m-d H:i:s") . "\n" . 
				$_SERVER['REMOTE_ADDR'] . "\n\n" . 
				parseCode(htmlspecialchars($bericht)) . "\n\n";
		
		mail($recipient, $subject, $message);
	}
	elseif ( ( $banned ) && ( ! isset($banlist[$_SERVER['REMOTE_ADDR']]) ) )
	{
		# If the message is banned, add the adres to the banned file
		$myFile = "/var/www/tadah.mine.nu/banned";
		$fh = fopen($myFile, 'a') or die("can't open file");
		fwrite($fh, $_SERVER['REMOTE_ADDR'] . "\n");
		fclose($fh);
	}
 }

 // page ending rejumping 2 index.
 header("Location: index.php?prefix=" . $prefix . '&tabel=' . $tabel . '&team=' . rawurlencode($team));
 ?>
