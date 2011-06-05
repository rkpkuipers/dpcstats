<?php 
require('classes.php');

# Record the start time to be able to show a Page loaded in x seconds line
$start_time = microtime();

# Start the session
session_start();

# Set/unset variables through cookies, used to save settings for hiding various parts on the site
$cActive  = handleCookie('cActive',  $_GET['setCActive'],  $_GET['cActive'],  $_COOKIE['cActive']);	# Calender
$sbActive = handleCookie('sbActive', $_GET['setSbActive'], $_GET['sbActive'], $_COOKIE['sbActive']);	# Shoutbox
$dtActive = handleCookie('dtActive', $_GET['setDtActive'], $_GET['dtActive'], $_COOKIE['dtActive']);	# Daily tops

# The allowed array contains all variables used with their default values. 
# These can be taken from the global array, if they're not set we use the default
$allowed = array(	'mode' => 'Members',		# Which page to load
			'searchString' => '',		# Inputstring used in search
			'tabel' => 'memberoffset',	# Tabel to take data from
			'datum' => date("Y-m-d"),	# Date to collect data from
			'naam' => '',			# Name used when collecting detailed member info
			'frame' => '',			# Let's find out
			'hl' => '',			# Which team to highlight, used for custom position lists
			'dlow' => 0,			# offset for the flush list (start at $dlow instead of 1)
			'low' => 0,			# offset for the overall list
			'flushlist' => 0,		# whether to show the entire list
			'team' => 'Dutch Power Cows');	# Default team name

# All table names we're changed to lower case, convert any old names to new ones for backward compatibility
$tabel = strtolower($tabel);

# Allowed contains the variables in use, check if they were passed as _GET of _POST, otherwise load default values
foreach($allowed as $name => $default)
{
	if ( isset($_REQUEST[$name]) )
		$$name = $_REQUEST[$name];
	else
		$$name = $default;
}

# Check some of the variables for valid contents

# Change mode to login when trying to access the admin directly
# This only affects visual output
if ( ( $mode == 'admin' ) && ( ! isset($_SESSION['username']) ) )
{
	$mode = 'login';
}

# Verify we're going to use a tabel the scripts have direct access to
checkTable($tabel);

# Date's in the future are changed into today
if ( $datum > date("Y-m-d") )
	$datum = date("Y-m-d");

if ( isset($_GET['detail']) )
	$graphDetail = $_GET['detail'];
else
	$graphDetail = 0;

if ( isset ( $_GET['sort']) )
	$sort = $_GET['sort'];
else
{
	if ( $mode == 'history' )
		$sort = 'dag';
	else
		$sort = 'avgDaily';
}

# Load the project data, if none is specified use TSC
if ( isset($_REQUEST['prefix']) )
	$project = new Project($db, $_REQUEST['prefix'], $tabel);
else
	$project = new Project($db, 'tsc', 'memberoffset');

# Make sure the page expires when the next statsrun has completed
header('Expires: ' . gmdate("D, d M Y H:i:s ", strtotime("+" . $project->getStatsrunInterval() . " minutes", strtotime($project->getLastUpdate()))) . 'GMT');
header('Content-Type: text/html; charset=ISO-8859-1');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title><?php echo $project->getDescription(); ?> - Statistics</title>

<script type="text/javascript">

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
       <a href="?mode=Members&amp;prefix=<?php echo $project->getPrefix() ?>&amp;tabel=memberoffset&amp;datum=<?php echo $datum; ?>"><img name="index_r4_c2" src="images/index_r4_c2.jpg" width="108" class="cellHeight5" alt=""></a>
      </td>
      <td align="center" valign="top" style="font-weight:bold; color:#EEEEEE; cursor:pointer; font-size:11px; background-image:url(images/teams.jpg); width:100" class="cellHeight5" onclick='window.open("<?php echo '?mode=Teams&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamoffset&amp;datum=' . $datum; ?>", "_self")'>
       <div style="position:relative; top:9px">
        <a style="hover:#FF0000; color:#EEEEEE; text-decoration:none;" href="<?php echo '?mode=Teams&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamoffset&amp;datum=' . $datum; ?>">Teams</a>
       </div>
      </td>
<?php
	if ( in_array($project->getPrefix(), array('fah', 'sah', 'smp', 'sob', 'ufl', 'rah', 'wcg', 'ldc', 'sp6')) )
	{
		$indUrl = $baseUrl . '/index.php?mode=Individuals&amp;tabel=individualoffset&amp;datum=' . 
			$datum . '&amp;prefix=' . $project->getPrefix();
?>
      <td align="center" valign="top" style="font-weight:bold; color:#EEEEEE; cursor:pointer; font-size:11px; background-image:url(images/spacer.3.jpg); width:100" class="cellHeight5" onclick='window.open("<?php echo $indUrl; ?>", "_self")'>
       <div style="position:relative; top:9px">
        <a style="hover:#FF0000; color:#EEEEEE; text-decoration:none;" href="<?php echo $indUrl; ?>">Individuals</a>
       </div>
      </td>
<?php
	}
	else
		echo '<td style="background-image:url(images/spacer.3.jpg); width:100" class="cellHeight5"></td>';
?>
      <td colspan="2"><img name="spacer.4.jpg" src="images/spacer.4.jpg" width="103" class="cellHeight5" alt=""></td>

      <td align="center" valign="top" style="font-weight:bold; color:#EEEEEE; cursor:pointer; font-size:11px; background-image:url(images/output-graph.jpg); width:100" class="cellHeight5" onclick='window.open("<?php echo '?mode=Graph&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum; ?>", "_self")'>
       <div style="position:relative; top:6px">
        <a style="hover:#FF0000; color:#EEEEEE; text-decoration:none;" href="<?php echo '?mode=Graph&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum; ?>">Output<br>Graphs</a>
       </div>
      </td>
      <td colspan="3" align="center" valign="top" style="font-weight:bold; color:#EEEEEE; cursor:pointer; font-size:11px; background-image:url(images/spacer6.jpg);" class="cellHeight5" onclick='window.open("<?php echo '?mode=Flush&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum; ?>", "_self")'>
       <div style="position:relative; top:9px;">
        <a style="hover:#FF0000; color:#EEEEEE; text-decoration:none;" href="?mode=Flush&amp;prefix=<?php echo $project->getPrefix();?>">Flush History</a>
       </div>
      </td>
      <td><img name="index_r4_c12" src="images/index_r4_c12.jpg" width="10" class="cellHeight5" alt=""></td>
      <td><img src="images/spacer.gif" width="1" class="cellHeight5" alt=""></td>
     </tr>
     <tr>
      <td align="center" colspan="12" style="background-image:url(images/index_r1_c1.jpg);" class="pageCell cellHeight1">
<?php
	include("menu.php");
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
						'memberGraphs' => array('Member Graphs', 'memberGraphs.php'),
						'state' => array('Statement', 'statement.html'),
						'faq' => array('Frequently Asked Questions', 'faq.php'),
						'login' => array('Login', 'admin/login.php'),
						'register' => array('Register', 'admin/register.php'),
						'verify' => array('Verify', 'admin/verify.php'),
						'admin' => array('Admin Center', 'admin/admin.php'));

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
	
	echo '<center style="color:#FFFFFF; font-size:14px; font-weight:bold;"><a class="wLink" href="' . $project->getWebsite() . '">' . $project->getDescription() . '</a></center>';
?>
      </td>
      <td colspan="9" style="background-image:url(images/index_r5_c3.jpg); background-position:right top; background-repeat:repeat-y" align="left" >
       <table width="100%" border="0">
        <tr style="color:#FFFFFF; font-size:14px;">
	 <td align="left"><?php echo $pagename . ' of ' . date("F d, Y", strtotime($datum)); ?></td>
         <td align="right">Last update: <?php echo date("l j F Y, H:i", strtotime($project->getLastUpdate())); ?></td>
	</tr>
       </table>
      </td>
      <td rowspan="6" style="background-image:url(images/index_r5_c12.jpg); background-position:right top; background-repeat:repeat-y"></td>
      <td><img src="images/spacer.gif" width="1" height="39" alt=""></td>
     </tr>
     <tr>
      <td valign="top" colspan="2" style="background-image:url(images/gray-back.jpg); background-position:right top; background-repeat:repeat-y"> 
<?php
	if ( isset($_SESSION['username']) )
	{
		echo '&nbsp;Logged in as: ' . $_SESSION['username'] . '<br>';
		echo '&nbsp;<a href="admin/logout.php">Logout</a>';
		echo '<div><hr></div>';
	}
	
	if ( $cActive == 'on' )
	{
		getCalender($datum);
	}

	if ( $dtActive == 'on' )
	{
		$ml = new MemberList($project->getPrefix() . '_teamoffset', $project->getCurrentDate(), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Teams', 'teamoffset');
		
		$ml = new MemberList($project->getPrefix() . '_memberoffset', $project->getCurrentDate(), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Members', 'memberoffset');
	}

	if ( $sbActive == 'on' )
	{
		getShoutboxTable($db, $project, $tabel, $team);
		
		# Show a login/register form if not logged in, otherwise show a post box
		if ( ! isset($_SESSION['username']) )
			getLoginRegisterBox($db);
		else
			getShoutBoxForm($project, $tabel, $team);
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
		echo '<br><br>';
		echo '</td>';
		echo '</tr>';
	}
?>
       </table>
      </td>
      <td valign="top" rowspan="1" colspan="8" style="background-image:url(images/gray-back2.jpg);">
<?php
	# Include the file containing the requested content
	include($pagefile);
?>
      </td>
      <td rowspan="0" style="background-image:url(images/index_r6_c11.jpg); background-position:right top; background-repeat:repeat-y" width="11" height="100%" ></td>
      <td style="background-image:url(images/spacer.gif);" width="1"></td>
     </tr>
     <tr>
      <td colspan="12" class="pageCell" style="background-image:url(images/index_r12_c1.jpg); background-position:right top; background-repeat:repeat-y" align="center">
       <a href="mailto:speedkikker@planet.nl" title="Mail SpeedKikker">Contact</a> 
       | <a href="http://tadah.mine.nu/traffic/awstats.tadah.mine.nu.html" title="View site traffic">Site Traffic</a>
       | &copy;opyright 2004-2008 TaDaH
       | <a href="http://rkuipers.mine.nu/viewcvs/" title="View revision history">Revision <?php echo $project->getVersion();?></a> 
       | <?php echo 'Page loaded in ' . sprintf("%0.3f", microtime_diff($start_time, microtime())) . ' seconds'; ?>
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
<?php
$db->disconnect();
?>
