<?php 
include ('/var/www/tadah.mine.nu/classes.php');
include ('/var/www/tadah.mine.nu/include.php');
include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_line.php"); 

if ( isset ( $_GET['prefix'] ) )
	$project = new Project($db, $_GET['prefix'], 'memberOffset');
else
	$project = new Project($db, 'tsc', 'memberOffset');

$dagen = array();

$timespan = 14;
function createLine($tabel)
{
	global $lines, $dagen, $project;

	$query = 'SELECT COUNT(naam) AS leden, dag FROM ' . $project->getPrefix() . '_memberOffset where dag>\'' . date("Y-m-d", strtotime("-1 month" )) . '\' group by dag';
	$result = mysql_query($query);
	$pos = 0;
	$lines = array();
	while ( $line = mysql_fetch_array($result) )
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
        createLine($project->getPrefix() . '_memberOffset');
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
