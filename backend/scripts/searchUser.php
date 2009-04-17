#!/usr/bin/php
<?

include ('/home/rkuipers/public_html/classes.php');

$tables = array('memberoffset', 'teamoffset');
$addtables = array('subteamoffset', 'individualoffset');
$addprefix = array('fah', 'sob', 'sah', 'rah', 'ufl');

if ( $argv[1] == '' )
	die('Script requires at least a name as argument' . "\n");

$name = $argv[1];

if ( isset($argv[2]) )
	$prefix = array($argv[2]);
else
{
	
	$query = 'SELECT project FROM project ORDER BY project';
	$result = $db->selectQuery($query);

	while ( $line = $db->fetchArray($result) )
		$prefix[] = $line['project'];
}

foreach ($prefix as $project)
{
	if ( in_array($project, $addprefix) )
		$tbllist = array_merge($tables, $addtables);
	else
		$tbllist = $tables;

	foreach ($tbllist as $table)
	{
		$query = 'SELECT 
				naam, 
				(cands+daily)AS total 
			FROM 
				' . $project . '_' . $table . ' 
			WHERE 
				dag = \'' . date("Y-m-d") . '\' 
			AND 	lower(naam) LIKE \'%' . strtolower($name) . '%\'';

		$result = $db->selectQuery($query);

		while ( $line = $db->fetchArray($result) )
		{
			echo $line['naam'] . "\r\t\t\t\t\t" . 
				str_pad($line['total'], 8, ' ', STR_PAD_LEFT) . "\t" . 
				$project . '_' . $table . "\n";
		}
	}
}
echo "\n";

$db->disconnect();
?>
