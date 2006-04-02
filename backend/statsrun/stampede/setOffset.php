#!/usr/bin/php
<?

include('/var/www/tstats/classes.php');

$db->updateQuery('UPDATE stampedeParticipants SET offset = 0');
$db->deleteQuery('DELETE FROM sp5_memberOffset WHERE dag < \'' . getPrevDate() . '\'');
$db->deleteQuery('DELETE FROM sp5_subteamOffset WHERE dag < \'' . getPrevDate() . '\'');
$db->deleteQuery('DELETE FROM sp5_memberOffset WHERE dag = \'' . date("Y-m-d") . '\'');
$db->deleteQuery('DELETE FROM sp5_subteamOffset WHERE dag = \'' . date("Y-m-d") . '\'');
$db->updateQuery('UPDATE sp5_memberOffset SET daily=0, cands=0');
$db->updateQuery('UPDATE sp5_subteamOffset SET daily=0, cands=0');

$query = 'SELECT 
		REPLACE(naam, \' - \', \'~\')AS name,
		(cands)AS offset 
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
			name = \'' . str_replace('\'', '\\\'', $line['name']) . '\'';
	
	$db->updateQuery($uQuery);
}

$db->disconnect();
?>
