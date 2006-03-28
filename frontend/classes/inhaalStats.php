<?
class TOSuperClass
{
        protected $tabel;
        protected $db;
        protected $avgProduction;
        protected $memberInfo;
        protected $datum;
        protected $listsize;
        protected $fieldname;
	protected $team;

	function __construct($db, $datum)
	{
		$this->db = $db;
		$this->datum = $datum;
	}

        function getAverageProduction()
        {
                return $this->avgProduction;
        }
}

class TOThreats extends TOSuperClass
{
	function TOThreats($db, $tabel, $avgProduction, $memberinfo, $datum, $listsize, $fieldname, $team)
        {
		parent::__construct($db, $datum);

		$this->team = $team;
                $this->tabel = $tabel;
		$this->avgProduction = $avgProduction;
                $this->memberInfo = $memberinfo;
                $this->listsize = $listsize;
                $this->fieldname = $fieldname;
        }

        function getThreatList()
	{
                $query = 'SELECT
                                o.naam,
                                ( o.cands + o.daily ) AS cands,
                                (
                                 (
                                  ( o.cands + o.daily ) - ' . $this->memberInfo->getCredits() . '
                                 )
                                 /
                                 ( ' . $this->avgProduction . ' - ap.' . $this->fieldname . ' )
                                ) AS dagen,
				ap.' . $this->fieldname . ' AS average
                        FROM
                                ' . $this->tabel . ' o,
                                averageproduction ap
                        WHERE
                                ap.' . $this->fieldname . ' > ' . $this->avgProduction . '
                        AND     id>' . $this->memberInfo->getRank() . '
                        AND     dag = \'' . $this->datum . '\'
                        AND     ap.naam = o.naam
			AND	ap.tabel = \'' . $this->tabel . '\' ' .
			((is_numeric(strpos($this->tabel, 'subteamOffset'))&&(strpos($this->tabel, 'subteamOffset')>0))?'AND o.subteam = \'' . $this->team . '\'':'') . '
                        HAVING  dagen > 0
                        ORDER BY
                                dagen
                        LIMIT ' . $this->listsize;
#		echo $query;

                $result = $this->db->selectQuery($query);

		$members = array();
                while ($line = $this->db->fetchArray($result, MYSQL_ASSOC))
                {
			$members[] = array(	'name' => $line['naam'], 
						'days' => $line['dagen'], 
						'average' => $line['average']);
                }

                return $members;
        }
}

class Opertunities extends TOSuperClass
{
        function __construct($db, $tabel, $avgProduction, $memberinfo, $datum, $listsize, $fieldname, $team)
        {
		parent::__construct($db, $datum);

                $this->tabel = $tabel;
                $this->avgProduction = $avgProduction;
                $this->memberInfo = $memberinfo;
                $this->listsize = $listsize;
		$this->fieldname = $fieldname;
		$this->team = $team;
        }

	function getOpertunityList()
	{
		$query = 'SELECT 
				o.naam, 
				( o.cands + o.daily ) as cands, 
				( ( ( o.cands + o.daily ) - ' . $this->memberInfo->getCredits() . ' ) / ( ' . $this->avgProduction . ' - ap.' . $this->fieldname . ' ) ) AS dagen,
				ap.' . $this->fieldname . ' AS average
			FROM 
				' . $this->tabel . ' o,
				averageproduction ap
			WHERE 
				o.daily < ' . $this->avgProduction . ' 
			AND 	o.id < ' . $this->memberInfo->getRank() . ' 
			AND	' . $this->avgProduction . ' > ap.' . $this->fieldname . '
			AND 	o.dag = \'' . $this->datum . '\'
			AND	o.cands > ' . $this->memberInfo->getCredits() . '
			AND	ap.naam = o.naam
			AND	ap.tabel = \'' . $this->tabel . '\' ' .
			((is_numeric(strpos($this->tabel, 'subteamOffset'))&&(strpos($this->tabel, 'subteamOffset')>0))?'AND o.subteam = \'' . $this->team . '\'':'') . '
			ORDER BY 
				dagen 
			LIMIT ' . $this->listsize;

		$result = $this->db->selectQuery($query);

		$members = array();
		while ($line = $this->db->fetchArray($result, MYSQL_ASSOC))
		{
			$members[] = array(	'name' => $line['naam'], 
						'days' => $line['dagen'], 
						'average' => $line['average']);
		}

		return $members;
	}
}
?>
