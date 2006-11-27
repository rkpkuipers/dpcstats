<?

include('classes/admin.php');

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

?>
