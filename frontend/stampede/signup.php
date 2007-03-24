<?

$_REQUEST['prefix'] = 'rah';

include('../classes.php');

if ( isset($_REQUEST['nick']) )
{
	$nick = $_REQUEST['nick'];
}

if ( isset($_REQUEST['team']) )
{
	$team = $_REQUEST['team'];
}

$errorAddingMember = false;
if ( ( $nick != '' ) && ( $team != '' ) )
{
	if ( ( memberExists('fah', 'individualoffset', $nick) ) || ( $_GET['forced'] == 1 ) )
	{
		$query = 'SELECT
				(cands+daily)AS offset
			FROM
				fah_individualoffset
			WHERE
				dag = \'' . date("Y-m-d") . '\'
			AND	naam = \'' . $db->real_escape_string($nick) . '\'';
		$result = $db->selectQuery($query);
		if ( $line = $db->fetchArray($result) )
			$offset = $line['offset'];
		else
			$offset = 0;
			
		$query = 'REPLACE INTO 
				stampede6participants
			(
				name,
				stampedeTeam,
				offset
			)
			VALUES
			(
				\'' . $db->real_escape_string($nick) . '\',
				\'' . $db->real_escape_string($team) . '\',
				' . $offset . '
			)';

		$db->selectQuery($query);
	}
	else
	{
		$errorAddingMember = true;
	}
}

?>

<html>
<head>
<link rel="stylesheet" href="/page.css" type="text/css">
</head>
<body style="background-color:#CBCBCB">
<center><h2>Stampede V - Inschrijfformulier</h2></center>
<hr>
Tot 31 maart is het mogelijk om een willekeurig stampedeteam te joinen. Om de competitie zo leuk mogelijk te houden wordt iedereen aangemoedigd om zich bij een van de lager geklasseerde teams aan te sluiten. Het veranderen van team of jezelf uitschrijven is in principe niet mogelijk, mocht er toch een dringende reden zijn waarom je van team wilt veranderen kan je hierover contact opnemen met Elteor via FDO of IRC. Lid worden van een stampede team kan door je nick in te voeren in het tekstveld, het team wat je wilt joinen uit het Stampede team lijstje te kiezen en op Join te klikken. 
<hr>
<center>
<?
if ( $errorAddingMember )
{
?>
<span style="color:#FF0000">De user <? echo $nick; ?> is geen actief lid van het Folding@Home DPC team.</span><br>
Criteria voor actief lid zijn is onderdeel uitmaken van het DPC team (team 92) en minimaal 1 punt gescoord hebben.<br>
Om <b><? echo $nick; ?></b> toch toe te voegen aan team <b><? echo $team; ?></b> klik <a href="/stampede/signup.php?nick=<? echo $nick; ?>&amp;team=<? echo $team; ?>&amp;forced=1">hier</a>
<br>
<hr>
<?
}
?>
<form name="addMember" action="signup.php" method="post">
<table>
<tr><td align="center">Nickname</td><td align="center">Stampede Team</td></tr>
<tr>
<td>
<?
/*
<select name="nick">
$query = '(
	SELECT
		mo.naam
	FROM
		fah_memberoffset mo
	WHERE
		mo.dag = \'' . date("Y-m-d") . '\'
	AND 	mo.naam NOT IN ( SELECT DISTINCT(subteam) FROM fah_subteamoffset WHERE dag = \'' . date("Y-m-d") . '\')
	AND	mo.naam NOT IN ( SELECT name FROM stampede6participants )
	)
	UNION
	(
	SELECT
		CONCAT(subteam, \'~\', naam) AS naam
	FROM
		fah_subteamoffset
	WHERE
		dag = \'' . date("Y-m-d") . '\'
	AND	CONCAT(subteam, \'~\', naam) NOT IN ( SELECT name FROM stampede6participants )
	)
	ORDER BY
		naam';

$result = $db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	echo '<option>' . $line['naam'] . '</option>';
}
</select>
*/

echo '<input type="text" class="TextField" name="nick" value="' . ($errorAddingMember?$nick:'') . '">';
?>
</td>
<td>
<select name="team">
<?
$subteams = array('Folding Beasts', 'Bruce\'s Angels', 'The Folding SoB-ers', 'LSD Stampers', 'De Stampertjes');
foreach($subteams as $name)
	echo '<option ' . (($errorAddingMember)&&($team==$name)?'selected':'') . '>' . $name . '</option>';
?>
</select>
</td>
</tr>
<tr>
<td colspan="2" align="center">
<input type="submit" value="Join">
</td>
</tr>
</table>
</form>
<center>
<hr>
<center>
<table>
<tr><td><h3>Huidige Indeling</h3></td></tr>
<tr><td></td></tr>
<?
$query = 'SELECT
		count(name)AS teamCount,
		stampedeTeam
	FROM
		stampede6participants
	GROUP BY
		stampedeTeam';

$result = $db->selectQuery($query);

$teamSize;
while ( $line = $db->fetchArray($result) )
	$teamSize[$line['stampedeTeam']] = $line['teamCount'];
	
$query = 'SELECT
		name,
		stampedeTeam
	FROM
		stampede6participants
	ORDER BY
		stampedeTeam,
		name';

$result = $db->selectQuery($query);

$cTeam = '';
while ( $line = $db->fetchArray($result) )
{
	if ( $cTeam != $line['stampedeTeam'] )
	{
		if ( $cTeam != '' ) echo '<tr><td><hr></td></tr>';
		echo '<tr><td align="center"><h4>' . $line['stampedeTeam'] . ' (' . $teamSize[$line['stampedeTeam']] . ' leden)</h4></td></tr>';
		$cTeam = $line['stampedeTeam'];
	}
	echo '<tr><td>' . $line['name'] . '</td></tr>';
}
?>
<tr><td><hr></td></tr>
</table>
</center>
<hr>
<table width="100%">
<tr>
<td><a href="teams.php">Grafische weergave aanmeldingen</a></td>
<td align="right"><a href="mailto:speedkikker@planet.nl">Mail Webmaster</a></td>
</tr>
</table>
</body>
</html>
