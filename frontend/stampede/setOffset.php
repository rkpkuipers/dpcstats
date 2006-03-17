#!/usr/bin/php
<?

include('../classes.php');
include('../include.php');

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
		)';

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
