<?php

class faq
{
	private $entries;

	private $db;
	
	public function __construct($db)
	{
		$this->db = $db;

		$entries = array();

		$this->gatherQuestions();
	}

	private function gatherQuestions()
	{
		$query = 'SELECT
				question,
				answer
			FROM
				faq';

		$result = $this->db->selectQuery($query);

		while ( $line = $this->db->fetchArray($result) )
		{
			$this->entries[] = array('question' => $line['question'],
						'answer' => $line['answer']);
		}
	}

	public function getEntries()
	{
		return $this->entries;
	}
}

?>
