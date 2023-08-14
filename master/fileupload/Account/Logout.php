<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php"); ?>
<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
 
// Redirect to login page
header("Location: /fileupload/index.php");
exit;
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");
