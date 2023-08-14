<?php
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/header.php");
// Define variables and initialize with empty values
$emailaddress = $password = "";
$emailaddress_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if emailaddress is empty
    if(empty(trim($_POST["emailaddress"]))){
        $emailaddress_err = "Please enter email address.";
    } else{
        $emailaddress = trim($_POST["emailaddress"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($emailaddress_err) && empty($password_err)){
        // Prepare a select statement
        $sql = "SELECT id, emailaddress, password FROM users WHERE emailaddress = ? AND verified = 1";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_emailaddress);
            
            // Set parameters
            $param_emailaddress = $emailaddress;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if emailaddress exists, if yes then verify password
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $emailaddress, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["emailaddress"] = $emailaddress;                            
                            
                            if(isset($_REQUEST["redirect"]))
                                header("location: " . $_REQUEST["redirect"]);
                            else
                                header("location: /fileupload/index.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Invalid email address or password.";
                        }
                    }
                } else{
                    // emailaddress doesn't exist, display a generic error message
                    $login_err = "Invalid email address or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <input type="hidden" name="redirect" value="<?php echo (isset($_REQUEST["redirect"]))? htmlspecialchars($_REQUEST["redirect"]) : ""; ?>">
            <div class="form-group">
                <label>Email Address</label>
                <input type="text" name="emailaddress" class="form-control <?php echo (!empty($emailaddress_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $emailaddress; ?>">
                <span class="invalid-feedback"><?php echo $emailaddress_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/footer.php");
