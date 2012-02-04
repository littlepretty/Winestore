<?php
// Source code example for Web Database Applications with PHP and MySQL, 2nd Edition
// Author: David Lane and Hugh E. Williams, 2001-3
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

// This script validates and changes the user's password

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";
require_once "../includes/validate.inc";

set_error_handler("customHandler");

session_start();

// Connect to a authenticated session
sessionAuthenticate(S_MAIN);

$connection = DB::connect($dsn, true);

if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

// Register and clear an error array - just in case!
if (isset($_SESSION["pwdErrors"]))
   unset($_SESSION["pwdErrors"]);
$_SESSION["pwdErrors"] = array();

// Set up a formVars array for the POST variables
$_SESSION["pwdFormVars"] = array();

foreach($_POST as $varname => $value)
   $_SESSION["pwdFormVars"]["{$varname}"] = 
     pearclean($_POST, $varname, 8, $connection);

// Validate passwords - between 6 and 8 characters
if (checkMandatory("currentPassword", "current password", 
              "pwdErrors", "pwdFormVars"))
  checkMinAndMaxLength("loginPassword", 6, 8, "current password", 
                  "pwdErrors", "pwdFormVars");

if (checkMandatory("newPassword1", "first new password", 
              "pwdErrors", "pwdFormVars"))
  checkMinAndMaxLength("newPassword1", 6, 8, "first new password", 
                  "pwdErrors", "pwdFormVars");

if (checkMandatory("newPassword2", "second new password", 
              "pwdErrors", "pwdFormVars"))
  checkMinAndMaxLength("newPassword2", 6, 8, "second new password", 
                  "pwdErrors", "pwdFormVars");

// Did we find no errors? Ok, check the new passwords are the
// same, and that the current password is different.
// Then, check the current password.
if (count($_SESSION["pwdErrors"]) == 0)
{
   if ($_SESSION["pwdFormVars"]["newPassword1"] !=
       $_SESSION["pwdFormVars"]["newPassword2"])
     $_SESSION["pwdErrors"]["newPassword1"] = 
       "The new passwords must match.";

   elseif ($_SESSION["pwdFormVars"]["newPassword1"] ==
           $_SESSION["pwdFormVars"]["currentPassword"])
     $_SESSION["pwdErrors"]["newPassword1"] =  
       "The password must change.";

   elseif (!authenticateUser($_SESSION["loginUsername"], 
                             $_SESSION["pwdFormVars"]["currentPassword"], 
                             $connection))
     $_SESSION["pwdErrors"]["currentPassword"] = 
       "The current password is incorrect.";
}

// Now the script has finished the validation, 
// check if there were any errors
if (count($_SESSION["pwdErrors"]) > 0)
{
    // There are errors.  Relocate back to the password form
    header("Location: " . S_PASSWORD);
    exit;
}
                      
// Create the encrypted password
$stored_password = md5(trim($_SESSION["pwdFormVars"]["newPassword1"]));

// Update the user row
$query = "UPDATE users SET password = '$stored_password'
          WHERE user_name = '{$_SESSION["loginUsername"]}'";

$result = $connection->query($query);
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

// Clear the formVars so a future <form> is blank
unset($_SESSION["pwdFormVars"]);
unset($_SESSION["pwdErrors"]);

// Set a message that says that the page has changed
$_SESSION["message"] = "Your password has been successfully changed.";

// Relocate to the customer details page
header("Location: " . S_DETAILS);
?>
