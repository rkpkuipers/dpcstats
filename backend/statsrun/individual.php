#!/usr/bin/php
<?

include ('/home/rkuipers/stats/database.php');
include ('/home/rkuipers/stats/include.php');
include ('/var/www/tstats/classes/members.php');

individualStatsrun('fah');
individualStatsrun('rah');
individualStatsrun('smp');
individualStatsrun('ufl');
individualStatsrun('sah');
individualStatsrun('sob');
individualStatsrun('sp5');

?>
