<?php

class Movement
{
	private $tabel;
	private $datum;
	private $db;

	private $members;
	private $totalCredits;
	
	public function __construct($db, $tabel, $datum)
	{
		$this->db = $db;
		$this->tabel = $tabel;
		$this->datum = $datum;
		$this->totalCredits = array();

		$this->members = array();
	}

	public function getTotalCredits($direction)
	{
		return $this->totalCredits[$direction];
	}

	public function getMembers($direction)
	{
		if ( ! isset($this->members[$direction]) )
		{
			$this->members[$direction] = array();
		
			$query = 'SELECT
					naam,
					candidates
				FROM
					movement
				WHERE
					direction = ' . $direction . '
				AND	tabel = \'' . $this->tabel . '\'
				AND	datum = \'' . $this->datum . '\'
				ORDER BY
					naam';

			$result = $this->db->selectQuery($query);

			while ( $line = $this->db->fetchArray($result) )
			{
				$this->members[$direction][] = array(	'name' => $line['naam'],
									'credits' => $line['candidates']);

				$this->totalCredits[$direction] += $line['candidates'];
			}
		}
		
		return $this->members[$direction];
	}
}

?>
