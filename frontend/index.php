<? 
require('classes.php');

$start_time = microtime();

# Set/unset variables through cookies, used to save settings for hiding various parts on the site
$cActive  = handleCookie('cActive',  $_GET['setCActive'],  $_GET['cActive'],  $_COOKIE['cActive']);
$sbActive = handleCookie('sbActive', $_GET['setSbActive'], $_GET['sbActive'], $_COOKIE['sbActive']);
$dtActive = handleCookie('dtActive', $_GET['setDtActive'], $_GET['dtActive'], $_COOKIE['dtActive']);

$plActive = handleCookie('plActive', $_GET['setPlActive'], $_GET['plActive'], $_COOKIE['plActive']);
$opActive = handleCookie('opActive', $_GET['setOpActive'], $_GET['opActive'], $_COOKIE['opActive']);
$glActive = handleCookie('glActive', $_GET['setGlActive'], $_GET['glActive'], $_COOKIE['glActive']);

if ( isset($_REQUEST['setCookieActive']) )
{
	$cName = $_REQUEST['cookieName'];

#	handleCookie($cName, $_GET['setCookieActive'], $_GET);
}

# The allowed array contains all variables used with their default values. 
# These can be taken from the global array, if they're not set we use the default
$allowed = array(	'mode' => 'Members',		# Which page to load
			'searchString' => '',		# Inputstring used in search
			'tabel' => 'memberoffsetdaily',	# Tabel to take data from
			'datum' => date("Y-m-d"),	# Date to collect data from
			'naam' => '',			# Name used when collecting detailed member info
			'frame' => '',			# Let's find out
			'hl' => '',			# Which team to highlight, used for custom position lists
			'dlow' => 0,			# offset for the flush list (start at $dlow instead of 1)
			'low' => 0,			# offset for the overall list
			'team' => 'Dutch Power Cows');

# All table names we're changed to lower case, convert any old names to new ones for backward compatibility
$tabel = strtolower($tabel);

foreach($allowed as $name => $default)
{
	if ( isset($_REQUEST[$name]) )
		$$name = $_REQUEST[$name];
	else
		$$name = $default;
}

# Check some of the variables for valid contents

# Verify we're going to use a tabel the scripts have direct access to
checkTable($tabel);

# Date's in the future are changed into today
if ( $datum > date("Y-m-d") )
	$datum = date("Y-m-d");

# Decide wether or not to use the faster memory table instead of the normal one
if ( $datum <= date("Y-m-d", strtotime("-3 day")) )
# Langer dan 3 dagen geleden, beide variabelen op de standaardtabel zonder suffix
{
	if ( is_numeric(strpos($tabel, 'daily') ) )
	{
		$tabel = substr($tabel, 0, strpos($tabel, 'daily'));
	}
	
	$speedTabel = $tabel;
}
else
# Datum tussen vandaag en 2 dagen geleden, tabel op standaard en speedtabel op daily
{
	if ( ! is_numeric(strpos($tabel, 'daily') ) )
	{
		$speedTabel = $tabel . 'daily';
	}
	else
	{
		$speedTabel = $tabel;
		$tabel = substr($tabel, 0, strpos($tabel, 'daily'));
	}
}

if ( isset($_GET['detail']) )
	$graphDetail = $_GET['detail'];
else
	$graphDetail = 0;

if ( isset ( $_REQUEST['fl']) )
	$flushList = $_REQUEST['fl'];
else
	$flushList = 0;

if ( isset ( $_GET['sort']) )
	$sort = $_GET['sort'];
else
{
	if ( $mode == 'history' )
		$sort = 'dag';
	else
		$sort = 'avgDaily';
}

if ( isset($_REQUEST['prefix']) )
	$project = new Project($db, $_REQUEST['prefix'], $tabel);
else
	$project = new Project($db, 'tsc', 'memberoffsetdaily');

if ( isset($_GET['debug']) )
	$debug = $_GET['debug'];

# Make sure the page expires when the next statsrun has completed
header('Expires: ' . gmdate("D, d M Y H:i:s ", strtotime("+" . $project->getStatsrunInterval() . " minutes", strtotime($project->getLastUpdate()))) . 'GMT');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title><? echo $project->getDescription(); ?> - Statistics</title>

<script type="text/javascript">
function submitCookieForm()
{
	alert("1");
	document.cookies.submit();
}

function TrackCount(fieldObj,countFieldName,maxChars)
{
	var countField = eval("fieldObj.form."+countFieldName);
    	var diff = maxChars - fieldObj.value.length;

      	// Need to check & enforce limit here also in case user pastes data
        if (diff < 0)
	{
		fieldObj.value = fieldObj.value.substring(0,maxChars);
	        diff = maxChars - fieldObj.value.length;
	}
	countField.value = diff;
}

function LimitText(fieldObj,maxChars)
{
	var result = true;
	if (fieldObj.value.length >= maxChars)
	result = false;
			        
	if (window.event)
		window.event.returnValue = result;
	return result;
}

//<![CDATA[
var onImageURL = new Array("images/DPCm-bleu.jpg", "images/TSC-Team-blue.jpg", "images/Node-Blue.jpg", 
			   "images/Member-blue.jpg", "images/Out-Graph-blue.jpg", "images/History-blue.jpg" );

var preload = new Array( onImageURL.length );
var imgNum;

for (imgNum = 0; imgNum < onImageURL.length; imgNum++)
{
    preload[imgNum] = new Image( );
    preload[imgNum].src = onImageURL[imgNum];
}

function change( imageName, newSource )
{
    document[imageName].src = newSource;
}
// ]]>
</script>
 <link rel="stylesheet" href="page.css" type="text/css">
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <meta http-equiv="Pragma" content="no-cache">
 <link rel="shortcut icon" href="favicon.ico">
</head>
<body>
 <table cellpadding="0" cellspacing="0" border="0">
  <tr>
   <td rowspan="10" class="styleCell" 
   	style="background-image:url(images/bg1222.jpg); background-position:right top; background-repeat:repeat-y"></td>
   <td>
    <table border="0" cellpadding="0" cellspacing="0" class="pageCell">
     <tr>
      <td><img src="images/spacer.gif" width="159" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="21" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="87" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="99" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="100" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="22" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="81" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="100" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="51" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="37" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="11" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="12" class="cellHeight4" alt=""></td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight4" alt=""></td>
     </tr>
     <tr>
      <td align="center" colspan="12" style="background-image:url(images/index_r1_c1.jpg);" class="pageCell cellHeight1"></td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight1" alt=""></td>
     </tr>
     <tr>
      <td colspan="12">
       <a href="/"><img name="index_r2_c1" src="images/index_r2_c1.jpg" class="pageCell cellHeight2" alt="TSC Stats"></a>
      </td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight2" alt=""></td>
     </tr>
     <tr>
      <td colspan="12"><img name="index_r3_c1" src="images/index_r3_c1.jpg" class="pageCell cellHeight3" alt=""></td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight3" alt=""></td>
     </tr>
     <tr>
      <td colspan="1"><img name="index_r4_c1" src="images/index_r4_c1.jpg" width="159" class="cellHeight5" alt=""></td>
      <td colspan="2">
       <a href="?mode=Members&amp;prefix=<? echo $project->getPrefix() ?>&amp;tabel=memberoffsetdaily&amp;datum=<? echo $datum; ?>" onmouseover="change('index_r4_c2', 'images/DPCm-bleu.jpg')" 
<? 
	if ( $mode == 'Members' )
	{ 
		echo "onmouseout=\"change('index_r4_c2', 'images/DPCm-bleu.jpg')\"> "; 
	}
	else 
	{ 
		echo "onmouseout=\"change('index_r4_c2', 'images/index_r4_c2.jpg')\"> "; 
	}
?>
       <img name="index_r4_c2" 
<? 
	if ( $mode == 'Members' )
	{ 
		echo ' src="images/DPCm-bleu.jpg"'; 
	}
	else 
	{ 
		echo 'src="images/index_r4_c2.jpg "'; 
	} 
?>  
       width="108" class="cellHeight5" alt=""></a>
      </td>
      <td>
<?
  	echo '<a href="?mode=Teams&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamoffsetdaily&amp;datum=' . 
		$datum . '" onmouseover="change(\'index_r4_c4\', \'images/TSC-Team-blue.jpg\')" ';

	if ( $mode == 'Teams' )
	{ 
		echo "onmouseout=\"change('index_r4_c4', 'images/TSC-Team-blue.jpg')\"> "; 
	}
	else 
	{ 
		echo "onmouseout=\"change('index_r4_c4', 'images/index_r4_c4.jpg')\"> "; 
	} 
?> 
       <img name="index_r4_c4" 
<? 
	if ( $debug == 1 )
		echo 'src="images/spacer.3.jpg"';
	else
	{
		if ( $mode == 'Teams' )
		{ 
			echo 'src="images/TSC-Team-blue.jpg"'; 
		}
		else 
		{ 
			echo 'src="images/index_r4_c4.jpg"'; 
		} 
	}
?>  
       width="99" class="cellHeight5" alt=""></a>
      </td>
<?
	if ( in_array($project->getPrefix(), array('fah', 'sah', 'smp', 'sob', 'ufl', 'rah')) )
	{
		$indUrl = $baseUrl . '/index.php?mode=Individuals&amp;tabel=individualoffset&amp;datum=' . 
			$datum . '&amp;prefix=' . $project->getPrefix();
?>
      <td align="center" valign="top" style="font-weight:bold; color:#EEEEEE; cursor:pointer; font-size:11px; background-image:url(images/spacer.3.jpg); width:100" class="cellHeight5" onclick='window.open("<? echo $indUrl; ?>", "_self")'>
       <div style="position:relative; top:9px">
        <a style="hover:#FF0000; color:#EEEEEE; text-decoration:none;" href="<? echo $indUrl; ?>">Individuals</a>
       </div>
      </td>
<?
	}
	else
		echo '<td style="background-image:url(images/spacer.3.jpg); width:100" class="cellHeight5"></td>';
?>
      <td colspan="2"><img name="spacer.4.jpg" src="images/spacer.4.jpg" width="103" class="cellHeight5" alt=""></td>
      <td><a href="?mode=Graph&amp;tabel=teamoffset&amp;prefix=<? echo $project->getPrefix(); ?>&amp;teams[0]=<? echo rawurlencode($project->getTeamName()); ?>" onmouseover="change('index_r4_c8', 'images/Out-Graph-blue.jpg')" 
<? 
	if ( $mode == 'Graph' )
	{ 
		echo "onmouseout=\"change('index_r4_c8', 'images/Out-Graph-blue.jpg')\"> "; 
	}
	else 
	{ 
		echo "onmouseout=\"change('index_r4_c8', 'images/index_r4_c8.jpg')\"> "; 
	} 

	echo '<img name="index_r4_c8" ';
	if ( $mode == 'Graph' )
	{ 
		echo 'src="images/Out-Graph-blue.jpg"'; 
	}
	else 
	{ 
		echo 'src="images/index_r4_c8.jpg"'; 
	} 
?> 
       width="100" class="cellHeight5" alt=""></a>
      </td>
      <td colspan="3"><a href="?mode=Flush&amp;prefix=<? echo $project->getPrefix() ?>" onmouseover="change('index_r4_c9', 'images/History-blue.jpg')" 
<? 
	if ( $mode == 'Flush' )
	{ 
		echo "onmouseout=\"change('index_r4_c9', 'images/History-blue.jpg')\"> "; 
	}
	else 
	{ 
		echo "onmouseout=\"change('index_r4_c9', 'images/index_r4_c9.jpg')\"> "; 
	} 
	echo '<img name="index_r4_c9" ';
	if ( $mode == 'Flush' )
	{ 
		echo 'src="images/History-blue.jpg"'; 
	}
	else 
	{ 
		echo 'src="images/index_r4_c9.jpg"'; 
	} 
?>
	width="99" class="cellHeight5" alt=""></a>
      </td>
      <td><img name="index_r4_c12" src="images/index_r4_c12.jpg" width="10" class="cellHeight5" alt=""></td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight5" alt=""></td>
     </tr>
     <tr>
      <td align="center" colspan="12" style="background-image:url(images/index_r1_c1.jpg);" class="pageCell cellHeight1">
<?
	$query = 'SELECT project, description FROM project WHERE active = 1 ORDER BY project';
	$result = $db->selectQuery($query);

	echo '.';
	while ( $line = $db->fetchArray($result) )
	{
		echo ': <a href="index.php?prefix=' . $line['project'] . '&amp;datum=' . $datum . ($line['project']=='sp5'?'&amp;mode=Stampede':'&amp;mode=Members') . '"><b>' . $line['description'] . '</b></a> :';
	}
	echo '.';
?>
      </td>
     </tr>
     <tr>
      <td colspan="2" style="background-image:url(images/index_r5_c3.jpg); background-position:right top; background-repeat:repeat-y" align="left">
<?php
	$pages = array(	'Members' => array(	'Member Stats', 'members.php'),
						'Teams' => array('Team Stats', 'members.php'),
						'Stampede' => array('Stampede Stats', 'stampede.php'),
						'Individuals' => array('Individual Stats', 'members.php'),
						'Subteam' => array('Subteam Stats', 'members.php'),
						'Graph' => array('Output Graph', 'graphs/progress.php'),
						'Flush' => array('Flush History', 'flush.php'),
						'changelog' => array('Changelog', 'changelog.php'),
						'search' => array('Search', 'search.php'),
						'history' => array('Flush History', 'flushHistory.php'),
						'detail' => array('Contestant Details', 'detail.php'),
						'shoutbox' => array('Shoutbox', 'shoutbox.php'),
						'stats' => array('Stats Engine', 'stats.php'),
						'avgProd' => array('Average Production Rate', 'average.php'),
						'monthlyStats' => array('Monthly Stats', 'monthly.php'),
						'memberGraphs' => array('Member Graphs', 'memberGraphs.php'));

	if ( isset($pages[$mode]) )
	{
		$pagename = $pages[$mode][0];
		$pagefile = $pages[$mode][1];
	}
	else
	{
		$pagename = 'Welcome';
		$pagefile = 'members.php';
	}
	
	echo '<center style="color:#FFFFFF; font-size:14px; font-weight:bold">' . $project->getDescription() . '</center>';
?>
      </td>
      <td colspan="9" style="background-image:url(images/index_r5_c3.jpg); background-position:right top; background-repeat:repeat-y" align="left" >
       <table width="100%" border="0">
        <tr style="color:#FFFFFF; font-size:14px;">
	 <td align="left"><? echo $pagename . ' of ' . date("F d, Y", strtotime($datum)); ?></td>
         <td align="right">Last update: <?php echo date("l j F Y, H:i", strtotime($project->getLastUpdate())); ?></td>
	</tr>
       </table>
      </td>
      <td rowspan="6" style="background-image:url(images/index_r5_c12.jpg); background-position:right top; background-repeat:repeat-y"></td>
      <td><img src="images/spacer.gif" width="1" height="39" alt=""></td>
     </tr>
     <tr>
      <td valign="top" colspan="2" style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y"> 
       <table width="180">
<?
	$link = 1;
	$dotPrefix = '<td align="center" valign="middle" width="7px"><img src="images/dot.png" alt="dot"></td>';
	echo getMenuHeader('General Links', 'glActive');
	if ( $glActive == 'on' )
	{
		echo getMenuEntry('Bug Tracker', $baseUrl . '/mantis/view_all_bug_page.php', $link++);
		echo getMenuEntry('GOT - /5', 'http://gathering.tweakers.net/forum/list_topics/5', $link++);
		echo getMenuEntry('Source Code (Beta)', $baseUrl . '/?mode=stats', $link++);
		echo getMenuEntry('Toggle Calendar', 'index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . 
				'&amp;datum=' . $datum. '&amp;mode=' . $mode  . '&amp;naam=' . rawurlencode($naam) . 
				'&amp;cActive=' . ($cActive=='on'?'off':'on') . '&amp;setCActive=&amp;team=' . rawurlencode($team), $link++);
		echo trBackground($link++) . $dotPrefix . '<td align="left"><a href="index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;naam=' . rawurlencode($naam) . '&amp;dtActive=' . ($dtActive=='on'?'off':'on') . '&amp;setDtActive=&amp;teams=' . $teams . '">Toggle Daily Top\'s</a></td></tr>';
		echo trBackground($link++) . $dotPrefix . '<td align="left"><a href="index.php?prefix=' . $project->getPrefix() . '&amp;tabel=' . $tabel . '&amp;datum=' . $datum . '&amp;mode=' . $mode . '&amp;naam=' . rawurlencode($naam) . '&amp;sbActive=' . ($sbActive=='on'?'off':'on') . '&amp;setSbActive=&amp;teams=' . $teams . '">Toggle Shoutbox</a></td></tr>';
		if ( $debug == 1 )
		{
			echo '<form name="cookies" method="get" action="index.php">';
			echo '<input type="hidden" name="setCookieActive" value="">';
			echo '<input type="hidden" name="ca" value="' . $cActive . '">';
			getDefaultFormFields();
			echo trBackground($link++) . $dotPrefix . '<td align="left">Toggle <select class="TextField" onchange="submitCookieForm()" name="cookieName"><option value="dtActive">Daily Top</option><option value="cActive">Calendar</option></select></td></tr>';
			echo '</form>';
		}
		echo trBackground($link++) . $dotPrefix . '<td align="left"><a href="http://www.dutchpowercows.org">WDO</a> & <a href="http://forum.dutchpowercows.org">FDO</a></td></tr>';
	}
	echo getMenuHeader('Project Links', 'plActive');
	if ( $plActive == 'on' )
	{
		echo getMenuEntry('Average Production', $baseUrl . '/index.php?mode=avgProd&amp;tabel=memberoffset&amp;prefix=' . $project->getPrefix(), $link++);
		echo getMenuEntry('DPC FAQ', 'http://www.dutchpowercows.org/faqs/' . $project->getWDOPrefix(), $link++);
		echo getMenuEntry('DPCH', 'http://www.dutchpowercows.org/dpch/' . $project->getWDOPrefix(), $link++);
		echo trBackground($link++) . $dotPrefix . '<td align="left">Official <a href="' . $project->getForum() . '">Forum</a> & ' . 
				'<a href="' . $project->getWebsite() . '">Website</a><td></tr>';
		echo getMenuEntry('Member Graphs', $baseUrl . '/index.php?mode=memberGraphs&amp;prefix=' . $project->getPrefix(), $link++);
		echo getMenuEntry('Monthly Stats', $baseUrl . '/index.php?mode=monthlyStats&amp;prefix=' . $project->getPrefix(), $link++);
	}
	echo getMenuHeader('Old Projects', 'opActive');
	if ( $opActive == 'on' )
	{
		echo getMenuEntry('Find a Drug', $baseUrl . '/index.php?mode=Members&amp;tabel=memberoffset&amp;naam=' . 
				'&amp;datum=2006-01-26&amp;prefix=fad', $link++);
		echo getMenuEntry('TSC Phase 1', $baseUrl . '/index.php?prefix=tp1&datum=2006-04-03&mode=Members', $link++);
	}
	echo '</table>';

	if ( $cActive == 'on' )
	{
		getCalender($datum);
	}

	if ( $dtActive == 'on' )
	{
		$ml = new MemberList($project->getPrefix() . '_teamoffsetdaily', $project->getCurrentDate(), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Teams', 'teamoffsetdaily');
		
		$ml = new MemberList($project->getPrefix() . '_memberoffsetdaily', $project->getCurrentDate(), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Members', 'memberoffsetdaily');
	}

	if ( $sbActive == 'on' )
	{
		getShoutboxTable($db, $project, $tabel, $team);
	}
?>
       <table width="180px" cellspacing="0" cellpadding="0">
        <tr style="background-image:url(images/left-banner.jpg); height:45px">
         <td align="center" style="font-size:13px; font-weight:bold; color:#FFFFFF;">Search</td>
	</tr>
       </table>					
       <table width="180" cellpadding="2" cellspacing="1">
        <tr>
         <td align="center">
          <form name="Search" action="index.php" method="get">
           <p>
            <input class="TextField" type="text" name="searchString" style="width:155px">
            <input type="hidden" name="mode" value="search">
            <br><br>
            <input type="image" SRC="images/zoek.jpg" value="Search" alt="Search">
           </p>
          </form>
         </td>
        </tr>
<?php
	if ( in_array($mode, array('Members', 'Teams', 'Subteam')) )
	{
		# XML list link
		echo '<tr><td colspan="2"><hr></td></tr>';
		echo '<tr>';
		echo '<td colspan="2" align="center">';
		echo '<a href="xml/list.php?tabel=' . $tabel . '&amp;prefix=' . $project->getPrefix() . '&amp;team=' . rawurlencode($team) . '&amp;datum=' . $datum . '">';
		echo '<img src="images/xml.gif" alt="xml" border="0">';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
	}
?>
       </table>
      </td>
      <td valign="top" rowspan="5" colspan="8" style="background-image:url(images/gray-back2.jpg);">
<?
	# Include the file containing the requested content
	include($pagefile);
?>
      </td>
      <td rowspan="0" style="background-image:url(images/index_r6_c11.jpg); background-position:right top; background-repeat:repeat-y" width="11" height="100%" ></td>
      <td style="background-image:url(images/spacer.gif);" width="1"></td>
     </tr>
     <tr valign="bottom">
      <td style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y" colspan="2"></td>
      <td style="background-image:url(images/index_r6_c11.jpg);"><img src="images/spacer.gif" width="1" height="45" alt=""></td>
     </tr>
     <tr>
      <td colspan="2" style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y" valign="top"></td>
      <td style="background-image:url(images/index_r6_c11.jpg);"><img src="images/spacer.gif" width="1" height="149" alt=""></td>
     </tr>
     <tr valign="bottom">
      <td style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y" colspan="2">
      <td style="background:url(images/index_r6_c11.jpg);"><img src="images/spacer.gif" width="1" height="44" alt=""></td>
     </tr>
     <tr>
      <td colspan="2" style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y" valign="top"></td>
      <td style="background-image:url(images/index_r6_c11.jpg);"><img src="images/spacer.gif" width="1" height="143" alt=""></td>
     </tr>
     <tr>
      <td colspan="12" class="pageCell" style="background-image:url(images/index_r12_c1.jpg); background-position:right top; background-repeat:repeat-y" align="center">
       <a href="mailto:speedkikker@planet.nl">Contact</a> 
       | <a href="http://rkuipers.mine.nu/traffic/awstats.tadah.mine.nu.html">Site Traffic</a>
       | &copy;opyright 2004-2006 TaDaH
       | <a href="http://rkuipers.mine.nu/viewcvs/">Revision <? echo $project->getVersion() . ' (' . date("d-m-Y", strtotime($project->getLastPageUpdate())) . ')';?></a> 
       | <? $duration = microtime_diff($start_time, microtime()); $duration = sprintf("%0.3f", $duration);?> Page loaded in <?=$duration?> seconds
      </td>
      <td><img src="images/spacer.gif" width="1" height="34" alt=""></td>
     </tr>
    </table>
   </td>
   <td rowspan="10" class="styleCell" style="background-image:url(images/bg1223.jpg); background-position:left top; background-repeat:repeat-y"></td>
  </tr>
 </table>
</body>
</html>
<?
$db->disconnect();
?>
