<?

include('../classes.php');
include('../classes/admin.php');

if ( ( ! isset($_REQUEST['username']) ) || ( ! isset($_REQUEST['password']) ))
{
	header('Location: /index.php?mode=register');
}

# Load the username and password
$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

# Check for a password
if ( isset($_REQUEST['email']) )
	$email = $_REQUEST['email'];

# Verify the username doesn't exist yet
$result = $db->selectQuery('SELECT username, email FROM a_users WHERE username = \'' . $username . '\' AND password = \'' . sha1($password) . '\'');

# Check for a result
if ( ! $line = $db->fetchArray($result) )
	die("ERROR: Gebruiker/passwoord is niet juist");

# Start a session
session_start();

# Set the username
$_SESSION['username'] = $line['username'];

# Set the email address if given
if ( ! empty($line['email']) )
	$_SESSION['email'] = $line['email'];

# Redirect to the header
header('Location:/index.php');
?>