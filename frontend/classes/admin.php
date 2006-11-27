<?

class admin
{
	private $username;
	private $password;
	private $email;

	private $db;

	function __construct($username, $password, $email, $db)
	{
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;

		$this->db = $db;
	}

	function userExists()
	{
		$query = 'SELECT
				username
			FROM
				users
			WHERE
				username = \'' . $this->username . '\'';

		$result = $this->db->selectQuery($query);

		if ( $this->db->getNumAffectedRows() > 0 )
			return true;
		else
			return false;
	}

	function saveUser()
	{
		$query = 'INSERT INTO
				users
			(
				username,
				email,
				password,
				active
			)
			VALUES
			(
				\'' . $this->username . '\',
				\'' . $this->email . '\',
				\'' . md5($this->password) . '\',
				1
			)';

		$this->db->insertQuery($query);
	}

	function verifyUser()
	{
		$query = 'SELECT
				username,
				email,
				password
			FROM
				users
			WHERE
				username = \'' . $this->username . '\'
			AND	password = \'' . md5($this->password) . '\'';

		$result = $this->db->selectQuery($query);

		if ( $this->db->getNumAffectedRows() != 1 )
			return false;

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->email = $line['email'];
		}

		return true;
	}
}
