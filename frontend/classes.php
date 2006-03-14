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

	function MemberInfo($naam, $tabel, $datum, $prefix, $speedTabel)
	{
		$this->db = new DataBase();
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

		$this->gatherInformation();
	}

	function gatherInformation()
	{
		$query = 'SELECT 
				AVG(dailypos) AS pos 
			FROM 
				' . $this->tabel . ' 
			WHERE 
				naam=\'' . $this->naam . '\' 
			AND 	dag > \'' . getPrevWeek($this->datum) . '\' 
			GROUP BY 
				naam';

		$result = $this->db->selectQuery($query);

		if ( $line = mysql_fetch_row($result) )
		        $this->avgDailyPos = $line['0'];

		$query = 'SELECT
			( o.cands + o.daily ) AS candidates,
			o.daily AS flush
		FROM
			' . $this->prefix . '_' . $this->speedTabel . ' o
		WHERE
			o.naam = \'' . $this->naam . '\'
		AND	o.dag = \'' . $this->datum . '\'';

		$result = $this->db->selectQuery($query);
		
		if ( $line = mysql_fetch_row($result) )
		{
			$this->candidates = $line['0'];
			$this->flush = $line['1'];
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
			COUNT(naam)AS aantal 
		FROM 
			' . $this->prefix . '_' . $this->speedTabel . ' 
		WHERE 
			( ( cands + daily ) > ' . ( $this->candidates ) . ' )
		AND	dag = \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
			$this->rank = ( $line['0'] + 1 );

		$query = 'SELECT
			COUNT(naam)AS aantal
		FROM
			' . $this->prefix . '_' . $this->speedTabel . '
		WHERE	
			( ( daily ) > ' . ( $this->getFlush() ) . ' )
		AND	dag = \'' . date("Y-m-d") . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
			$this->dailyRank = ( $line['0'] + 1 );

		$query = 'SELECT 
			daily, 
			dag 
		FROM 
			' . $this->tabel . ' 
		WHERE 
			naam = \'' . $this->naam . '\'
		ORDER BY 
			daily DESC 
		limit 	1';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
		{
			$this->lFlushSize = $line['0'];
			$this->lFlushDate = $line['1'];
		}

		$query = 'SELECT 
			MIN( cands + daily )AS difference
		FROM 
			' . $this->prefix . '_' . $this->speedTabel . ' 
		WHERE 
			dag = \'' . $this->datum . '\' 
		AND 	( cands + daily ) > ' . $this->candidates . ' 
		GROUP BY 
			dag';
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
		{
			$this->distanceNext = ( $line['0'] - $this->candidates );
			$query = 'SELECT
					naam
	                        FROM
	                                ' . $this->prefix . '_' . $this->speedTabel . '
	                        WHERE
        	                        dag = \'' . $this->datum . '\'
		                AND     cands+daily = ' . ( $this->candidates + $this->distanceNext );
			        $result = $this->db->selectQuery($query);
			        if ( $line = mysql_fetch_row($result) )
				        $this->naamNext = $line['0'];
				else
				        $this->naamNext = '';
		}
		else
		{
			$this->distanceNext = "-";
			$this->naamNext = '';
		}

		$query = 'SELECT 
			MAX( cands + daily )AS difference, 
			naam 
		FROM 
			' . $this->prefix . '_' . $this->speedTabel . ' 
		WHERE 
			dag = \'' . $this->datum . '\' 
		AND 	( cands + daily ) < ' . $this->candidates . ' 
		GROUP BY 
			( cands + daily ) 
		ORDER BY 
			difference DESC 
		LIMIT 	1';
		$result = $this->db->selectQuery($query);
                if ( $line = mysql_fetch_row($result) )
                        $this->distancePrev = ( $this->candidates - $line['0']);
                else
                        $this->distancePrev = "-";

		$query = 'SELECT
				naam
			FROM
				' . $this->prefix . '_' . $this->speedTabel . '
			WHERE
				dag = \'' . $this->datum . '\'
			AND	cands+daily = ' . ( $this->candidates - $this->distancePrev );
		$result = $this->db->selectQuery($query);
		if ( $line = mysql_fetch_row($result) )
			$this->naamPrev = $line['0'];
		else
			$this->naamPrev = '';

		$query = 'SELECT
				COUNT(*)
			FROM
				' . $this->prefix . '_subteamOffset
			WHERE
				subteam = \'' . $this->naam . '\'';

		$result = $this->db->selectQuery($query);

		if ( $line = mysql_fetch_array($result) )
		{
			if ( $line['0'] == 0 )
				$this->subteam = false;
			else
				$this->subteam = true;
		}
		else
			$this->subteam = false;
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

class NodeInfo
{
        var $naam;
        var $flushed;
	var $nodeid;

	function NodeInfo($naam, $nodeid, $flushed)
	{
		$this->os = $os;
		$this->mem = $mem;
		$this->proc = $proc;
		$this->naam = $naam;
		$this->nodeid = $nodeid;
		$this->flushed = $flushed;
	}

	function getNaam()
	{
		return $this->naam;
	}

	function getNodeid()
	{
		return $this->nodeid;
	}
	
	function getFlushed()
	{
		return $this->flushed;
	}
}

class NodeDescription
{
	var $owner;

	var $nodeInformation;

	var $db;

	function NodeDescription($owner)
	{
		$this->owner = $owner;

		$this->db = new DataBase();

		$this->nodeInformation = array();

		$this->gather();
	}

	function gather()
	{
		$query = 'SELECT 
				ow.description, 
				( o.daily + o.cands )AS total,
				ow.nodeid
			FROM 
				nodeOwners ow, 
				tsc_nodeOffset o 
			WHERE 
				ow.owner = \'' . $this->owner . '\' 
			AND 	o.dag = \'' . date("Y-m-d") . '\' 
			AND 	o.naam = ow.description
			ORDER BY
				total DESC';
	#			echo $query;

		$result = $this->db->selectQuery($query);

		while ( $line = mysql_fetch_array($result, MYSQL_ASSOC) )
		{
			$this->nodeInformation[count($this->nodeInformation)] = new NodeInfo($line['description'], 
											     $line['nodeid'],
											     $line['total']);
		}
	}

	function getNodeInformation()
	{
		return $this->nodeInformation;
	}
}

class AverageList
{
	var $members;
	var $tabel;
	var $datum;
	
	var $db;

	function AverageList($tabel, $datum)
	{
		$this->db = new DataBase();
		
		$this->tabel = $tabel;
		$this->datum = $datum;
	}

	function gather()
	{
		$quer2y = 'SELECT 
				naam, 
				avgDaily 
			FROM 
				averageProduction 
			WHERE 
				naam IN 
				( 
					SELECT 
						DISTINCT(naam) 
					FROM 
						' . $this->tabel . ' 
					WHERE
						dag = \'' . date("Y-m-d") . '\'
				) 
			AND	avgDaily > 0
			AND	tabel = \'' . $this->tabel . '\'
			ORDER BY 
				avgDaily DESC';

		$query = 'SELECT 
				a.naam, 
				a.avgDaily, 
				a.avgMonthly 
			FROM 
				averageProduction a, 
				' . $this->tabel . ' m 
			WHERE 
				a.tabel = \'' . $this->tabel . '\' 
			AND 	a.naam = m.naam 
			AND 	m.dag = \'' . $this->datum . '\'
			AND 	avgDaily > 0
			ORDER BY 
				avgDaily DESC';
				echo $query;
		$result = $this->db->selectQuery($query);

		while ( $line = mysql_fetch_array($result) )
		{
			$this->members[count($this->members)] = new Member($line['naam'], $line['avgDaily']);
		}
	}

	function getMemberList()
	{
		return $this->members;
	}
}
