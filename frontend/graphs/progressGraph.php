<?php 
include ('../classes.php');
include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_line.php"); 

$dagen = array();

if ( isset($_POST['tabel']) )
	$tabel = $_POST['tabel'];
elseif ( isset($_GET['tabel']) )
	$tabel = $_GET['tabel'];
else
        $tabel = 'memberOffset';

if ( isset($_POST['teams']) )
	$teams = $_POST['teams'];
elseif ( isset($_GET['teams']) )
	$teams = $_GET['teams'];
else
	die("No members / teams were selected");

if ( isset($_POST['timespan']) )
	$timespan = $_POST['timespan'];
else
	$timespan = 14;

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

if ( isset ($_POST['prefix'] ) )
	$prefix = $_POST['prefix'];
elseif ( isset ($_GET['prefix']) )
	$prefix = $_GET['prefix'];

$width = 550;
$sDate = date("Y-m-d", strtotime("-" . $timespan . " days"));

function createLine($naam, $no, $tabel)
{
	global $lines, $dagen, $timespan, $prefix, $db, $team;

	$query = 'SELECT 
			dag, 
			(cands+daily) AS cands 
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			naam = \'' . $naam . '\' ' .
		( $tabel=='subteamOffset'?'AND subteam=\'' . $team . '\'':'') . '
		ORDER BY 
			dag DESC 
		LIMIT 	' . $timespan;
	#echo $query;
	$result = $db->selectQuery($query);
	$pos = 0;
	$lines[$no] = array();
	while ( $line = $db->fetchArray($result) )
	{
		$lines[$no][$pos] = $line['cands'];
		$pos++;
		$dagen[$pos] = $line['dag'];
	}
	$lines[$no] = array_reverse($lines[$no]);
}

$graph = new Graph($width,450,"auto");
$graph->SetScale("textlin");
for($i=0;$i<count($teams);$i++)
{
	if ( $teams[$i] == '' )break;
        createLine($teams[$i], $i, $tabel);
	$lineplot[$i] = new LinePlot($lines[$i]);
	$lineplot[$i]->SetColor($kleur[$i]);
	$lineplot[$i]->SetWeight("2");
	$graph->Add($lineplot[$i]);
#	$lineplot[$i]->SetLegend(str_replace('\\', '', $teams[$i]));
}
$dagen = array_reverse($dagen);

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

$graph->img->SetMargin(70,20,20,70); 
$graph->xaxis->SetPos("min"); 

#$graph->legend->Pos(0.65,0.15,"right","center");

// Display the graph 
$graph->Stroke(); 
?>
