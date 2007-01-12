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
		if ( strpos($this->tabel, 'subteamoffset') !== FALSE )
		{
			$where = ' 	AND of.subteam = \'' . $this->subteam . '\' ';
			$join  = '	AND y.subteam = \'' . $this->subteam . '\' ';
		}

		$query = '
  		SELECT
	       		of.naam,
			of.daily,
	       		of.id,
			of.currrank,
			(of.cands + of.daily) AS total,
	       		of.dailypos,
			y.dailypos AS yddailypos
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
	      		of.dailypos ' .
		( $this->db->getType() == 'mysql'?'LIMIT ' . $this->listOffset . ', ' . $this->listsize
						:'OFFSET ' . $this->listOffset . ' LIMIT ' . $this->listsize);

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$this->members[] = new DetailedMember($db,
							      $this->tabel,
							      $this->datum,
							      $line['naam'],
							      $line['total'],
							      $line['id'],
							      $line['daily'],
							      $line['dailypos'],
							      $line['yddailypos'],
							      $line['currrank']);
		}

		$this->setTeams();
	}

	function generateMonthlyFlushList($startDate, $endDate)
	{
		$query = 'SELECT
			of.naam,
			( of.cands + of.daily ) AS total,
			of.dailypos AS id,
			oy.id AS dailypos,
			of.id,
			MIN(oy.dag),
			CASE WHEN oy.cands IS NULL THEN 0 else oy.cands END AS oudtotal,
			CASE WHEN ((of.cands+of.daily)-oy.cands) IS NULL THEN (of.cands+of.daily) ELSE ((of.cands+of.daily)-oy.cands) END AS flushed
		FROM
			' . $this->tabel . ' of
		LEFT JOIN
			' . $this->tabel . ' oy
		ON
			oy.naam = of.naam
		AND	oy.dag LIKE \'' . $startDate . '%\'
		WHERE
			of.dag = \'' . $endDate . '\' ' .
		( $this->subteam!=''?'AND of.subteam = \'' . $this->subteam . '\' AND oy.subteam = \'' . $this->subteam . '\'':'') . '
		GROUP BY
			of.naam 
		ORDER BY
			flushed DESC ' .
		( $this->db->getType() == 'mysql'?'LIMIT ' . $this->listOffset . ', ' . $this->listsize
			:'OFFSET ' . $this->listOffset . ' LIMIT ' . $this->listsize);

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			if ( ( $line['flushed'] != 0 ) || ( ( $line['flushed'] == 'NULL' ) && ( $line['dailypos'] == 'NULL') ) )
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
			}
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

		while ( $line = $this->db->fetchArray($result) )
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

		if ( ! is_numeric(strpos($this->tabel, 'subteamoffset')) )
		{
			$query = 'SELECT
					DISTINCT(subteam)
				FROM
					' . $prefix . '_subteamoffset
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
		if ( strpos($this->tabel,'subteamoffset') !== FALSE )
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
			of.currrank,
			y.dailypos AS yddailypos
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
	       		total DESC ' .
		( $this->db->getType() == 'mysql'?'LIMIT ' . $this->listOffset . ',' . $this->listsize
						:'OFFSET ' . $this->listOffset . ' LIMIT ' . $this->listsize);

#echo $query;
		$result = $this->db->selectQuery($query);

		$this->members = array();
		while ( $line = $this->db->fetchArray($result, MYSQL_ASSOC) )
		{
			$this->members[] = new DetailedMember($db,
							      $this->tabel,
							      $this->datum,
							      $line['naam'],
							      $line['total'],
							      $line['id'],
							      $line['daily'],
							      $line['dailypos'],
							      $line['yddailypos'],
							      $line['currrank']);

		}

		$this->setTeams();
	}
	

	function generateMonthlyRankList($startDate, $endDate)
	{
		$query = 'SELECT
			of.naam,
			( of.cands + of.daily ) AS total,
			of.id AS endid,
			MIN(oy.dag),
			oy.id AS startid,
			of.dailypos AS dailypos,
			oy.cands AS oudtotal,
			CASE WHEN ((of.cands+of.daily)-oy.cands) IS NULL THEN (of.cands+of.daily) ELSE ( ( of.cands + of.daily ) - oy.cands ) END AS flushed
		FROM
			' . $this->tabel . ' of
		LEFT JOIN
			' . $this->tabel . ' oy
		ON
			oy.naam = of.naam
		AND 	oy.dag LIKE \'' . $startDate . '-%\'
		WHERE
			of.dag = \'' . $endDate . '\' ' .
		( $this->subteam!=''?'AND of.subteam=\'' . $this->subteam . '\'':'') . '
		GROUP BY
			of.naam 
		ORDER BY
			total DESC ' .
			( $this->db->getType() == 'postgres' ? 'OFFSET	' . $this->listOffset . ' LIMIT	' . $this->listsize :
							'LIMIT ' . $this->listOffset . ',' . $this->listsize);

		$result = $this->db->selectQuery($query);

		$this->members = array();
		while ( $line = $this->db->fetchArray($result, MYSQL_ASSOC) )
		{
			$tmpMemberID = count($this->members);
			$this->members[$tmpMemberID] = new DetailedMember($this->db,
								$this->tabel,
								$this->datum,
								$line['naam'],
								$line['total'],
								$line['startid'],
								$line['flushed'],
								$line['dailypos'], 
								$line['startid']);
		}

	}
	
	function generateYearlyRankList($startDate, $endDate)
	{
		$query = 'SELECT
			of.naam,
			( of.cands + of.daily ) AS total,
			oy.naam,
			MIN(oy.dag),
			of.id AS endid,
			oy.id AS startid,
			of.dailypos AS dailypos,
			oy.cands AS oudtotal,
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
		while ( $line = $this->db->fetchArray($result, MYSQL_ASSOC) )
		{
			$tmpMemberID = count($this->members);
			$this->members[$tmpMemberID] = new DetailedMember($this->db,
								$this->tabel,
								$this->datum,
								$line['naam'],
								$line['total'],
								$line['startid'],
								$line['flushed'],
								$line['dailypos'], 
								$line['startid']);
		}

	}





	function getMembers()
	{
		return $this->members;
	}
}

class MemberInfo
{
	private $db;
	private $tabel;
	private $naam;
	private $description;
	private $datum;
	private $prefix;

	private $avgDailyPos;
	private $candidates;
	private $flush;
	private $nodes;
	private $rank;
	private $dailyRank;
	private $lFlushSize;
	private $lFlushDate;
	private $distanceNext;
	private $naamNext;
	private $distancePrev;
	private $naamPrev;
	private $prevDayFlushCount;
	private $subteam;

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
				COUNT(*)AS membercount
			FROM
				' . $this->prefix . '_subteamoffset
			WHERE
				subteam = \'' . $this->naam . '\'';

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			if ( $line['membercount'] == 0 )
				$this->subteam = false;
			else
				$this->subteam = true;
		}
		else
			$this->subteam = false;

		if ( is_numeric(strpos($this->tabel, 'subteamoffset')) && ( strpos($this->tabel, 'subteamoffset') > 0 ) )
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
			o.currrank,
			o.dailypos
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
			$this->rank = $line['currrank'];
			$this->dailyRank = $line['dailypos'];
		}

		$query = 'SELECT
				n.aantal
			FROM
				additional n
			WHERE
				n.naam = \'' . $this->naam . '\'
			AND	n.prefix = \'' . $this->tabel . '\'';
		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
			$this->nodes = $line['0'];

		$query = 'SELECT 
			daily, 
			dag 
		FROM 
			' . $this->tabel . ' 
		WHERE 
			naam = \'' . $this->naam . '\'
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
				( o.cands + o.daily )as total
			FROM
				' . $this->prefix . '_' . $this->speedTabel . ' o
			WHERE
				currrank = ' . ( $this->rank - 1 ) . '
			AND	dag = \'' . $this->datum . '\'' .
				$where;

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->naamNext = $line['naam'];
			$this->distanceNext = ( $line['total'] - $this->candidates );
		}
		else
		{
			$this->naamNext = '';
			$this->distanceNext = '-';
		}

		$query = 'SELECT
				o.naam,
				( o.cands + o.daily ) as total
			FROM
				' . $this->prefix . '_' . $this->speedTabel . ' o
			WHERE
				currrank = ' . ( $this->rank + 1 ) . '
			AND	dag = \'' . $this->datum . '\'' . 
				$where;

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->distancePrev = ( $this->candidates - $line['total'] );
			$this->naamPrev = $line['naam'];
		}
		else
		{
			$this->distancePrev = '-';
			$this->naamPrev = '';
		}
	}

	function getFlushHistory($timeperiod)
	{
		$query = 'SELECT
				naam,
				daily,
				dailypos,
				dag
			FROM
				' . $this->tabel . '
			WHERE
				naam = \'' . $this->naam . '\'' .
			( $this->speedTabel=='subteamoffset'?'AND subteam = \'' . $this->team . '\'':'') . '
			ORDER BY
				dag DESC ' .
			( $timeperiod==0?'':'LIMIT ' . $timeperiod);

		$result = $this->db->selectQuery($query);

		$flush = array();
		while ( $line = $this->db->fetchArray($result) )
		{
			$flush[] = array(	'name' => $line['naam'],
						'flush' => $line['daily'],
						'flushrank' => $line['dailypos'],
						'date' => $line['dag']);
		}

		return $flush;
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
