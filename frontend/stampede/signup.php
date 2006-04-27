<?

$_REQUEST['prefix'] = 'rah';

include('../classes.php');

/*

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
	{
		$query = 'SELECT
				(cands+daily)AS offset
			FROM
				rah_individualoffset
			WHERE
				dag = \'' . date("Y-m-d") . '\'
			AND	naam = \'' . str_replace('~', ' - ', $nick) . '\'';
		$result = $db->selectQuery($query);
		if ( $line = $db->fetchArray($result) )
			$offset = $line['offset'];
		else
			$offset = 0;
			
		$query = 'REPLACE INTO 
				stampedeparticipants
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
*/
?>

<html>
<body>
<center><h2>Stampede V - Inschrijfformulier</h2></center>
<hr>
Vanaf 2 April tot 27 April is het mogelijk om een willekeurig stampedeteam te joinen. Om de competitie zo leuk mogelijk te houden wordt iedereen aangemoedigd om zich bij een van de lager geklasseerde teams aan te sluiten. Het veranderen van team of jezelf uitschrijven is in principe niet mogelijk, mocht er toch een dringende reden zijn waarom je van team wilt veranderen kan je hierover contact opnemen met Elteor via FDO of IRC. Lid worden van een stampede team kan door je nick te selecteren uit de Nickname lijst en het team wat je wilt joinen uit het Stampede team lijstje. 
<hr>
<? /*
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
		rah_memberoffset mo
	WHERE
		mo.dag = \'' . date("Y-m-d") . '\'
	AND 	mo.naam NOT IN ( SELECT DISTINCT(subteam) FROM rah_subteamoffset WHERE dag = \'' . date("Y-m-d") . '\')
	AND	mo.naam NOT IN ( SELECT name FROM stampedeparticipants )
	)
	UNION
	(
	SELECT
		CONCAT(subteam, \'~\', naam) AS naam
	FROM
		rah_subteamoffset
	WHERE
		dag = \'' . date("Y-m-d") . '\'
	AND	CONCAT(subteam, \'~\', naam) NOT IN ( SELECT name FROM stampedeparticipants )
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
<option>Bearhunters</option>
<option>eXtreme Stampers</option>
<option>Furious Dutch Cows</option>
<option>Joint Forces</option>
<option>Lucky Angel: Stampede Chicken</option>
<option>Stampertjes</option>
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
<center>
<hr>
*/ ?>
<center>
<table>
<tr><td><h3>Huidige Indeling</h3></td></tr>
<tr><td></td></tr>
<?
$query = 'SELECT
		count(name)AS teamCount,
		stampedeTeam
	FROM
		stampedeparticipants
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
		stampedeparticipants
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
