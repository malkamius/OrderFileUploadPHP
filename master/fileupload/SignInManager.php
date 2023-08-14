<?php
require_once($_SERVER['DOCUMENT_ROOT'] .'/fileupload/config.php');
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
    public $UserName = "";

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

    public function __construct($UserId, $EmailAddress)
    {
        $this->UserId = $UserId;
        $this->UserName = $EmailAddress;
    }

}

class UserManager
{

    public function GetUserName($user)
    {
        return $user->UserName;
    }

}
session_start();
$SignInManager = new UserSignInManager;
$UserManager= new UserSignInManager;
$User = new UserData(isset($_SESSION["id"])? $_SESSION["id"] : "", isset($_SESSION["emailaddress"])? $_SESSION["emailaddress"] : "");

