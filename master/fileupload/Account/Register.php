<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// Include config file
require_once $_SERVER['DOCUMENT_ROOT'] . "/fileupload/header.php";

// Define variables and initialize with empty values
$emailaddress = $password = $confirm_password = "";
$emailaddress_err = $password_err = $confirm_password_err = "";
$verification_token = "";
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["emailaddress"]))){
        $emailaddress_err = "Please enter an email address.";
    } elseif(!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i', trim($_POST["emailaddress"]))){
        $emailaddress_err = "Email address is not valid.";
    } else{
        $emailaddress = trim($_POST["emailaddress"]);
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE emailaddress = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_emailaddress);
            
            // Set parameters
            $param_emailaddress = $emailaddress;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $emailaddress_err = "This email is already registered.";
                } else{
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have atleast 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
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

    // Check input errors before inserting in database
    if(empty($emailaddress_err) && empty($password_err) && empty($confirm_password_err)){
        $verification_token = uniqidReal();
        // Prepare an insert statement
        $sql = "INSERT INTO users (emailaddress, password, verification_token) VALUES (?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "sss", $param_emailaddress, $param_password, $param_verification_token);
            
            // Set parameters
            $param_emailaddress = $emailaddress;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            $param_verification_token = $verification_token;

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                //Create an instance; passing `true` enables exceptions
                $mail = new PHPMailer(true);

                try {
                    //Server settings
                    $mail->SMTPDebug = 0;                      //Enable verbose debug output
                    $mail->isSMTP();                                            //Send using SMTP
                    $mail->Host       = SMTP_SERVER;                     //Set the SMTP server to send through
                    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                    $mail->Username   = SMTP_USERNAME;                     //SMTP username
                    $mail->Password   = SMTP_PASSWORD;                               //SMTP password
                    //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                    $mail->Port       = 25;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                    //Recipients
                    $mail->setFrom(SMTP_FROMEMAIL, 'LiveOrder');
                    $mail->addAddress($emailaddress);     //Add a recipient
                    $mail->addReplyTo(SMTP_FROMEMAIL, 'LiveOrder');

                    //Content
                    $mail->isHTML(true);                                  //Set email format to HTML
                    $mail->Subject = 'Verify your email address';
                    $mail->Body    = 'Click <a href="http://linux.kbs-cloud.com/fileupload/Account/ConfirmEmail.php?verification_token=' . $verification_token .'">here</a> to verify your email address.';
                    $mail->AltBody = '';

                    $mail->send();
                    //echo 'Message has been sent';
                } catch (Exception $e) {
                    print("Failed to send confirmation email. ");
                    print('Click <a href="http://linux.kbs-cloud.com/fileupload/Account/ConfirmEmail.php?verification_token=' . $verification_token .'">here</a> to verify your email address.');
                    //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                // Redirect to login page
                header("location: RegisterConfirmation.php");
                print("<script>window.location.replace('http://linux.kbs-cloud.com/fileupload/Account/RegisterConfirmation.php");
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
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email Address</label>
                <input type="text" name="emailaddress" class="form-control <?php echo (!empty($emailaddress_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $emailaddress; ?>">
                <span class="invalid-feedback"><?php echo $emailaddress_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="Login.php">Login here</a>.</p>
        </form>
    </div>    
<?php
    require_once("../footer.php");
