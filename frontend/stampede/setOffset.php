#!/usr/bin/php
<?

include('../classes.php');
include('../include.php');

$query = 'REPLACE INTO
		stampedeParticipants
	(
		name,
		offset
	)
	VALUES
	(
		SELECT naam, (offset+cands) FROM rah_memberOffset
	
