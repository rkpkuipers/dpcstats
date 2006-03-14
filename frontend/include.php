<?php

$db = new DataBase();
$db->connect();

# Globals

$listsize = 30;
$baseUrl = 'http://tadah.mine.nu';

$jpgraphdir = '/usr/local/jpgraph-php5/';

 $kleur[0] = "#CC0000";
 $kleur[6] = "#FFFF00";   # Rood
 $kleur[9] = "#00FF00";   # Groen
 $kleur[3] = "#0000FF";   # Blauw
 $kleur[4] = "#FFFF00";   # Geel
 $kleur[5] = "#00FFFF";   # Cyaan
 $kleur[1] = "#FF00FF";   # Paars
 $kleur[7] = "#CACACA";   # Donker Rood
 $kleur[8] = "#FF8888";   # Roze
 $kleur[2] = "#009900";   # Donker groen
$kleur[10] = "#88FF88";   # Licht groen
$kleur[11] = "#0000CC";   # Donker blauw
$kleur[12] = "#8888FF";   # Blauw-grijs
$kleur[13] = "#FF8800";   # Oranje
$kleur[14] = "#CD5C5C";   # Grijs
$kleur[15] = "#153F62";   # Blauw
$kleur[16] = "#FF0000";	  # Geel
$kleur[17] = "#A52A2A";	  # Brown

for($i=18;$i<31;$i++)
        $kleur[$i] = "#000000";

function openColorTable($width = 0)
{
	$output = "";
	if ( $width == 0 )
		$output .= '<table class="outerTable">';
	else
		$output .= '<table class="outerTable" width="' . $width . '%">';
	$output .= '<tr><td>';
	$output .= '<table class="innerTable">';
	$output .= '<tr><td>';
	return $output;
}

function closeTable($times)
{
	for($i=0;$i<$times;$i++)
		echo '</td></tr></table>';
}

function getCurrentDate($prefix)
{
	return date("Y-m-d");
/*
        switch($prefix)
        {
	case 'fah':
		return date("Y-m-d", strtotime("+30 minutes", date("U")));
		break;
	default:
		return date("Y-m-d");
        }*/
}

function getPrevDate($datum = '')
{
	if ( $datum == '' )
		$datum = date("Y-m-d");
	
	$datum = date("U", strtotime($datum));

        return date("Y-m-d", strtotime("-1 day", $datum));
}

function getPrevWeek($datum = '')
{
	if ( $datum == '' )$datum = date("Y-m-d");
	return date("Y-m-d", strtotime("-1 week" ));
}

function trBackground($row)
{
	if ( $row == 0 )
		return '<tr class="firstCell">';
	elseif ( $row % 2 == 0 )
		return '<tr class="evenCell">';
	else
		return '<tr class="oddCell">';
}

function trBackgroundColor($row)
{
        if ( $row == 0 )
                return 'EAEAEA';
        elseif ( $row % 2 == 0 )
                return 'C0C0C0';
        else
                return 'CCCCCC';
}


function checkTable($tabel)
{
	if (
            ( $tabel != 'memberOffset' ) &&
            ( $tabel != 'teamOffset' ) &&
            ( $tabel != 'subteamOffset' ) &&
	    ( $tabel != 'memberOffsetDaily' ) &&
	    ( $tabel != 'subteamOffsetDaily' ) &&
	    ( $tabel != 'teamOffsetDaily' ) &&
	    ( $tabel != 'individualOffset' ) &&
	    ( $tabel != 'individualOffsetDaily') 
   	   )die('Onjuiste tabel opgegeven');
}

function getURL($link)
{
	global $project, $tabel, $naam, $datum, $debug, $mode;

	$href = 'index.php?mode=' . $mode . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) .
		'&amp;datum=' . $datum . '&amp;prefix=' . $project->getPrefix();
	
	if ( $debug == 1 )
		$href .= '&amp;debug=1';
	
	return '<a href="' . $href . '">' . $link . '</a>';
}

function getCompleteURL($link, $naam = '', $mode = '', $tabel = '', $prefix = '', $team = '')
{
	if ( $naam == '' )
		global $naam;
	
	if ( $mode == '' )
		global $mode;
	
	if ( $tabel == '' )
		global $tabel;
	
	if ( $prefix == '' )
	{
		global $project;
		$prefix = $project->getPrefix();
	}
	
	if ( $team == '' )
		global $team;
	
	if ( $datum == '' )
		global $datum;
	
	$href = 'index.php?mode=' . $mode . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) .
		'&amp;datum=' . $datum . '&amp;team=' . rawurlencode($team) . '&amp;prefix=' . $prefix;

	if ( $debug == 1 )
		$href .= '&amp;debug=1';
	
	return '<a href="' . $href . '">' . $link . '</a>';
}

function getURLByDate($link, $datum)
{
	#return getCompleteURL($link, $datum = $datum);
	global $project, $tabel, $naam, $debug, $mode;

	$href = 'index.php?mode=' . $mode . '&amp;tabel=' . $tabel . '&amp;naam=' . rawurlencode($naam) .
		'&amp;datum=' . $datum . '&amp;prefix=' . $project->getPrefix();
	
	if ( $debug == 1 )
		$href .= '&amp;debug=1';
	
	return '<a href="' . $href . '">' . $link . '</a>';
}
?>
