<?php
#################################################################################
# Variables used by the stats program				 	   	#
#################################################################################

# How long should the member lists on the frontend be
$listsize = 30;

# Baseurl on which the stats are hosted, used for links
$baseUrl = 'http://tadah.mine.nu';

# Location of the jpgraph include files, version >2.0 required
$jpgraphdir = '/usr/share/php5/jpgraph/';

# Colors used in the graphs, remaining 15 default to black
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

# Database settings
$dbuser = '';		# Username
$dbpass = '';		# Password
$dbport = '0';		# Connection port, unused for MySQL, 3306 for default PostGres
$dbname = 'stats';	# Database name
$dbhost = '127.0.0.1';	# Host running the database

?>
