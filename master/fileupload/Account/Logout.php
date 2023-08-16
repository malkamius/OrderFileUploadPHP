<?php
require_once("/var/www/html/vendor/autoload.php");
use Google\Client;
// Initialize the session
if(session_id() == '')
     session_start();
 
if(isset($_SESSION["oauth_token"]))
{
	$client = new Google\Client();
	$client->revokeToken($_SESSION['oauth_token']);
}
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("Location: /fileupload/index.php");
exit;
?>
