<?

if ( isset($_REQUEST['teams']) )
	$teams = $_REQUEST['teams'];
else
{
	$teams = array($project->getTeamName());
}

if ( isset($_REQUEST['timespan']) )
	$timespan = $_REQUEST['timespan'];
else
	$timespan = 14;

?>
<br>
<center>
<h2>Overall Progress</h2>
<hr>
<?
$variables = array(	'tabel' => $tabel,
			'prefix' => $project->getPrefix(),
			'timespan' => $timespan,
			'teams' => $teams,
			'team' => $team);
echo '<img src="/graphs/progressGraph.php?' . http_build_query($variables) . '">';
?>
<br><br>
<table>
<?
for($i=0;$i<count($teams);$i++)
{
	echo '<tr>';
	echo trBackground(($i+1));
	echo '<td style="width:13px; height:10px;" bgcolor="' . $kleur[$i] . '">&nbsp;</td>';
	echo '<td style="text-align:left">' . $teams[$i] . '</td>';
	echo '</tr>';
}
?>
</table>
</body>
</html>
