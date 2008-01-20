<?

include('classes/admin.php');

?>

<script src="hash.js"></script>
<script language="javascript">
<!--
function checkPass()
{
	if ( password.value != pass2.value )
	{
		alert("Passwords zijn niet gelijk");
		return false;
	}

	password.value = hex_sha1(password.value); 
	return true;
}
-->
</script>
<center>
<br>
<h2>Register</h2>
<form name="newUser" action="admin/verify.php" method="post" onsubmit="checkPass();">
<table>
<tr><td>Username</td><td><input class="TextField" type="text" name="username" value=""></td></tr>
<tr><td>Password</td><td><input class="TextField" type="password" name="password" value=""></td></tr>
<tr><td>Password</td><td><input class="TextField" type="password" name="pass2" value=""></td></tr>
<tr><td colspan="2" align="center"><input class="TextField" type="submit" value="Register"></td></tr>
</table>
</center>
<?
/*

if ( 
    ( ! isset($_REQUEST['username']) ) || 
    ( ! isset($_REQUEST['password']) ) || 
    ( ! isset($_REQUEST['email']) ) ||
    ( ! isset($_REQUEST['passagain']) )
   )
{
	header('Location: /index.php?mode=login');
}

if (
    ( empty($_REQUEST['username']) ) ||
    ( empty($_REQUEST['password']) ) ||
    ( empty($_REQUEST['email']) ) ||
    ( empty($_REQUEST['passagain']) )
   )
{
	echo '<center><h3>Error: One or more fields where not supplied</h3></center>';
	return;
}

$username = $_REQUEST['username'];

if ( $_REQUEST['password'] != $_REQUEST['passagain'] )
{
	echo '<center><h3>Error: Passwords do not match</h3></center>';
	return;
}
#	header('Location: /index.php?mode=login');

$password = $_REQUEST['password'];

if ( ! eregi("^[[:alnum:]]*@[[:alnum:]]*\.[a-z]{2,6}$", $_REQUEST['email']) )
{
	echo '<center><h3>Error: Invalid e-mail address supplied</h3></center>';
	return;
}

$email = $_REQUEST['email'];

$adm = new Admin($username, $password, $email, $db);

if ( $adm->userExists() )
{
	echo 'User allready exists, please choose another username';
	return;
}

$adm->saveUser();
$_SESSION['username'] = $username;

echo '<center><h3>User registered successfully</h3></center>';
echo '<a href="/index.php?mode=admin">Admin page</a>';
*/
?>
