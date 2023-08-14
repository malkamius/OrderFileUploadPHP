<?php ob_start(); ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FileUpload</title>
    <link rel="stylesheet" href="/fileupload/lib/bootstrap/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="/fileupload/css/site.css" />
</head>
<?php
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/InitDBAndSMTP.php");
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/SignInManager.php");
 ?>
<body>
    <header>
        <nav class="navbar navbar-expand-sm navbar-toggleable-sm navbar-light bg-white border-bottom box-shadow mb-3">
            <div class="container">
                <a class="navbar-brand" href="/fileupload/index.php">FileUpload</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target=".navbar-collapse" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="navbar-collapse collapse d-sm-inline-flex justify-content-between">
                    <ul class="navbar-nav flex-grow-1">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/fileupload/index.php">Home</a>
                        </li>
<?php
                        if ($SignInManager->IsSignedIn($User))
                        {
                            if ($User->IsInRole("ADMINISTRATOR") || $User->IsInRole("BROWSE"))
                            {
?>
                                <li class="nav-item">
                                    <a class="nav-link text-dark" href="/fileupload/ViewOrders.php">View Orders</a>
                                </li>
<?php
                            }
                            if ($User->IsInRole("ADMINISTRATOR"))
                            {

?>
                                <li class="nav-item">
                                    <a class="nav-link text-dark" href="/fileupload/EditUsers.php">Edit Users</a>
                                </li>
<?php

                            }
                        }

?>
                    </ul>
<?php 
include_once("loginpartial.php"); 
?>
                </div>
            </div>
        </nav>
    </header>
    <script src="/fileupload/lib/jquery/dist/jquery.js"></script>
    <script src="/fileupload/lib/jquery-validation/dist/jquery.validate.js"></script>
    <script src="/fileupload/lib/jquery-validation/dist/additional-methods.js"></script>
    <script src="/fileupload/lib/bootstrap/dist/js/bootstrap.js"></script>
    <script src="/fileupload/js/site.js" asp-append-version="true"></script>
    <div class="container">
        <main role="main" class="pb-3">
