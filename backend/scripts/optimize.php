#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$db = new miDataBase($dbuser, $dbpass, $dbhost, $dbport, $dbname);
$db->connect();

$result = $db->selectQuery('SHOW TABLES FROM stats');

$optQuery = 'OPTIMIZE TABLE ';

$i = 0;
while ( $line = $db->fetchArray($result) )
{
	if ( $i > 0 ) $optQuery .= ',';
	else $i++;
	$optQuery .= '`' . $line['0'] . '`';
#	echo $line['0'];
}
#echo $optQuery;
$result = $db->selectQuery($optQuery);

while ( $line = $db->fetchArray($result))
	echo $line['0'] . "\r\t\t\t" . $line['1'] . "\t" . $line['2'] . "\t" . $line['3'] . "\n";
?>
