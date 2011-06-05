<?php

include ('classes.php');

$members = array();

$query = 'SELECT DISTINCT(naam) FROM d2ol_memberoffset';
$result = $db->selectQuery($query);
while ( $line = $db->fetchArray($result) )
	$members[] = $line['naam'];

$query = 'SELECT DISTINCT(naam) FROM tsc_memberoffset';
$result = $db->selectQuery($query);
while ( $line = $db->fetchArray($result) )
	$members[] = $line['naam'];

$query = 'SELECT DISTINCT(naam) FROM tsc_memberoffsetBackup';
$result = $db->selectQuery($query);
while ( $line = $db->fetchArray($result) )
        $members[] = $line['naam'];

$members = array_unique($members);
sort($members);
echo count($members) . ' leden<br>';
echo '<textarea style="width:300px; height:150px">';
for($i=0;$i<count($members);$i++)
	echo $members[$i] . "\n";
echo '</textarea>';
?>
