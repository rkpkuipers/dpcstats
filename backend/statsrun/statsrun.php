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
		
		# Ensure the files directory exists
		if ( ! is_dir($this->tempdir) )
			mkdir($this->tempdir, 0777, true) or die("ERROR: Unable to create temporary directory " . $this->tempdir . "\n");
		
		# Team stats

		# Obtain the datafile from the project, unzip the file and load it into a simplexml object
		system('wget --timeout=500 -q '. $statslocation . '/team' . $statsfilesuffix . '.gz -O ' . $this->tempdir . '/' . $prefix . '.team.gz');

		# Check if the file was obtained correctly
		if ( filesize($this->tempdir . '/' . $prefix . '.team.gz') > 0 )
		{
			system('gunzip ' . $this->tempdir . '/' . $prefix . '.team.gz');

			$xmldata = simplexml_load_file($this->tempdir . '/' . $prefix . '.team');

			# Build an array with the teamname as key and the score as value
			$team = array();
			foreach($xmldata->team as $xmlteam)
			{
				if ( $prefix == 'wcg' )
					addTeam($team, strval($xmlteam->name), intval($xmlteam->total_credit * 7)) ;
				else
					addTeam($team, strval($xmlteam->name), intval($xmlteam->total_credit));
			}
			unlink($this->tempdir . '/' . $prefix . '.team');

			arsort($team, SORT_NUMERIC);

			updateStats($team, $prefix . '_teamoffset');
		}

		# Member and subteam stats

		# Obtain the datafile from the project, unzip the file and load it into a simplexml object
		system('wget --timeout=500 -q ' . $statslocation . '/user' . $statsfilesuffix . '.gz -O ' . $this->tempdir . '/' . $prefix . '.user.gz');

		if ( filesize($this->tempdir . '/' . $prefix . '.user.gz') == 0 )
			return;

		system('gunzip ' . $this->tempdir . '/' . $prefix . '.user.gz');

		$xmldata = simplexml_load_file($this->tempdir . '/' . $prefix . '.user');

		unlink($this->tempdir . '/' . $prefix . '.user');

		$member = array();
		$subteam = array();

		foreach($xmldata->user as $xmluser)
		{
			if ( (int)$xmluser->teamid == $teamid )
			{
				if ( $prefix == 'wcg' )
					addMember($member, $subteam, (string)$xmluser->name, (int)($xmluser->total_credit*7), $seperator);
				else
					addMember($member, $subteam, (string)$xmluser->name, (int)$xmluser->total_credit, $seperator);
			}
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
}

$sr = new StatsRun($db, '/home/rkuipers/stats/statsrun/files/');

$run = $argv[1];

switch($run)
{
	case 60:	$sr->boinc('smp', 'http://boinc.bio.wzw.tum.de/boincsimap/stats/', '~', 119, '');
			$sr->boinc('eah', 'http://einstein.phys.uwm.edu/stats/', '~', 822, '_id');
			$sr->boinc('cp', 'http://climateapps2.oucs.ox.ac.uk/stats/', '~', 28, '.xml');
			$sr->boinc('ufl', 'http://www.ufluids.net/stats/', '~', 202 , '');
			$sr->boinc('rah', 'http://boinc.bakerlab.org/rosetta/stats/', '~', 78, '');
			$sr->boinc('ldc', 'http://boinc.gorlaeus.net/stats/', '~', 99, '.xml');
			$sr->boinc('szt', 'http://szdg.lpds.sztaki.hu/szdg/stats/', '~', 168, '');
			$sr->boinc('sph', 'http://spin.fh-bielefeld.de/stats/', '~', 79, '');
			$sr->boinc('prg', 'http://www.primegrid.com/stats/', '~', 103, '');
			$sr->boinc('mc', 'http://www.malariacontrol.net/stats/', '~', 192, '');
			break;
	case 240:	# UD, WCG, S@H
			$sr->boinc('wcg', 'http://www.worldcommunitygrid.org/boinc/stats/', '.', 245, '');
			break;
	default:	echo 'Unknown option passed to statsrun.php';
			break;
}

$db->disconnect();
?>
