<?php 
include ('/var/www/tstats/classes.php');
include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_line.php"); 

if ( isset ( $_GET['prefix'] ) )
	$project = new Project($db, $_GET['prefix'], 'memberoffset');
else
	$project = new Project($db, 'tsc', 'memberoffset');

$dagen = array();

$timespan = 14;
function createLine($tabel)
{
	global $lines, $dagen, $project, $db;

	$query = 'SELECT COUNT(naam) AS leden, dag FROM ' . $project->getPrefix() . '_memberoffset where dag>\'' . date("Y-m-d", strtotime("-1 month" )) . '\' group by dag';
	$result = $db->selectQuery($query);
	$pos = 0;
	$lines = array();
	while ( $line = $db->fetchArray($result) )
	{
		$lines[$pos] = $line['leden'];
		#$pos++;
		$dagen[$pos] = date('d-m', strtotime($line['dag']));
		$pos++;
	}
}

$graph = new Graph(500,350,"auto");
$graph->SetScale("textlin");
{
        createLine($project->getPrefix() . '_memberoffset');
	$lineplot = new LinePlot($lines);
	$lineplot->SetColor($kleur[0]);
	$lineplot->SetWeight("2");
	$graph->Add($lineplot);
	$lineplot->SetLegend($teams[0]);
}

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

$graph->title->Set('DPC Members @ ' . $project->getPrefix());
$graph->img->SetMargin(50,20,20,70); 
$graph->xaxis->SetPos("min"); 

$graph->legend->Pos(0.05,0.5,"right","center");

// Display the graph 
$graph->Stroke(); 
?>
