#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

$db->updateQuery('UPDATE stampedeparticipants SET offset = 0');
$db->deleteQuery('DELETE FROM sp5_memberoffset WHERE dag < \'' . getPrevDate() . '\'');
$db->deleteQuery('DELETE FROM sp5_subteamoffset WHERE dag < \'' . getPrevDate() . '\'');
$db->deleteQuery('DELETE FROM sp5_memberoffset WHERE dag = \'' . date("Y-m-d") . '\'');
$db->deleteQuery('DELETE FROM sp5_subteamoffset WHERE dag = \'' . date("Y-m-d") . '\'');
$db->updateQuery('UPDATE sp5_memberoffset SET daily=0, cands=0');
$db->updateQuery('UPDATE sp5_subteamoffset SET daily=0, cands=0');

$query = 'SELECT 
		REPLACE(naam, \' - \', \'~\')AS name,
		(cands)AS offset 
	FROM 
		rah_individualoffset
	WHERE
		naam IN
		(
			SELECT
				REPLACE(name, \'~\', \' - \')
			FROM
				stampedeparticipants
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
			name = \'' . str_replace('\'', '\\\'', $line['name']) . '\'';
	
	$db->updateQuery($uQuery);
}

$db->disconnect();
?>
