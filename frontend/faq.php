<center><h3>Frequently Asked Questions</h3></center>
<?

$faq = new FAQ($db);
$entry = $faq->getEntries();

echo '<table width="550px">';
for($i=-0;$i<count($entry);$i++)
{
	echo trBackground(0) . '<td>' . $entry[$i]['question'] . '</td></tr>';
	echo trBackground($i+1) . '<td>' . $entry[$i]['answer'] . '</td></tr>';
	echo '<tr><td><br></td></tr>';
}
echo '</table>';
?>
