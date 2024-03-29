<?php

class admin
{
	private $userid;
	private $username;
	private $password;
	private $email;

	private $db;

	function __construct($username, $password, $email, $db)
	{
		$this->username = $username;
		$this->password = $password;
		$this->email = $email;

		$this->userid = -1;

		$this->db = $db;
	}

	function userExists()
	{
		$query = 'SELECT
				username
			FROM
				a_users
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
				a_users
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
				userid,
				password
			FROM
				a_users
			WHERE
				username = \'' . $this->username . '\'
			AND	password = \'' . md5($this->password) . '\'';

		$result = $this->db->selectQuery($query);

		if ( $this->db->getNumAffectedRows() != 1 )
			return false;

		if ( $line = $this->db->fetchArray($result) )
		{
			$this->email = $line['email'];
			$this->userid = $line['userid'];
		}

		return true;
	}

	function getUserID()
	{
		return $this->userid;
	}
}
