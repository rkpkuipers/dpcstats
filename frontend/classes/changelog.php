<?

class Change
{
	var $datum;
	var $entry;
	var $author;
	var $version;

	function Change($datum, $entry, $author, $version)
	{
		$this->datum = $datum;
		$this->entry = $entry;
		$this->author = $author;
		$this->version = $version;
	}

	function getDatum()
	{
		return $this->datum;
	}

	function getEntry()
	{
		return $this->entry;
	}

	function getAuthor()
	{
		return $this->author;
	}

	function getVersion()
	{
		return $this->version;
	}
}

class ChangeLog
{
	var $changes;
	
	var $db;

	function ChangeLog($db)
	{
		$this->db = $db;

		$this->changes = array();
	}
	
	function createChangelog()
	{
		$query = 'SELECT
				author,
				entry,
				dag,
				version
			FROM
				changelog
			ORDER BY
				dag DESC';
		
		$result = $this->db->selectQuery($query);
		
		while ( $line = $this->db->fetchArray($result) )
		{
			$this->changes[] = new Change($line['dag'], $line['entry'], $line['author'], $line['version']);
		}
	}
	
	function getChanges()
	{
		return $this->changes;
	}
}
