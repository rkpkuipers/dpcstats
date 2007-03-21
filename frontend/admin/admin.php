<?

function crossProjectLinks()
{
	global $db;

	$query = 'SELECT project, description FROM project ORDER BY description';
	$result = $db->selectQuery($query);

	echo '<hr>';
	echo '<form name="cpl" action="index.php" method="post">';
	echo '<input type="hidden" name="mode" value="admin">';
	echo '<input type="hidden" name="action" value="cpl">';
	echo '<center>';
	echo '<table width="75%">';
	while ( $line = $db->fetchArray($result) )
	{
		echo '<tr>';
		echo '<td align="left">' . $line['description'] . '</td>';

		echo '<td>';
		echo '<input type="text" name="nm' . $line['project'] . '" class="TextField" value="' . $_POST['nm' . $line['project']] . '">';
		echo '</td>';
		if ( ( ! isset($_POST['nm' . $line['project']]) ) || ( $_POST['nm' . $line['project']] == '' ) )
		{
			echo '<td></td>';
		}
		else if ( memberExists($line['project'], 'memberoffset', $_POST['nm' . $line['project']]) )
		{
			# Store member cross link in database
			# <green>Validated</green>
			echo '<td style="color:#005500">Member exists</td>';
		}
		else
		{
			echo '<td style="color:#FF0000">Member does not exist</td>';
		}
		echo '</td>';
	}
	echo '<tr><td colspan="2"><input type="submit" value="Save" class="TextField"></td></tr>';
	echo '</table>';
	echo '</center>';
	echo '</form>';
}

if ( ! isset($_SESSION['username']) )
{
	#header('Location: /index.php');
	$mode = 'login';
	return false;
}
echo '<center><h2>Welcome ' . $_SESSION['username'] . '</h2></center>';

if ( isset($_POST['action']) )
	$action = $_POST['action'];
?>
<hr>
<table width="95%">
 <tr>
  <td align="center">
   <form name="subteaminfo" action="index.php" method="post">
    <input name="mode" type="hidden" value="admin">
    <input type="hidden" name="action" value="cpl">
    <input type="submit" value="Cross Project Links" class="TextField">
   </form>
  </td>
<td align="center">SoB/F@H Members</td>
<td align="center">Sengent Info</td>
</tr>
</table>
<?
switch ($action)
{
	case 'cpl':	crossProjectLinks();
			break;
}
