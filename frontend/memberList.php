<?

include ('classes.php');

$members = array();

$query = 'SELECT DISTINCT(naam) FROM d2ol_memberOffset';
$result = $db->selectQuery($query);
while ( $line = mysql_fetch_array($result) )
	$members[] = $line['naam'];

$query = 'SELECT DISTINCT(naam) FROM tsc_memberOffset';
$result = $db->selectQuery($query);
while ( $line = mysql_fetch_array($result) )
	$members[] = $line['naam'];

$query = 'SELECT DISTINCT(naam) FROM tsc_memberOffsetBackup';
$result = $db->selectQuery($query);
while ( $line = mysql_fetch_array($result) )
        $members[] = $line['naam'];

$members = array_unique($members);
sort($members);
echo count($members) . ' leden<br>';
echo '<textarea style="width:300px; height:150px">';
for($i=0;$i<count($members);$i++)
	echo $members[$i] . "\n";
echo '</textarea>';
?>
