#!/usr/bin/php
<?

include('/var/www/tstats/classes.php');
include('/var/www/tstats/include.php');

$query = 'SELECT 
		REPLACE(naam, \' - \', \'~\')AS name,
		(daily+cands)AS offset 
	FROM 
		rah_individualOffset
	WHERE
		naam IN
		(
			SELECT
				REPLACE(name, \'~\', \' - \')
			FROM
				stampedeParticipants
			ORDER BY
				name
		)
	AND	dag = \'' . date("Y-m-d") . '\'';

$result =$db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	$uQuery = 'UPDATE 
			stampedeParticipants
		SET
			offset = ' . $line['offset'] . '
		WHERE
			name = \'' . $line['name'] . '\'';
	
	$db->updateQuery($uQuery);
}

$db->disconnect();
?>
