<?php

class Joins
{
        var $tabel;
        var $datum;

        var $NWList;
        var $totCands;

        var $database;

        function Joins($tabel, $datum, $db = 'None')
        {
                $this->tabel = $tabel;
                $this->datum = $datum;

                $this->NWList = array();
                $this->totCands = 0;

		if ( $db == 'None' )
	                $this->database = new DataBase();
		else
			$this->database = $db;
        }

        function getTotCands()
        {
                return $this->totCands;
        }

        function getJoins()
        {

                $query = 'SELECT 
				naam, 
				candidates 
			FROM 
				movement 
			WHERE 
				direction = 1 
			AND	tabel = \'' . $this->tabel . '\'
			AND 	datum = \'' . $this->datum . '\'
			ORDER BY
				naam';

                $result = $this->database->selectQuery($query);

                while ( $line = mysql_fetch_array($result) )
                {
                        $this->NWList[count($this->NWList)] = new Member($line['naam'], $line['candidates']);
                        $this->totCands += $line['candidates'];
                }

                return $this->NWList;
        }
}

class Leaves
{
        var $tabel;
        var $datum;

        var $RMList;
        var $totCands;

        var $database;

        function Leaves($tabel, $datum, $db = 'None')
        {
                $this->tabel = $tabel;
                $this->datum = $datum;

                $this->RMList = array();
                $this->totCands = 0;

		if ( $db == 'None' )
	                $this->database = new DataBase();
		else
			$this->database = $db;
        }

        function getTotCands()
        {
                return $this->totCands;
        }


        function getLeaves()
        {
		$query = 'SELECT 
				naam,
				candidates
			FROM
				movement
			WHERE
				direction = 0
			AND	datum = \'' . $this->datum . '\'
			AND	tabel = \'' . $this->tabel . '\'
			ORDER BY
				naam';

                $result = $this->database->selectQuery($query);

                while ( $line = mysql_fetch_array($result) )
                {
                        $this->RMList[count($this->RMList)] = new Member($line['naam'], $line['candidates']);
                        $this->totCands += $line['candidates'];
                }

                return $this->RMList;
        }
}
?>
