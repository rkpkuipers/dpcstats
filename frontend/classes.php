<?
# Include the classes used 
require ('classes/util.php');

require ('classes/database.php');

require ('classes/mijlpalen.php');

require ('classes/members.php');

require ('classes/tableStatistics.php');

require ('classes/inhaalStats.php');

require ('classes/movement.php');

require ('classes/shoutbox.php');

require ('classes/project.php');

require ('classes/flush.php');

require ('classes/changelog.php');

require ('classes/subteam.php');

require ('classes/AverageProduction.php');

require ('classes/faq.php');

# Initialize the database
$db = new pgDataBase();
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
$kleur[16] = "#FF0000";   # Geel
$kleur[17] = "#A52A2A";   # Brown

for($i=18;$i<31;$i++)
	$kleur[$i] = "#000000";

?>
