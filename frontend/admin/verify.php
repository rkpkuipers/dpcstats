<?

include('../classes.php');
include('../classes/admin.php');

if ( ( ! isset($_REQUEST['username']) ) || ( ! isset($_REQUEST['password']) ))
{
	header('Location: /index.php?mode=register');
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

header('Location:/index.php');
?>
