<?php
include('../classes.php');

include ($jpgraphdir . "/jpgraph.php");
include ($jpgraphdir . "/jpgraph_bar.php");

if ( isset($HTTP_POST_VARS['tabel']) )
           $tabel = $HTTP_POST_VARS['tabel'];
else
        $tabel = 'memberoffset';

if ( isset($_REQUEST['teams']) )
	$teams = $_REQUEST['teams'];
else
	die("No teams/members were specified");

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

if ( isset($HTTP_POST_VARS['timespan']) )
	$timespan = $HTTP_POST_VARS['timespan'];
else
	$timespan = 7;

if ( isset($_POST['prefix']) )
	$project = new Project($db, $_POST['prefix'], $tabel);

function createLine($naam, $no, $tabel)
{
        global $lines, $dagen, $timespan, $project, $db, $team;

        $query = 'SELECT 
			dag, 
			daily 
		FROM 
			' . $project->getPrefix() . '_' . $tabel . ' 
		WHERE 
			naam = \'' . $naam . '\' ' .
		( $tabel=='subteamoffset'?'AND subteam = \'' . $team . '\'':'') . '
		ORDER BY 
			dag DESC 
		LIMIT 	' . $timespan;

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
	$namen = str_replace('\\', '', $teams);
	$bar[$i]->SetLegend($namen[$i]);
}
$dagen = array_reverse($dagen);
$gbplot = new GroupBarPlot($bar);

// Create the graph. These two calls are always required
$graph->SetScale("textlin");

$graph->SetShadow();
$graph->img->SetMargin(60,200,20,80);

// ...and add it to the graPH
$graph->Add($gbplot);

$graph->xaxis->SetTickLabels($dagen);
$graph->xaxis->SetLabelAngle(90);

#$graph->Settitle('title');
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$graph->legend->Pos(0.05,0.5,"right","center");

// Display the graph
$graph->Stroke();
echo '<hr>';
?>
