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
	private $tabel;
	private $flushList;
	private $MFList;
	
	private $db;
	
	function __construct($tabel, $db, $team = '')
	{
		$this->tabel = $tabel;
		
		$this->db = $db;

		$this->team = $team;
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
		
		while ( $line = $this->db->fetchArray($result) )
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
				daily > 0 ' .
			( $this->team != ''?'AND subteam=\'' . $this->team . '\'':'') . ' 
			ORDER BY
				daily DESC
			LIMIT
				1000';
				
		$result = $this->db->selectQuery($query);

		$names = array();
		while ( ( $line = $this->db->fetchArray($result) ) && ( count($this->MFList) < 30 ) )
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
