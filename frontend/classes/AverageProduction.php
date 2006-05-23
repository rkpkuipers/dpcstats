<?

class AverageList
{
	private $members;
	private $tabel;
	private $datum;
	
	private $db;

	function AverageList($db, $tabel, $datum)
	{
		$this->db = $db;
		
		$this->tabel = $tabel;
		$this->datum = $datum;
	}

	function gather()
	{
		if ( ( is_numeric(strpos($this->tabel, 'subteamoffset')) ) && ( strpos($this->tabel, 'subteamoffset') > 0 ) )
			$field = '(m.subteam || \' - \' || m.naam)AS naam, m.subteam';
		else
			$field = 'm.naam';
			
		$query = 'SELECT 
				' . $field . ', 
				m.naam AS realname,
				a.avgdaily, 
				a.avgmonthly 
			FROM 
				averageproduction a, 
				' . $this->tabel . ' m 
			WHERE 
				a.tabel = \'' . $this->tabel . '\' 
			AND 	a.naam = m.naam 
			AND 	m.dag = \'' . $this->datum . '\'
			AND 	avgdaily > 0
			ORDER BY 
				avgdaily DESC
			LIMIT
				100';
				
		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$this->members[] = array(	'name' => $line['naam'], 
							'daily' => $line['avgdaily'],
							'monthly' => $line['avgmonthly'],
							'team' => $line['subteam'],
							'realname' => $line['realname']);
		}
	}

	function getList()
	{
		return $this->members;
	}
}
?>
