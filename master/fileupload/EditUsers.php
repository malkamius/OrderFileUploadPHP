<?php 
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php"); 
$SignInManager->Authorize(array("ADMINISTRATOR"));
?>
<?php 
global $link;
$users = array();
$pagesize = 20;
if(isset($_REQUEST["page"]))
    $page = intval($_REQUEST["page"]);
else
    $page = 1;

$sql = "SELECT count(users.id) FROM users;";

if($stmt = mysqli_prepare($link, $sql)){
    
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->bind_result($usercount);
        
        $stmt->fetch();
        
    } 
    else {
        echo "Error querying users.";
    }

    // Close statement
    $stmt->close();
}

$sql = "SELECT users.id, users.emailaddress FROM users LIMIT " . (($page - 1) * $pagesize) . ", " . $pagesize;

if($stmt = mysqli_prepare($link, $sql)){
    
    // Attempt to execute the prepared statement
    if($stmt->execute()){
        // Store result
        $stmt->bind_result($userid, $email);
        
        while ($stmt->fetch()) {
            $users[] = array(
                "userid" => $userid,
                "email" => $email
                );
        }
        
    } 
    else {
        echo "Error querying users.";
    }

    // Close statement
    $stmt->close();
}
?>
<?php
if(count($users) == 0)
    echo "No users to see here.";
else
{
    echo "<table class='styled-table'>";
    echo "<thead><tr><th>Id</th><th>Email</th><th></th></tr></thead>";
    foreach($users as $user)
    {
        echo "<tr>";
        echo "<td>" . $user["userid"] . "</td>";
        echo "<td>" . $user["email"] . "</td>";
        echo "<td><a href='/fileupload/EditUser.php?id=" . $user["userid"] . "' class='button'>Edit</a></td>";
        echo "</tr>";
    }
    echo "</table>";
    for($i = 1; $i < ($usercount / $pagesize) + 1; $i++)
    {
        if($i == $page)
            echo "<a>" . $i . "</a>";
        else
            echo "<a href='/fileupload/EditUsers.php?page=" . $i . "'>" . $i . "</a>";
    }
}
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");
