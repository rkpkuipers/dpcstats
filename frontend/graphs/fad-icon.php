<?php 
include ('../classes.php');
include ('../include.php');

include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_line.php"); 
include ($jpgraphdir . "/jpgraph_flags.php");
include ($jpgraphdir . "/jpgraph_iconplot.php");

$tabel = 'memberOffset';
$teams[0] = 'SpeedKikker';
$position = 0;
$datum = date("Y-m-d", strtotime("-2 hours" ));

$query = 'SELECT id FROM fad_memberOffset WHERE dag = \'' . $datum . '\' AND naam = \'SpeedKikker\'';
$result = mysql_query($query);
if ( $line = mysql_fetch_array($result) )
	$position = $line['id'];
	

function createLine($naam, $no, $tabel)
{
	global $lines, $datum;

	$query = 'SELECT (cands+daily) AS cands FROM fad_' . $tabel . ' WHERE naam = \'' . $naam . '\' AND dag < \'' . $datum . '\' ORDER BY dag DESC LIMIT 14';
	$result = mysql_query($query);
	$pos = 0;
	$lines[$no] = array();
	while ( $line = mysql_fetch_array($result) )
	{
		$lines[$no][$pos] = $line['cands'];
		$pos++;
	}
	$lines[$no] = array_reverse($lines[$no]);
}

$graph = new Graph(60,60,"auto");
$graph->SetScale("textlin");

for($i=0;$i<sizeof($teams);$i++)
{
        createLine($teams[$i], $i, $tabel);
	$lineplot[$i] = new LinePlot($lines[$i]);
	$lineplot[$i]->SetColor('black');
	$lineplot[$i]->SetWeight("1");
	$graph->Add($lineplot[$i]);
}

$graph->xaxis->Hide();
$graph->yaxis->Hide();

$graph->img->SetMargin(0,0,21,0); 
$graph->SetColor('white');

$txt_pos = new Text ('#' . $position); 
#$txt_pos->Pos(20, 46) ; 
$txt_pos->SetColor('black') ; 
$graph->AddText($txt_pos) ;

$txt_ra = new Text ('SpeedKikker'); 
#$txt_ra->Pos(3, 0) ; 
$txt_ra->SetColor('black') ; 
#$txt_ra->SetFont('FF_ARIAL', 'FS_BOLD');
$graph->AddText($txt_ra) ;

$icon = new IconPlot();
$icon->SetCountryFlag('Kingdom of the Netherlands', 30, 41, 1.7, 40, 1);
$icon->SetAnchor('center','center');
$graph->Add($icon);

$graph->Stroke(); 
?>
