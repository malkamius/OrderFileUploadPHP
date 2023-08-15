<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
// Include config file
require_once $_SERVER['DOCUMENT_ROOT'] . "/fileupload/Layout/header.php";

// Define variables and initialize with empty values
$emailaddress = "";
$emailaddress_err = "";
$verification_token = "";
$printform = true;

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
        $sql = "UPDATE users SET verification_token = ? WHERE emailaddress = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_verification_token, $param_emailaddress);
            
            // Set parameters
            $param_emailaddress = $emailaddress;
            $param_verification_token = uniqidReal();

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                //mysqli_stmt_store_result($stmt);
                
                if(mysqli_affected_rows($link) == 1){
                    $mail = new PHPMailer(true);

                    try {

                        //Server settings
                        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
						$mail->SMTPDebug = 0;
                        $mail->isSMTP();                                            //Send using SMTP
                        $mail->Host       = SMTP_SERVER;                     //Set the SMTP server to send through
						$mail->HostName = "kbs-cloud.com";
                        //$mail->AuthType = 'LOGIN';
						$mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                        $mail->Username   = SMTP_USERNAME;                     //SMTP username
                        $mail->Password   = SMTP_PASSWORD;                               //SMTP password
                        $mail->SMTPSecure = 0;//PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                        $mail->Port       = 25;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                        //Recipients
                        $mail->setFrom(SMTP_FROMEMAIL);
                        $mail->addAddress($emailaddress);     //Add a recipient
                        $mail->addReplyTo(SMTP_FROMEMAIL);

                        //Content
                        $mail->isHTML(true);                                  //Set email format to HTML
                        $mail->Subject = 'Verify your email address';
                        $mail->Body    = 'Click <a href="http://linux.kbs-cloud.com/fileupload/Account/ConfirmEmail.php?verification_token=' . $param_verification_token .'">here</a> to verify your email address.';
                        $mail->AltBody = '';

                        $mail->send();
                        //echo 'Message has been sent';
                    } catch (Exception $e) {
                        print("Failed to send confirmation email. ");
                        print('Click <a href="http://linux.kbs-cloud.com/fileupload/Account/ConfirmEmail.php?verification_token=' . $param_verification_token .'">here</a> to verify your email address.');
                        //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                    $printform = false;
                    print("An email has been sent to the address for verification.");
                } else{
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

if($printform)
{
?>
    <div class="wrapper">
        <h2>Resend Verification Email</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Email Address</label>
                <input type="text" name="emailaddress" class="form-control <?php echo (!empty($emailaddress_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $emailaddress; ?>">
                <span class="invalid-feedback"><?php echo $emailaddress_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
        </form>
    </div>    
<?php
}
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
