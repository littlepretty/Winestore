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

// Add a new user to the users table
function newUser($loginUsername, $loginPassword, $cust_id, $connection)
{
   // Create the encrypted password
   $stored_password = md5(trim($loginPassword));

   // Insert a new user into the users table
   $query = "INSERT INTO users SET 
             cust_id = {$cust_id},
             password = '{$stored_password}',
             user_name = '{$loginUsername}'";

   $result = $connection->query($query);

   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 
}

// Check if a user has an account that matches the username and password
function authenticateUser($loginUsername, $loginPassword, $connection)
{
   // Create a digest of the password collected from the challenge
   $password_digest = md5(trim($loginPassword));

   // Formulate the SQL to find the user
   $query = "SELECT password FROM users
             WHERE user_name = '$loginUsername'
             AND password = '$password_digest'";

   $result = $connection->query($query);

   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);

   // exactly one row? then we have found the user
   if ($result->numRows() != 1)
      return false;
   else
      return true;
}

// Register that user has logged in
function registerLogin($loginUsername)
{
   // Register the loginUsername to show the user is logged in
   $_SESSION["loginUsername"] = $loginUsername;

   // Register the IP address that started this session
   $_SESSION["loginIP"] = $_SERVER["REMOTE_ADDR"];
}

// Logout (unregister the login)
function unregisterLogin()
{
   // Ensure login is not registered
   if (isset($_SESSION["loginUsername"]))
      unset($_SESSION["loginUsername"]);

   if (isset($_SESSION["loginIP"]))
      unset($_SESSION["loginIP"]);
}

// Connects to a session and checks that the user has 
// authenticated and that the remote IP address matches 
// the address used to create the session.
function sessionAuthenticate($destinationScript)
{
  // Check if the user hasn't logged in
  if (!isset($_SESSION["loginUsername"]))
  {
    // The request does not identify a session
    $_SESSION["message"] = "You are not authorized to access the URL
                            {$_SERVER["REQUEST_URI"]}";

    unregisterLogin();
    header("Location: {$destinationScript}");
    exit;
  }

  // Check if the request is from a different IP address to previously
  if (isset($_SESSION["loginIP"]) &&
     ($_SESSION["loginIP"] != $_SERVER["REMOTE_ADDR"]))
  {
    // The request did not originate from the machine
    // that was used to create the session.
    // THIS IS POSSIBLY A SESSION HIJACK ATTEMPT

    $_SESSION["message"] = "You are not authorized to access the URL
                            {$_SERVER["REQUEST_URI"]} from the address
                            {$_SERVER["REMOTE_ADDR"]}";

    unregisterLogin();
    header("Location: {$destinationScript}");
    exit;
  }
}

?>
