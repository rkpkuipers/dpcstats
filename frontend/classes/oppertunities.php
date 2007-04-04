<?

class Oppertunities
{
	private $name;
	private $subteam;
	private $project;
	private $db;

	function __construct($name, $subteam, $project, $db)
	{
		$this->name = $name;
		$this->subteam = $subteam;
		$this->project = $project;
		$this->db = $db;
	}

	function getOppList()
	{
		$info = array();

		$info['name'] = $this->name;
		$info['subteam'] = $this->subteam;

		$query = 'SELECT
				ap.avgdaily,
				ap.avgmonthly,
				o.id,
				o.cands,
				o.daily
			FROM
				averageproduction ap,
				' . $this->project->getPrefix() . '_' . $this->project->getTabel() . ' o
			WHERE
				o.naam = \'' . $this->db->real_escape_string($this->name) . '\'
			AND	ap.naam = \'' . $this->db->real_escape_string(($this->project->getTabel()=='subteamoffset'?$this->subteam.$this->project->getSeperator().$this->name:$this->name)) . '\'
			AND	o.dag = \'' . date("Y-m-d") . '\'
			AND	tabel = \'' . $this->project->getPrefix() . '_' . $this->project->getTabel() . '\'';

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$credits = $line['cands'];
			$average = $line['avgdaily'];
			$maverage = $line['avgmonthly'];
			$rank = $line['id'];
		}

		$info['average'] = $average;
		$info['maverage'] = $maverage;
		
		$query = 'SELECT
				o.naam,
				( o.cands + o.daily ) AS cands,
				ap.avgdaily,
				ap.avgmonthly,
				( ( ( o.cands + o.daily ) - ' . $credits . ' ) / 
				  ( ' . $average . ' - ap.avgdaily ) ) AS dagen,
				( ( ( o.cands + o.daily ) - ' . $credits . ' ) /
				  ( ' . $maverage . ' - ap.avgmonthly ) ) as mdagen
			FROM
				averageproduction ap,
				' . $this->project->getPrefix() . '_' . $this->project->getTabel() . ' o
			WHERE
				o.dag = \'' . date("Y-m-d") . '\' ' .
				($this->project->getTabel()=='subteamoffset'?
					'AND subteam = \'' . $this->db->real_escape_string($this->subteam) . '\'':'') . '
			AND	o.id < ' . $rank . '
			AND	ap.tabel = \'' . $this->project->getPrefix() . '_' . $this->project->getTabel() . '\' ' . 
				($this->project->getTabel()=='subteamoffset'?
					'AND ap.naam = CONCAT(o.subteam,\'' . $this->project->getSeperator() . '\',o.naam)':
					'AND ap.naam = o.naam') . '
			AND	( ap.avgdaily < ' . $average . ' OR ap.avgmonthly < ' . $maverage . ' )
			HAVING	( dagen < 1000 OR mdagen < 1000 )
			ORDER BY
				mdagen,
				dagen
			LIMIT
				30';

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
			$info['opp'][] = array(	'name' => $line['naam'], 
						'days' => $line['dagen'], 
						'mdays' => $line['mdagen'],
						'average' => $line['avgdaily'], 
						'maverage' => $line['avgmonthly']);

		$query = 'SELECT
				o.naam,
				( o.cands + o.daily ) AS cands,
				ap.avgdaily,
				ap.avgmonthly,
				( ( ( o.cands + o.daily ) - ' . $credits . ' ) / 
				  ( ' . $average . ' - ap.avgdaily ) ) AS dagen,
				( ( ( o.cands + o.daily ) - ' . $credits . ' ) /
				  ( ' . $maverage . ' - ap.avgmonthly ) ) AS mdagen
			FROM
				averageproduction ap,
				' . $this->project->getPrefix() . '_' . $this->project->getTabel() . ' o
			WHERE
				o.dag = \'' . date("Y-m-d") . '\' ' .
				($this->project->getTabel()=='subteamoffset'?
					'AND subteam = \'' . $this->db->real_escape_string($this->subteam) . '\'':'') . '
			AND	o.id > ' . $rank . '
			AND	ap.tabel = \'' . $this->project->getPrefix() . '_' . $this->project->getTabel() . '\' ' . 
				($this->project->getTabel()=='subteamoffset'?
					'AND ap.naam = CONCAT(o.subteam,\'' . $this->project->getSeperator() . '\',o.naam)':
					'AND ap.naam = o.naam') . '
			AND	( ap.avgdaily > ' . $average . ' OR ap.avgmonthly > ' . $maverage . ' )
			HAVING	( dagen < 1000 OR mdagen < 1000 )
			ORDER BY
				mdagen,
				dagen
			LIMIT
				30';

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$info['thr'][] = array(	'name' => $line['naam'],
						'days' => $line['dagen'],
						'mdays' => $line['mdagen'],
						'average' => $line['avgdaily'],
						'maverage' => $line['avgmonthly']);
		}

		return $info;
	}
}

?>
