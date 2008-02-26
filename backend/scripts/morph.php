#!/usr/bin/php
<?
include('include.php');

$query = 'SELECT DISTINCT(naam) FROM fah_individualoffset WHERE naam like \'% - %\'';
$result = $db->selectQuery($query);

$updates = array();
while ( $line = $db->fetchArray($result) )
{
	$updates[] = 'UPDATE fah_individualoffset SET naam = \'' . str_replace(' - ', '0', $line['0']) . '\' WHERE naam = \'' . $line['0'] . '\'';
}

for($i=0;$i<count($updates);$i++)
	$db->updateQuery($updates[$i]);
#print_r($updates);

$db->disconnect();
?>
