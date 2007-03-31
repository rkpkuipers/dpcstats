<?

include('../classes.php');
include('../classes/admin.php');

if ( ( ! isset($_REQUEST['username']) ) || ( ! isset($_REQUEST['password']) ))
{
	header('Location: /index.php?message=notset');
}

if ( ( empty($_REQUEST['username']) ) || ( empty($_REQUEST['password']) ) )
{
	header('Location: /index.php?message=empty');
}

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

$adm = new admin($username, $password, '', $db);

if ( ! $adm->verifyUser() )
{
	header('Location: /index.php?message=invalid');
}

session_start();

$_SESSION['username'] = $username;
$_SESSION['userid'] = $adm->getUserID();

header('Location:/index.php?mode=admin');
?>
