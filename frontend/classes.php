<?php

error_reporting(E_ERROR);

if ( ! isset($_SESSION) )
	session_start();

# Include the classes used 
require ('classes/config.php');

require ('classes/util.php');

require ('classes/database.php');

require ('classes/mijlpalen.php');

require ('classes/members.php');

require ('classes/tableStatistics.php');

require ('classes/inhaalStats.php');

require ('classes/movement.php');

require ('classes/project.php');

require ('classes/AverageProduction.php');

require ('classes/oppertunities.php');

require ('classes/flush.php');

# Initialize the database
$db = new miDataBase($dbuser, $dbpass, $dbhost, $dbport, $dbname);
$db->connect();

?>
