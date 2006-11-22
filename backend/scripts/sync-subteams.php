#!/usr/bin/php
<?php

include ('/home/httpd/vhosts/dpchserver.nl/public_html/classes/database.php');
include ('/home/httpd/vhosts/dpchserver.nl/public_html/Backend/include.php');

# Projects to retrieve subteam information for
$project = array('sob');

for($i=0;$i<count($project);$i++)
{
	$query = 'SELECT
			name,
			member
		FROM
			' . $project[$i] . '_subteam
		ORDER BY
			name,
			member';
	
	$result = $db->selectQuery($query);

	$subteam = array();
	while ( $line = $db->fetchArray($result) )
		$subteam[$line['name']][] = $line['member'];

	$remotesubteam = array();
	$xmldata = simplexml_load_file('http://tadah.mine.nu/xml/subteam.php?prefix=' . $project[$i]);
	foreach($xmldata->subteam as $xmlsubteam)
	{
		foreach($xmlsubteam->members as $xmlsubteammembers)
		{
			foreach($xmlsubteammembers->member as $xmlsubteammember)
			{
				$remotesubteam['' . $xmlsubteam->teamname][] = '' . $xmlsubteammember->username;
			}
		}
	}

	foreach($remotesubteam as $subteamname => $memberlist)
	{
		$new_members = array_diff($memberlist, $subteam[$subteamname]);
		$ret_members = array_diff($subteam[$subteamname], $memberlist);

		foreach($new_members as $newMemberName)
			$db->insertQuery('INSERT INTO ' . $project[$i] . '_subteam (name, member) VALUES (\'' . $subteamname . '\', \'' . $newMemberName . '\')');
#			echo $newMemberName . ' joined ' . $subteamname . "\n";

		foreach($ret_members as $retMemberName)
			$db->deleteQuery('DELETE FROM ' . $project[$i] . '_subteam WHERE name = \'' . $subteamname . '\' AND member = \'' . $retMemberName . '\'');
#			echo $retMemberName . ' left ' . $subteamname . "\n";
	}
}

$db->disconnect();
?>
