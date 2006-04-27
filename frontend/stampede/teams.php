<?php

$_REQUEST['prefix'] = 'rah';

include ("/usr/local/jpgraph-php5/jpgraph.php");
include ("/usr/local/jpgraph-php5/jpgraph_pie.php");
include ('/var/www/tstats/classes.php');

$query = 'SELECT
		stampedeTeam,
		COUNT(name)AS memberCount
	FROM
		stampedeparticipants
	GROUP BY
		stampedeTeam
	ORDER BY
		memberCount DESC';

$result = $db->selectQuery($query);

while ( $line = $db->fetchArray($result) )
{
	$data[] = $line['memberCount'];
	$legend[] = $line['stampedeTeam'] . ' (' . $line['memberCount'] . ')';
#	$data[$line['stampedeTeam']] = $line['memberCount'];
}

$graph = new PieGraph(800,600,"auto");

$graph->title->Set("Stampede Teams");
$graph->title->SetFont(FF_FONT2,FS_BOLD);

$p1 = new PiePlot($data);
$p1->SetLegends($legend);
$p1->SetSliceColors(array('black', 'blue', 'green', 'red', 'yellow', 'brown', 'orange'));
$p1->SetCenter(0.3);

$graph->Add($p1);
$graph->Stroke();

$db->disconnect();
?>
