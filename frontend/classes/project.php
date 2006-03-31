<?

class Project
{
	private $wuName;
	private $description;
	private $prefix;
	private $website;
	private $forum;
	private $tabel;
	private $lastUpdate;
	private $lastPageUpdate;
	private $version;
	private $teamName;
	private $dpchTitle;
	private $teamRank;
	private $teamDaily;
	private $srInterval;
	private $wdoPrefix;

	private $db;
	private $datum;

	function Project($db, $prefix, $tabel, $datum = 0)
	{
		$this->db  = $db ;

		$this->prefix = $prefix;

		$this->tabel = $tabel;

		if ( $datum == 0 )
			$this->datum = date("Y-m-d");
		else
			$this->datum = $datum;
		
		if ( is_numeric(strpos($tabel, 'Daily')) )
			$this->tabel = substr($tabel, 0, strpos($tabel, 'Daily') );

		$this->teamRank = -1;
		$this->teamDaily = -1;

		$this->getInfo();
	}

	function getInfo()
	{
		$query = 'SELECT 
				p.description, 
				p.wuName, 
				p.website,
				p.forum,
				u.tijd,
				p.dpchTitle,
				p.teamName,
				p.statsrunInterval,
				p.wdoPrefix
			FROM 
				project p,
				updates u
			WHERE 
				p.project = u.project
			AND	u.tabel = \'' . $this->tabel . '\'
			AND	p.project = \'' . $this->prefix . '\'';
		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->description = $line['0'];
			$this->wuName = $line['1'];
			$this->website = $line['2'];
			$this->forum = $line['3'];
			$this->lastUpdate = $line['4'];
			$this->teamName = $line['teamName'];
			$this->dpchTitle = $line['dpchTitle'];
			$this->srInterval = $line['statsrunInterval'];
			
			if ( $line['wdoPrefix'] == '' )
				$this->wdoPrefix = $this->prefix;
			else
				$this->wdoPrefix = $line['wdoPrefix'];
		}
		else
		{
			$this->description = '';
			$this->wuName = '';
			$this->website = '';
			$this->forum = '';
			$this->lastUpdate = 'Onbekend';
			$this->teamName = 'Dutch Power Cows';
			$this->dpchTitle = '';
			$this->srInterval = 60;
		}

/*
		$query = 'SELECT
				MAX(dag)AS lastPageUpdate
			FROM
				changelog';

		$result = $this->db->selectQuery($query);

		if ( $line = $this->db->fetchArray($result) )
			$this->lastPageUpdate = $line['lastPageUpdate'];
		else
			$this->lastPageUpdate = 'Unknown';
			*/

		$data = exec('time svn info file:///home/rkuipers/svrepos/frontend/ | grep -e Revision -e "Last Changed Date" | tr "\n" " "');

		$info = preg_split("/\:|\ /", $data);
#		print_r($info2);
		$this->version = $info[2];
		$this->lastPageUpdate = $info[7];

		
	#	$this->version = '2.1.5';
	}

	function getCurrentDate()
	{
        	switch($this->prefix)
	        {
        	case 'd2ol': case 'tsc':
              		return date("Y-m-d", strtotime("-1 hours" ));
        	case 'fad': default:        
			return date("Y-m-d");
		default:
			return date("Y-m-d");
        	}
	}

	function getTeamRank()
	{
		if ( $this->teamRank == -1 )
		{
			$query = 'SELECT 
					currRank
				FROM 
					' . $this->prefix . '_teamOffsetDaily 
				WHERE 
					dag = \'' . date("Y-m-d", strtotime($this->datum)). '\' 
				AND	naam = \'' . $this->teamName . '\'
				LIMIT 	1';
			$result = $this->db->selectQuery($query);

			if ( $line = $this->db->fetchArray($result) )
				$this->teamRank = $line['currRank'];
		}

		return $this->teamRank;
	}

	function getTeamDaily()
	{
		if ( $this->teamDaily == -1 )
		{
			$query = 'SELECT
					dailypos
				FROM
					' . $this->prefix . '_teamOffsetDaily
				WHERE
					dag = \'' . date("Y-m-d", strtotime($this->datum)) . '\'
				AND	naam = \'' . $this->teamName . '\'
				LIMIT	1';
			$result = $this->db->selectQuery($query);

			if ( $line = $this->db->fetchArray($result) )
				$this->teamDaily = $line['dailypos'];
		}

		return $this->teamDaily;
	}

	function getDpchTitle()
	{
		return $this->dpchTitle;
	}

	function getTeamName()
	{
		return $this->teamName;
	}

	function getDescription()
	{
		return $this->description;
	}

	function getWuName()
	{
		return $this->wuName;
	}

	function getPrefix()
	{
		return $this->prefix;
	}

	function getWebsite()
	{
		return $this->website;
	}

	function getForum()
	{
		return $this->forum;
	}

	function getLastUpdate()
	{
		return $this->lastUpdate;
	}

	function getLastPageUpdate()
	{
		return $this->lastPageUpdate;
	}

	function getStatsrunInterval()
	{
		return $this->srInterval;
	}

	function getWDOPrefix()
	{
		return $this->wdoPrefix;
	}

	function getVersion()
	{
		return $this->version;
	}
}

?>
