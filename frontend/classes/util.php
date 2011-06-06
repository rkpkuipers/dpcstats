<?php
function getLoginRegisterBox($db)
{
	echo '<hr>';
	echo '<h3>Login</h3>';
	echo '<table style="width:100%">';
	echo '<form name="login" action="admin/login.php" method="post">';
	echo '<tr><td>Username</td><td style="text-align:right"><input style="width:95px" type="text" name="username" value=""></td></tr>';
	echo '<tr><td>Password</td><td style="text-align:right"><input style="width:95px" type="password" name="password" value=""></td></tr>';
	echo '<tr><td colspan="2" style="text-align:center"><input type="submit" value="Login"></td></tr>';
	echo '</form>';
	echo '</table>';
	echo 'Of <a href="/index.php?mode=register">Register</a>';
}

function getCalender($datum)
{
	echo '<center>';
	echo '<table width="92%">';
	echo '<tr><td align="left">Stats van:</td>';
	echo '<td align="right">' . date("d-m-Y", strtotime($datum)) . '</td>';
	echo '</tr></table>';
	echo '<table width="92%" bgcolor="#000000" cellpadding="1" cellspacing="1">';
	echo '<tr><td>';
	echo '<table width="100%" cellpadding="1" cellspacing="1">';
	echo trBackground(0);
	echo '<td align="center" colspan="7">';
	echo getURL(array('link' => '&lt;&lt;', 'date' => date("Y-m-d", strtotime("-1 month", strtotime($datum))))) . ' ';
	echo getURL(array('link' => '&lt;', 'date' => date("Y-m-d", strtotime("-1 day", strtotime($datum))))) . ' ';
	echo date("M Y", strtotime($datum)) . '&nbsp;';
	echo getURL(array('link' => '&gt;', 'date' => date("Y-m-d", strtotime("+1 day", strtotime($datum))))) . ' ';
	echo getURL(array('link' => '&gt;&gt;', 'date' => date("Y-m-d", strtotime("+1 month", strtotime($datum))))) . ' ';
	echo '</td></tr>';
	echo '<tr bgcolor="#CBCBCB"><td align="center">Z</td><td align="center">M</td><td align="center">D</td><td align="center">W</td><td align="center">D</td><td align="center">V</td><td align="center">Z</td></tr>';
	$pos = date("w", strtotime(date("Y-m-1", strtotime($datum))));
	echo '<tr style="background-color:#CBCBCB">';

	# For loop, van laatste dag van de vorige maand -$pos aantal dagen tot laatste dag van de vorige maand om de bovenste rij van de calender bij te vullen
	for($i=(date("t", strtotime("-1 month", strtotime($datum))) - ($pos-1));$i<=(date("t", strtotime("-1 month", strtotime($datum))));$i++)
	{
		if ( date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime("-1 month", strtotime($datum))) > date("Y-m-d") )
			echo '<td style="background-color:#CBCBCB" bgcolor="#CBCBCB" align="center">' . $i . '</td>';
		else
		{
			echo '<td style="background-color:' . trBackgroundColor($i) . '" bgcolor="' . trBackgroundColor($i) . '" align="center">';
			echo getURL(array('link' => $i, 'date' => date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime("-1 month", strtotime($datum)))));
			echo '</td>';
		}
	}

	for($i=1;$i<=date("t", strtotime($datum));$i++)
	{
		if ( $pos % 7 == 0 )
			echo '</tr><tr bgcolor="#CBCBCB">';
		echo '<td align="center" bgcolor="';
		if ( date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime($datum)) == date("Y-m-d", strtotime($datum) ) )
			echo '#FFFFFF';
		elseif ( ( date("Y-m", strtotime($datum)) == date("Y-m") ) && ( $i == date("d") ) )
			echo trBackgroundColor(0);
		else
			echo trBackgroundColor($i);

		echo '">';

		if ( date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime($datum)) > date("Y-m-d") )
			echo $i;
		else
		{
			echo getURL(array('link' => $i, 'date' => date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime($datum))));
		}
		echo '</td>';
		$pos++;
	}

	if ( $pos%7 != 0 )
		for($i=($pos%7);$i<7;$i++)
		{
			echo '<td bgcolor="#CBCBCB" align="center">';
			if ( ( date("Y-m-" . str_pad((($i-($pos%7))+1), 2, 0, STR_PAD_LEFT), strtotime("+1 month", strtotime($datum))) ) > date("Y-m-d") )
				echo ($i-($pos%7)+1);
			else
			{
				echo getURL(array('link' => ($i-($pos%7)+1), 'date' => date("Y-m-" . str_pad((($i-($pos%7))+1), 2, 0, STR_PAD_LEFT), strtotime("+1 month", strtotime($datum)))));
			}
			echo '</td>';
		}
	echo '</table>';
	echo '</td></tr></table>';
	echo '</center>';
	echo '<br>';
}

function getTop5Table($project, $ml, $headertext, $tabel)
{
	$mbs = $ml->getMembers();
	if ( count($mbs) > 0 )
	{
		echo '<table width="180px" cellspacing="0" cellpadding="0">';
		echo '<tr style="background-image:url(images/left-banner.jpg); height:45px">';
		echo '<td align="center" style="font-size:13px; font-weight:bold; color:#FFFFFF; cursor:pointer;" ';
		echo 'onclick=\'window.open("' . $baseUrl . '/index.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '", "_self")\'>' . $headertext . '</td>';
		echo '</tr></table>';
						
		echo '<table cellspacing="2" width="175">';
		
		for($i=0;$i<count($mbs);$i++)
		{
			echo trBackground($i);

			$change = $mbs[$i]->getYesterday() - ( $i + 1 );

			if ( $change == 0 )
			{
				$image = '<img src="images/yellow.gif" alt="yellow">';
				$change = '';
			}
			elseif ( $change < 0 )
			{
				$image = '<img src="images/red.gif" alt="red">';
				$change = $change - ( $change * 2 );
			}
			elseif ( $change > 0 )
			{
				if ( $change > 50 )
					$change = '';
				$image = '<img src="images/green.gif" alt="green">';
			}
			
			echo '<td width="15px">' . ( $i + 1 ) . '.</td>';
			echo '<td width="30px" align="center">' . $image . '' . $change . '</td>';
			echo '<td>';
			echo getURL(array('link' => $mbs[$i]->getShortName(), 'mode' => 'detail', 'name' => $mbs[$i]->getName(), 
				'table' => $tabel, 'title' => 'Details for ' . $mbs[$i]->getName()));
			echo '</td>';
			echo '</tr>';
		}
		
		echo '</table><br>';
	}
}

function getShoutboxTable($db, $project, $tabel, $team)
{
	global $baseUrl;

	echo '<table width="180px" cellspacing="0" cellpadding="0">';
	echo '<tr style="background-image:url(images/left-banner.jpg); height:45px">';
	echo '<td align="center" style="font-size:13px; font-weight:bold; color:#FFFFFF; cursor:pointer;" ';
	echo 'onclick=\'window.open("' . $baseUrl . '/index.php?mode=shoutbox", "_self")\'>Shoutbox</td>';
	echo '</tr></table>';
	
	echo '<table width="180px" cellspacing="2">';
	
	# Retrieve the shoutbox elements from the database
	$query = 'SELECT 
			naam,
			bericht,
			geplaatst,
			email
		FROM
			shoutbox
		ORDER BY
			geplaatst DESC
		LIMIT
			8';

	# Execute the query
	$result = $db->selectQuery($query);
	
	# Row number
	$row = 1;
	
	# Loop through the results
	while ( $line = $db->fetchArray($result) )
	{
		echo trBackground(0);
		echo '<td>';
		
		# Show the date nicely formatted
		echo date("d-m H:i", strtotime($line['geplaatst'])) . ' - ';
		
		# Show the poster name, link to an email adress if present
		if ( empty($line['email']) )
			echo $line['naam'];
		else
			echo '<a href="mailto:' . $line['email'] . '" title="Mail ' . $line['naam'] . '">' . $line['naam'] . '</a>';
		
		echo '</td>';
		echo '</tr>';
		
		echo trBackground($row++);
		echo '<td width="180px">';
		
		$mess = explode(' ', $line['bericht']);
		for($j=0;$j<count($mess);$j++)
		{
			if ( substr($mess[$j], 0, 4) == 'http' )
				echo '<a href="' . $mess[$j] . '">link</a>';
			else if ( ( strlen($mess[$j]) > 15 ) && 
			     ( ! is_numeric(strpos($mess[$j], 'tadah.mine.nu')) ) &&
			     ( ! is_numeric(strpos($mess[$j], 'images')) ) &&
			     ( ! is_numeric(strpos($mess[$j], 'href')) ) 
			   )
			   	echo wordwrap($mess[$j], 10, ' ', -1) . ' ';
			else
				echo $mess[$j] . ' ';
		}
		
			echo '</td>';
			echo '</tr>';
			echo '<tr><td><br></td></tr>';
	}
	
	echo '</table>';
}

function getShoutboxForm($project, $tabel, $team)
{
?>
	<form method="get" action="addMessage.php" name="shoutbox">
		<input type="hidden" name="tabel" value="<?php echo $tabel; ?>">
		<input type="hidden" name="prefix" value="<?php echo $project->getPrefix(); ?>">
		<input type="hidden" name="team" value="<?php echo rawurlencode($team); ?>">
	<hr>
	<table width="100%">
	<tr>
	<td>Message</td>
	<td align="right"><input class="TextField" style="text-align:right;" type="text" name="textcount" size="3" value="350" readonly></td>
	</tr>
	
	<tr><td colspan="2"><textarea class="TextField" name="bericht" cols="18" rows="3" onkeyup="TrackCount(this,'textcount',350)" onkeypress="LimitText(this,350)"></textarea></td></tr>
	<tr><td colspan="2"><br></td></tr>
	<tr><td align="center"><input class="TextField" type="submit" value="Post"></td></tr>
	</table>
	</form>
	<br>
	&nbsp;<b>Code:</b><br>
	&nbsp;&nbsp;[b]<b>Bold</b>[/b]<br>
	&nbsp;&nbsp;[i]<i>Italic</i>[/i]<br>
	&nbsp;&nbsp;[u]<span class="uExample">Underline</span>[/u]<br>
	&nbsp;&nbsp;[br]Enter<br>
	<br>
	&nbsp;<b>Smilies:</b><br>
	&nbsp;&nbsp;
<?php
	$smDir = $baseUrl . '/images/smilies/';
?>
	<img class="smiley" src="<?php echo $smDir; ?>smile.gif" onClick="document.shoutbox.bericht.value+=':) '" alt=":)">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>biggrin.gif" onClick="document.shoutbox.bericht.value+=':D '" alt=":D">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>clown.gif" onClick="document.shoutbox.bericht.value+=':+ '" alt=":+">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>cry.gif" onClick="document.shoutbox.bericht.value+=':\'( '" alt=":'(">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>devil.gif" onClick="document.shoutbox.bericht.value+='>:) '" alt=">:)">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>frown.gif" onClick="document.shoutbox.bericht.value+=':( '" alt=":(">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>frusty.gif" onClick="document.shoutbox.bericht.value+='|:( '" alt="|:("><br>
	&nbsp;&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>kwijl.gif" onClick="document.shoutbox.bericht.value+=':9~ '" alt=":9~">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>puh2.gif" onClick="document.shoutbox.bericht.value+=':P '" alt=":P">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>pukey.gif" onClick="document.shoutbox.bericht.value+=':r '" alt=":r">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>redface.gif" onClick="document.shoutbox.bericht.value+=':o '" alt=":0">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>wink.gif" onClick="document.shoutbox.bericht.value+=';) '" alt=";)">&nbsp;
	<img class="smiley" src="<?php echo $smDir; ?>bye.gif" onClick="document.shoutbox.bericht.value+=':w '" alt=":w"><br>

	&nbsp;&nbsp;<small><a href="http://www.tweakers.net">&copy; Tweakers.net</a></small>
	<br><br>
<?php
}

function getMemberList($prefix, $tabel, $datum = 0,$order = 'naam')
{
	global $db;

	if ( $datum == 0 ) $datum = date("Y-m-d");
	
	echo '<center>';
	echo '<table width="200px">';

	$query = 'SELECT
			naam
		FROM
			' . $prefix . '_' . $tabel . '
		WHERE
			dag = \'' . $datum . '\'
		ORDER BY
			' . $order;
	
	$result = $db->selectQuery($query);

	$row = 1;
	while ( $line = $db->fetchArray($result) )
	{
#		echo '<tr>';
		echo trBackground($row++);
		echo '<td align="center">' . getURL(array('link' => $line['naam'], 'name' => $line['naam'])) . '</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '</center>';
}

function microtime_diff($a, $b)
{
        list($a_dec, $a_sec) = explode(" ", $a);
        list($b_dec, $b_sec) = explode(" ", $b);
        return $b_sec - $a_sec + $b_dec - $a_dec;
}

function handleCookie($name, $getSet, $getValue, $cookie)
{
	if ( isset($getSet) )
	{
		setcookie($name, FALSE);
		setcookie($name, $getValue, time()+60*60*24*30);
		$value = $getValue;
	}
	elseif ( isset($cookie) )
		$value = $cookie;
	else
		$value = 'on';
	
	return $value;
}

function getYesterday($prefix)
{
	return date("Y-m-d", strtotime("Yesterday", date("U")));
}

function getNavBarEntry($link,$target)
{

	return '<li><a href="' . $target . '">' . $link . '</a></li>'."\n";
}

function getChangeImage($change, $ts)
{
        if ( $change == 0 )
        {
                $image = '<img src="images/yellow.gif" alt="yellow">';
                $change = '';
        }
        elseif ( $change < 0 )
        {
                $image = '<img src="images/red.gif" alt="red">';
                $change = $change - ( $change * 2 );
        }
        elseif ( $change > 0 )
	{
		if ( ( $change + ( $pos + $dlow ) ) > $ts->getPrevDayFlushCount() )
			$change = "";
                $image = '<img src="images/green.gif" alt="green">';
	}
	
	return $image . $change;
}

function getDPCHChangeImage($change, $ts)
{
        if ( $change == 0 )
        {
                $image = '<img src="http://tadah.mine.nu/images/y.gif" alt="yellow">';
                $change = '';
        }
        elseif ( $change < 0 )
        {
                $image = '<img src="http://tadah.mine.nu/images/r.gif" alt="red">';
                $change = $change - ( $change * 2 );
        }
        elseif ( $change > 0 )
	{
		if ( ( $change + ( $pos + $dlow ) ) > $ts->getPrevDayFlushCount() )
			$change = "";
                $image = '<img src="http://tadah.mine.nu/images/g.gif" alt="green">';
	}
	
	return '(' . $image . $change . ')';
}

function getRMLDPCHChangeImage($change, $ts)
{
        if ( $change == 0 )
        {
                $image = '[img]http://www.tweakers.net/g/dpc/stil.gif[/img]';
                $change = '';
        }
        elseif ( $change < 0 )
        {
                $image = '[img]http://www.tweakers.net/g/dpc/down.gif[/img]';
                $change = $change - ( $change * 2 );
        }
        elseif ( $change > 0 )
	{
		if ( ( $change + ( $pos + $dlow ) ) > $ts->getPrevDayFlushCount() )
			$change = "";
                $image = '[img]http://www.tweakers.net/g/dpc/up.gif[/img]';
	}
	
	return '(' . $image . $change . ')';
}


function openColorTable($width = 0)
{
	$output = "";
	if ( $width == 0 )
		$output .= '<table class="outerTable">';
	else
		$output .= '<table class="outerTable" width="' . $width . '%">';
	$output .= '<tr><td>';
	$output .= '<table class="innerTable">';
	$output .= '<tr><td>';
	return $output;
}

function closeTable($times)
{
	for($i=0;$i<$times;$i++)
		echo '</td></tr></table>';
}

function getCurrentDate($prefix)
{
	return date("Y-m-d");
/*
        switch($prefix)
        {
	case 'fah':
		return date("Y-m-d", strtotime("+30 minutes", date("U")));
		break;
	default:
		return date("Y-m-d");
        }*/
}

function getPrevDate($datum = '')
{
	if ( $datum == '' )
		$datum = date("Y-m-d");
	
	$datum = date("U", strtotime($datum));

        return date("Y-m-d", strtotime("-1 day", $datum));
}

function getPrevWeek($datum = '')
{
	if ( $datum == '' )$datum = date("Y-m-d");
	return date("Y-m-d", strtotime("-1 week" ));
}

function trBackground($row)
{
	if ( $row == 0 )
		return '<tr class="firstCell">';
	elseif ( $row % 2 == 0 )
		return '<tr class="evenCell">';
	else
		return '<tr class="oddCell">';
}

function trBackgroundColor($row)
{
        if ( $row == 0 )
                return 'EAEAEA';
        elseif ( $row % 2 == 0 )
                return 'C0C0C0';
        else
                return 'CCCCCC';
}


function checkTable($tabel)
{
	if (
            ( $tabel != 'memberoffset' ) &&
            ( $tabel != 'teamoffset' ) &&
            ( $tabel != 'subteamoffset' ) &&
	    ( $tabel != 'individualoffset' )
   	   )die('Onjuiste tabel opgegeven');
}

function getURL($array)
{
	if ( ! isset($array['name']) )
		global $naam;
	else
		$naam = $array['name'];
	
	if ( ! isset($array['mode']) )
		global $mode;
	else
		$mode = $array['mode'];
	
	if ( ! isset($array['team']) )
		global $team;
	else
		$team = $array['team'];
	
	if ( ! isset($array['date']) )
		global $datum;
	else
		$datum = $array['date'];
	
	if ( ! isset($array['table']) )
		global $tabel;
	else
		$tabel = $array['table'];
	
	if ( ! isset($array['prefix']) )
	{
		global $project;
		$prefix = $project->getPrefix();
	}
	else
		$prefix = $array['prefix'];
	
	$link = $array['link'];
	$title = $array['title'];

	$href = 'index.php?mode=' . $mode . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) .
		'&amp;datum=' . $datum . '&amp;team=' . rawurlencode($team) . '&amp;prefix=' . $prefix;
	
	return '<a title="' . $title . '" href="' . $href . '">' . $link . '</a>';
}

function parseRML($rml)
{
	$search = array(	'/\[b\](.*?)\[\/b\]/is',
				'/\[table(.*?)\](.*?)\[\/table\]/is',
				'/\[tr(.*?)\](.*?)\[\/tr\]/is',
				'/\[tr\](.*?)\[\/tr\]/is',
				'/\[td(.*?)\](.*?)\[\/td\]/is',
				'/\[td\](.*?)\[\/td\]/is',
				'/\[img\](.*?)\[\/img\]/is',
				'/\[img=(.*?),(.*?)](.*?)\[\/img\]/is',
				'/\[url="(.*?)"\](.*?)\[\/url\]/is',
				'/\[red\](.*?)\[\/red\]/is',
				'/\[blue\](.*?)\[\/blue\]/is',
				'/' . chr(10) . '/is',
				'/\[small\](.*?)\[\/small\]/is',
				'/\[br\]/is');

	$replace = array(	'<b>\1</b>',
				'<table\1>\2</table>',
				'<tr\1>\2</tr>',
				'<tr>\1</tr>',
				'<td\1>\2</td>',
				'<td>\1</td>',
				'<img src="\1" alt="">',
				'<img width="\1px" height="\2px" src="\3" alt="">',
				'<a href="\1">\2</a>',
				'<font color="#FF0000">\1</font>',
				'<font color="#0000FF">\1</font>',
				'<br>',
				'\1',
				'<br>');

	$data = preg_replace($search, $replace, $rml);
	$data = preg_replace($search, $replace, $data);

	return $data;
}

function memberExists($project, $tabel, $member)
{
	global $db;

	$query = 'SELECT 
					naam 
				FROM 
					' . $project . '_' . $tabel . ' 
				WHERE 
					naam = \'' . $member . '\'
				AND	dag = \'' . date("Y-m-d") . '\'';
	
	$result = $db->selectQuery($query);

	if ( $line = $db->fetchArray($result) )
		return true;
	else
		return false;
}
?>
