<?

if ( ! isset($_SESSION['username']) )
{
	#header('Location: /index.php');
	$mode = 'login';
	return false;
}
echo '<center><h2>Welcome ' . $_SESSION['username'] . '</h2></center>';
?>
<hr>
<table width="95%">
 <tr>
  <td align="center">
   <form name="subteaminfo" action="index.php" method="post">
    <input name="mode" type="hidden" value="admin">
    <input type="hidden" name="action" value="subteaminfo">
    <input type="submit" value="Subteam Info" class="TextField">
   </form>
  </td>
<td align="center">SoB/F@H Members</td>
<td align="center">Sengent Info</td>
</tr>
</table>
