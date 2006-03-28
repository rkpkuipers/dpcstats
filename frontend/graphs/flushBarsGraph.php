<?php
include('../classes.php');

include ($jpgraphdir . "/jpgraph.php");
include ($jpgraphdir . "/jpgraph_bar.php");

if ( isset($HTTP_GET_VARS['tabel']) )
           $tabel = $HTTP_POST_VARS['tabel'];
else
        $tabel = 'memberOffset';

if ( isset($HTTP_GET_VARS['teams']) )
	$teams = $HTTP_POST_VARS['teams'];
else
	die("No teams/members were specified");

if ( isset($HTTP_GET_VARS['timespan']) )
	$timespan = $HTTP_POST_VARS['timespan'];
else
	$timespan = 7;

function getNodeNames()
{
	global $teams;

	$tmpNamen = array();

	for($i=0;$i<count($teams);$i++)
	{
		$query = 'SELECT description FROM nodeOwners WHERE nodeid = ' . $teams[$i];
		$result = mysql_query($query);
		if ( $line = mysql_fetch_row($result) )
			$tmpNamen[$i] = $line['0'];
	}
	return $tmpNamen;	
}

function createLine($naam, $no, $tabel)
{
        global $lines, $dagen, $timespan, $db;

        $query = 'SELECT dag, daily FROM ' . $tabel . ' WHERE naam = \'' . $naam . '\' ORDER BY dag DESC LIMIT ' . $timespan;
        $result = $db->selectQuery($query);
        $pos = 0;
        $lines[$no] = array();
        while ( $line = $db->fetchArray($result) )
        {
                $lines[$no][$pos] = $line['daily'];
                $pos++;
                $dagen[$pos] = $line['dag'];
        }
        $lines[$no] = array_reverse($lines[$no]);
}

$graph = new Graph(700,450);

for($i=0;$i<count($teams);$i++)
{
	createLine($teams[$i], $i, $tabel);
	$bar[$i] = new BarPlot($lines[$i]);
	$bar[$i]->SetFillColor($kleur[$i]);
	if ( $tabel == 'nodeOffset' )
		$namen = getNodeNames();
	else
		$namen = $teams;
	$bar[$i]->SetLegend($namen[$i]);
}
$dagen = array_reverse($dagen);
$gbplot = new GroupBarPlot($bar);

// Create the graph. These two calls are always required
$graph->SetScale("textlin");

$graph->SetShadow();
$graph->img->SetMargin(50,220,20,80);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->legend->Pos(0.05,0.5,"right","center");

// Display the graph
$graph->Stroke();
echo '<hr>';

$db->disconnect();
?>
