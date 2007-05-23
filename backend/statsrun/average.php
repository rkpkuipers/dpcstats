#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

function calculateAverage($prefix, $tabel)
{
	global $datum, $db;

	$db->deleteQuery('DELETE FROM averageproduction WHERE tabel = \'' . $tabel . '\'');
	
	$query = 'INSERT INTO
			averageproduction
		SELECT
		';
	
	if ( $tabel == $prefix . '_subteamoffset' )
		$query .= 'DISTINCT(CONCAT(m1.subteam, \'' . getSeperator($prefix) . '\', m1.naam)), ';
	else
		$query .= 'DISTINCT(m1.naam), ';
	
	$query .='	AVG(m2.daily), 
			AVG(m3.daily), 
			\'' . $tabel . '\'
		FROM
			' . $tabel . ' m1, 
			' . $tabel . ' m2, 
			' . $tabel . ' m3 
		WHERE 
			m2.naam = m1.naam 
		AND 	m1.naam = m3.naam 
		AND 	m2.naam = m3.naam ';
	
	if ( $tabel == $prefix . '_subteamoffset' )
		$query .= 'AND m1.subteam = m2.subteam AND m2.subteam = m3.subteam AND m1.subteam = m3.subteam ';
	
	$query .='
		AND 	m1.dag = \'' . $datum . '\' 
		AND 	m2.dag >= \'' . date("Y-m-d", strtotime("-7 day", strtotime($datum))) . '\'
		AND 	m3.dag >= \'' . date("Y-m-d", strtotime("-1 month", strtotime($datum))) . '\' 
		GROUP BY 
			m1.naam';
	$db->insertQuery($query);

	$query = 'INSERT INTO
			averageproduction
		SELECT 
			DISTINCT(CONCAT(subteam, \'' . getSeperator($prefix) . '\', naam))AS cnaam, 
			0, 
			0, 
			\'' . $tabel . '\'
		FROM 
			' . $tabel . '
		WHERE 
			dag = \'' . $datum . '\' 
		AND 	CONCAT(subteam,\'' . getSeperator($prefix) . '\', naam) NOT IN 
			(
				SELECT 
					naam 
				FROM 
					averageproduction 
				WHERE 
					tabel = \'' . $tabel . '\'
			)';
	
	$db->insertQuery($query);
}

$query = 'SHOW TABLES FROM stats';
$result = $db->selectQuery($query);

$tables = array();
while ( $line = $db->fetchArray($result) )
{
	if ( substr($line[0], -6, 6) == 'offset' )
		$tables[] = $line[0];
}

foreach($tables as $table)
{
	$datum = getCurrentDate(substr($table, 0, strpos($table, '_') ));
	calculateAverage(substr($table, 0, strpos($table, '_') ), $table);
}

$db->disconnect();

?>
