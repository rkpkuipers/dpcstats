#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');

function calculateAverage($tabel)
{
	global $datum, $db;

	mysql_query('DELETE FROM averageproduction WHERE tabel = \'' . $tabel . '\'');
	
#	echo date("U") . "\t" . $tabel . "\n";
	$query = 'INSERT INTO
			averageproduction
		SELECT 
			DISTINCT(m1.naam), 
			AVG(m2.daily), 
			AVG(m3.daily), 
			\'' . $tabel . '\'
		FROM
			' . $tabel . ' m1, 
			' . $tabel . ' m2, 
			' . $tabel . ' m3 
		WHERE 
			m2.naam = m1.naam 
		AND 	m1.naam = m3.naam 
		AND 	m2.naam = m3.naam 
		AND 	m1.dag = \'' . $datum . '\' 
		AND 	m2.dag >= \'' . date("Y-m-d", strtotime("-7 day", strtotime($datum))) . '\'
		AND 	m3.dag >= \'' . date("Y-m-d", strtotime("-1 month", strtotime($datum))) . '\' 
		GROUP BY 
			m1.naam';

	$db->insertQuery($query);
#	echo $query . "\n\n";
}

$datum = getCurrentDate('tsc');

calculateAverage('tp2_memberOffset');
#calculateAverage('tp2_subteamOffset');
calculateAverage('tp2_teamOffset');

$datum = getCurrentDate('d2ol');

calculateAverage('d2ol_memberOffset');
calculateAverage('d2ol_teamOffset');

/*
$datum = getCurrentDate('fad');

calculateAverage('fad_memberOffset');
calculateAverage('fad_teamOffset');
calculateAverage('fad_subteamOffset');
*/

$datum = getCurrentDate('sob');

calculateAverage('sob_memberOffset');
calculateAverage('sob_teamOffset');
calculateAverage('sob_subteamOffset');
calculateAverage('sob_individualOffset');

$datum = getCurrentDate('rah');

calculateAverage('rah_memberOffset');
calculateAverage('rah_teamOffset');
calculateAverage('rah_subteamOffset');
calculateAverage('rah_individualOffset');

$datum = getCurrentDate('ud');

calculateAverage('ud_teamOffset');
calculateAverage('ud_memberOffset');

$datum = getCurrentDate('sah');

calculateAverage('sah_teamOffset');
calculateAverage('sah_memberOffset');
calculateAverage('sah_subteamOffset');

$datum = getCurrentDate('ufl');

calculateAverage('ufl_teamOffset');
calculateAverage('ufl_memberOffset');
calculateAverage('ufl_subteamOffset');
calculateAverage('ufl_individualOffset');

$datum = getCurrentDate('fah');

calculateAverage('fah_teamOffset');
calculateAverage('fah_memberOffset');
calculateAverage('fah_subteamOffset');
calculateAverage('fah_individualOffset');

$datum = getCurrentDate('smp');

calculateAverage('smp_teamOffset');
calculateAverage('smp_memberOffset');
calculateAverage('smp_subteamOffset');
calculateAverage('smp_individualOffset');

$datum = getCurrentDate('sp5');

calculateAverage('sp5_memberOffset');
calculateAverage('sp5_subteamOffset');
calculateAverage('sp5_individualOffset');
