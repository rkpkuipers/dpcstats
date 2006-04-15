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
	private $team;

	private $db;
	private $datum;

	function Project($db, $prefix, $tabel, $datum = 0, $team = '')
	{
		$this->db  = $db ;

		$this->prefix = $prefix;

		$this->tabel = $tabel;

		if ( $datum == 0 )
			$this->datum = date("Y-m-d");
		else
			$this->datum = $datum;
		
		if ( is_numeric(strpos($tabel, 'daily')) )
			$this->tabel = substr($tabel, 0, strpos($tabel, 'daily') );
			
		$this->teamRank = -1;
		$this->teamDaily = -1;

		$this->teamName = $team;

		$this->getInfo();
	}

	function getInfo()
	{
		$query = 'SELECT 
				p.description, 
				p.wuname, 
				p.website,
				p.forum,
				u.tijd,
				p.dpchtitle,
				p.teamname,
				p.statsruninterval,
				p.wdoprefix
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
			if ( $this->teamName == '' )
				$this->teamName = $line['teamname'];
			$this->dpchTitle = $line['dpchtitle'];
			$this->srInterval = $line['statsruninterval'];
			
			if ( $line['wdoprefix'] == '' )
				$this->wdoPrefix = $this->prefix;
			else
				$this->wdoPrefix = $line['wdoprefix'];
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

		$data = exec('time svn info file:///home/rkuipers/svnrepos/frontend/ | grep -e Revision -e "Last Changed Date" | tr "\n" " "');

		$info = preg_split("/\:|\ /", $data);
		$this->version = $info[2];
		$this->lastPageUpdate = $info[7];
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

	function getSubteamRank()
	{
		if ( $this->teamRank == -1 )
		{
			$query = 'SELECT
					currrank
				FROM
					' . $this->prefix . '_memberoffsetdaily
				WHERE
					dag = \'' . date("Y-m-d", strtotime($this->datum)) . '\'
				AND	naam = \'' . $this->teamName . '\'
				LIMIT 1';
			
			$result = $this->db->selectQuery($query);

			if ( $line = $this->db->fetchArray($result) )
				$this->teamRank = $line['currrank'];
		}

		return $this->teamRank;
	}

	function getSubteamDaily()
	{
		if ( $this->teamDaily == -1 )
		{
			$query = 'SELECT
					dailypos
				FROM
					' . $this->prefix . '_memberoffsetdaily
				WHERE
					dag = \'' . date("Y-m-d", strtotime($this->datum)) . '\'
				AND	naam = \'' . $this->teamName . '\'
				LIMIT 1';

			$result = $this->db->selectQuery($query);

			if ( $line = $this->db->fetchArray($result) )
				$this->teamDaily = $line['dailypos'];
		}

		return $this->teamDaily;
	}

	function getTeamRank()
	{
		if ( $this->teamRank == -1 )
		{
			$query = 'SELECT 
					currrank
				FROM 
					' . $this->prefix . '_teamoffsetdaily 
				WHERE 
					dag = \'' . date("Y-m-d", strtotime($this->datum)). '\' 
				AND	naam = \'' . $this->teamName . '\'
				LIMIT 	1';
			$result = $this->db->selectQuery($query);

			if ( $line = $this->db->fetchArray($result) )
				$this->teamRank = $line['currrank'];
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
					' . $this->prefix . '_teamoffsetdaily
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
