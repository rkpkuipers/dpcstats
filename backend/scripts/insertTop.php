#!/usr/bin/php
<?php

include('/home/rkuipers/public_html/classes.php');

$table = $argv[1];

$data = file('data.txt');

for($line=0;$line<count($data);$line++)
{
	$fields = explode("\t", $data[$line]);
	$query = 'INSERT INTO ' . 
			$table . 
		' ( naam, dag, daily ) VALUES (\'' . $fields[1] . '\', ' .
		'\'' . preg_replace('/([0-9]{2})-([0-9]{2})-([0-9]{4})/', '\3-\2-\1', trim($fields[3])) . '\', ' . str_replace('.', '', $fields[2]) . ')';
	echo $query . "\n";
	$db->insertQuery($query);
}

$db->disconnect();
?>
