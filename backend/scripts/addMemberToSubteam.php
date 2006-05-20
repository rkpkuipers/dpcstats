#!/usr/bin/php
<?

include ('/var/www/tstats/classes.php');

$team = $argv[1];
$user = $argv[2];
$project = $argv[3];

echo 'Adding ' . $user . ' to subteam ' . $team . ' for project ' . $project . ' (ctrl-c to abort)' . "\n";
readline("Str");

$datum = getCurrentDate($project);

# Add user day offset to team offset
$query = 'SELECT
		cands
	FROM
		' . $project . '_memberoffset
	WHERE
		naam = \'' . $user . '\'
	AND	dag = \'' . $datum . '\'';

$result = $db->selectQuery($query);

if ( $line = $db->fetchArray($result) )
	$userOffset = $line['cands'];
else
{
	$userOffset = 0;
	readline("User not found, add with offset 0?");
}

$query = 'UPDATE
		' . $project . '_memberoffset
	SET
		cands = cands + ' . $userOffset . '
	WHERE
		naam = \'' . $team . '\'
	AND	dag = \'' . $datum . '\'';

$db->updateQuery($query);
# Add entry for user in subteamOffset

$query = 'INSERT INTO
		' . $project . '_subteamoffset
		( naam, cands, dag, subteam )
	VALUES
	(
		\'' . $user . '\',
		' . $userOffset . ',
		\'' . getPrevDate($datum) . '\',
		\'' . $team . '\'
	)';

$db->insertQuery($query);

# Add user to subteam tabel
$query = 'INSERT INTO ' . $project . '_subteam ( name, member ) VALUES ( \'' . $team . '\', \'' . $user . '\')';
$db->insertQuery($query);

$db->disconnect();

?>
