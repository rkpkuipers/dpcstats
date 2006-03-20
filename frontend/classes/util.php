<?
function getCalender($datum)
{
	echo '<center>';
	echo '<hr width="95%">';
	echo '<table width="92%">';
	echo '<tr><td align="left">Stats van:</td>';
	echo '<td align="right">' . date("d-m-Y", strtotime($datum)) . '</td>';
	echo '</tr></table>';
	echo '<table width="92%" bgcolor="#000000" cellpadding="1" cellspacing="1">';
	echo '<tr><td>';
	echo '<table width="100%" cellpadding="1" cellspacing="1">';
	echo trBackground(0);
	echo '<td align="center" colspan="7">';
	echo getURLByDate('&lt;&lt;', date("Y-m-d", strtotime("-1 month", strtotime($datum)))) . ' ';
	echo getURLByDate('&lt;', date("Y-m-d", strtotime("-1 day", strtotime($datum)))) . ' ';
	echo date("M Y", strtotime($datum)) . '&nbsp;';
	echo getURLByDate('&gt;', date("Y-m-d", strtotime("+1 day", strtotime($datum)))) . ' ';
	echo getURLByDate('&gt;&gt;', date("Y-m-d", strtotime("+1 month", strtotime($datum))));
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
			echo '<td style="background-color:' . trBackgroundColor($i) . '" bgcolor="' . trBackgroundColor($i) . '" align="center">' . getURLByDate($i, date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime("-1 month", strtotime($datum)))) . '</td>';
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
			echo getURLByDate($i, $datum = date("Y-m-" . str_pad($i, 2, 0, STR_PAD_LEFT), strtotime($datum)));
		}
		echo '</td>';
		$pos++;
	}

	if ( $pos%7 != 0 )
		for($i=($pos%7);$i<7;$i++)
		{
			if ( ( date("Y-m-" . str_pad((($i-($pos%7))+1), 2, 0, STR_PAD_LEFT), strtotime("+1 month", strtotime($datum))) ) > date("Y-m-d") )
				echo '<td bgcolor="#CBCBCB" align="center">' . ($i-($pos%7)+1) . '</td>';
			else
				echo '<td bgcolor="#CBCBCB" align="center">' . getURLByDate(($i-($pos%7))+1, date("Y-m-" . str_pad((($i-($pos%7))+1), 2, 0, STR_PAD_LEFT), strtotime("+1 month", strtotime($datum)))) . '</td>';
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
		echo '<tr cellspacing="0" cellpadding="0" style="background-image:url(images/left-banner.jpg); height:45px">';
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
			echo '<td><a href="index.php?mode=detail&amp;prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($mbs[$i]->getName()) . '">' . $mbs[$i]->getShortName() . '</a></td>';
			echo '</tr>';
			echo '<tr>';
		}
		
		echo '</table><br>';
	}
}

function getShoutboxTable($db, $project, $tabel, $team)
{
	global $baseUrl;

	echo '<table width="180px" cellspacing="0" cellpadding="0">';
	echo '<tr cellspacing="0" cellpadding="0" style="background-image:url(images/left-banner.jpg); height:45px">';
	echo '<td align="center" style="font-size:13px; font-weight:bold; color:#FFFFFF; cursor:pointer;" ';
	echo 'onclick=\'window.open("' . $baseUrl . '/index.php?mode=shoutbox", "_self")\'>Shoutbox</td>';
	echo '</tr></table>';

	echo '<table width="180px" cellspacing="2">';

	$shoutbox = new Shoutbox($db);
	$shoutbox->getMessages(8);
	$messages = $shoutbox->getMessageList();
	
	for($i=0;$i<count($messages);$i++)
	{
		echo trBackground(0);
		echo '<td>';
		echo date("d-m H:i", strtotime($messages[$i]->getTijd())) . ' - ';
		
		if ( $messages[$i]->getEmail() == '' )
			echo $messages[$i]->getPoster();
		else
			echo '<a href="mailto:' . $messages[$i]->getEmail() . '">' . $messages[$i]->getPoster() . '</a>';

		echo '</td>';
		echo '</tr>';

		echo trBackground($i+1);
		echo '<td width="180px">';
		
		$mess = explode(' ', $messages[$i]->getBericht());
		for($j=0;$j<count($mess);$j++)
		{
			if ( ( strlen($mess[$j]) > 15 ) && 
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
?>
	<form method="get" action="addMessage.php" name="shoutbox">
		<input type="hidden" name="tabel" value="<? echo $tabel; ?>">
		<input type="hidden" name="prefix" value="<? echo $project->getPrefix(); ?>">
		<input type="hidden" name="team" value="<? echo rawurlencode($team); ?>">
	<table>
	
	<tr><td>Poster</td></tr>
	<tr><td><input class="TextField" type="text" name="poster" style="width:155px"></td></tr>
	<tr><td><br></td></tr>
	<tr><td>E-Mail <small>(optional)</small></td></tr>
	<tr><td><input class="TextField" type="text" name="email" style="width:155px"></td></tr>
	<tr><td><br></td></tr>
	<tr><td>
	
	<table width="100%">
	<tr>
	<td>Message</td>
	<td align="right"><input class="TextField" style="text-align:right;" type="text" name="textcount" size="3" value="350" readonly></td>
	</tr>
	</table>
	
	</td></tr>
	<tr><td><textarea class="TextField" name="bericht" cols="18" rows="3" onkeyup="TrackCount(this,'textcount',350)" onkeypress="LimitText(this,350)"></textarea></td></tr>
	<tr><td><br></td></tr>
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
<?
	$smDir = $baseUrl . '/images/smilies/';
?>
	<img class="smiley" src="<? echo $smDir; ?>smile.gif" onClick="document.shoutbox.bericht.value+=':) '" alt=":)">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>biggrin.gif" onClick="document.shoutbox.bericht.value+=':D '" alt=":D">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>clown.gif" onClick="document.shoutbox.bericht.value+=':+ '" alt=":+">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>cry.gif" onClick="document.shoutbox.bericht.value+=':\'( '" alt=":'(">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>devil.gif" onClick="document.shoutbox.bericht.value+='>:) '" alt=">:)">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>frown.gif" onClick="document.shoutbox.bericht.value+=':( '" alt=":(">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>frusty.gif" onClick="document.shoutbox.bericht.value+='|:( '" alt="|:("><br>
	&nbsp;&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>kwijl.gif" onClick="document.shoutbox.bericht.value+=':9~ '" alt=":9~">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>puh2.gif" onClick="document.shoutbox.bericht.value+=':P '" alt=":P">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>pukey.gif" onClick="document.shoutbox.bericht.value+=':r '" alt=":r">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>redface.gif" onClick="document.shoutbox.bericht.value+=':o '" alt=":0">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>wink.gif" onClick="document.shoutbox.bericht.value+=';) '" alt=";)">&nbsp;
	<img class="smiley" src="<? echo $smDir; ?>bye.gif" onClick="document.shoutbox.bericht.value+=':w '" alt=":w"><br>

	&nbsp;&nbsp;<small><a href="http://www.tweakers.net">&copy; Tweakers.net</a></small>
	<br><br>
<?
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
		echo '<td align="center">' . getCompleteURL($line['naam'], $naam = $line['naam']) . '</td>';
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

function getDefaultFormFields()
{
	global $naam, $tabel, $project, $team, $datum, $debug;

	echo '<input type="hidden" name="naam" value="' . $naam . '">';
	echo '<input type="hidden" name="tabel" value="' . $tabel . '">';
	echo '<input type="hidden" name="prefix" value="' . $project->getPrefix() . '">';
	echo '<input type="hidden" name="datum" value="' . $datum . '">';
	echo '<input type="hidden" name="team" value="' . $team . '">';

	if ( isset($debug) )
		echo '<input type="hidden" name="debug" value="' . $debug . '">';
}

function getMenuHeader($link, $cookie)
{
	global $project, $naam, $tabel, $datum, $mode, $$cookie;

	$html = trBackground(0);
	$html .= '<td colspan="2" align="left">';
	$html .= '<table width="100%" cellpadding="0" cellspacing="0">';
	$html .= '<tr>';
	$html .= '<td style="font-weight:bold" align="left">' . $link . '</td>';
	$html .= '<td align="right" valign="middle" style="font-size:10px">';
	$html .= '<a href="index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;datum=' . $datum. '&amp;mode=' . $mode  . 
		'&amp;naam=' . rawurlencode($naam) . '&amp;' . $cookie . '=' . ($$cookie=='on'?'off':'on') . 
		'&amp;set' . ucfirst($cookie) . '">' . ($$cookie=='off'?'un':'') . 'hide</a>';
	$html .= '</td></tr></table></td></tr>';

	return $html;
}

function getMenuEntry($link, $target, $cellNo)
{
	$html = trBackground($cellNo);
	$html .= '<td align="center" valign="middle" width="7px"><img src="images/dot.png" alt="dot"></td>';
	$html .= '<td align="left"><a href="' . $target . '">' . $link . '</a></td></tr>';

	return $html;
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
