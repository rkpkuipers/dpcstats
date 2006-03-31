<?

class STTableStatistics extends TableStatistics
{
	private $subteam;
	
	function STTableStatistics($tabel, $datum, $db, $subteam)
	{
		$this->TableStatistics($tabel, $datum, $db);
		
		$this->subteam = $subteam;
	}
	
	function gather()
	{
		$query = 'SELECT 
				COUNT(of.naam) AS aantal 
			FROM 
				' . $this->tabel . ' of 
			WHERE 
				of.dag = \'' . $this->datum . '\' 
			AND 	of.daily > 0
			AND	of.subteam = \'' . $this->subteam . '\'';

		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyFlushers = $line['0'];
		else
			$this->dailyFlushers = 0;

		$query = 'SELECT 
				COUNT(*) AS aantal 
			FROM 
				' . $this->tabel . ' of
			WHERE 	of.dag = \'' . $this->datum . '\'
			AND	of.subteam = \'' . $this->subteam . '\'';

		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->totalMembers = $line['0'];
		else
			$this->totalMembers = 0;

		$query = 'SELECT 
				SUM(of.daily) AS total, 
				of.dag 
			FROM 
				' . $this->tabel . ' of
			WHERE 	of.dag = \'' . $this->datum . '\' 
			AND	of.subteam = \'' . $this->subteam . '\'
			AND 	of.daily > 0 
			GROUP BY 
				of.dag';
				
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyOutput = $line['0'];
		else
		        $this->dailyOutput = 0;

		$query = 'SELECT 
				( SUM(of.cands) + SUM(of.daily) ) AS totaal 
			FROM 
				' . $this->tabel . ' of
			WHERE 	of.dag = \'' . $this->datum . '\'
			AND	of.subteam = \'' . $this->subteam . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result))
		        $this->totalOutput = $line['0'];
		else
			$this->totalOutput = 0;

                $query = 'SELECT
                                COUNT(naam)
                        FROM
                                ' . $this->tabel . ' of
                        WHERE
                                dag = \'' . getPrevDate($this->datum) . '\'
                        AND     daily > 0
                        AND	of.subteam = \'' . $this->subteam . '\'';
                $result = $this->db->selectQuery($query);
                if ( $line = $this->db->fetchArray($result) )
                        $this->prevDayFlushCount = $line['0'];
                else
                        $this->prevDayFlushCount = 0;
	}
}

class TableStatistics
{
	protected $tabel;
	protected $datum;

	protected $db;

	protected $dailyFlushers;
	protected $totalMembers;
	protected $dailyOutput;
	protected $totalOutput;
	protected $prevDayFlushCount;
	
	function TableStatistics($tabel, $datum, $db)
	{
		$this->tabel = $tabel;
		$this->datum = $datum;
		
		$this->db = $db;
	}

	function gather()
	{
		$query = 'SELECT 
				count(of.naam) AS aantal 
			FROM 
				' . $this->tabel . ' of 
			WHERE 
				of.dag = \'' . $this->datum . '\' 
			AND NOT	of.daily = 0';

		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyFlushers = $line['0'];
		else
			$this->dailyFlushers = 0;

		$query = 'SELECT 
				count(*) AS aantal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag = \'' . $this->datum . '\'';

		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->totalMembers = $line['0'];
		else
			$this->totalMembers = 0;

		$query = 'SELECT 
				SUM(daily) AS total, 
				dag 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag = \'' . $this->datum . '\' 
			AND 	daily > 0 
			GROUP BY 
				dag';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyOutput = $line['0'];
		else
		        $this->dailyOutput = 0;

		$query = 'SELECT 
				( SUM(cands) + SUM(daily) ) AS totaal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag = \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result))
		        $this->totalOutput = $line['0'];
		else
			$this->totalOutput = 0;

                $query = 'SELECT
                                COUNT(naam)
                        FROM
                                ' . $this->tabel . '
                        WHERE
                                dag = \'' . getPrevDate($this->datum) . '\'
                        AND     daily > 0';
                $result = $this->db->selectQuery($query);
                if ( $line = $this->db->fetchArray($result) )
                        $this->prevDayFlushCount = $line['0'];
                else
                        $this->prevDayFlushCount = 0;
        }

        function getPrevDayFlushCount()
        {
                return $this->prevDayFlushCount;
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

class TableStatisticsYearly
{
	var $tabel;
	var $datum;

	var $db;

	var $dailyFlushers;
	var $totalMembers;
	var $dailyOutput;
	var $totalOutput;
	
	function TableStatisticsYearly($tabel, $datum)
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
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyFlushers = $line['0'];
		else
			$this->dailyFlushers = 0;

		$query = 'SELECT 
				count(distinct(naam)) AS aantal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag >= \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
		        $this->totalMembers = $line['0'];
		else
			$this->totalMembers = 0;

		# hiero
		$query = 'SELECT 
				SUM(daily) AS total 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag >= \'' . date("Y-m-01", strtotime($this->datum)) . '\' 
			AND 	daily>0';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
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
		if ( $line = $this->db->fetchArray($result))
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
		if ( $line = $this->db->fetchArray($result) )
		        $this->dailyFlushers = $line['0'];
		else
			$this->dailyFlushers = 0;

		$query = 'SELECT 
				count(distinct(naam)) AS aantal 
			FROM 
				' . $this->tabel . ' 
			WHERE 	dag >= \'' . $this->datum . '\'';
		$result = $this->db->selectQuery($query);
		if ( $line = $this->db->fetchArray($result) )
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
		if ( $line = $this->db->fetchArray($result) )
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
		if ( $line = $this->db->fetchArray($result))
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

?>
