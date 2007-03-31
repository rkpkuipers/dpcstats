<?

session_start();

// Clean the session array
$_SESSION = array();

// Unset the cookie used for the session
if ( isset($_COOKIE[session_name()]) )
	setcookie(session_name(), '', time()-4200, '/');

session_destroy();

header("Location: /index.php");

?>
