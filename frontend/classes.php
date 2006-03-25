<?
require ('classes/util.php');

require ('classes/database.php');

require ('classes/mijlpalen.php');

require ('classes/members.php');

require ('classes/tableStatistics.php');

require ('classes/inhaalStats.php');

require ('classes/NewRetMembers.php');

require ('classes/shoutbox.php');

require ('classes/project.php');

require ('classes/flush.php');

require ('classes/changelog.php');

require ('classes/subteam.php');

require ('classes/AverageProduction.php');

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

class TableStatisticsMonthly
{
	var $tabel;
	var $datum;

	var $db;

	var $dailyFlushers;
	var $totalMembers;
	var $dailyOutput;
	var $totalOutput;
	
	function TableStatisticsMonthly($tabel, $datum)
	{
		$this->tabel = $tabel;
		$this->datum = $datum;
		
		$this->db = new DataBase();
		$this->db->connect();
	}

	function gather()
	{
		$query = 'SELECT 
				count(distinct(of.naam)) AS aantal
			FROM 
				' . $this->tabel . ' of 
			WHERE 
				of.dag >= \'' . $this->datum . '\' 
			AND	daily > 0';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
		        $this->dailyFlushers = $line['0'];
		else
			$this->dailyFlushers = 0;

		$query = 'SELECT 
				count(distinct(naam)) AS aantal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag >= \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
		        $this->totalMembers = $line['0'];
		else
			$this->totalMembers = 0;

		# hiero
		$query = 'SELECT 
				SUM(daily) AS total 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag >= \'' . date("Y-m-01", strtotime($this->datum)) . '\'';

		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
		        $this->dailyOutput = $line['0'];
		else
		        $this->dailyOutput = 0;

		$query = 'SELECT 
				max(dag),
				( SUM(cands) + SUM(daily) ) AS totaal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag = \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result))
		        $this->totalOutput = $line['0'];
		else
			$this->totalOutput = 0;
	}

	function getDailyFlushers()
	{
		return $this->dailyFlushers;
	}

	function getTotalMembers()
	{
		return $this->totalMembers;
	}

	function getDailyOutput()
	{
		return $this->dailyOutput;
	}

	function getTotalOutput()
	{
		return $this->totalOutput;
	}
}
