<?

class AverageList
{
	private $members;
	private $tabel;
	private $datum;
	private $project;
	
	private $db;

	function AverageList($db, $tabel, $datum, $project)
	{
		$this->db = $db;
		
		$this->tabel = $tabel;
		$this->datum = $datum;
		$this->project = $project;
	}

	function gather()
	{
#		if ( ( is_numeric(strpos($this->tabel, 'subteamoffset')) ) && ( strpos($this->tabel, 'subteamoffset') > 0 ) )
#			$field = 'CONCAT(m.subteam, \'' . $this->project->getSeperator() . '\', m.naam)AS naam, m.subteam';
#		else
		if ( $this->tabel==$this->project->getPrefix().'_subteamoffset' )
		{
			$fields = 'm.subteam, m.naam';
			$cnaam = 'CONCAT(m.subteam, \'' . $this->project->getSeperator() . '\', m.naam)';
		}
		else
		{
			$fields = 'm.naam';
			$cnaam = 'm.naam';
		}

		$query = 'SELECT 
				' . $fields . ', 
				a.avgdaily, 
				a.avgmonthly 
			FROM 
				averageproduction a, 
				' . $this->tabel . ' m 
			WHERE 
				a.tabel = \'' . $this->tabel . '\' 
			AND 	a.naam = ' . $cnaam . '
			AND 	m.dag = \'' . $this->datum . '\'
			AND 	avgdaily > 0
			ORDER BY 
				avgdaily DESC
			LIMIT
				100';
				
		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$this->members[] = array(	'name' => (isset($line['subteam'])?$line['subteam'].$this->project->getSeperator().$line['naam']:$line['naam']), 
							'daily' => $line['avgdaily'],
							'monthly' => $line['avgmonthly'],
							'team' => $line['subteam'],
							'realname' => $line['naam']);
		}
	}

	function getList()
	{
		return $this->members;
	}
}
?>
