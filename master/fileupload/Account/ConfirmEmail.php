<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/header.php");
    
    if(!isset($_GET["verification_token"]))
    {
        print("Error: Verification Token not set.");
    }
    else
    {
        $sql = "UPDATE users SET verified = 1 WHERE verification_token = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_verificationtoken);
            
            // Set parameters
            $param_verificationtoken = $_GET["verification_token"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                //mysqli_stmt_store_result($stmt);
                
                if(mysqli_affected_rows($link) == 1){
                    print("Account verified.");
                }       
                else {
                    print("Error verifying account. Please click <a href=/fileupload/Account/ResendVerificationEmail.php>here</a> to send a new verification token.");
                }
            }
            else {
                print("Error verifying account. Please click <a href=/fileupload/Account/ResendVerificationEmail.php>here</a> to send a new verification token.");
            }
        }
    }
?>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
