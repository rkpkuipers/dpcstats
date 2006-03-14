<?
class SubTeamMember
{
	private $naam;
}

class SubTeam
{
	private $db;
	
	private $naam;
	private $leader;
	private $email;
	private $password;
	private $nodes;
	private $members;

	function SubTeam($db, $naam, $password)
	{
		$this->db = $db;
		$this->naam = $naam;
		$this->leader = '';
		$this->email = '';
		$this->password = $password;
		$this->nodes = array();
		$this->members = array();
	}

	function getMembers()
	{
		$query = 'SELECT
				member
			FROM
				tsc_subteam
			WHERE
				name = \'' . $this->naam . '\'
			ORDER BY
				member';

		$result = $this->db->selectQuery($query);

		while ( $line = mysql_fetch_array($result) )
			$this->members[] = $line['member'];
	}

	function getMemberList()
	{
		return $this->members;
	}

	function setLeader($leader)
	{
		$this->leader = $leader;
	}

	function setEmail($email)
	{
		$this->email = $email;
	}

	function load()
	{
		$query = 'SELECT
				leader,
				email,
				description
			FROM
				tsc_subteamInfo
			WHERE
				name = \'' . $this->naam . '\'';

		$result = $this->db->selectQuery($query);

		if ( $line = mysql_fetch_array($result) )
		{
			$this->leader = $line[0];
			$this->email = $line[1];
			#$this->description = $line[2];
		}
	}

	function save()
	{
		$query = 'SELECT
				name
			FROM
				tsc_subteamInfo
			WHERE
				name = \'' . $this->naam . '\'';

		$result = $this->db->selectQuery($query);

		if ( $line = mysql_fetch_array($result) )
			$this->updateTeam();
		else
			$this->insertTeam();
	}

	function updateTeam()
	{
		$query = 'UPDATE
				tsc_subteamInfo
			SET
				leader = \'' . $this->leader . '\',
				email = \'' . $this->email . '\',
				password = \'' . $this->password . '\'
			WHERE
				name = \'' . $this->naam . '\'';

		$this->db->updateQuery($query);		
	}

	function insertTeam()
	{
		$query = 'INSERT INTO
				tsc_subteamInfo
			(
				name,
				password
			)
			VALUES
			(
				\'' . $this->naam . '\',
				\'' . $this->password . '\'
			)';
		
		$this->db->selectQuery($query);
	}

	function getNaam()
	{
		return $this->naam;
	}

	function getLeader()
	{
		return $this->leader;
	}

	function getEmail()
	{
		return $this->email;
	}

	function getPassword()
	{
		return $this->password;
	}

	function getNodes()
	{
		return $this->nodes;
	}
}
?>
