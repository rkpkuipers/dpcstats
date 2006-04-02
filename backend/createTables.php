#!/usr/bin/php
<?

include('/var/www/tstats/classes.php');

$prefix = $argv[1];

echo 'Creating tables for prefix ' . $prefix . "\n";
readline();

$query = 'CREATE TABLE 
		`' . $prefix . '_memberOffsetDaily` 
		(
			`naam` varchar(100) NOT NULL default \'\', 
			`cands` int(10) default NULL,
  			`id` int(4) default NULL,
  			`dag` date NOT NULL default \'0000-00-00\',
  			`daily` int(6) default NULL,
			`dailypos` int(3) default NULL,
			`currRank` int(4) default NULL,
			PRIMARY KEY  (`naam`,`dag`)
		)
		ENGINE=HEAP DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$query = 'CREATE TABLE 
		`' . $prefix . '_subteamOffset` 
		(
			`naam` varchar(100) NOT NULL default \'\',
			`cands` int(10) default NULL,
			`id` int(4) default NULL,
			`dag` date NOT NULL default \'0000-00-00\',
			`daily` int(6) default NULL,
			`dailypos` int(3) default NULL,
			`subteam` varchar(100) NOT NULL default \'\',
			`currRank` int(4) NOT NULL default \'0\',
			PRIMARY KEY  (`naam`,`dag`)
		) 
		ENGINE=InnoDB DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$query = 'CREATE TABLE 
		`' . $prefix . '_teamOffsetDaily` 
		(
			`naam` varchar(100) NOT NULL default \'\',
			`cands` bigint(15) default NULL,
			`id` int(4) default NULL,
			`dag` date NOT NULL default \'0000-00-00\',
			`daily` int(6) default NULL,
			`dailypos` int(3) default NULL,
			`currRank` int(4) default NULL,
			PRIMARY KEY  (`naam`,`dag`)
		) 
		ENGINE=HEAP DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$query = 'CREATE TABLE 
		`' . $prefix . '_memberOffset` 
		(
			`naam` varchar(100) NOT NULL default \'\',
			`cands` int(10) default NULL,
			`id` int(4) default NULL,
			`dag` date NOT NULL default \'0000-00-00\',
			`daily` int(6) default NULL,
			`dailypos` int(3) default NULL,
			`currRank` int(4) NOT NULL default \'0\',
			PRIMARY KEY  (`naam`,`dag`),
			KEY `indSOBMembersDag` (`dag`)
		) 
		ENGINE=InnoDB DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$query = 'CREATE TABLE 
		`' . $prefix . '_teamOffset` 
		(
			`naam` varchar(200) NOT NULL default \'\',
			`cands` bigint(15) default NULL,
			`id` int(6) default NULL,
			`dag` date NOT NULL default \'0000-00-00\',
			`daily` bigint(10) default NULL,
			`dailypos` int(5) default NULL,
			`currRank` int(4) NOT NULL default \'0\',
			PRIMARY KEY  (`naam`,`dag`),
			KEY `indSOBTeamsDag` (`dag`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$query = 'CREATE TABLE 
		`' . $prefix . '_subteamOffsetDaily` 
		(
			`naam` varchar(100) NOT NULL default \'\',
			`cands` bigint(15) default NULL,
			`id` int(4) default NULL,
			`dag` date NOT NULL default \'0000-00-00\',
			`daily` int(6) default NULL,
			`dailypos` int(3) default NULL,
			`subteam` varchar(100) default NULL,
			`currRank` int(4) default NULL,
			PRIMARY KEY  (`naam`,`dag`)
		) 
		ENGINE=HEAP DEFAULT CHARSET=latin1';
$result = $db->selectQuery($query);

$db->disconnect();
?>
