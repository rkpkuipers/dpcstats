<?

class Message
{
	var $poster;
	var $bericht;
	var $tijd;
	var $email;

	function Message($poster, $bericht, $tijd, $email)
	{
		$this->poster = $poster;
		$this->bericht = $bericht;
		$this->tijd = $tijd;
		$this->email = $email;
	}

	function getPoster()
	{
		return $this->poster;
	}

	function getBericht()
	{
		return $this->bericht;
	}

	function getTijd()
	{
		return $this->tijd;
	}

	function getEMail()
	{
		return $this->email;
	}
}

class ShoutBox
{
	var $messages;
	var $db;

	function ShoutBox($db)
	{
		$messages = array();

		$this->db = $db;
	}

	function addMessage($poster, $bericht, $email)
	{
	}

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

	function getMessages($aantal=0)
	{
                $query = 'SELECT 
				naam, 
				bericht, 
				geplaatst, 
				email 
			FROM 
				shoutbox 
			ORDER BY 
				geplaatst DESC';
		
		if ( $aantal != 0 )
			$query .= ' LIMIT ' . $aantal;
			
                $result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
                {
			$this->messages[] = new Message($line['naam'], 
									      $this->parseSmiley($line['bericht']), 
									      $line['geplaatst'], 
									      $line['email']);
		}
	}

	function getMessageList()
	{
		return $this->messages;
	}
}
