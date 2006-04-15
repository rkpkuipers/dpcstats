<?php
include('../classes.php');

include ($jpgraphdir . "/jpgraph.php");
include ($jpgraphdir . "/jpgraph_bar.php");

if ( isset($_GET['tabel']) )
           $tabel = $_GET['tabel'];
else
        $tabel = 'memberoffset';

if ( isset( $_GET['naam'] ) )
	$naam = $_GET['naam'];
else
	die("Er is geen team naam opgegeven");

if ( isset($HTTP_GET_VARS['timespan']) )
	$timespan = $HTTP_POST_VARS['timespan'];
else
	$timespan = 7;

if ( isset ($_GET['prefix']) )
	$project = new Project($db, $_GET['prefix'], $tabel);
else
	$project = new Project($db, 'tsc', 'memberoffset');

function getMonthOutput($tabel, $naam, $maand)
{
	global $project, $db;
	$query = 'SELECT SUM(daily) AS output FROM ' . $project->getPrefix() . '_' . $tabel . ' WHERE naam = \'' . $naam . '\' AND dag LIKE \'' . $maand . '-%\'';
	echo $query;
	$result = $db->selectQuery($query);

	if ( $line = $db->fetchArray($result) )
		$output = $line['output'];
	else
		$output = 0;

	return $output;
}

$graph = new Graph(400,350);

$dagen = array();
$lines = array();

for($i=1;$i<=date('m');$i++)
{
	$dagen[$i-1] = date('F', strtotime('2005-' . $i . '-01'));
	$lines[$i-1] = getMonthOutput($tabel, $naam, '2005-' . str_pad($i, 2, 0, STR_LEFT_PAD));
}

$bar = new BarPlot($lines);
$bar->SetFillColor($kleur[0]);
#$bar->SetLegend($naam);
$graph->title->Set($naam . ' Monthly Output');

#$dagen = array_reverse($dagen);
// Create the graph. These two calls are always required
$graph->SetScale("textlin");

$graph->img->SetMargin(50,20,20,80);

// ...and add it to the graPH
$graph->Add($bar);

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->legend->Pos(0.05,0.5,"right","center");

// Display the graph
$graph->Stroke();
#echo 'hallo';
#?>
