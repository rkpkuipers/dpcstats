<?

include ('../classes.php');
include ('../include.php');

# Tabel from which the data is retrieved
if ( isset($_REQUEST['tabel']) )
	$tabel = $_REQUEST['tabel'];
else
	$tabel = 'memberOffset';

# Prefix, indicating the project
if ( isset($_REQUEST['prefix']) )
	$prefix = $_REQUEST['prefix'];
else
	die('No project selected');

if ( isset($_REQUEST['datum']) )
	$datum = $_REQUEST['datum'];
else
	$datum = getCurrentDate($prefix);

if ( isset($_REQUEST['naam']) )
	$naam = $_REQUEST['naam'];
else
	die('Detail mode requires a name');

if ( ! is_array($naam) )
	$naam = array($naam);

$xmlstring = '<detail>' . "\n";

for($i=0;$i<count($naam);$i++)
{
	$mi = new MemberInfo($naam[$i], $prefix . '_' . $tabel, $datum, $prefix, $tabel);
	
	$xmlstring .= '<user>' . "\n";
	$xmlstring .= '<name>' . $naam[$i] . '</name>' . "\n";
	$xmlstring .= '<date>' . $datum . '</date>' . "\n";
	$xmlstring .= '<credits>' . $mi->getCredits() . '</credits>' . "\n";
	$xmlstring .= '<flush>' . $mi->getFlush() . '</flush>' . "\n";
	$xmlstring .= '<rank>' . $mi->getRank() . '</rank>' . "\n";
	$xmlstring .= '<dailypos>' . $mi->getDailyRank() . '</dailypos>' . "\n";
	$xmlstring .= '<averagedailypos>' . $mi->getAvgDailyPos() . '</averagedailypos>' . "\n";
	$xmlstring .= '<increase>' . $mi->getIncrease() . '</increase>' . "\n";
	
	$xmlstring .= '<nextmember>' . "\n";
	$xmlstring .= '<name>' . $mi->getNaamNext() . '</name>' . "\n";
	$xmlstring .= '<distance>' . $mi->getDistanceNext() . '</distance>' . "\n";
	$xmlstring .= '</nextmember>' . "\n";

	$xmlstring .= '<previousmember>' . "\n";
	$xmlstring .= '<name>' . $mi->getNaamPrev() . '</name>' . "\n";
	$xmlstring .= '<distance>' . $mi->getDistancePrev() . '</distance>' . "\n";
	$xmlstring .= '</previousmember>' . "\n";

	$xmlstring .= '<largestflush>' . "\n";
	$xmlstring .= '<credits>' . $mi->getLargestFlush() . '</credits>' . "\n";
	$xmlstring .= '<date>' . $mi->getLargestFlushDate() . '</date>' . "\n";
	$xmlstring .= '</largestflush>' . "\n";
	
	$xmlstring .= '</user>' . "\n";
}

$xmlstring .= '</detail>' . "\n";

$xml = simplexml_load_string($xmlstring);

echo $xml->asXML();

$db->disconnect();

?>
