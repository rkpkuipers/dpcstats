<?php
include('../classes.php');

include ($jpgraphdir . "/jpgraph.php");
include ($jpgraphdir . "/jpgraph_pie.php");

if ( isset($_GET['tabel']) )
	$tabel = $_GET['tabel'];
else
	$tabel = 'memberOffset';

if ( isset($_GET['datum']) )
        $datum = $_GET['datum'];
else
	$datum = date("Y-m-d");

if ( isset ( $_GET['prefix'] ) )
        $project = new Project($db, $_GET['prefix'], $tabel);
else
        $project = new Project($db, 'sob', 'memberOffset');

$ts = new TableStatistics($project->getPrefix() . '_' . $tabel, $datum, $db);
$ts->gather();

$query = 'SELECT
		naam,
		daily
	FROM
		sob_memberOffset
	WHERE
		dag = \'' . $project->getCurrentDate() . '\'
	ORDER BY
		daily DESC
	LIMIT
		20';

$result = $db->selectQuery($query);

$total = $ts->getDailyOutput();
while ( $line = $db->fetchArray($result) )
{
	$percentage = number_format($line['daily'] / ( $ts->getDailyOutput() / 100 ), 2, ',', '.');
	#echo $percentage . ' ' . $line['daily'] . '<br>';
	$values[] = $line['daily'];
	$legend[] = $line['naam'];

	$total -= $line['daily'];
}

$values[] = $total;
$legend[] = 'Overige';

$data = array(40,60,21,33);
$data = $values;

$graph = new PieGraph(800,600,"auto");
$graph->SetShadow();

$graph->title->Set("Flushers");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot($data);
$p1->SetLegends($legend);//$gDateLocale->GetShortMonth());
$p1->SetCenter(0.4);

$graph->Add($p1);
$graph->Stroke();

?>
