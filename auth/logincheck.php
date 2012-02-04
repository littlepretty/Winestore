<?php
// Source code example for Web Database Applications with PHP and MySQL, 2nd Edition
// Author: Hugh E. Williams and David Lane, 2001-3
// 
// Unless otherwise stated, the source code distributed with this book can be
// redistributed in source or binary form so long as an acknowledgment appears
// in derived source files.
// The citation should list that the code comes from Hugh E. Williams and David 
// Lane, "Web Database Application with PHP and MySQL" published by O'Reilly & 
// Associates.
//
// This code is under copyright and cannot be included in any other book,
// publication, or educational product without permission from O'Reilly &
// Associates. No warranty is attached; we cannot take responsibility for errors 
// or fitness for use.

// This script manages the login process.
// It should only be called when the user is not logged in.
// If the user is logged in, it will redirect back to the calling page.
// If the user is not logged in, it will show a login <form>

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";
require_once "../includes/validate.inc";

set_error_handler("customHandler");

function checkLogin($loginUsername, $loginPassword, $connection)
{

  if (authenticateUser($loginUsername, $loginPassword, $connection))
  {
     registerLogin($loginUsername);

     // Clear the formVars so a future <form> is blank
     unset($_SESSION["loginFormVars"]);
     unset($_SESSION["loginErrors"]);

     header("Location: " . S_MAIN);
     exit;
  }
  else
  {
     // Register an error message
     $_SESSION["message"] = "Username or password incorrect. Login failed.";

     header("Location: " . S_LOGIN);
     exit;
  }        
}

// ------

session_start();

$connection = DB::connect($dsn, true);

if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

// Check if the user is already logged in
if (isset($_SESSION["loginUsername"]))
{
     $_SESSION["message"] = "You are already logged in!";
     header("Location: " . S_HOME);
     exit;
}

// Register and clear an error array - just in case!
if (isset($_SESSION["loginErrors"]))
   unset($_SESSION["loginErrors"]);
$_SESSION["loginErrors"] = array();

// Set up a formVars array for the POST variables
$_SESSION["loginFormVars"] = array();

foreach($_POST as $varname => $value)
   $_SESSION["loginFormVars"]["{$varname}"] = 
   pearclean($_POST, $varname, 50, $connection);

// Validate password -- has it been provided and is the length between 6 and
// 8 characters?
if (checkMandatory("loginPassword", "password", 
              "loginErrors", "loginFormVars"))
  checkMinAndMaxLength("loginPassword", 6, 8, "password", 
                  "loginErrors", "loginFormVars");

// Validate email -- has it been provided and is it valid?
if (checkMandatory("loginUsername", "email/username", 
              "loginErrors", "loginFormVars"))
  emailCheck("loginUsername", "email/username", 
             "loginErrors", "loginFormVars");

// Check if this is a valid user and, if so, log them in
checkLogin($_SESSION["loginFormVars"]["loginUsername"], 
           $_SESSION["loginFormVars"]["loginPassword"], 
           $connection);
?>
