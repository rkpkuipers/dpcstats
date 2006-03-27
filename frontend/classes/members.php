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

class MemberInfo
{
	var $db;
	var $tabel;
	var $naam;
	var $description;
	var $datum;
	var $prefix;

	var $avgDailyPos;
	var $candidates;
	var $flush;
	var $nodes;
	var $rank;
	var $dailyRank;
	var $lFlushSize;
	var $lFlushDate;
	var $distanceNext;
	var $naamNext;
	var $distancePrev;
	var $naamPrev;
	var $prevDayFlushCount;
	var $subteam;

	private $team;

	function MemberInfo($db, $naam, $tabel, $datum, $prefix, $speedTabel, $team)
	{
		$this->db = $db;
		$this->naam = $naam;
		$this->tabel = $tabel;
		$this->speedTabel = $speedTabel;
		$this->datum = $datum;
		$this->prefix = $prefix;

		$this->avgDailyPos = 0;
		$this->candidates = 0;
		$this->flush = 0;
		$this->nodes = 1;
		$this->rank = 0;
		$this->dailyRank = 0;
		$this->lFlushSize = 0;
		$this->lFlushDate = $this->datum;
		$this->prevDayFlushCount = 0;
		$this->subteam = FALSE;

		$this->team = $team;

		$this->gatherInformation();
	}

	function gatherInformation()
	{
		$query = 'SELECT
				COUNT(*)AS memberCount
			FROM
				' . $this->prefix . '_subteamOffset
			WHERE
				subteam = \'' . $this->naam . '\'';

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			if ( $line['memberCount'] == 0 )
				$this->subteam = false;
			else
				$this->subteam = true;
		}
		else
			$this->subteam = false;

		if ( is_numeric(strpos($this->tabel, 'subteamOffset')) && ( strpos($this->tabel, 'subteamOffset') > 0 ) )
			$where = ' AND subteam = \'' . $this->team . '\' ';
		else
			$where;
			
		$query = 'SELECT 
				AVG(dailypos) AS pos 
			FROM 
				' . $this->tabel . ' 
			WHERE 
				naam=\'' . $this->naam . '\' 
			AND 	dag > \'' . getPrevWeek($this->datum) . '\'
			' . $where . '
			GROUP BY 
				naam';

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		        $this->avgDailyPos = $line['pos'];

		$query = 'SELECT
			( o.cands + o.daily ) AS credits,
			o.daily AS flush,
			o.currRank,
			o.dailyPos
		FROM
			' . $this->prefix . '_' . $this->speedTabel . ' o
		WHERE
			o.naam = \'' . $this->naam . '\'
		AND	o.dag = \'' . $this->datum . '\'
		' . $where;

		$result = $this->db->selectQuery($query);
		
		if ( $line = $this->db->fetchArray($result) )
		{
			$this->candidates = $line['credits'];
			$this->flush = $line['flush'];
			$this->rank = $line['currRank'];
			$this->dailyRank = $line['dailyPos'];
		}

		$query = 'SELECT
				n.aantal
			FROM
				additional n
			WHERE
				n.naam = \'' . $this->naam . '\'
			AND	n.prefix = \'' . $this->prefix . '\'';
		$result = $this->db->selectQuery($query);

		if ( $line = mysql_fetch_array($result) )
			$this->nodes = $line['0'];

		$query = 'SELECT 
			daily, 
			dag 
		FROM 
			' . $this->tabel . ' 
		WHERE 
			naam = \'' . $this->naam . '\'
		AND	dag = \'' . $this->datum . '\'
		' . $where . '
		ORDER BY 
			daily DESC 
		LIMIT 	1';
		
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		{
			$this->lFlushSize = $line['daily'];
			$this->lFlushDate = $line['dag'];
		}

		$query = 'SELECT
				o.naam,
				o.cands
			FROM
				' . $this->prefix . '_' . $this->speedTabel . ' o
			WHERE
				currRank = ' . ( $this->rank - 1 ) . '
			AND	dag = \'' . $this->datum . '\'' .
				$where;

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->naamNext = $line['naam'];
			$this->distanceNext = ( $line['cands'] - $this->candidates );
		}
		else
		{
			$this->naamNext = '';
			$this->distanceNext = '-';
		}

		$query = 'SELECT
				o.naam,
				o.cands
			FROM
				' . $this->prefix . '_' . $this->speedTabel . ' o
			WHERE
				currRank = ' . ( $this->rank + 1 ) . '
			AND	dag = \'' . $this->datum . '\'' . 
				$where;

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->distancePrev = ( $this->candidates - $line['cands'] );
			$this->naamPrev = $line['naam'];
		}
		else
		{
			$this->distancePrev = '-';
			$this->naamPrev = '';
		}
	}

	function getAvgDailyPos()
	{
		return $this->avgDailyPos;
	}

	function getCandidates()
	{
		echo 'DEPRECATED USE OF getCandidates IN MemberInfo';
		return $this->candidates;
	}

	function getCredits()
	{
		return $this->candidates;
	}

	function getFlush()
	{
		return $this->flush;
	}

	function getNodes()
	{
		return $this->nodes;
	}

	function getANOOverall()
	{
		return ( $this->candidates / $this->nodes );
	}

	function getANOToday()
	{
		return ( $this->flush / $this->nodes );
	}

	function getIncrease()
	{
		if ( ( $this->flush == 0 ) || ( $this->candidates == 0 ) || ( $this->candidates - $this->flush == 0 ) )
			return 0;
		else
			return ( $this->flush / ( ( $this->candidates - $this->flush ) / 100 ) );
	}

	function getRank()
	{
		return $this->rank;
	}

	function getDailyRank()
	{
		return $this->dailyRank;
	}

	function getLargestFlush()
	{
		return $this->lFlushSize;
	}

	function getLargestFlushDate()
	{
		return $this->lFlushDate;
	}

	function getRealNaam()
	{
		return $this->naam;
	}

	function getNaam()
	{
		return $this->naam;
	}

	function getDistanceNext()
	{
		return $this->distanceNext;
	}

	function getNaamNext()
	{
		return $this->naamNext;
	}

	function getDistancePrev()
	{
		return $this->distancePrev;
	}

	function getNaamPrev()
	{
		return $this->naamPrev;
	}

	function isSubteam()
	{
		return $this->subteam;
	}
}
?>
