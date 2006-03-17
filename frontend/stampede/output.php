<?

include('../classes.php');
include('../include.php');

$query = 'SELECT
		name,
		stampedeTeam
	FROM
		stampedeParticipants
	ORDER BY
		stampedeTeam,
		name';

$result = $db->selectQuery($query);

echo '<pre>' . "\n";
while ( $line = $db->fetchArray($result) )
{
	echo $line['stampedeTeam'] . ';' . $line['name'] . ';' . "\n";
}
echo '</pre>';

$db->disconnect();
?>
