<?php 
require('classes.php');

# Handle retrieving/setting settings using a cookie
function handleCookie($name)
{
	# Check if the value should be set
	if ( isset($_REQUEST['set' . $name]) )
	{
		# Ensure the cookie is not set
		setcookie($name, FALSE);
		
		# Set cookie
		setcookie($name, $_REQUEST['set' . $name], time()+60*60*24*30);
		
		# Return the value it was set to
		return $_REQUEST['set' . $name];
	}
	# Check if the cookie itself is available
	elseif ( isset($_COOKIE[$name]) )
		return $_COOKIE[$name];
	# Default to on for all settings
	else
		return 'on';
}

# Set/unset variables through cookies, used to save settings for hiding various parts on the site
$cActive  = handleCookie('cActive');	# Calender
$sbActive = handleCookie('sbActive');	# Shoutbox
$dtActive = handleCookie('dtActive');	# Daily tops

# The allowed array contains all variables used with their default values. 
# These can be taken from the global array, if they're not set we use the default
$allowed = array(	'mode' => 'Members',		# Which page to load
			'tabel' => 'memberoffset',	# Tabel to take data from
			'datum' => date("Y-m-d"),	# Date to collect data from
			'naam' => '',			# Name used when collecting detailed member info
			'hl' => '',			# Which team to highlight, used for custom position lists
			'dlow' => 0,			# offset for the flush list (start at $dlow instead of 1)
			'low' => 0,			# offset for the overall list
			'flushlist' => 0,		# whether to show the entire list
			'team' => 'Dutch Power Cows');	# Default team name

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

# Load the project data, if none is specified use CP
if ( ( isset($_REQUEST['prefix']) ) && ( preg_match('/^[a-z]{2,3}$/', $_REQUEST['prefix']) ) )
	$project = new Project($db, $_REQUEST['prefix'], $tabel);
else
	$project = new Project($db, 'cp', 'memberoffset');

# Array of allowed modes
$pages = array(	'Members' => array('Member Stats', 'members.php'),
		'Teams' => array('Team Stats', 'members.php'),
		'Individuals' => array('Individual Stats', 'members.php'),
		'Subteam' => array('Subteam Stats', 'members.php'),
		'Graph' => array('Output Graph', 'graphs/progress.php'),
		'Flush' => array('Flush History', 'flush.php'),
		'search' => array('Search', 'search.php'),
		'history' => array('Flush History', 'flushHistory.php'),
		'detail' => array('Contestant Details', 'detail.php'),
		'shoutbox' => array('Shoutbox', 'shoutbox.php'),
		'stats' => array('Stats Engine', 'stats.php'),
		'avgProd' => array('Average Production Rate', 'average.php'),
		'monthlyStats' => array('Monthly Stats', 'monthly.php'),
		'memberGraphs' => array('Member Graphs', 'memberGraphs.php'),
		'login' => array('Login', 'admin/login.php'),
		'register' => array('Register', 'admin/register.php'),
		'verify' => array('Verify', 'admin/verify.php'));

# When the mode is valid set the pagename and pagefile
if ( isset($pages[$mode]) )
{
	$pagename = $pages[$mode][0];
	$pagefile = $pages[$mode][1];
}
# Otherwise revert to default
else
{
	$pagename = 'Welcome';
	$pagefile = 'members.php';
}

# Make sure the page expires when the next statsrun has completed
header('Expires: ' . gmdate("D, d M Y H:i:s ", strtotime("+" . $project->getStatsrunInterval() . " minutes", strtotime($project->getLastUpdate()))) . 'GMT');
header('Content-Type: text/html; charset=ISO-8859-1');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title><?php echo $project->getDescription(); ?> - Statistics</title>
  <script type="text/javascript" src="/resources/shoutbox.js"></script>
  <link rel="stylesheet" href="/resources/page.css" type="text/css">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="Pragma" content="no-cache">
  <link rel="shortcut icon" href="favicon.ico">
 </head>
 <body>
  <table cellpadding="0" cellspacing="0" border="0">
   <tr>
    <td rowspan="10" class="styleCell" style="background-image:url(images/bg1222.jpg); background-position:right top; background-repeat:repeat-y"></td>
    <td>
     <table border="0" cellpadding="0" cellspacing="0" class="pageCell">
      <tr>
       <td align="center" colspan="12" style="background-image:url(images/index_r1_c1.jpg);" class="pageCell cellHeight1">
<?php
	include("menu.php");
?>

       </td>
      </tr>
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
       <td colspan="12"><a href="/"><img name="index_r2_c1" src="images/index_r2_c1.jpg" class="pageCell cellHeight2" alt="Stats"></a></td>
       <td><img src="images/spacer.gif" width="1" class="cellHeight2" alt=""></td>
      </tr>
      <tr>
       <td align="center" colspan="12" style="background-image:url(images/index_r1_c1.jpg);" class="pageCell cellHeight1">
        <table style="width:100%">
         <tr>
          <td style="width:20%; text-align:center"><a class="menulink" href="?mode=Members&amp;prefix=<?php echo $project->getPrefix() ?>&amp;tabel=memberoffset&amp;datum=<?php echo $datum; ?>">Member Stats</a></td>
          <td style="width:20%; text-align:center"><a class="menulink" href="<?php echo '?mode=Teams&amp;prefix=' . $project->getPrefix() . '&amp;tabel=teamoffset&amp;datum=' . $datum; ?>">Team Stats</a></td>
          <td style="width:20%; text-align:center"><a class="menulink" href="<?php echo $baseUrl; ?>/index.php?mode=Individuals&amp;tabel=individualoffset&amp;datum=<?php echo $datum; ?>&amp;prefix=<?php echo $project->getPrefix() ?>">Individual Stats</a>
          <td style="width:20%; text-align:center"><a class="menulink" href="<?php echo '?mode=Graph&amp;tabel=teamoffset&amp;prefix=' . $project->getPrefix() . '&amp;datum=' . $datum; ?>">Output Graphs</a></td>
          <td style="width:20%; text-align:center"><a class="menulink" href="?mode=Flush&amp;prefix=<?php echo $project->getPrefix();?>">Flush History</a></td>
         </tr>
        </table>
       </td>
      </tr>
      <tr>
       <td colspan="2" style="background-image:url(images/index_r5_c3.jpg); background-position:right top; background-repeat:repeat-y;" align="left">
        <center style="color:#FFFFFF; font-size:14px; font-weight:bold;"><a class="wLink" href="<?php echo $project->getWebsite(); ?>"><?php echo $project->getDescription(); ?></a></center>
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
		$ml = new MemberList($project->getPrefix() . '_teamoffset', date("Y-m-d"), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Teams', 'teamoffset');
		
		$ml = new MemberList($project->getPrefix() . '_memberoffset', date("Y-m-d"), 0, 5, $db);
		$ml->generateFlushList();
		getTop5Table($project, $ml, 'Top 5 Members', 'memberoffset');
	}

	if ( $sbActive == 'on' )
	{
		getShoutboxTable($db, $project, $tabel, $team);
		
		# Show a login/register form if not logged in, otherwise show a post box
		if ( ! isset($_SESSION['username']) )
		{
			echo '<div style="width:180px; background-image:url(images/left-banner.jpg); height:45px; text-align:center; font-size:13px; font-weight:bold; color:#FFFFFF;">';
			echo '<div style="position:relative; top:11px; ">Login</div></div>';
			echo '<form name="login" action="admin/login.php" method="post">';
			echo '<table style="width:100%">';
			echo '<tr><td>Username</td><td style="text-align:right"><input style="width:95px" type="text" name="username" value=""></td></tr>';
			echo '<tr><td>Password</td><td style="text-align:right"><input style="width:95px" type="password" name="password" value=""></td></tr>';
			echo '<tr><td colspan="2" style="text-align:center"><input type="submit" value="Login" class="TextField"></td></tr>';
			echo '</table>';
			echo '</form>';
			echo '&nbsp;Of <a href="/index.php?mode=register">Register</a>';
		}
		else
			getShoutBoxForm($project, $tabel, $team);
	}
?>
        <div style="width:180px; background-image:url(images/left-banner.jpg); height:45px; text-align:center; font-size:13px; font-weight:bold; color:#FFFFFF;">
         <div style="position:relative; top:11px; ">Search</div>
        </div>
        <table width="180" cellpadding="2" cellspacing="1">
         <tr>
          <td align="center">
           <form name="Search" action="index.php" method="get">
            <p>
             <input class="TextField" type="text" name="searchString" style="width:155px">
             <input type="hidden" name="mode" value="search">
             <br><br>
             <input type="submit" class="TextField" value="Search" alt="Search">
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
        | &copy;opyright 2004-<?php echo date("Y"); ?> TaDaH
        | <a href="http://rkuipers.mine.nu/svn/dpcstats/" title="View revision history">Revision <?php echo $project->getVersion();?></a> 
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

# Close the database connection
$db->disconnect();

?>
