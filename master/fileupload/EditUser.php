<?php 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/header.php"); 
$SignInManager->Authorize(array("ADMINISTRATOR"));
?>
<?php 
global $link;
$userid = "0";
$pagesize = 20;
$userupdated = false;
if(isset($_REQUEST["id"]))
    $userid = $_REQUEST["id"];

$sql = "SELECT users.emailaddress FROM users WHERE users.id = ?;";

if($stmt = mysqli_prepare($link, $sql)){
    $stmt->bind_param('d', $userid);
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->bind_result($email);
        
        if(!$stmt->fetch())
        {
            echo("User not found.");
            require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
            die();
        }
    } 
    else {
        echo "Error querying user information.";
    }

    // Close statement
    $stmt->close();
}

if(isset($_POST["roles"]))
{
    $sql = "DELETE FROM user_roles WHERE user_roles.user_id = ?;";
    if($stmt = mysqli_prepare($link, $sql)){
        $stmt->bind_param('d', $userid);
        $stmt->execute();
        $stmt->close();
    }
    
    foreach($_POST["roles"] as $roleid)
    {
        $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (?,?);";
        if($stmt = mysqli_prepare($link, $sql)){
            $stmt->bind_param('dd', $userid, $roleid);
            $stmt->execute();
            $stmt->close();
        }
    }
    $userupdated = true;
}

$sql = "SELECT roles.role_id, roles.role_name,(SELECT COUNT(1) FROM user_roles WHERE user_roles.role_id = roles.role_id AND user_roles.user_id = ?) AS UserInRole FROM roles;";
$roles = array();
if($stmt = mysqli_prepare($link, $sql)){
    $stmt->bind_param('d', $userid);
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->bind_result($role_id, $role_name, $user_in_role);
        
        while($stmt->fetch())
        {
            $roles[] = array ("role_id" => $role_id, "role_name" => $role_name, "user_in_role" => $user_in_role);
        }
    } 
    else {
        echo "Error querying user roles.";
    }

    // Close statement
    $stmt->close();
}

?>
<form method="post" action="/fileupload/EditUser.php">
    <input type='hidden' name='id' value='<?php echo htmlspecialchars($userid); ?>'>
    Email: <?php echo htmlspecialchars($email); ?><br>
    <?php
        foreach($roles as $role)
        {
            echo "<input type='checkbox' " . (($role["user_in_role"] == 1)? 'checked' : '') . 
                " name='roles[]' value='". $role["role_id"] ."'> ";
            echo htmlspecialchars($role["role_name"]) . "<br>";
        }
    ?>
    <br>
    <input type="submit" value="Update">
</form>
<?php
    if($userupdated)
        echo "User updated.";
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
