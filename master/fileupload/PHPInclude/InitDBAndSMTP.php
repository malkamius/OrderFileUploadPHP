<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/PHPInclude/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] .'/PHPMailer/src/PHPMailer.php');
require_once($_SERVER['DOCUMENT_ROOT'] .'/PHPMailer/src/SMTP.php');
require_once($_SERVER['DOCUMENT_ROOT'] .'/PHPMailer/src/Exception.php');
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
$verification_token = "";

// Check connection
if($link === false){
    die("ERROR: Could not connect to MySQL. " . mysqli_connect_error());
}
$mail = new PHPMailer(true);
