#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');

$result = mysql_list_tables('stats');

$optQuery = 'OPTIMIZE TABLE ';

$i = 0;
while ( $line = mysql_fetch_array($result) )
{
	if ( $i > 0 ) $optQuery .= ',';
	else $i++;
	$optQuery .= '`' . $line['0'] . '`';
#	echo $line['0'];
}
#echo $optQuery;
$result = $db->selectQuery($optQuery);

while ( $line=mysql_fetch_array($result))
	echo $line['0'] . "\r\t\t\t" . $line['1'] . "\t" . $line['2'] . "\t" . $line['3'] . "\n";
?>
