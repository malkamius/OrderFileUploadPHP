<?php
use Google\Client;
session_start();

require_once("/var/www/html/vendor/autoload.php");
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/PHPInclude/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/PHPInclude/InitDBAndSMTP.php');

$client = new Google\Client();
$client->setAuthConfig(GOOGLE_SECRETS);
$client->setRedirectUri(GOOGLE_REDIRECT);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope('https://www.googleapis.com/auth/userinfo.profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code'], $_SESSION['code_verifier']);

    $client->setAccessToken($token);
	$tokeninfo = $client->verifyIdToken();
	$emailaddress = $tokeninfo['email'];

	$sql = "SELECT id, verified FROM users WHERE emailaddress = ?";
        
	if($stmt = mysqli_prepare($link, $sql)){
		// Bind variables to the prepared statement as parameters
		$stmt->bind_param("s", $emailaddress);
		if($stmt->execute()){
			$stmt->bind_result($userid, $verified);
			if($stmt->fetch())
			{
				if($userid != 0)
				{
					if($verified == 0)
					{
						$sql = "UPDATE users SET verified = 1 WHERE id = ?";
						if($updatestmt = mysqli_prepare($link, $sql)){
						// Bind variables to the prepared statement as parameters
							$updatestmt->bind_param("s", $userid);
							$updatestmt->execute();
						}
						$updatestmt->close();
					}
				}
			}
			else
			{
				$sql = "INSERT INTO users (emailaddress, password, verification_token, verified) VALUES (?, ?, '', 1)";
		
				if($insertstmt = mysqli_prepare($link, $sql)){
					$password = uniqidReal(13);
					$insertstmt->bind_param("ss", $emailaddress, $password);
					if($insertstmt->execute())
					{
						$userid = $link->insert_id;
					}
					else
						fail();
				}
				$insertstmt->close();
			}
		}
		$stmt->close();
		if($userid != 0)
		{
			$_SESSION["loggedin"] = true;
			$_SESSION["id"] = $userid;
			$_SESSION["emailaddress"] = $emailaddress;
			header('location: /fileupload');
		}
	}
	else
	{
		fail();
	}
	
    // store in the session also
    $_SESSION['oauth_token'] = $token;

    // redirect back to the example
    header('Location: /fileupload');
}
function uniqidReal($length = 13) {
	if(function_exists("random_bytes"))
	{
		$bytes = random_bytes(ceil($length / 2));
	}
	else if(function_exists("openssl_random_pseudo_bytes"))
	{
		$bytes = openssl_random_pseudo_bytes(ceil($length / 2));
	}
	else
	{
		throw new Exception("Failed to generate verification token.");
	}
	return substr(bin2hex($bytes), 0, $length);
}

function fail()
{
	require_once($_SERVER['DOCUMENT_ROOT'] . "/fileupload/Layout/header.php");
	echo("Failed to log in.");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/fileupload/Layout/footer.php");
	exit();
}
?>