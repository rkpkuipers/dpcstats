<?

include ('../classes.php');

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

if ( isset($_REQUEST['team']) )
	$team = $_REQUEST['team'];
elseif ( $tabel == 'subteamOffset' )
	die('In subteammode moet een team worden meegegeven');

if ( isset($_REQUEST['datum']) )
	$datum = $_REQUEST['datum'];
else
	$datum = getCurrentDate($prefix);

if ( isset($_REQUEST['offset']) )
	$offset = $_REQUEST['offset'];
else
	$offset = 0;
	
if ( isset($_REQUEST['listsize']) )
	$entries = $_REQUEST['listsize'];
else
	$listsize = 30;

	$xmlstring = '<list>' . "\n";
	$xmlstring .= ' <listsize>' . $listsize . '</listsize>' . "\n";
	$xmlstring .= ' <offset>' . $offset . '</offset>' . "\n";
	
	$xmlstring .= ' <flushlist>' . "\n";
	$ml = new MemberList($prefix . '_' . $tabel, $datum, $offset, $listsize, $db, $team);
	$ml->generateFlushList();
	$mbs = $ml->getMembers();

	for($i=0;$i<count($mbs);$i++)
	{
		$xmlstring .= '  <user>' . "\n";
		$xmlstring .= '   <name>' . urlencode($mbs[$i]->getName()) . '</name>' . "\n";
		$xmlstring .= '   <rank>' . $mbs[$i]->getCurrRank() . '</rank>' . "\n";
		$xmlstring .= '   <flush>' . $mbs[$i]->getFlush() . '</flush>' . "\n";
		$xmlstring .= '   <dailypos>' . $mbs[$i]->getFlushRank() . '</dailypos>' . "\n";
		$xmlstring .= '   <dailychange>' . ( $mbs[$i]->getYesterday() - $mbs[$i]->getFlushRank() ) . '</dailychange>' . "\n";
		$xmlstring .= '   <ydailypos>' . $mbs[$i]->getYesterday() . '</ydailypos>' . "\n";
		$xmlstring .= '   <total>' . $mbs[$i]->getCredits() . '</total>' . "\n";
		$xmlstring .= '  </user>' . "\n";
	}
	$xmlstring .= ' </flushlist>' . "\n";

	$xmlstring .= ' <ranklist>' . "\n";
	$ml = new MemberList($prefix . '_' . $tabel, $datum, $offset, $listsize, $db, $team);
	$ml->generateRankList();
	$mbs = $ml->getMembers();
	for($i=0;$i<count($mbs);$i++)
	{
		$xmlstring .= '  <user>' . "\n";
		$xmlstring .= '   <name>' . urlencode($mbs[$i]->getName()) . '</name>' . "\n";
		$xmlstring .= '   <rank>' . $mbs[$i]->getCurrRank() . '</rank>' . "\n";
		$xmlstring .= '   <rankchange>' . ( $mbs[$i]->getRank() - $mbs[$i]->getCurrRank() ) . '</rankchange>' . "\n";
		$xmlstring .= '   <flush>' . $mbs[$i]->getFlush() . '</flush>' . "\n";
		$xmlstring .= '   <dailypos>' . $mbs[$i]->getFlushRank() . '</dailypos>' . "\n";
		$xmlstring .= '   <total>' . $mbs[$i]->getCredits() . '</total>' . "\n";
		$xmlstring .= '  </user>' . "\n";
	}
	$xmlstring .= ' </ranklist>' . "\n";

	$mp = new MijlPalen($prefix . '_' . $tabel, $datum, $prefix, $db, $team);
	$mpl = $mp->getMijlpalen();
	if ( count($mpl) > 0 )
	{
		$xmlstring .= ' <mijlpalen>' . "\n";
		for($user=0;$user<count($mpl);$user++)
		{
			$xmlstring .= '  <user>' . "\n";
			$xmlstring .= '   <name>' . urlencode($mpl[$user]->getName()) . '</name>' . "\n";
			$xmlstring .= '   <mijlpaal>' . $mpl[$user]->getCredits() . '</mijlpaal>' . "\n";
			$xmlstring .= '  </user>' . "\n";
		}
		$xmlstring .= ' </mijlpalen>' . "\n";
	}

	$movement = new Movement($db, $prefix . '_' . $tabel, $datum);
	$leaves = $movement->getMembers(0);

	if ( count($leaves) > 0 )
	{
		$xmlstring .= ' <retirements>' . "\n";
		for($user=0;$user<count($leaves);$user++)
		{
			$xmlstring .= '  <user>' . "\n";
			$xmlstring .= '   <name>' . urlencode($leaves[$user]['name']) . '</name>' . "\n";
			$xmlstring .= '   <workunits>' . $leaves[$user]['credits'] . '</workunits>' . "\n";
			$xmlstring .= '  </user>' . "\n";
		}
		$xmlstring .= ' </retirements>' . "\n";
	}

	$joins = $movement->getMembers(1);

	if ( count($joins) > 0 )
	{
		$xmlstring .= ' <joins>' . "\n";
		for($i=0;$i<count($joins);$i++)
		{
			$xmlstring .= '  <user>' . "\n";
			$xmlstring .= '   <name>' . urlencode($joins[$i]['name']) . '</name>' . "\n";
			$xmlstring .= '   <workunits>' . $joins[$i]['credits'] . '</workunits>' . "\n";
			$xmlstring .= '  </user>' . "\n";
		}
		$xmlstring .= ' </joins>' . "\n";
	}

	$xmlstring .= '</list>';

$xml = simplexml_load_string($xmlstring);

echo $xml->asXML();

$db->disconnect();

?>
