<?

class FlushMember extends Member
{
	private $datum;

	function FlushMember($naam, $candidates, $date)
	{
		$this->Member($naam, $candidates);

		$this->datum = $date;
	}

	function getDate()
	{
		return $this->datum;
	}
}

class FlushList
{
	var $tabel;
	var $flushList;
	var $MFList;
	
	var $db;
	
	function FlushList($tabel, $db)
	{
		$this->tabel = $tabel;
		
		$this->db = $db;
	}
	
	function createFlushList()
	{
		$query = 'SELECT 
				naam, 
				dag, 
				daily 
			FROM 
				' . $this->tabel . ' 
			ORDER BY 
				daily DESC 
			LIMIT 
				30';
		
		$result = $this->db->selectQuery($query);
		
		while ( $line = mysql_fetch_array($result) )
		{
			$this->flushList[] = new FlushMember($line['naam'], $line['daily'], $line['dag']);
		}
	}
	
	function createMFList()
	{
		$query = 'SELECT 
				naam,
				dag,
				daily 
			FROM
				' . $this->tabel . '
			WHERE
				daily > 0
			ORDER BY
				daily DESC
			LIMIT
				1000';
				
		$result = $this->db->selectQuery($query);

		$names = array();
		while ( ( $line = mysql_fetch_array($result) ) && ( count($names) < 30 ) )
		{
			if ( ! isset($names[$line['naam']]) )
			{
				$names[$line['naam']] = '';
				$this->MFList[] = new FlushMember($line['naam'], $line['daily'], $line['dag']);
			}
		}
	}
	
	function getFlushList()
	{
		return $this->flushList;
	}
	
	function getMFList()
	{
		return $this->MFList;
	}
}
?>
