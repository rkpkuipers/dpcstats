#!/usr/bin/php
<?

include ('/var/www/tstats/classes.php');

$tables = array('memberoffset', 'teamoffset', 'subteamoffset', 'individualoffset');

if ( $argv[1] == '' )
	die('Script requires at least a name as argument' . "\n");

$name = $argv[1];

if ( isset($argv[2]) )
	$prefix = array($argv[2]);
else
{
	
	$query = 'SELECT project FROM project ORDER BY project';
	$result = $db->selectQuery($query);

	while ( $line = $db->fetchArray($result) )
		$prefix[] = $line['project'];
}

foreach ($prefix as $project)
{
	foreach ($tables as $table)
	{
		$query = 'SELECT naam, (cands+daily)AS total FROM ' . $project . '_' . $table . ' WHERE dag = \'' . date("Y-m-d") . '\' AND naam = \'' . $naam . '\'';
		$result = $db->selectQuery($query);

		while ( $line = $db->fetchArray($result) )
			echo "1${line['naam']}\t${line['total']}\n";
	}
}
echo "\n";

$db->disconnect();
?>
