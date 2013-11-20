<?php

class miDatabase extends SDataBase
{
	public function __construct($dbuser, $dbpass, $dbhost, $dbport, $dbname)
	{
		parent::__construct($dbname, $dbhost, $dbport, $dbuser, $dbpass);
	}

	public function connect()
	{
		$this->connection = new mysqli($this->host, $this->user, $this->pass, $this->name);

		if ( mysqli_connect_errno() ) 
		{
			echo "connection failed with error: " . mysqli_connect_error();
		}

		$this->connection->query('set names utf8');
		$this->connection->query('set character set latin1');
	}

	public function disconnect()
	{
		$this->connection->close();
	}

	public function selectQuery($query)
	{
		return $this->connection->query($query);
	}

	public function updateQuery($query)
	{
		return $this->connection->query($query);
	}

	public function deleteQuery($query)
	{
		return $this->connection->query($query);
	}

	public function insertQuery($query)
	{
		return $this->connection->query($query);
	}

	public function getType()
	{
		return 'mysql';
	}

	public function fetchArray($result)
	{
		if ( is_object($result) )
			return $result->fetch_array();
		else
			return array();
	}
	
	function fetchAssocArray($result)
	{
		return mysqli_fetch_assoc($result);
	}
	
	public function real_escape_string($string)
	{
		return $this->connection->real_escape_string($string);
	}

	public function getNumAffectedRows()
	{
		return $this->connection->affected_rows;
	}
	
	# Function to retrieve a key or field from a table using a specified condition
	function getFieldByCondition($table, $keyfield, $constrains)
	{
		# Create an array of keyfield
		if ( ! is_array($keyfield) )
			$fields = array($keyfield);
		else
			$fields = $keyfield;
		
		# Build the query
		$query = 'SELECT ' . implode(',', $fields) . ' FROM ' . $table . ' WHERE ';
		
		# Array to store the where clauses
		$where = array();
		
		# Loop through the contrains and add each clause to the array
		foreach($constrains as $fieldname => $fieldvalue)
			$where[] = $fieldname . ' = \'' . $this->real_escape_string($fieldvalue) . '\'';
		
		# Add the where clauses to the query
		$query .= implode(' AND ', $where);
		
		# Limit the number of results to 1
		$query .= ' LIMIT 1';
		
		# Execute the query
		$result = $this->selectQuery($query);
		
		# Return the result if any or null otherwise
		if ( $line = $this->fetchAssocArray($result) )
		{
			# If only a single field was requested return the field, otherwise return the list
			if ( ! is_array($keyfield) )
				return $line[$keyfield];
			else
				return $line;
		}
		else
			return null;
	}
	
	# Function to retrieve a series of records
	function getRecordsByCondition($table, $fields, $constrains, $orderfield)
	{
		# Build the query
		$query = 'SELECT ';
		
		# Add the fields to the query
		$query .= implode(',', $fields);
		
		# Add the from <table> clause
		$query .= ' FROM ' . $table . ' ';
		
		# Variable to store the where clause
		$where = array();
		
		# Loop through the contrains and add each clause to the array
		foreach($constrains as $fieldname => $fieldvalue)
			$where[] = $fieldname . ' = \'' . $this->real_escape_string($fieldvalue) . '\'';
		
		if ( ! empty($where) )
		{
			# Add the where clauses to the query
			$query .= ' WHERE ' . implode(' AND ', $where);
		}
		
		# Ensure orderfield is an array
		if ( ! is_array($orderfield) )
			$orderfield = array($orderfield);
		
		# Add the order fields to the query
		$query .= ' ORDER BY ' . implode(',', $orderfield);
		
		# Execute the query
		$results = $this->selectQuery($query);
		
		# Variable to store the results
		$records = array();
		
		# Loop through the results
		while ( $line = $this->fetchAssocArray($results) )
			$records[] = $line;
		
		# Return the results
		return $records;
	}
}

abstract class SDatabase
{
	protected $name;
	protected $user;
	protected $host;
	protected $pass;
	protected $port;

	protected $connection;

	public function __construct($name, $host, $port, $user, $pass)
	{
		$this->name = $name;
		$this->host = $host;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
	}

	abstract public function selectQuery($query);
	abstract public function deleteQuery($query);
	abstract public function updateQuery($query);
	abstract public function insertQuery($query);

	abstract public function fetchArray($result);

	abstract public function getType();

	abstract public function real_escape_string($string);

	abstract public function connect();
	abstract public function disconnect();
}

?>
