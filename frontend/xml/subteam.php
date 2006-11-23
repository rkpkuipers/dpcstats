<?

include('../classes.php');

if ( isset($_REQUEST['prefix']) )
	$project = $_REQUEST['prefix'];
else
	die('No project given');

$query = 'SELECT
		name,
		member
	FROM
		' . $project . '_subteam
	ORDER BY
		name';

$result = $db->selectQuery($query);

$xmldata = '<subteamdata>' . "\n";
$xmldata .= ' <date>' . date("Y-m-d") . '</date>' . "\n";

$subteam = '';

while ( $line = $db->fetchArray($result) )
{
	if ( ( $subteam != $line['name'] ) && ( $subteam != '' ) )
	{
		$xmldata .= '  </members>' . "\n";
		$xmldata .= ' </subteam>' . "\n";
		$xmldata .= ' <subteam>' . "\n";
		$xmldata .= '  <teamname>' . $line['name'] . '</teamname>' . "\n";
		$xmldata .= '  <members>' . "\n";
		$subteam = $line['name'];
	}
	elseif ( ( $subteam != $line['name'] ) && ( $subteam == '' ) )
	{
		$xmldata .= ' <subteam>' . "\n";
		$xmldata .= '  <teamname>' . $line['name'] . '</teamname>' . "\n";
		$xmldata .= '  <members>' . "\n";
		$subteam = $line['name'];
	}
	$xmldata .= '   <member>' . "\n";
	$xmldata .= '    <username>' . $line['member'] . '</username>' . "\n";
	$xmldata .= '   </member>' . "\n";
}
$xmldata .= '  </members>' . "\n";
$xmldata .= ' </subteam>' . "\n";
$xmldata .= '</subteamdata>' . "\n";

$xml = simplexml_load_string($xmldata);

echo $xml->asXML();

$db->disconnect();

?>
