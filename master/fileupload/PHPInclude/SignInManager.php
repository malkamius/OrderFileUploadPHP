<?php
use Google\Client;
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/PHPInclude/config.php');
class UserSignInManager
{
    public function IsSignedIn()
    {
        return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;    
    }

    public function Authorize($roles_array)
    {
        global $User;
        if(!$this->IsSignedIn())
        {
            header("location: /fileupload/Account/Login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            die("Not signed in.");
        }
        $authorized = false;
        foreach($roles_array as $role)
        {
            if($User->IsInRole($role))
            {
                $authorized = true;
                break;
            }
        }
        if(!$authorized)
        {
            header("location: /fileupload/Account/AccessDenied.php");
            die("Not authorized.");
        }
    }
}

class UserData
{
    public $UserId = 0;
    public $EmailAddress = "";

    public function IsInRole($role)
    {
        global $link;
        $sql = "SELECT roles.role_name FROM roles JOIN user_roles ON user_roles.role_id = roles.role_id JOIN users ON user_roles.user_id = users.id WHERE users.id = ?;";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = $this->UserId;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($role_name);
                
                while ($stmt->fetch()) {
                    if($role_name == $role)
                        return true;
                }
                
            } 
            else {
                return false;
            }

            // Close statement
            $stmt->close();
        }
        return false;
    }
	
	public function IsInRoleAny($roles)
    {
        global $link;
        $sql = "SELECT roles.role_name FROM roles JOIN user_roles ON user_roles.role_id = roles.role_id JOIN users ON user_roles.user_id = users.id WHERE users.id = ?;";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = $this->UserId;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->bind_result($role_name);
                
                while ($stmt->fetch()) {
					foreach($roles as $role)
						if($role_name == $role)
							return true;
                }
                
            } 
            else {
                return false;
            }

            // Close statement
            $stmt->close();
        }
        return false;
    }

    public function __construct($UserId, $EmailAddress)
    {
        $this->UserId = $UserId;
        $this->EmailAddress = $EmailAddress;
    }

}

class UserManager
{

    public function GetUserName($user)
    {
        return $user->EmailAddress;
    }
	
	public function GetUsers()
	{
		global $link;
		$results = array();
		$sql = "SELECT id, emailaddress
				FROM users WHERE verified != 0;";

        if($stmt = mysqli_prepare($link, $sql)){
            if($stmt->execute()){
                $stmt->bind_result($userid, $emailaddress);
                
                while ($stmt->fetch()) {
                    $result = new UserData($userid, $emailaddress);
					$results[] = $result;
                }
                
            } 
            $stmt->close();
        }
		
		return $results;
	}
}
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . "/vendor/autoload.php");

$redirect_uri = "https://linux.kbs-cloud.com/fileupload/GoogleLogin.php";

$client = new Google\Client();
$client->setAuthConfig(GOOGLE_SECRETS);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
$client->addScope('https://www.googleapis.com/auth/userinfo.profile');

if(isset($_SESSION["oauth_token"]))
{
	$client->setAccessToken($_SESSION["oauth_token"]);
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['oauth_token']);
		require_once($_SERVER['DOCUMENT_ROOT'] . '/fileupload/Account/Logout.php');
    }
	$tokeninfo = $client->verifyIdToken();
	//echo $tokeninfo['email'];
}
$SignInManager = new UserSignInManager;
$UserManager= new UserManager;
$User = new UserData(isset($_SESSION["id"])? $_SESSION["id"] : "", isset($_SESSION["emailaddress"])? $_SESSION["emailaddress"] : "");

