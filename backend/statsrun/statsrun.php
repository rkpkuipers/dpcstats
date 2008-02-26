#!/usr/bin/php
<?

include (dirname(realpath($argv[0])) . '/../include.php');

class StatsRun
{
	private $tempdir;
	private $date;
	private $prefix;
	private $db;

	public function __construct($db, $tempdir)
	{
		$this->db = $db;
		$this->tempdir = $tempdir;
	}

	public function boinc($prefix, $statslocation, $seperator, $teamid, $statsfilesuffix)
	{
		# Set the date for the stats
		global $datum;
		$datum = getCurrentDate($prefix);

		# Verify the tables are setup to recieve the data
		# boinc projects have all these tables

		dailyOffset('teamoffset', $prefix);
		dailyOffset('memberoffset', $prefix);
		dailyOffset('subteamoffset', $prefix);
		dailyOffset('individualoffset', $prefix);

		# Team stats

		# Obtain the datafile from the project, unzip the file and load it into a simplexml object
		system('wget -q '. $statslocation . '/team' . $statsfilesuffix . '.gz -O ' . $this->tempdir . '/' . $prefix . '.team.gz');

		# Check if the file was obtained correctly
		if ( filesize($this->tempdir . '/' . $prefix . '.team.gz') == 0 )
		{
			# Stop gathering stats if the file is empty
			# Contains possible problem when team stats are down but user stats are not
			echo 'Downloading team data returned empty file';
	#		return 0;
		}

		system('gunzip ' . $this->tempdir . '/' . $prefix . '.team.gz');

		$xmldata = simplexml_load_file($this->tempdir . '/' . $prefix . '.team');

		# Build an array with the teamname as key and the score as value
		$team = array();
		foreach($xmldata->team as $xmlteam)
		{
			addTeam($team, strval($xmlteam->name), intval($xmlteam->total_credit));
		}
		unlink($this->tempdir . '/' . $prefix . '.team');

		arsort($team, SORT_NUMERIC);

		updateStats($team, $prefix . '_teamoffset');

		# Member and subteam stats

		# Obtain the datafile from the project, unzip the file and load it into a simplexml object
		system('wget -q ' . $statslocation . '/user' . $statsfilesuffix . '.gz -O ' . $this->tempdir . '/' . $prefix . '.user.gz');

		system('gunzip ' . $this->tempdir . '/' . $prefix . '.user.gz');

		$xmldata = simplexml_load_file($this->tempdir . '/' . $prefix . '.user');

		unlink($this->tempdir . '/' . $prefix . '.user');

		$member = array();
		$subteam = array();

		foreach($xmldata->user as $xmluser)
		{
			if ( (int)$xmluser->teamid == $teamid )
				addMember($member, $subteam, (string)$xmluser->name, (int)$xmluser->total_credit, $seperator);

		}
		
		fixLists($member, $subteam, $seperator);

		updateStats($member, $prefix . '_memberoffset');

		if ( count($subteam) > 0 )
		{
			$subteammembers = array();
			foreach ($subteam as $subteamname => $subteammember)
			{
			        arsort($subteammember, SORT_NUMERIC);
			        foreach ($subteammember as $membername => $memberscore)
			        {
			                $subteammembers[] = new TeamMember($membername, $memberscore, $subteamname);
				}
			}

			addSubTeamStatsRun($subteammembers, $prefix . '_subteamoffset');
			individualStatsrun($prefix);
		}

	}

	function D2OL()
	{
		global $datum;
	
		$datum = getCurrentDate('d2ol');

		$unassignedscore = 0;
	
		if ( $html = @implode('', file ('http://app.d2ol.com/stats/topMembersAll.jsp?t=Alltime')) )
		{
			dailyOffset('memberoffset', 'd2ol');
	
			$teams = split("[\n|]", $html);
			
			$d2olmembers = array();
			for($i=10;$i<count($teams);$i+=6)
			{
			        if ( ( $teams[$i+4] == 'Dutch Power Cows' ) && ( $teams[$i+1] > 0 ) )
			                $d2olmembers[$teams[$i]] = $teams[$i+1];

				if ( ( $teams[$i+4] == 'Unassigned' ) && ( $teams[$i+1] > 0 ) )
					$unassignedscore += $teams[$i+1];
			}
				
			updateStats($d2olmembers, 'd2ol_memberoffset');
			unset($d2olmembers, $teams);
		}
		else
		{
			echo 'Error fetching stats for D2OL members' . "\n";
		}

		unset($html);

		if ( $html = @implode('', file ('http://app.d2ol.com/stats/topTeamsAll.jsp?t=Alltime')) )
		{
			dailyOffset('teamoffset', 'd2ol');

			$teams = explode('|', $html);

			$d2olteams = array();

			for($i=6;$i<count($teams);$i+=5)
			{
		        	if ( ( $teams[$i+1] > 0 ) && (  ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 10000 ) ) ) )
			        	        $d2olteams[$teams[$i]] = $teams[$i+1];
			}

			$d2olteams['Unassigned'] = $unassignedscore;

			arsort($d2olteams);

			updateStats($d2olteams, 'd2ol_teamoffset');
		}
		else
		{
			echo 'Error fetching stats for D2OL teams' . "\n";
		}
	}

	function TSC()
	{
		global $datum;

		$datum = getCurrentDate('tsc');

		if ( $html = @implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) )
		{
			dailyOffset('memberoffset', 'tsc');
			dailyOffset('subteamoffset', 'tsc');
			$teams = split("[\n|]", $html);

			$tscmembers = array();

			for($i=10;$i<count($teams);$i+=6)
			{
			        if ( ( $teams[$i+4] == 'Dutch Power Cows' ) && ( $teams[$i+1] > 0 ) )
			        {
					$tscmembers[$teams[$i]] = $teams[$i+1];
				}
			}

			updateStats($tscmembers, 'tsc_memberoffset');
		}
		else
		{
			echo 'Error fetching stats for TSC members' . "\n";
		}

		dailyOffset('teamoffset', 'tsc');

		if ( $html = @implode('', file ('http://d2ol.childhooddiseases.org/stats/topTeamsAll.jsp?t=Alltime')) )
		{
			dailyOffset('teamoffset', 'tsc');
			$teams = explode('|', $html);

			$tscteams = array();

			for($i=6;$i<count($teams);$i+=5)
			{
			        if ( ( $teams[$i+1] > 0 ) && ( ( $teams[$i] != 'Russia' ) || ( ( $teams[$i] == 'Russia' ) && ( $teams[$i+1] > 10000 ) ) ) )
				        {
				                $tscteams[$teams[$i]] = $teams[$i+1];
				        }
			}

			$html = implode('', file ('http://d2ol.childhooddiseases.org/stats/topMembersAll.jsp?t=Alltime')) or die("Error retrieving information");
			$teams = explode('|', $html);

			$score = 0;

			for($i=10;$i<count($teams);$i+=5)
			{
			        if ( ( strstr($teams[$i], 'Unassigned') ) && ( $teams[$i-3] > 0 ) )
			        {
			                $score += $teams[$i-3];
			        }
			}

			$tscteams['Unassigned'] = $score;

			arsort($tscteams, SORT_NUMERIC);

			updateStats($tscteams, 'tsc_teamoffset');
		}
		else
		{
			echo 'Error fetching stats for TSC teams' . "\n";
		}
	}
}

$sr = new StatsRun($db, '/home/rkuipers/stats/statsrun/files/');

$run = $argv[1];

switch($run)
{
	case 20:	$sr->d2ol();
			$sr->tsc();
			break;
	case 60:	$sr->boinc('smp', 'http://boinc.bio.wzw.tum.de/boincsimap/stats/', '~', 119, '');
			$sr->boinc('eah', 'http://einstein.phys.uwm.edu/stats/', '~', 822, '_id');
			$sr->boinc('cp', 'http://climateapps2.oucs.ox.ac.uk/stats/', '~', 28, '.xml');
			$sr->boinc('ufl', 'http://www.ufluids.net/stats/', '~', 202 , '');
			$sr->boinc('rah', 'http://boinc.bakerlab.org/rosetta/stats/', '~', 78, '');
			$sr->boinc('ldc', 'http://boinc.gorlaeus.net/stats/', '~', 99, '.xml');
			break;
	case 240:	# UD, WCG, S@H
			break;
	default:	echo 'Unknown option passed to statsrun.php';
			break;
}

$db->disconnect();
?>
