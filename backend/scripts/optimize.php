#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$db = new miDataBase($dbuser, $dbpass, $dbhost, $dbport, $dbname);
$db->connect();

$result = $db->selectQuery('SHOW TABLES FROM stats');

$tables = array();
while ( $line = $db->fetchArray($result) )
{
	$tables[] = $line['0'];
}

foreach($tables as $table)
{
	$query = 'OPTIMIZE TABLE ' . $table;
	$result = $db->selectQuery($query);

	while ( $line = $db->fetchArray($result))
		echo $line['0'] . "\r\t\t\t\t" . $line['1'] . "\t" . $line['2'] . "\t" . $line['3'];
	
	sleep(15);

	echo "\n";
}
?>
