<?
class TOMember extends Member
{
	var $dagen;
	var $average;

	function TOMember($naam, $dagen, $average=0)
	{
		$this->Member($naam, 0);
		$this->dagen = $dagen;
		$this->average = $average;
	}

	function getDagen()
	{
		return $this->dagen;
	}

	function getAverage()
	{
		return $this->average;
	}
}

class TOSuperClass
{
        var $tabel;
        var $db;
        var $avgProduction;
        var $memberInfo;
        var $datum;
        var $listsize;
        var $fieldname;
        var $TOMemberList;

	function TOSuperClass()
	{
		$this->db = new DataBase();
		
		$this->TOMemberList = array();
	}

        function getAverageProduction()
        {
                return $this->avgProduction;
        }
}

class TOThreats extends TOSuperClass
{
	function TOThreats($tabel, $avgProduction, $memberinfo, $datum, $listsize, $fieldname)
        {
		$this->TOSuperClass();

                $this->tabel = $tabel;
	#	if ( $this->avgProduction == "" )
	#		$this->avgProduction = 0;
	#	else
	                $this->avgProduction = $avgProduction;
                $this->memberInfo = $memberinfo;
                $this->datum = $datum;
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
                                averageProduction ap
                        WHERE
                                ap.' . $this->fieldname . ' > ' . $this->avgProduction . '
                        AND     id>' . $this->memberInfo->getRank() . '
                        AND     dag = \'' . $this->datum . '\'
                        AND     ap.naam = o.naam
			AND	ap.tabel = \'' . $this->tabel . '\'
                        HAVING  dagen > 0
                        ORDER BY
                                dagen
                        LIMIT ' . $this->listsize;
#		echo $query;

                $result = $this->db->selectQuery($query);

                while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
                {
                        $this->TOMemberList[count($this->TOMemberList)] = new TOMember($line['naam'], $line['dagen'], $line['average']);
                }

                return $this->TOMemberList;
        }
}

class Opertunities extends TOSuperClass
{
        function Opertunities($tabel, $avgProduction, $memberinfo, $datum, $listsize, $fieldname)
        {
		$this->TOSuperClass();

                $this->tabel = $tabel;
                $this->avgProduction = $avgProduction;
                $this->memberInfo = $memberinfo;
                $this->datum = $datum;
                $this->listsize = $listsize;
		$this->fieldname = $fieldname;
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
				averageProduction ap
			WHERE 
				o.daily < ' . $this->avgProduction . ' 
			AND 	o.id < ' . $this->memberInfo->getRank() . ' 
			AND	' . $this->avgProduction . ' > ap.' . $this->fieldname . '
			AND 	o.dag = \'' . $this->datum . '\'
			AND	o.cands > ' . $this->memberInfo->getCredits() . '
			AND	ap.naam = o.naam
			AND	ap.tabel = \'' . $this->tabel . '\'
			ORDER BY 
				dagen 
			LIMIT ' . $this->listsize;

		$result = $this->db->selectQuery($query);

		while ($line = mysql_fetch_array($result, MYSQL_ASSOC))
		{
			$this->TOMemberList[count($this->TOMemberList)] = new TOMember($line['naam'], $line['dagen'], $line['average']);
		}

		return $this->TOMemberList;
	}
}
?>
