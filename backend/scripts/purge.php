#!/usr/bin/php
<?php

include('/home/rkuipers/public_html/classes/database.php');

$db = new miDataBase();
$db->connect();

$query = 'SHOW PROCESSLIST';
$result = $db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	if ( ( $line['User'] == 'statsuser' ) && ( $line['Time'] > 300 ) )
	{
	#	echo 'Killed '. $line['Id'];
		$db->selectQuery('kill ' . $line['Id']);
	}
}

$db->disconnect();
