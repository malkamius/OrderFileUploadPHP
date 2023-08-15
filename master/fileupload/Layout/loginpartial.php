<?php
    require_once($_SERVER['DOCUMENT_ROOT'] ."/fileupload/PHPInclude/SignInManager.php");
 ?>
<ul class="navbar-nav">
<?php
if ($SignInManager->IsSignedIn())
{
?>
    <li class="nav-item">
        <a id="manage" class="nav-link text-dark" asp-area="Identity" href="/fileupload/Account/Manage/Index.php" title="Manage">Hello <?php print(htmlspecialchars($User->EmailAddress));?>!</a>
    </li>
    <li class="nav-item">
        <form id="logoutForm" class="form-inline" asp-area="Identity" action="/fileupload/Account/Logout.php?returnurl=index.php">
            <button id="logout" type="submit" class="nav-link btn btn-link text-dark">Logout</button>
        </form>
    </li>
<?php
}
else
{
?>
    <li class="nav-item">
        <a class="nav-link text-dark" id="register" asp-area="Identity" href="/fileupload/Account/Register.php">Register</a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" id="login" asp-area="Identity" href="/fileupload/Account/Login.php">Login</a>
    </li>
<?php
}
?>
</ul>
