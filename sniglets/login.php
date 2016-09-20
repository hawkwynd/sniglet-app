<?php
session_start();
require("config.inc.php");
$sniglet = new sniglet();



if(isset($_SESSION['loggedin']))
{

    echo "You are already logged in, {$_SESSION['name']}.";
    die(' <a href="javascript: logout()")>Logout</a> or go <a href="sniglet_edit.php">back</a>');

}
// That bit of code checks if you are logged in or not, and if you are, you can't log in again!
if(isset($_POST['submit']))
{
   $mysql = $sniglet->login($_POST);
   if($mysql[0]['users_id'] < 1)
   {
    die("Username or Password was incorrect! <a href=login.php>Try again</a> or <A href='sniglet_edit.php'>Go back...</A> ");

   } // That snippet checked to see if the number of rows the MySQL query was less than 1, so if it couldn't find a row, the password is incorrect or the user doesn't exist!

   $_SESSION['loggedin'] = "YES"; // Set it so the user is logged in!
   $_SESSION['name'] = $mysql[0]['users_name']; // Make it so the username can be called by $_SESSION['name']
   $_SESSION['access'] = $mysql[0]['users_acessLevel'];

   redirect_to('sniglet_edit.php');

}
?>
<html>
<head>
    <title>Sniglet Login</title>
    <link rel="stylesheet" href="css/sniglets.css">
    <script src="js/jquery-1.8.3.js"></script>
    <script src="js/sniglets.js"></script>
</head>
<body>
<?php
$html = <<< HTML

<div id="formArea">
<div id=formHeader>Sniglet Admin Login</div>
HTML;

echo $html;

echo "<form type='login.php' method='POST'>
<legend>Username</legend>
<input type='text' name='username'>
<legend>Password</legend>
<input type='password' name='password'><br>
<input type='submit' name='submit' value='Login'>
</form></div>";
?>