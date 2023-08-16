<?php
use Google\Client;
	
require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/header.php");
// Define variables and initialize with empty values
$emailaddress = $password = "";
$emailaddress_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST["connection"])){
 
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

if(isset($_POST["connection"]) && $_POST["connection"] == "google-oauth2")
{
	require_once("/var/www/html/vendor/autoload.php");
	if(session_id() == '')
     session_start();
	$client = new Google\Client();
	$client->setAuthConfig(GOOGLE_SECRETS);
	$client->setRedirectUri(GOOGLE_REDIRECT);
	$client->addScope("https://www.googleapis.com/auth/userinfo.email");
	$client->addScope('https://www.googleapis.com/auth/userinfo.profile');
	$_SESSION['code_verifier'] = $client->getOAuth2Service()->generateCodeVerifier();
    $authUrl = $client->createAuthUrl();
	
	//die($authUrl);
	print('<script type="text/javascript"> 
	window.location.href="' . $authUrl . '" 
	</script>');
	//header("Location:https://www.google.com");// . $authUrl);
	exit();
}
?>
	
    <!-- <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> -->
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
		
		:root {
			--primary-color: #0059d6;
			--primary-color-no-override: #0059d6;
			--link-color-values: 0,123,173;
			--link-color: rgb(var(--link-color-values));
			--page-background-color: #000;
			--info-color: #0a66e1;
			--success-color: #0a8852;
			--error-color: #d00e17;
			--error-text-color: #fff;
			--warning-color: #ffdb5f;
			--button-font-color: #fff;
			--widget-background-color: #fff;
			--presentational-content-color: #6f7780;
			--gray-lightest: #f1f2f3;
			--gray-light: #dee2e6;
			--gray-mid: #c2c8d0;
			--gray-dark: #6f7780;
			--gray-darkest: #2d333a;
			--font-family: ulp-font,-apple-system,BlinkMacSystemFont,Roboto,Helvetica,sans-serif;
			--font-default-color: var(--gray-darkest);
			--font-light-color: var(--gray-dark);
			--small-font-size: 12px;
			--default-font-size: 14px;
			--font-default-weight: 400;
			--font-bold-weight: 700;
			--title-font-color: var(--gray-darkest);
			--title-font-size: 24px;
			--title-font-weight: var(--font-default-weight);
			--base-line-height: 1.1;
			--lg-font-size: 16px;
			--logo-alignment: 0 auto;
			--logo-height: 52px;
			--header-alignment: center;
			--page-background-alignment: center;
			--icon-height: 20px;
			--icon-width: 20px;
			--icon-default-color: var(--gray-dark);
			--icon-auth0-badge: url("data:image/svg+xml;charset=utf-8,%3Csvg width='19' height='22' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M15.084 17.797L12.952 11l5.582-4.2h-6.9L9.5.002V0h6.9l2.136 6.8.002-.001c1.238 3.944-.038 8.43-3.453 11zm-11.166 0l-.002.002L9.5 22l5.584-4.202-5.583-4.202-5.583 4.201zm-3.45-11c-1.305 4.159.209 8.564 3.449 11.001v-.002L6.05 11 .47 6.8h6.898L9.5.002V0H2.6L.467 6.798z' fill='%23FFF'/%3E%3C/svg%3E");
			--icon-key: url("data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12.373 8.654c-1.86 1.86-4.794 1.949-6.785.312L3.56 10.814l2.305 2.309-.745.746-2.34-2.344-1.453 1.323 2.412 2.406-.748.746L0 13.016l.038-.037a.474.474 0 0 1 .13-.515L4.84 8.208c-1.582-1.99-1.475-4.883.365-6.723a5.068 5.068 0 1 1 7.168 7.169zm-.752-6.409a3.994 3.994 0 1 0-5.649 5.649 3.994 3.994 0 0 0 5.649-5.649z' fill='%235C677D' fill-rule='evenodd'/%3E%3C/svg%3E");
			--icon-phone: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M5.827 13.38a.5.5 0 0 0 .022-.406c-.346-.904-.639-1.593-.875-2.066l-.112-.224.112-.223c.89-1.781 3.706-4.597 5.487-5.487l.223-.112.224.112c.473.236 1.162.529 2.066.875a.5.5 0 0 0 .407-.022c1.749-.895 2.809-1.6 3.113-2.01-.078-.806-.887-1.586-2.517-2.305-4.116.544-11.558 7.84-12.458 12.48.717 1.62 1.494 2.424 2.299 2.502.41-.304 1.114-1.364 2.01-3.113zm.956-.765a1.5 1.5 0 0 1-.066 1.221c-1.077 2.104-1.882 3.28-2.546 3.611l-.106.053h-.118c-1.372 0-2.494-1.122-3.407-3.25l-.057-.135.024-.145C1.359 8.856 9.415.961 14.003.502L14.13.49l.119.05c2.128.913 3.25 2.035 3.25 3.407v.118l-.053.106c-.332.664-1.507 1.47-3.61 2.546a1.5 1.5 0 0 1-1.221.066 28.91 28.91 0 0 1-1.92-.801c-1.53.868-3.846 3.184-4.714 4.713.23.486.496 1.125.8 1.92z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-1h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-email: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='13' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M16.263 12l-5.39-4.975L9 8.665 7.147 7.042 1.74 12h14.524zm.737-.68V1.664l-5.37 4.699L17 11.32zm-16 0l5.39-4.94L1 1.665v9.656zM16.24 1H1.76L9 7.336 16.24 1zM1 0h16a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V1a1 1 0 0 1 1-1z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-3h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-lock: url("data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M2.5 7V5a4.5 4.5 0 0 1 9 0v2h.5a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h.5zm1 0h7V5a3.5 3.5 0 0 0-7 0v2zM2 8a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H2zm4.5 3a.5.5 0 1 1 1 0v3a.5.5 0 1 1-1 0v-3z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-3 0h20v20H-3z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-user: url("data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='17' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M5.605 9.39a5 5 0 1 1 4.79 0A7.503 7.503 0 0 1 15.5 16.5v.5h-1v-.5a6.5 6.5 0 1 0-13 0v.5h-1v-.5a7.503 7.503 0 0 1 5.105-7.11zM8 9a4 4 0 1 0 0-8 4 4 0 0 0 0 8z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-2-1h20v20H-2z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-show-pass: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='13' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M9 12c3.13 0 5.732-1.788 7.856-5.5C14.732 2.788 12.13 1 9 1S3.268 2.788 1.144 6.5C3.268 10.212 5.87 12 9 12zM9 0c3.667 0 6.667 2.167 9 6.5-2.333 4.333-5.333 6.5-9 6.5s-6.667-2.167-9-6.5C2.333 2.167 5.333 0 9 0zm0 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zm0 1a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-4h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-hide-pass: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='15' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M12.148 5.969a3.5 3.5 0 0 1-4.68 4.68l.768-.768a2.5 2.5 0 0 0 3.145-3.145l.767-.767zM5.82 12.297c.993.47 2.052.703 3.18.703 3.13 0 5.732-1.788 7.856-5.5-.837-1.463-1.749-2.628-2.738-3.501l.708-.708C15.994 4.337 17.052 5.74 18 7.5c-2.333 4.333-5.333 6.5-9 6.5a8.294 8.294 0 0 1-3.926-.957l.746-.746zM15.89.813L2.313 14.39a.5.5 0 0 1-.667-.744L3.393 11.9C2.138 10.837 1.007 9.37 0 7.5 2.333 3.167 5.333 1 9 1c1.51 0 2.907.367 4.19 1.102L15.147.146a.5.5 0 0 1 .744.667zm-3.436 2.026A7.315 7.315 0 0 0 9 2C5.87 2 3.268 3.788 1.144 7.5c.9 1.572 1.884 2.798 2.959 3.69l1.893-1.893a3.5 3.5 0 0 1 4.801-4.801l1.657-1.657zm-2.396 2.395a2.5 2.5 0 0 0-3.324 3.324l3.324-3.324z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-3h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-arrow-left: url("data:image/svg+xml;charset=utf-8,%3Csvg width='10' height='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%23B0B5C0' stroke-width='2' d='M9 1L2 8l7 7' fill='none'/%3E%3C/svg%3E");
			--icon-arrow-right: url("data:image/svg+xml;charset=utf-8,%3Csvg width='6' height='13' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M0 1l6 5.5L0 12' stroke='%236F7780' fill='none'/%3E%3C/svg%3E");
			--icon-device: url("data:image/svg+xml;charset=utf-8,%3Csvg width='12' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M2 1a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2zm0-1h8a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM1 3h10v1H1V3zm0 9h10v1H1v-1zm5 4a1 1 0 1 1 0-2 1 1 0 0 1 0 2z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-4-1h20v20H-4z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-tenant: url("data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2.39.5l-1.75 7h4.153L8 4.293 11.207 7.5h4.153l-1.75-7H2.39zM2.5 8v6.5h4V10a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 .5.5v4.5h4V8' fill='none' stroke='%235C677D'/%3E%3C/svg%3E");
			--icon-guardian: url("data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M6.5 4V1a.5.5 0 0 1 1 0v4h2V4H13a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3.5v1h2V4zm1 1v7.293l2.146-2.147a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 .708-.708L6.5 12.293V5H1v12h12V5H7.5z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-3-1h20v20H-3z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-sms: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M9 16.255L12.616 13H16a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h7v3.255zM2 0h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3l-4.166 3.749A.5.5 0 0 1 8 17.377V14H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm1.5 4a.5.5 0 0 1 0-1h11a.5.5 0 1 1 0 1h-11zm0 3a.5.5 0 0 1 0-1h11a.5.5 0 1 1 0 1h-11zm0 3a.5.5 0 0 1 0-1h5a.5.5 0 0 1 0 1h-5z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-1h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-recovery-code: url("data:image/svg+xml;charset=utf-8,%3Csvg width='18' height='18' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'%3E%3Cdefs%3E%3Cpath d='M12.16 6.547c.526.678.84 1.529.84 2.453 0 .924-.314 1.775-.84 2.453l2.84 2.84A7.97 7.97 0 0 0 17 9a7.97 7.97 0 0 0-2-5.292l-2.84 2.84zm-.707-.707L14.293 3A7.97 7.97 0 0 0 9 1a7.97 7.97 0 0 0-5.292 2l2.84 2.84A3.983 3.983 0 0 1 9 5c.924 0 1.775.314 2.453.84zm-5.613.707L3 3.707A7.97 7.97 0 0 0 1 9a7.97 7.97 0 0 0 2 5.292l2.84-2.84A3.983 3.983 0 0 1 5 9c0-.924.314-1.775.84-2.453zm.707 5.613L3.707 15A7.97 7.97 0 0 0 9 17a7.97 7.97 0 0 0 5.292-2l-2.84-2.84A3.983 3.983 0 0 1 9 13a3.983 3.983 0 0 1-2.453-.84zM9 18A9 9 0 1 1 9 0a9 9 0 0 1 0 18zm0-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z' id='a'/%3E%3C/defs%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cmask id='b' fill='%23fff'%3E%3Cuse xlink:href='%23a'/%3E%3C/mask%3E%3Cuse fill='%235C677D' fill-rule='nonzero' xlink:href='%23a'/%3E%3Cg mask='url(%23b)' fill='%235C677D'%3E%3Cpath d='M-1-1h20v20H-1z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
			--icon-webauthn-roaming: url("data:image/svg+xml;charset=utf-8,%3Csvg width='20' height='20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M1.5 6.5v8h13v-8h-13zM1 5a1 1 0 0 0-1 1v9a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1.667h3a1 1 0 0 0 1-1V8.5a1 1 0 0 0-1-1h-3V6a1 1 0 0 0-1-1H1zm17.5 4h-2v2.833h2V9zm-9.215 2.5a1.167 1.167 0 1 0 0-2.334 1.167 1.167 0 0 0 0 2.334zm0 1.5a2.667 2.667 0 1 0 0-5.333 2.667 2.667 0 0 0 0 5.333zM3.95 10.333a.667.667 0 1 1-1.333 0 .667.667 0 0 1 1.333 0z' fill='%23757575'/%3E%3C/svg%3E");
			--icon-webauthn-platform: url("data:image/svg+xml;charset=utf-8,%3Csvg width='20' height='20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M9.789.949c-2.107 0-4.037.63-5.56 1.68a.474.474 0 1 1-.539-.781C5.374.688 7.493 0 9.79 0c2.16 0 4.165.61 5.797 1.649a.474.474 0 1 1-.51.8C13.599 1.508 11.772.95 9.79.95zm0 2.646c-2.107 0-4.037.631-5.56 1.68a8.182 8.182 0 0 0-2.35 2.449.474.474 0 1 1-.81-.496A9.13 9.13 0 0 1 3.69 4.494c1.684-1.16 3.803-1.848 6.099-1.848 3.75 0 7.038 1.838 8.719 4.582a.475.475 0 0 1-.81.496c-1.493-2.44-4.46-4.129-7.91-4.129z' fill='%236F7780'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M9.787 6.2c-1.707 0-3.508.593-4.748 1.605-.836.683-1.586 1.607-1.961 2.848-.375 1.24-.388 2.841.328 4.906a.474.474 0 1 1-.897.31c-.769-2.216-.782-4.025-.34-5.49.442-1.463 1.324-2.537 2.27-3.309 1.428-1.165 3.448-1.819 5.348-1.819 2.249 0 4.304.829 5.761 2.319 1.46 1.494 2.292 3.625 2.136 6.175a2.239 2.239 0 0 1-.669 1.483c-.437.422-1.07.675-1.883.675-.977 0-1.674-.262-2.143-.697-.465-.431-.647-.977-.704-1.43-.012-.097-.021-.206-.031-.324-.037-.438-.084-.999-.334-1.528a1.871 1.871 0 0 0-.685-.803c-.32-.205-.779-.355-1.448-.355-1.077 0-1.696.349-2.045.77-.36.435-.49 1.017-.451 1.576.08 1.126.627 2.62 1.544 3.68 1 1.156 1.901 1.583 2.518 1.875.162.077.304.144.423.213a.475.475 0 0 1-.474.822 4.473 4.473 0 0 0-.308-.152c-.597-.279-1.726-.806-2.877-2.137C7.06 16.19 6.438 14.5 6.344 13.18c-.052-.735.114-1.58.668-2.248.565-.683 1.477-1.114 2.775-1.114.815 0 1.459.184 1.959.504s.823.754 1.033 1.198c.333.708.396 1.51.43 1.93.006.087.012.158.018.208.039.308.154.618.407.853.25.23.692.444 1.498.444.61 0 .992-.185 1.225-.409.237-.23.36-.54.38-.858.14-2.301-.608-4.165-1.868-5.454C13.606 6.941 11.802 6.2 9.787 6.2z' fill='%236F7780'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M9.247 8.37c-1.929.279-2.894 1.106-3.557 2.053-.953 1.36-1.367 5.01 1.776 8.438a.474.474 0 0 1-.7.641c-3.39-3.698-3.104-7.838-1.854-9.624.804-1.147 1.997-2.133 4.216-2.45l.015-.002.016-.001c2.046-.158 3.602.44 4.644 1.506 1.032 1.056 1.508 2.521 1.508 4.015a.475.475 0 0 1-.949 0c0-1.302-.415-2.51-1.238-3.352-.812-.831-2.072-1.36-3.877-1.224z' fill='%236F7780'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M9.789 12.252c.262 0 .474.213.474.475 0 .618.076 1.238.356 1.848.279.606.774 1.235 1.667 1.844 1.578 1.078 3.513 1.085 4.067 1.085a.475.475 0 0 1 0 .948h-.01c-.571 0-2.762 0-4.592-1.249-1.015-.694-1.634-1.45-1.994-2.232-.358-.779-.443-1.55-.443-2.244 0-.262.213-.475.475-.475z' fill='%236F7780'/%3E%3C/svg%3E");
			--icon-error: url("data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M8 14.667A6.667 6.667 0 1 0 8 1.333a6.667 6.667 0 0 0 0 13.334z' fill='%23D00E17' stroke='%23D00E17' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M8 4.583a.75.75 0 0 1 .75.75V8a.75.75 0 0 1-1.5 0V5.333a.75.75 0 0 1 .75-.75z' fill='%23fff'/%3E%3Cpath d='M8.667 10.667a.667.667 0 1 1-1.334 0 .667.667 0 0 1 1.334 0z' fill='%23fff'/%3E%3C/svg%3E");
			--icon-passkey: url("data:image/svg+xml;charset=utf-8,%3Csvg width='20' height='20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M8.75 8.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5zm10 0a2.917 2.917 0 1 0-4.167 2.624v4.458l1.25 1.25L17.917 15l-1.25-1.25 1.25-1.25-1.034-1.033A2.916 2.916 0 0 0 18.75 8.75zm-2.917 0a.834.834 0 1 1 0-1.668.834.834 0 0 1 0 1.667zm-3.8 1.683A5 5 0 0 0 10 10H7.5a5 5 0 0 0-5 5v1.667h10.833v-4.592a4.3 4.3 0 0 1-1.3-1.642z' fill='%230059d6'/%3E%3C/svg%3E");
			--icon-webauthn-platform-icon-blue: url("data:image/svg+xml;charset=utf-8,%3Csvg width='40' height='40' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M19.98 7.727c-5.455 0-9.779 3.632-11.662 8.66l-.025.068-.713 1.428a1.361 1.361 0 1 1-2.436-1.22l.65-1.301C8.01 9.515 13.205 5 19.98 5c3.386 0 7.301 1.31 10.294 4.119 3.032 2.846 5.048 7.168 4.684 13.012-.25 4.006-1.367 6.232-2.424 8.34-.38.759-.754 1.502-1.076 2.308a1.361 1.361 0 1 1-2.529-1.013c.451-1.129.901-2.023 1.319-2.853.978-1.945 1.778-3.535 1.991-6.953.316-5.065-1.412-8.584-3.828-10.852-2.454-2.304-5.689-3.38-8.43-3.38zm-.72 4.347c-.192.03-.358.104-.713.282a1.364 1.364 0 0 1-1.218-2.44l.037-.018c.327-.164.828-.416 1.463-.517.688-.11 1.43-.035 2.282.264a1.364 1.364 0 0 1-.9 2.574c-.509-.179-.79-.171-.952-.145zm4.304-.25a1.36 1.36 0 0 1 1.858-.508c1.845 1.056 5.401 4.41 4.761 9.535a1.362 1.362 0 1 1-2.702-.338c.45-3.603-2.078-6.067-3.41-6.829a1.365 1.365 0 0 1-.507-1.86zm-10.675.394a1.36 1.36 0 0 1 1.926 0 1.365 1.365 0 0 1 0 1.928l-.001.001-.001.001-.001.001-.001.001-.001.001-.001.001-.001.001-.001.001-.001.001-.001.002h-.001l-.002.002-.002.002-.004.004-.001.002-.004.003-.001.002h-.002l-.004.005-.003.003-.001.002h-.002l-.003.004-.64.642c-.803.803-1.274 1.823-1.705 3.007-.075.205-.151.425-.23.653-.334.963-.721 2.08-1.3 2.95-.74 1.11-1.368 1.778-2.08 2.253-.343.23-.674.392-.972.53l-.284.13c-.195.087-.377.17-.593.278a1.364 1.364 0 0 1-1.218-2.44c.267-.133.528-.251.741-.348l.212-.096c.255-.118.435-.211.603-.323.31-.206.702-.561 1.325-1.497.367-.552.605-1.232.924-2.143.095-.273.198-.566.314-.883.461-1.266 1.096-2.758 2.336-4l.64-.64.004-.004.001-.002h.001l.004-.004.002-.003.002-.001.003-.003.002-.002.001-.002.006-.005.002-.002.001-.002h.001l.001-.002h.001l.001-.002.002-.002h.002v-.002h.002v-.002l.002-.001zm5.49 5.058c-.614.602-1.036 1.406-1.22 1.867a1.361 1.361 0 1 1-2.529-1.013c.27-.675.87-1.849 1.844-2.803.999-.98 2.511-1.826 4.454-1.437 2.132.427 3.694 1.74 4.47 3.518.765 1.749.726 3.837-.068 5.826-1.446 3.62-4.972 8.543-7.791 11.367a1.36 1.36 0 0 1-1.926 0 1.365 1.365 0 0 1 0-1.929c2.628-2.631 5.911-7.254 7.188-10.451.568-1.42.53-2.742.102-3.72-.415-.95-1.236-1.682-2.509-1.937-.78-.156-1.425.134-2.015.712zm1.96 1.408c.725.199 1.153.948.955 1.675-1.756 6.448-6.327 10.243-8.784 11.72a1.36 1.36 0 0 1-1.868-.468 1.365 1.365 0 0 1 .467-1.871c2.082-1.251 6.045-4.547 7.557-10.099a1.362 1.362 0 0 1 1.672-.957zm-5.731 2.909c.625.417.794 1.264.377 1.89-1.483 2.228-3.02 3.759-5.971 5.237a1.364 1.364 0 0 1-1.218-2.44c2.496-1.25 3.682-2.446 4.923-4.31a1.36 1.36 0 0 1 1.889-.377zm14.049 1.914a1.364 1.364 0 0 1 .759 1.772l-.296.744c-1.184 2.99-2.045 5.165-4.772 7.896a1.36 1.36 0 0 1-1.926 0 1.365 1.365 0 0 1 0-1.929c2.32-2.324 2.999-4.032 4.183-7.014l.282-.71a1.361 1.361 0 0 1 1.77-.76z' fill='%23635DFF'/%3E%3C/svg%3E");
			--icon-device-globe: url("data:image/svg+xml;charset=utf-8,%3Csvg width='40' height='40' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M27.985 2h-15.97C9.797 2 8 3.614 8 5.605v28.79C8 36.386 9.797 38 12.015 38h15.97C30.203 38 32 36.386 32 34.395V5.605C32 3.614 30.203 2 27.985 2z' fill='%23D0CEFF'/%3E%3Cpath d='M19.667 35.334a1.662 1.662 0 1 1 0-3.326 1.662 1.662 0 1 1 0 3.326z' fill='%23635DFF'/%3E%3Crect x='12.5' y='10.5' width='15' height='15' rx='7.5' stroke='%23635DFF'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M17.064 13.63c.175-.78.39-1.488.638-2.108.213-.532.445-.987.689-1.36a8.032 8.032 0 0 1 3.218 0c.243.373.476.828.689 1.36.248.62.463 1.329.638 2.108-.923.116-1.91.18-2.936.18a23.62 23.62 0 0 1-2.936-.18zm-1.131-.174a16.01 16.01 0 0 1-2.117-.531 8.026 8.026 0 0 1 3.014-2.273c-.065.145-.128.294-.189.446-.28.7-.519 1.493-.708 2.358zm7.236-2.804c.066.145.13.294.19.446.28.7.519 1.493.708 2.358a16.01 16.01 0 0 0 2.117-.531 8.026 8.026 0 0 0-3.015-2.273zm-.019 4.103c-.996.129-2.055.198-3.15.198-1.095 0-2.154-.07-3.15-.198A22.842 22.842 0 0 0 16.574 18h6.852a22.842 22.842 0 0 0-.276-3.245zM24.57 18a23.988 23.988 0 0 0-.29-3.421 16.63 16.63 0 0 0 2.59-.682A7.962 7.962 0 0 1 28 18c0 1.767-.573 3.4-1.544 4.725a17.272 17.272 0 0 0-2.176-.541 23.87 23.87 0 0 0 .28-3.041h3.011V18H24.57zm-1.152 1.143h-6.834c.031 1.01.123 1.972.267 2.864A24.665 24.665 0 0 1 20 21.81c1.095 0 2.154.069 3.15.198.144-.893.236-1.856.267-2.865zm-.482 3.99a23.577 23.577 0 0 0-2.935-.18 23.43 23.43 0 0 0-2.936.18c.175.779.39 1.488.638 2.107.068.17.138.33.209.485.666.18 1.366.275 2.089.275.723 0 1.423-.096 2.089-.276a9.74 9.74 0 0 0 .209-.484c.248-.62.463-1.328.638-2.108zm-6.478 2.042c-.202-.573-.378-1.2-.524-1.868a16.87 16.87 0 0 0-1.575.365 8.022 8.022 0 0 0 2.1 1.503zm-.737-2.991c-.78.143-1.51.325-2.176.54A7.964 7.964 0 0 1 12 18c0-1.5.413-2.903 1.13-4.103a16.62 16.62 0 0 0 2.59.682A23.988 23.988 0 0 0 15.431 18h-3.288v1.143h3.297c.031 1.063.128 2.085.28 3.04zm7.823 2.99c.202-.572.378-1.199.524-1.867a16.87 16.87 0 0 1 1.575.365 8.022 8.022 0 0 1-2.1 1.503z' fill='%23635DFF'/%3E%3C/svg%3E");
			--icon-checkmark-shield: url("data:image/svg+xml;charset=utf-8,%3Csvg width='40' height='40' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M32.73 7.376L21.07 3.493a3.099 3.099 0 0 0-1.006-.16 3.119 3.119 0 0 0-1.01.16L7.398 7.376A3.505 3.505 0 0 0 5 10.702v12.305l.016.045c.23 3.303 8.807 11.41 14.987 13.613h.122c6.18-2.202 14.759-10.31 14.987-13.613l.013-.045V10.702c0-1.51-.964-2.849-2.395-3.326z' fill='%23D0CEFF'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M20.897 24.088l7.07-7.07a1.78 1.78 0 0 0 0-2.52 1.78 1.78 0 0 0-2.52 0l-7.07 7.07-3.064-3.064a1.745 1.745 0 0 0-2.468 2.468l3.064 3.063-.007.007 2.52 2.521.007-.007.002.001 2.467-2.467v-.002z' fill='%23635DFF'/%3E%3C/svg%3E");
			--icon-lock-heavy: url("data:image/svg+xml;charset=utf-8,%3Csvg width='48' height='48' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M37.916 44h-27.91A4.005 4.005 0 0 1 6 39.994V26a4.005 4.005 0 0 1 4.006-4.005h27.91A4.005 4.005 0 0 1 41.92 26v13.994A4.005 4.005 0 0 1 37.916 44z' fill='%23D0CEFF'/%3E%3Cpath d='M23.931 37.95a4.988 4.988 0 1 0 0-9.977 4.988 4.988 0 0 0 0 9.976zm-5.966-15.955v-6.018a5.984 5.984 0 0 1 5.985-5.985 5.984 5.984 0 0 1 5.985 5.985l.019.016a3 3 0 0 0 5.999 0l.005.014C35.96 9.378 30.583 4 23.95 4c-6.63 0-12.007 5.377-12.007 12.01v5.985' fill='%23635DFF'/%3E%3C/svg%3E");
			--button-height: var(--base-form-element-height);
			--input-height: var(--base-form-element-height);
			--input-background-color: var(--widget-background-color);
			--input-text-color: var(--font-default-color);
			--social-button-border-width: 1px;
			--social-button-border-color: var(--gray-mid);
			--secondary-button-border-color: var(--gray-mid);
			--secondary-button-text-color: var(--font-default-color);
			--radio-button-border-color: var(--gray-mid);
			--spacing: 8px;
			--spacing-1: var(--spacing);
			--spacing-1-5: calc(var(--spacing)*1.5);
			--spacing-2: calc(var(--spacing)*2);
			--spacing-3: calc(var(--spacing)*3);
			--spacing-4: calc(var(--spacing)*4);
			--spacing-5: calc(var(--spacing)*5);
			--spacing-6: calc(var(--spacing)*6);
			--spacing-6-5: calc(var(--spacing)*6.5);
			--base-form-element-height: var(--spacing-6-5);
			--prompt-width: calc(var(--spacing)*50);
			--outer-padding: calc(var(--spacing)*10);
			--prompt-min-height: calc(var(--spacing)*67.5);
			--transition-speed: 0.15s;
			--transition-easing: ease-in-out;
			--border-default-color: var(--gray-mid);
			--button-border-width: 1px;
			--box-border-color: transparent;
			--box-border-width: 0;
			--box-border-style: solid;
			--out-input-border-radius: 3px;
			--out-input-padding: 0 var(--spacing-2);
			--out-input-box-shadow-depth: 0 0 0 1px;
			--out-input-line-height: var(--input-height);
			--out-input-label-top: calc(var(--input-height)/2);
			--out-input-label-top-focus: -2px;
			--out-input-label-transform: translateY(-50%);
			--out-input-label-transform-focus: scale(0.88) translateX(calc(-1*var(--spacing-1))) translateY(-50%);
			--out-input-border-color: var(--border-default-color);
			--out-input-border-width: 1px;
			--input-box-shadow-depth: var(--out-input-box-shadow-depth);
			--input-border-radius: var(--out-input-border-radius);
			--input-padding: var(--out-input-padding);
			--input-line-height: var(--out-input-line-height);
			--input-label-top: var(--out-input-label-top);
			--input-label-top-focus: var(--out-input-label-top-focus);
			--input-label-transform: var(--out-input-label-transform);
			--input-label-transform-focus: var(--out-input-label-transform-focus);
			--input-border-color: var(--out-input-border-color);
			--input-border-width: var(--out-input-border-width);
			--FSI-input-border-radius: 5px 5px 0 0;
			--FSI-input-padding: var(--spacing-1) var(--spacing-2) 0;
			--FSI-input-box-shadow: none;
			--FSI-input-line-height: 1;
			--FSI-input-label-top: 50%;
			--FSI-input-label-top-focus: var(--spacing-1);
			--FSI-input-label-transform: translateY(-50%);
			--FSI-input-label-transform-focus: scale(0.7) translateX(-7px) translateY(-50%);
			--FSI-input-border-width: 0 0 1px 0;
			--overlay-box-shadow-size: inset 0 0 0 150px;
			--base-hover-color-values: 0,0,0;
			--base-hover-color: rgb(var(--base-hover-color-values));
			--hover-transparency-value: 0.1;
			--transparency-hover-color: rgba(var(--base-hover-color-values),var(--hover-transparency-value));
			--button-hover-shadow: var(--overlay-box-shadow-size) var(--transparency-hover-color);
			--base-focus-color: var(--link-color);
			--focus-transparency-value: 0.15;
			--transparency-focus-color: rgba(var(--link-color-values),var(--focus-transparency-value));
			--button-dark-focus-shadow: var(--overlay-box-shadow-size) var(--transparency-focus-color);
			--border-radius-component: 3px;
			--border-radius-outer: 5px;
			--border-radius-form-elements: 3px;
			--button-border-radius: 3px;
			--radio-border-width: 1px;
			--radio-border-radius: var(--button-border-radius);
			--textarea-num-rows: 5;
			--textarea-height: calc(var(--base-line-height)*var(--textarea-num-rows)*var(--lg-font-size) + 2*(var(--spacing-2) + var(--input-border-width)));
			--shadow-component-outer: 0 12px 40px rgba(0,0,0,0.12);
			--z-index-background: -1;
			--z-index-base: 1;
			--z-index-first: 2;
			--z-index-second: 3;
			--z-index-third: 4;
		}
		.c83859ab9 .c9ea325b9 {
			position: absolute;
			left: 26px;
			left: calc(var(--spacing-6-5)/2);
			top: 50%;
			transform: translateX(-50%) translateY(-50%);
		}
		.c9ea325b9[data-provider^=google] {
			background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 48 48'%3E%3Cdefs%3E%3Cpath id='a' d='M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z'/%3E%3C/defs%3E%3CclipPath id='b'%3E%3Cuse xlink:href='%23a' overflow='visible'/%3E%3C/clipPath%3E%3Cpath clip-path='url(%23b)' fill='%23FBBC05' d='M0 37V11l17 13z'/%3E%3Cpath clip-path='url(%23b)' fill='%23EA4335' d='M0 11l17 13 7-6.1L48 14V0H0z'/%3E%3Cpath clip-path='url(%23b)' fill='%2334A853' d='M0 37l30-23 7.9 1L48 0v48H0z'/%3E%3Cpath clip-path='url(%23b)' fill='%234285F4' d='M48 48L17 24l-4-3 35-10z'/%3E%3C/svg%3E");
		}
		.c9ea325b9 {
			display: inline-block;
			width: 20px;
			height: 20px;
			position: relative;
			background-size: contain;
			background-repeat: no-repeat;
			background-position: 50%;
		}
		.c83859ab9, .passkey-challenge-button, .webauthn-enrollment-button {
			display: flex;
			position: relative;
			padding: 0 8px 0 52px;
			padding: 0 var(--spacing-1) 0 var(--spacing-6-5);
			background: #fff;
			background: var(--widget-background-color);
			align-items: center;
			width: 100%;
			font-size: 16px;
			font-size: var(--lg-font-size);
			font-family: inherit;
			height: 52px;
			height: var(--button-height);
			border: 1px solid #c2c8d0;
			border: var(--social-button-border-width) solid var(--social-button-border-color);
			border-radius: 3px;
			border-radius: var(--button-border-radius);
			color: #2d333a;
			color: var(--secondary-button-text-color);
			cursor: pointer;
			outline: 0;
			transition: box-shadow .15s ease-in-out,background-color .15s ease-in-out;
			transition: box-shadow var(--transition-speed) var(--transition-easing),background-color var(--transition-speed) var(--transition-easing);
		}

		button {
			appearance: auto;
			font-style: ;
			font-variant-ligatures: ;
			font-variant-caps: ;
			font-variant-numeric: ;
			font-variant-east-asian: ;
			font-variant-alternates: ;
			font-weight: ;
			font-stretch: ;
			font-size: ;
			font-family: ;
			font-optical-sizing: ;
			font-kerning: ;
			font-feature-settings: ;
			font-variation-settings: ;
			text-rendering: auto;
			color: buttontext;
			letter-spacing: normal;
			word-spacing: normal;
			line-height: normal;
			text-transform: none;
			text-indent: 0px;
			text-shadow: none;
			display: inline-block;
			text-align: center;
			align-items: flex-start;
			cursor: default;
			box-sizing: border-box;
			background-color: buttonface;
			margin: 0em;
			padding: 1px 6px;
			border-width: 2px;
			border-style: outset;
			border-color: buttonborder;
			border-image: initial;
		}
		
		.cc04c7973 {
			padding: 0 40px 40px;
			padding: 0 var(--spacing-5) var(--spacing-5);
			text-align: center;
			flex-shrink: 0;
		}
		.ce53f8304 {
			position: relative;
			font-size: 14px;
			font-size: var(--default-font-size);
			color: #2d333a;
			color: var(--font-default-color);
			background-color: #fff;
			background-color: var(--widget-background-color);
			box-shadow: 0 12px 40px rgba(0,0,0,.12);
			box-shadow: var(--shadow-component-outer);
			border-radius: 5px;
			border-radius: var(--border-radius-outer);
			border: 0 solid transparent;
			border: var(--box-border-width) var(--box-border-style) var(--box-border-color);
		}
    </style>

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
            <input type="hidden" name="redirect" value="<?php echo (isset($_REQUEST["redirect"]))? htmlspecialchars($_REQUEST["redirect"]) : "/fileupload/index.php"; ?>">
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
		<form method="post" data-provider="google" class="c16668c3a c9dd0addd cce432dbd" data-form-secondary="true">
		  <input type="hidden" name="state" value="">
		
		  <input type="hidden" name="connection" value="google-oauth2">
		
		  <button type="submit" class="c83859ab9 c6080078a ce510e60e" data-provider="google" data-action-button-secondary="true">
			
			  <span class="c9ea325b9 ccc91ec88" data-provider="google"></span>
			
		  
			<span class="c2c404ecd">Continue with Google</span>
		  </button>
		</form>
    </div>
<?php require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/Layout/footer.php");
