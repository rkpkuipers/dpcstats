<?php

class MijlPalen
{
	private $palen = array();
	private $dag;
	private $tabel;
	private $dbase;
	private $prefix;
	private $milestones;
	private $subteam;

	function MijlPalen($tabel, $dag, $prefix, $db, $subteam = '')
	{
		$this->dbase = $db;
	
		$this->tabel = $tabel;
		$this->dag = $dag;

		$this->subteam = $subteam;

		$this->prefix = $prefix;

		$this->getPalenFromDB();

		$this->verzamelPalen();
		$this->sorteer();
	}

	function getPalenFromDB()
	{
		$this->milestones = array();

		$query = 'SELECT
				lowerBound,
				upperBound,
				milestone
			FROM
				milestones m,
				project p
			where
				m.project = p.milestones
			AND	p.project = \'' . $this->prefix . '\'
			ORDER BY
				m.lowerBound,
				m.upperBound';

		$result = $this->dbase->selectQuery($query);

		while ( $line = $this->dbase->fetchArray($result) )
		{
			$this->milestones[] = array(	'lowerBound' => $line['lowerBound'],
							'upperBound' => $line['upperBound'],
							'milestone'  => $line['milestone']);
		}
	}

	function verzamelPalen()
	{
		if ( $this->tabel == 'sah_teamOffset' )
			return 0;

		if ( is_numeric(strpos($this->tabel, 'subteamOffset')) )
			$where = ' AND o.subteam = \'' . $this->subteam . '\' ';
		else
			$where;

		$query = 'SELECT
	                	o.naam,
	        	        o.daily,
        	        	(o.cands + o.daily) AS total
		        FROM
        		        ' . $this->tabel . ' o
	        	WHERE
	        	NOT     o.daily = 0 ' .
			$where . '
		        AND     o.dag = \'' . $this->dag . '\'
        		ORDER BY
                		daily DESC';
	        
		$result =$this->dbase->selectQuery($query);
		
		while ($line = $this->dbase->fetchArray($result, MYSQL_ASSOC)) 
		{
                	$tmpNaam = '';
                	if ( ( $this->tabel == 'nodeOffset' ) && ( strlen($line['description']) > 0 ) )
                        	$tmpNaam = $line['description'] . ' (' . number_format($line['naam'], 0, ',', '.') . ')';
	                elseif ( $this->tabel == 'nodeOffset' )
        	                $tmpNaam = number_format($line['naam'], 0, ',', '.');
                	else
                        	$tmpNaam = $line['naam'];

			for($i=0;$i<count($this->milestones);$i++)
				$this->checkIfPaal(	$this->milestones[$i]['lowerBound'],
							$this->milestones[$i]['upperBound'],
							$line['total'],
							$line['daily'],
							$tmpNaam,
							$this->milestones[$i]['milestone']);
		}
	}

	function checkIfPaal($low, $high, $total, $daily, $naam, $paal)
	{
	        if (
        	        ( $total >= $low ) &&
                	( $total < $high ) &&
	                ( floor( $total / $paal ) != floor( ( $total - $daily ) / $paal ) )
        	   )
	        {
			$this->palen[] = new Member($naam, ( floor( $total / $paal ) * $paal ));
	        }
	}

	function sorteer()
	{
		for($j=0;$j<count($this->palen)-1;$j++)
		{
				for($i=0;$i<count($this->palen)-1;$i++)
			{
				if ( $this->palen[$i]->getCredits() < $this->palen[$i+1]->getCredits() )
					$this->wissel($i, $i + 1);
				elseif 	( 
					 ( $this->palen[$i]->getCredits() == $this->palen[$i+1]->getCredits() ) 
					&& 
					 ( $this->palen[$i]->getName() > $this->palen[$i+1]->getName() ) 
				        )
				       	$this->wissel($i, $i + 1);
					
			}
		}
	}

	function wissel($first, $second)
	{
		$tmpPaal = $this->palen[$second];

	        $this->palen[$second] = $this->palen[$first];

	        $this->palen[$first] = $tmpPaal;
	}

	function getMijlpalen()
	{
		return $this->palen;
	}
}
?>
