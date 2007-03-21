<?php

$_REQUEST['prefix'] = 'rah';

include ('../classes.php');
include ($jpgraphdir . "/jpgraph.php");
include ($jpgraphdir . "/jpgraph_pie.php");

$query = 'SELECT
		stampedeTeam,
		COUNT(name)AS memberCount
	FROM
		stampede6participants
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
