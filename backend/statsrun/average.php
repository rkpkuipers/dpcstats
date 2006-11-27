#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

function calculateAverage($tabel)
{
	global $datum, $db;

	$db->deleteQuery('DELETE FROM averageproduction WHERE tabel = \'' . $tabel . '\'');
	
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

$datum = getCurrentDate('rah');

calculateAverage('rah_memberoffset');
calculateAverage('rah_teamoffset');
calculateAverage('rah_subteamoffset');
calculateAverage('rah_individualoffset');

$datum = getCurrentDate('sob');

calculateAverage('sob_memberoffset');
calculateAverage('sob_teamoffset');
calculateAverage('sob_subteamoffset');
calculateAverage('sob_individualoffset');

$datum = getCurrentDate('smp');

calculateAverage('smp_teamoffset');
calculateAverage('smp_memberoffset');
calculateAverage('smp_subteamoffset');
calculateAverage('smp_individualoffset');

$datum = getCurrentDate('fah');

calculateAverage('fah_teamoffset');
calculateAverage('fah_memberoffset');
calculateAverage('fah_subteamoffset');
calculateAverage('fah_individualoffset');

$datum = getCurrentDate('sah');

calculateAverage('sah_teamoffset');
calculateAverage('sah_memberoffset');
calculateAverage('sah_subteamoffset');

$datum = getCurrentDate('tsc');

calculateAverage('tsc_memberoffset');
calculateAverage('tsc_teamoffset');

$datum = getCurrentDate('d2ol');

calculateAverage('d2ol_memberoffset');
calculateAverage('d2ol_teamoffset');

$datum = getCurrentDate('ufl');

calculateAverage('ufl_teamoffset');
calculateAverage('ufl_memberoffset');
calculateAverage('ufl_subteamoffset');
calculateAverage('ufl_individualoffset');

$datum = getCurrentDate('ud');

calculateAverage('ud_teamoffset');
calculateAverage('ud_memberoffset');

$datum = getCurrentDate('ldc');
calculateAverage('ldc_teamoffset');
calculateAverage('ldc_memberoffset');
calculateAverage('ldc_subteamoffset');
calculateAverage('ldc_individualoffset');
