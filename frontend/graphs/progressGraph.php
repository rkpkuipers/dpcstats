<?php 

# Generic include
include ('../classes.php');

# Jpgraph include
include ($jpgraphdir . "/jpgraph.php"); 
include ($jpgraphdir . "/jpgraph_line.php"); 

function getDataByTeam($db, $timespan, $days, $naam, $tabel)
{
	# Fetch variables from the global scope
	global $prefix, $team;
	
	# Select the total overall output for the team in the requested timeframe
	$query = 'SELECT 
			dag, 
			(cands+daily) AS cands 
		FROM 
			' . $prefix . '_' . $tabel . ' 
		WHERE 
			naam = \'' . $naam . '\' ' .
		( $tabel=='subteamoffset'?'AND subteam=\'' . $team . '\'':'') . '
		AND	dag >= \'' . date("Y-m-d", strtotime("-" . $timespan . " days")) . '\'
		ORDER BY 
			dag';
	
	# Execute the query
	$result = $db->selectQuery($query);
	
	# Pre-fill the data array with indices for each day in the timespan
	$data = array_combine($days, array_fill(0, count($days), 0));
	
	# Loop through the results and insert the data into the array
	while ( $line = $db->fetchArray($result) )
		$data[$line['dag']] = $line['cands'];
	
	# Return the datapoints
	return $data;
}

# Set locale to provide dutch names for days, months and such
setlocale(LC_ALL, 'nl_NL.utf8', 'nl_NL');

# Retrieve the table
if ( ( isset($_REQUEST['tabel']) ) )
	$tabel = $_REQUEST['tabel'];
else
        $tabel = 'memberoffset';

# Verify the table
checkTable($_REQUEST['tabel']);

# Retrieve the teams
if ( isset($_REQUEST['teams']) )
	$teams = $_REQUEST['teams'];
else
	die("ERROR: No members / teams were selected");

# Retrieve the timespan to graph
if ( ( isset($_REQUEST['timespan']) ) && ( is_numeric($_REQUEST['timespan']) ) )
	$timespan = $_REQUEST['timespan'];
else
	$timespan = 14;

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];

if ( isset ($_REQUEST['prefix'] ) )
	$prefix = $_REQUEST['prefix'];

# Determine the first and last day of the timeframe to show data for
$sStartDate = date("Y-m-d", strtotime("-" . $timespan . " days"));

# Initialize the days array with the first day of the timeframe
$days = array($sStartDate);

# Loop through the days between the start date and today
while($sStartDate < date("Y-m-d"))
{
	// Add a day to the current date
	$sStartDate = date("Y-m-d", strtotime("+1 day", strtotime($sStartDate)));
	
	// Add this new day to the aDays array
	$days[] = $sStartDate;
}

# Create the graph
$graph = new Graph(550, 550, "auto");
$graph->SetScale("textlin");

# Title
$graph->title->SetFont(FF_ARIAL,FS_BOLD, 14);
$graph->title->Set("Overall Progress");

# Use a nicer font for the labels on the axis
$graph->xaxis->SetFont(FF_ARIAL);
$graph->yaxis->SetFont(FF_ARIAL);

# Loop through the teams
for($i=0;$i<count($teams);$i++)
{
	# Check if we have a valid team name
	if ( ( ! empty($teams[$i]) ) && ( ! preg_match('/^[a-zA-Z0-9\ _\.\]\[]+$/', $teams[$i]) ) )
		continue;
	
	# Retrieve the datapoints for the team
	$data = getDataByTeam($db, $timespan, $days, $teams[$i], $tabel);
	
	# Create a new lineplot
	$lineplot = new LinePlot(array_values($data));
	
	# Pick a color from the central list of available colors
	$lineplot->SetColor($kleur[$i]);
	
	# Thicker line for better visibility
	$lineplot->SetWeight("2");
	
	# Add the lineplot to the graph
	$graph->Add($lineplot);
}

# Convert the days array to nicely formatted days
foreach($days as &$day)
	$day = ucwords(strftime("%e %B", strtotime($day)));

# Use the formatted days as label and increase angle for readability
$graph->xaxis->SetTickLabels($days);
$graph->xaxis->SetLabelAngle(45);

# Margins between the graph and the canvas
$graph->img->SetMargin(70,10,25,75); 

# Display the graph 
$graph->Stroke(); 

?>