<?

class Member
{
	protected $name;
	private $credits;

	function Member($name, $credits)
	{
		$this->name = $name;
		$this->credits = $credits;
	}

	function getName()
	{
		return $this->name;
	}

	function getNaam()
	{
		echo 'DEPRECATED USE OF getNaam()';
		return $this->name;
	}

	function getCredits()
	{
		return $this->credits;
	}

	function getCandidates()
	{
		echo 'DEPECRATED USE OF getCandidates()';
		return $this->credits;
	}
}

class TeamMember extends Member
{
	var $team;

	function TeamMember($naam, $candidates, $team)
	{
		$this->Member($naam, $candidates);

		$this->team = $team;
	}

	function getTeam()
	{
		return $this->team;
	}
}

class DetailedMember extends Member
{
	var $flush;
	var $rank;
	var $flushrank;
	var $flushrankYesterday;
	var $tabel;
	var $datum;
	var $description;
	var $owner;
	var $currRank;

	private $subteam;

	var $db;
	
	function DetailedMember($db, $tabel, $datum, $naam, $candidates, $rank = 0, $flush = 0, $flushrank = 0, $flushrankYesterday = 0, $currRank = 0)
	{
		$this->Member($naam, $candidates);
		$this->flush = $flush;
		$this->rank = $rank;
		$this->flushrank = $flushrank;
		$this->tabel = $tabel;
		$this->datum = $datum;
		$this->flushrankYesterday = $flushrankYesterday;
		if ( $flushrankYesterday == '' )
			$this->flushrankYesterday = $flushrank;

		$this->currRank = $currRank;

	#	echo $flushrankYesterday . ' ';

		$this->db = $db;

		if ( $this->flush == 0 )
			$this->flushrank = '-';
	}

	function getCurrRank()
	{
		return $this->currRank;
	}

	function getShortName()
	{
		$maxlength = 16;
        	if ( strlen($this->name) > $maxlength )
	                return substr($this->name, 0, $maxlength) . '..';
        	else
                	return $this->name;
	}

	function getFlush()
	{
		return $this->flush;
	}

	function getDailyPosChange()
	{
		echo $this->flushrankYesterday . "-" .  $this->flushrank;
		return ( $this->flushrank - $this->flushrankYesterday);
	}

	function getYesterday()
	{
		return $this->flushrankYesterday;
	}

	function getRank()
	{
		return $this->rank;
	}

	function getFlushRank()
	{
		return $this->flushrank;
	}

	function setOwner($owner)
	{
		$this->owner = $owner;
	}

	function getOwner()
	{
		return $this->owner;
	}

	function subteam($bool)
	{
		$this->subteam = $bool;
	}

	function isSubteam()
	{
		return $this->subteam;
	}
}

class MemberList
{
	var $members = array();
	var $datum;
	var $db;
	var $listOffset;
	var $listsize;
	var $subteam;

	private $subteamCount;

	function MemberList($tabel, $datum, $listOffset, $listsize, $db, $subteam = '')
	{
		$this->db = $db;
		$this->datum = $datum;
		$this->tabel = $tabel;
		$this->listOffset = $listOffset;
		$this->listsize = $listsize;
		$this->subteam = $subteam;
		$this->subteamCount = 0;
	}

	function generateFlushList()
	{
		if ( strpos($this->tabel, 'subteamOffset') !== FALSE )
		{
			$where = ' 	AND of.subteam = \'' . $this->subteam . '\' ';
			$join  = '	AND y.subteam = \'' . $this->subteam . '\' ';
		}

		$query = '
  	        SELECT
               		of.naam,
	                of.daily,
               		of.id,
			of.currRank,
	                (of.cands + of.daily) AS total,
               		of.dailypos,
			y.dailypos AS ydDailyPos
	        FROM
               		' . $this->tabel . ' of
		LEFT JOIN
			' . $this->tabel . ' y
		ON	y.naam = of.naam 
		AND 	y.dag = \'' . getPrevDate($this->datum) . '\' 
		' . $join . ' 
	        WHERE
		NOT	of.daily = 0
		AND NOT	of.dailyPos = 0
	        AND     of.dag = \'' . $this->datum . '\'
		' . $where . '
	        ORDER BY
              		of.dailypos
	        LIMIT ' . $this->listOffset . ',' . $this->listsize;
#		echo $query;

		$result = $this->db->selectQuery($query);

		while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
		{
			$this->members[] = new DetailedMember($db,
							      $this->tabel,
							      $this->datum,
							      $line['naam'],
							      $line['total'],
							      $line['id'],
							      $line['daily'],
							      $line['dailypos'],
							      $line['ydDailyPos'],
							      $line['currRank']);
		}

		$this->setTeams();
	}

        function generateMonthlyFlushList($startDate, $endDate)
        {
		$query = 'SELECT
                        of.naam,
                        ( of.cands + of.daily ) AS total,
                        oy.naam,
                        MIN(oy.dag),
                        of.dailypos AS id,
                        oy.id AS dailypos,
                        of.id,
                        oy.cands AS oudTotal,
                        ( ( of.cands + of.daily ) - oy.cands ) AS flushed
                FROM
                        ' . $this->tabel . ' oy
                LEFT JOIN
                        ' . $this->tabel . ' of
                ON
                        oy.naam = of.naam
		AND	of.dag = \'' . $endDate . '\'
		WHERE
                	oy.dag LIKE \'' . $startDate . '-%\'
                GROUP BY
                        of.naam
		HAVING	flushed > 0
                ORDER BY
                        flushed DESC
                LIMIT   ' . $this->listOffset . ',' . $this->listsize;
                $result = $this->db->selectQuery($query);

                while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
                {
                        $tmpMemberID = count($this->members);
                        $this->members[$tmpMemberID] = new DetailedMember($this->db,
								$this->tabel,
                                                                $this->datum,
                                                                $line['naam'],
                                                                $line['total'],
                                                                $line['id'],
                                                                $line['flushed'],
                                                                $line['dailypos']);
                        #$this->members[$tmpMemberID]->getYesterdayFlushPos();
                }
        }

        function generateYearlyFlushList($startDate, $endDate)
        {
                $query = 'SELECT
                        of.naam,
                        ( of.cands + of.daily ) AS total,
                        oy.naam,
                        MIN(oy.dag),
                        of.dailypos AS id,
                        oy.id AS dailypos,
                        of.id,
                        oy.cands AS oudTotal,
                        ( ( of.cands + of.daily ) - oy.cands ) AS flushed
                FROM
                        ' . $this->tabel . ' oy
                LEFT JOIN
                        ' . $this->tabel . ' of
                ON
                        oy.naam = of.naam
		AND	of.dag = \'' . $endDate . '\'
		WHERE
                	oy.dag LIKE \'' . $startDate . '%\'
                GROUP BY
                        of.naam
                HAVING  flushed > 0
                ORDER BY
                        flushed DESC
                LIMIT   ' . $this->listOffset . ',' . $this->listsize;

                $result = $this->db->selectQuery($query);

                while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
                {
                        $tmpMemberID = count($this->members);
                        $this->members[$tmpMemberID] = new DetailedMember($db,
								$this->tabel,
                                                                $this->datum,
                                                                $line['naam'],
                                                                $line['total'],
                                                                $line['id'],
                                                                $line['flushed'],
                                                                $line['dailypos']);
                        #$this->members[$tmpMemberID]->getYesterdayFlushPos();
                }
        }

	function setTeams()
	{
		# Functie om in de instances van de class aan te geven wat subteams zijn
		$prefix = substr($this->tabel, 0, strpos($this->tabel, '_'));

		if ( ! is_numeric(strpos($this->tabel, 'subteamOffset')) )
		{
			$query = 'SELECT
					DISTINCT(subteam)
				FROM
					' . $prefix . '_subteamOffset
				WHERE
					dag = \'' . $this->datum . '\'';

			$result = $this->db->selectQuery($query);
			
			while ( $line = $this->db->fetchArray($result) )
			{
				$subteams[$line['subteam']] = 1;
			}

			for($i=0;$i<count($this->members);$i++)
			{
				if ( isset($subteams[$this->members[$i]->getName()]) )
				{
					$this->members[$i]->subteam(true);
					$this->subteamCount++;
				}
			}
		}
	}

	function getSubteamCount()
	{
		return $this->subteamCount;
	}

	function generateRankList()
	{
		if ( strpos($this->tabel,'subteamOffset') !== FALSE )
		{
	        	$where = ' AND of.subteam = \'' . $this->subteam . '\' ';
			$joinWhere = ' AND y.subteam = \'' . $this->subteam . '\'';
		}
	
		$query = '
		SELECT
                	of.naam,
                	(of.cands + of.daily) as total,
		        of.daily,
                	of.dailypos,
		        of.id,
			of.currRank,
			y.dailypos AS ydDailyPos
	        FROM
               		' . $this->tabel . ' of
		LEFT JOIN
			' . $this->tabel . ' y
		ON
			y.naam = of.naam
			' . $joinWhere . '
		AND	y.dag = \'' . getPrevDate($this->datum) . '\'
	        WHERE
               		of.dag = \'' . $this->datum . '\'
			' . $where . '
	        ORDER BY
               		total DESC
	        LIMIT ' . $this->listOffset . ',' . $this->listsize;

#echo $query;
                $result = $this->db->selectQuery($query);

		$this->members = array();
                while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
                {
                        $this->members[] = new DetailedMember($db,
							      $this->tabel,
							      $this->datum,
							      $line['naam'],
							      $line['total'],
							      $line['id'],
							      $line['daily'],
							      $line['dailypos'],
							      $line['ydDailyPos'],
							      $line['currRank']);

                }

		$this->setTeams();
	}
	

	function generateMonthlyRankList($startDate, $endDate)
        {
                $query = 'SELECT
                        of.naam,
                        ( of.cands + of.daily ) AS total,
                        oy.naam,
                        MIN(oy.dag),
                        of.id AS endId,
                        oy.id AS startId,
			of.dailypos AS dailyPos,
                        oy.cands AS oudTotal,
                        ( ( of.cands + of.daily ) - oy.cands ) AS flushed
                FROM
                        ' . $this->tabel . ' oy
                LEFT JOIN
                        ' . $this->tabel . ' of
                ON
                        oy.naam = of.naam
		AND 	of.dag = \'' . $endDate . '\'
		WHERE
                	oy.dag LIKE \'' . $startDate . '-%\'
                GROUP BY
                        of.naam
                ORDER BY
                        total DESC
                LIMIT   ' . $this->listOffset . ',' . $this->listsize;

                $result = $this->db->selectQuery($query);

                $this->members = array();
                while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
                {
                        $tmpMemberID = count($this->members);
                        $this->members[$tmpMemberID] = new DetailedMember($this->db,
								$this->tabel,
                                                                $this->datum,
                                                                $line['naam'],
                                                                $line['total'],
                                                                $line['startId'],
                                                                $line['flushed'],
                                                                $line['dailyPos'], 
								$line['startId']);
                }

        }
	
	function generateYearlyRankList($startDate, $endDate)
        {
                $query = 'SELECT
                        of.naam,
                        ( of.cands + of.daily ) AS total,
                        oy.naam,
                        MIN(oy.dag),
                        of.id AS endId,
                        oy.id AS startId,
			of.dailypos AS dailyPos,
                        oy.cands AS oudTotal,
                        ( ( of.cands + of.daily ) - oy.cands ) AS flushed
                FROM
                        ' . $this->tabel . ' oy
                LEFT JOIN
                        ' . $this->tabel . ' of
                ON
                        oy.naam = of.naam
		AND 	of.dag = \'' . $endDate . '\'
		WHERE
                	oy.dag LIKE \'' . $startDate . '-%\'
                GROUP BY
                        of.naam
                ORDER BY
                        total DESC
                LIMIT   ' . $this->listOffset . ',' . $this->listsize;

                $result = $this->db->selectQuery($query);

                $this->members = array();
                while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
                {
                        $tmpMemberID = count($this->members);
                        $this->members[$tmpMemberID] = new DetailedMember($this->db,
								$this->tabel,
                                                                $this->datum,
                                                                $line['naam'],
                                                                $line['total'],
                                                                $line['startId'],
                                                                $line['flushed'],
                                                                $line['dailyPos'], 
								$line['startId']);
                }

        }





	function getMembers()
	{
		return $this->members;
	}
}

?>
