<?

class Project
{
	private $wuName;
	private $description;
	private $prefix;
	private $website;
	private $forum;
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
	private $additional;
	private $projectinfo = array();

	private $db;
	private $datum;

	function Project($db, $prefix, $tabel, $datum = 0, $team = '')
	{
		$this->db  = $db ;

		$this->prefix = $prefix;

		$this->projectinfo['tabel'] = $tabel;

		if ( $datum == 0 )
			$this->datum = date("Y-m-d");
		else
			$this->datum = $datum;
		
		$this->teamRank = -1;
		$this->teamDaily = -1;

		$this->teamName = $team;

		$this->additional = '';

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
				p.additional,
				p.statsruninterval,
				p.wdoprefix,
				p.seperator
			FROM 
				project p,
				updates u
			WHERE 
				p.project = u.project
			AND	u.tabel = \'' . $this->projectinfo['tabel'] . '\'
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
			$this->additional = $line['additional'];
			
			if ( $line['wdoprefix'] == '' )
				$this->wdoPrefix = $this->prefix;
			else
				$this->wdoPrefix = $line['wdoprefix'];

			$this->projectinfo['seperator'] = $line['seperator'];
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

		$data = file('/var/www/tstats/classes/version.txt');
		$this->version = $data[0];
		$this->lastPageUpdate = $data[1];
	}

	function getSeperator()
	{
		if ( $this->projectinfo['seperator'] != '' )
			return $this->projectinfo['seperator'];
		else
			return ' - ';
	}

	function getAdditional()
	{
		return $this->additional;
	}

	function getTabel()
	{
		return $this->projectinfo['tabel'];
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
					' . $this->prefix . '_memberoffset
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
					' . $this->prefix . '_memberoffset
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

	function getTeamRank($subteam = 0)
	{
		if ( $this->teamRank == -1 )
		{
			$query = 'SELECT 
					currrank
				FROM 
					' . $this->prefix . ($subteam==1?'_memberoffset':'_teamoffset') . '
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

	function getTeamDaily($subteam = 0)
	{
		if ( $this->teamDaily == -1 )
		{
			$query = 'SELECT
					dailypos
				FROM
					' . $this->prefix . ($subteam==1?'_memberoffset':'_teamoffset') . '
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
