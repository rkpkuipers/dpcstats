<?
include('../classes.php');
include('../include.php');

if ( isset($_REQUEST['nick']) )
{
	$nick = $_REQUEST['nick'];
}

if ( isset($_REQUEST['team']) )
{
	$team = $_REQUEST['team'];
}

if ( ( $nick != '' ) && ( $team != '' ) )
{
	if ( $team == 'No Team' )
	{
		$query = 'DELETE FROM
				stampedeParticipants
			WHERE
				name = \'' . $nick . '\'';
	}
	else
	{
		$query = 'SELECT
				(cands+daily)AS offset
			FROM
				rah_individualOffset
			WHERE
				dag = \'' . date("Y-m-d") . '\'
			AND	naam = \'' . str_replace('~', ' - ', $nick) . '\'';
		$result = $db->selectQuery($query);
		if ( $line = $db->fetchArray($result) )
			$offset = $line['offset'];
		else
			$offset = 0;
			
		$query = 'REPLACE INTO 
				stampedeParticipants
			(
				name,
				stampedeTeam,
				offset
			)
			VALUES
			(
				\'' . $nick . '\',
				\'' . $team . '\',
				' . $offset . '
			)';
	}
	$db->selectQuery($query);
}

?>

<html>
<body>
<center><h2>Stampede V - Inschrijfformulier</h2></center>
<hr>
Join een team door je nick te selecteren uit de Nickname lijst en het team wat je wilt joinen uit het Stampede team lijstje. Van team veranderen kan door nogmaals je nick te selecteren en dan het nieuwe team in de tweede box. Op dezelfde manier kan de inschrijving ongedaan gemaakt worden door No Team te joinen. De inschrijving sluit om middernacht op 28 maart, het formulier zal dan uitgeschakeld worden.
<hr>
<center>
<form name="addMember" action="signup.php" method="post">
<table>
<tr><td align="center">Nickname</td><td align="center">Stampede Team</td></tr>
<tr>
<td>
<select name="nick">
<?

$query = '(
	SELECT
		mo.naam
	FROM
		rah_memberOffset mo
	WHERE
		mo.dag = \'' . date("Y-m-d") . '\'
	AND 	mo.naam NOT IN ( SELECT DISTINCT(subteam) FROM rah_subteamOffset WHERE dag = \'' . date("Y-m-d") . '\')
	)
	UNION
	(
	SELECT
		CONCAT(subteam, \'~\', naam) AS naam
	FROM
		rah_subteamOffset
	WHERE
		dag = \'' . date("Y-m-d") . '\'
	)
	ORDER BY
		naam';

$result = $db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	echo '<option>' . $line['naam'] . '</option>';
}

?>
</select>
</td>
<td>
<select name="team">
<option>No Team</option>
<option>Bearhunters</option>
<option>eXtreme Stampers</option>
<option>Furious Dutch Cows</option>
<option>Joint Forces</option>
<option>Lucky Angel: Stampede Chicken</option>
<option>The Fok! Flock</option>
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
<hr>
<table>
<tr><td><h3>Huidige Indeling</h3></td></tr>
<tr><td></td></tr>
<?
$query = 'SELECT
		count(name)AS teamCount,
		stampedeTeam
	FROM
		stampedeParticipants
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
		stampedeParticipants
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
<div align="right"><a href="mailto:speedkikker@planet.nl">Mail Webmaster</a></div>
</body>
</html>
