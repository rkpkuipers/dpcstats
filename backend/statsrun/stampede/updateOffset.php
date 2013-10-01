#!/usr/bin/php
<?php

include (dirname(realpath($argv[0])) . '/../include.php');

$query = 'SELECT 
		REPLACE(naam, \' - \', \'~\')AS name,
		(daily+cands)AS offset 
	FROM 
		rah_individualoffset
	WHERE
		naam IN
		(
			SELECT
				REPLACE(name, \'~\', \' - \')
			FROM
				stampedeparticipants
			WHERE
				offset = 0
			ORDER BY
				name
		)
	AND	dag = \'' . date("Y-m-d") . '\'';

$result =$db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	$uQuery = 'UPDATE 
			stampedeparticipants
		SET
			offset = ' . $line['offset'] . '
		WHERE
			name = \'' . $line['name'] . '\'';
	
	$db->updateQuery($uQuery);
}

$db->disconnect();
?>
