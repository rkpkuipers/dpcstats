<?        
	require('../classes.php');

if ( isset($_GET['tabel']) )
        $tabel = $_GET['tabel'];
else
        $tabel = 'memberoffset';

if ( isset($_GET['prefix']) )
	$project = new Project($db, $_GET['prefix'], $tabel);
else
	$project = new Project($db, 'tsc', 'memberoffset');

if ( isset($_GET['naam']) )
	$naam = $_GET['naam'];
else
	die("Er is geen naam opgegeven");

if ( isset($_GET['timespan']) )
	$timespan = $_GET['timespan'];
else
	$timespan = 7;

if ( isset($_GET['labelInterval']) )
	$lblInt = $_GET['labelInterval'];
else
	$lblInt = 1;

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

function switchArray($data)
{
	$tmp = array();
	for($i=0;$i<count($data);$i++)
		$tmp[$i] = $data[count($data)-$i];
	
	return $tmp;
}

        include ($jpgraphdir . "/jpgraph.php");
        include ($jpgraphdir . "/jpgraph_bar.php");
	include ($jpgraphdir . "/jpgraph_line.php");

	if ( $tabel == 'subteamoffset' )
		$where = ' AND subteam = \'' . $db->real_escape_string($team) . '\' ';
	else
		$where;

        // We need some data
	$query = 'SELECT 
			dag, 
			daily 
		FROM 
			' . $project->getPrefix() . '_' . $tabel . ' 
		WHERE 
			naam = \'' . $db->real_escape_string($naam) . '\' ' .
			$where . '
		ORDER BY 
			dag DESC 
		LIMIT 	' . $timespan;

	$result = $db->selectQuery($query);
	
	$pos = 0;
	$maxValue = 0;
	$datax = array();
	$datay = array();
	while ( $line = $db->fetchArray($result) )
	{
		$datax[$pos] = date("d-m-Y", strtotime($line['dag']));
		$datay[$pos] = $line['daily'];
		if ( $line['daily'] > $maxValue )
			$maxValue = $line['daily'];
		$pos++;
	}

	$datax = array_reverse($datax);
	$datay = array_reverse($datay);

        // Setup the graph.
	$graph = new Graph(300, 300, "auto");
        $graph->SetScale("textlin");
	if ( ( $maxValue >= 0 ) && ( $maxValue < 1000 ) )
		$graph->img->SetMargin(40,15,30,75);
	elseif ( ( $maxValue >= 1000 ) && ( $maxValue < 10000 ) )
		$graph->img->SetMargin(50,15,30,75);
	elseif ( $maxValue >= 10000 )
		$graph->img->SetMargin(60,15,30,75);

        $graph->title->Set('Flush History');
        $graph->title->SetColor('darkred');

        // Setup font for axis
        $graph->xaxis->SetFont(FF_FONT1);
        $graph->yaxis->SetFont(FF_FONT1);

#	$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
#	$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

	$graph->xaxis->SetPos("min");
        $graph->xaxis->SetTickLabels($datax);

#	if ( $timespan > 10 )
	        $graph->xaxis->SetLabelAngle(90);
#	else
#		$graph->xaxis->SetLabelAngle(50);
	$graph->xaxis->SetTextLabelInterval($lblInt);

        // Create the bar pot
        $bplot = new BarPlot($datay);
        $bplot->SetWidth(0.6);

	if ( $timespan < 10 )
	{
		$bplot->value->Show();
		$bplot->value->SetFormat('%d');
		$bplot->value->SetFont(FF_FONT1,FS_BOLD);
		$bplot->value->SetColor('#000000');
	}

        // Setup color for gradient fill style
        $bplot->SetFillGradient("navy","lightsteelblue",GRAD_MIDVER);

        // Set color for the frame of each bar
        $bplot->SetColor("navy");
#        $graph->Add($bplot);

	$graph->Add($bplot);

        // Finally send the graph to the browser
        $graph->Stroke();

	$db->disconnect();
?>
