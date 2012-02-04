<?php
// Source code example for Web Database Applications with PHP and MySQL, 2nd Edition
// Author: Hugh E. Williams, 2001-3
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

// This script validates customer data entered into details.php
// If validation succeeds, it INSERTs or UPDATEs
// a customer and redirects to a receipt page; if it 
// fails, it creates error messages and these are later 
// displayed by details.php

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/validate.inc";
require_once "../includes/authenticate.inc";

set_error_handler("customHandler");

session_start();

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

// Clear and register an error array - just in case!
$_SESSION["custErrors"] = array();

// Set up a custFormVars array for the POST variables
$_SESSION["custFormVars"] = array();

// Clean and load the POST variables
foreach($_POST as $varname => $value)
   $_SESSION["custFormVars"]["{$varname}"] = 
     pearclean($_POST, $varname, 50, $connection);

// Validate the firstname
checkMandatory("firstname", "first name", "custErrors", "custFormVars"); 

// Validate the Surname
checkMandatory("surname", "surname", "custErrors", "custFormVars");

// Validate the Address
checkMandatory("address", "address", "custErrors", "custFormVars");

// Validate the Initial
if (!empty($_SESSION["custFormVars"]["initial"]) && 
    !eregi("^[[:alpha:]]{1}$", $_SESSION["custFormVars"]["initial"]))
   $_SESSION["custErrors"]["initial"] = "The initial field must be empty or one " .
                                    "alphabetic character in length.";

// Validate the City
checkMandatory("city", "city", "custErrors", "custFormVars");

// Validate Zipcode
if (checkMandatory("zipcode", "Zip code", "custErrors", "custFormVars"))
   checkZipcode("zipcode", "Zip code", "custErrors", "custFormVars");

// Phone is optional, but if it is entered it must have correct format
if (!empty($_SESSION["custFormVars"]["phone"]))
   checkPhone("phone", "telephone", "custErrors", "custFormVars");

// Validate Date of Birth
if (checkMandatory("birth_date", "date of birth", "custErrors", 
              "custFormVars"))
   checkDateAndAdult("birth_date", "date of birth", "custErrors", 
                "custFormVars");

// Only validate email if this is an INSERT
if (!isset($_SESSION["loginUsername"]))
{  
   if (checkMandatory("loginUsername", "email/username", 
                 "custErrors", "custFormVars") &&
       emailCheck("loginUsername", "email/username", 
                  "custErrors", "custFormVars"))
   {
      // Check if the email address is already in use in
      //  the winestore
      $query = "SELECT * FROM users WHERE user_name = 
                '{$_SESSION["custFormVars"]["loginUsername"]}'";

      $result = $connection->query($query);
      if (DB::isError($result))
         trigger_error($result->getMessage(), E_USER_ERROR); 

      if ($result->numRows() == 1)
         $_SESSION["custErrors"]["loginUsername"] = 
            "A customer already exists with this " .
            "email address.";
   }

   // Validate password - between 6 and 8 characters
   if (checkMandatory("loginPassword", "password", 
                 "custErrors", "custFormVars"))
      checkMinAndMaxLength("loginPassword", 6, 8, "password", 
                      "custErrors", "custFormVars");
}

// Now the script has finished the validation, 
// check if there were any errors
if (count($_SESSION["custErrors"]) > 0)
{
    // There are errors.  Relocate back to the client form
    header("Location: " . S_DETAILS);
    exit;
}

// Is this an update?
if (isset($_SESSION["loginUsername"]))
{
   // Check the user is properly logged in
   sessionAuthenticate(S_DETAILS);   

   $cust_id = getCust_id($_SESSION["loginUsername"], $connection);

   $query = "UPDATE customer SET 
             title_id =    {$_SESSION["custFormVars"]["title_id"]},
             surname =     '{$_SESSION["custFormVars"]["surname"]}',
             firstname =   '{$_SESSION["custFormVars"]["firstname"]}',
             initial =     '{$_SESSION["custFormVars"]["initial"]}',
             address =     '{$_SESSION["custFormVars"]["address"]}',
             city =        '{$_SESSION["custFormVars"]["city"]}',
             state =       '{$_SESSION["custFormVars"]["state"]}',
             zipcode =     '{$_SESSION["custFormVars"]["zipcode"]}',
             country_id =  {$_SESSION["custFormVars"]["country_id"]},
             phone =       '{$_SESSION["custFormVars"]["phone"]}',
             birth_date =  '{$_SESSION["custFormVars"]["birth_date"]}'
             WHERE cust_id = {$cust_id}";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 
}
else
{
   // Lock to get the next available customer ID
   $result = $connection->query("LOCK TABLES customer WRITE");
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Find the max cust_id
   $result = $connection->query("SELECT max(cust_id) FROM customer");
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
   // Work out the next available ID
   $cust_id = $row["max(cust_id)"] + 1;

   // Insert the new customer
   $query = "INSERT INTO customer VALUES ({$cust_id}, 
            '{$_SESSION["custFormVars"]["surname"]}', 
            '{$_SESSION["custFormVars"]["firstname"]}',  
            '{$_SESSION["custFormVars"]["initial"]}', 
            {$_SESSION["custFormVars"]["title_id"]}, 
            '{$_SESSION["custFormVars"]["address"]}', 
            '{$_SESSION["custFormVars"]["city"]}', 
            '{$_SESSION["custFormVars"]["state"]}', 
            '{$_SESSION["custFormVars"]["zipcode"]}', 
            {$_SESSION["custFormVars"]["country_id"]}, 
            '{$_SESSION["custFormVars"]["phone"]}', 
            '{$_SESSION["custFormVars"]["birth_date"]}')";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Unlock the customer table
   $result = $connection->query("UNLOCK TABLES");
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // As this was an INSERT, we need to INSERT into the users table too
   newUser($_SESSION["custFormVars"]["loginUsername"], 
           $_SESSION["custFormVars"]["loginPassword"], 
           $cust_id, $connection);

   // Log the user into their new account
   registerLogin($_SESSION["custFormVars"]["loginUsername"]);
}

// Clear the custFormVars so a future form is blank
unset($_SESSION["custFormVars"]);
unset($_SESSION["custErrors"]);

// Now show the customer receipt
header("Location: " . S_CUSTRECEIPT);
?>
