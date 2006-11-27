<?php 
include ('../classes.php');
include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_bar.php"); 

if ( isset ( $_GET['prefix'] ) )
	$project = new Project($db, $_GET['prefix'], 'memberoffset');
else
	$project = new Project($db, 'tsc', 'memberoffset');

$dagen = array();

$timespan = 14;

function createLine($tabel)
{
	global $lines, $dagen, $project, $db;

	$query = 'SELECT 
			distinct(dag) 
		FROM 
			' . $project->getPrefix() . '_memberoffset 
		WHERE 
			dag>\'' . date("Y-m-d", strtotime("-1 month" )) . '\' 
		ORDER BY 
			dag';
	#echo $query;
	$result = $db->selectQuery($query);

	$pos = 0;
	$lines = array();
	while($line = $db->fetchArray($result))
	{
		$cntQuery = 'SELECT 
				COUNT(naam)AS cntnaam
			FROM 
				movement 
			WHERE 
				direction=1 
			AND 	tabel=\'' . $project->getPrefix() . '_memberoffset\' 
			AND 	datum = \'' . $line['dag'] . '\'';
#		echo $cntQuery;
		$cntResult = $db->selectQuery($cntQuery);
		if ( $cntline = $db->fetchArray($cntResult) )
			$lines[$pos] = $cntline['cntnaam'];
		else
			$lines[$pos] = 0;

		$dagen[$pos] = date('d-m', strtotime($line['dag']));
		$pos++;
	}
}

$graph = new Graph(500,350,"auto");
$graph->SetScale("textlin");
{
        createLine($project->getPrefix() . '_memberoffset');
	$barplot = new BarPlot($lines);
	$barplot->SetColor($kleur[0]);
	#$lineplot->SetWeight("2");
	$barplot->SetColor("navy");
	$barplot->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);
	$barplot->SetWidth(0.6);
        $barplot->value->Show();
	$barplot->value->SetFormat('%d');
	$barplot->value->SetFont(FF_FONT1,FS_BOLD);
	$barplot->value->SetColor('#000000');
									
	$graph->Add($barplot);
	#$lineplot->SetLegend($teams[0]);
}

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

$graph->title->Set('Toename DPC Members @ ' . $project->getPrefix() );
$graph->img->SetMargin(50,20,20,70); 
$graph->xaxis->SetPos("min"); 

$graph->legend->Pos(0.05,0.5,"right","center");

// Display the graph 
$graph->Stroke(); 
?>
