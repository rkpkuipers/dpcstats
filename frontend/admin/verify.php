<?php

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
$result = $db->selectQuery('SELECT * FROM a_users WHERE "username" = \'' . $username . '\'');

# Check for a result
if ( $line = $db->fetchArray($result) )
	die("ERROR: Die gebruiker bestaat al");

# Insert the user
$db->insertQuery('INSERT INTO 
			a_users
		(username, password, email, active)
		VALUES
		(
			\'' . $username . '\',
			\'' . sha1($password) . '\',
			' . (isset($email)?'\'' . $email . '\'':null) . ',
			1
		)');

# Start a session
session_start();

# Set the username
$_SESSION['username'] = $username;

# Set the email address if given
if ( isset($email) )
	$_SESSION['email'] = $email;

# Notify me!
mail('speedkikker@planet.nl', 'Nieuwe gebruiker van tadah.mine.nu', 'De gebruiker ' . $username . ' heeft zich ingeschreven op de website');

# Redirect to the header
header('Location:/index.php');
?>
