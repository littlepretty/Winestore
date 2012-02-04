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

// This script validates the credit card details, and stores valid details

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";
require_once "../includes/validate.inc";

set_error_handler("customHandler");

session_start();

// Connect to a authenticated session
sessionAuthenticate(S_SHOWCART);

// Check that the cart isn't empty
if (!isset($_SESSION["order_no"]))
{
   $_SESSION["message"] = "Your cart is empty!";

   header("Location: " . S_SHOWCART);
   exit;
}   

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

// Register an error array - just in case!
$_SESSION["ccErrors"] = array();

// Set up a formVars array for the POST variables
$_SESSION["ccFormVars"] = array();

foreach($_POST as $varname => $value)
   $_SESSION["ccFormVars"]["{$varname}"] = 
     pearclean($_POST, $varname, 128, $connection);

// Check if mandatory credit card entered
if (checkMandatory("creditcard", "SurchargeCard", 
              "ccErrors", "ccFormVars"))
   // Validate credit card using Luhn algorithm
   checkCard("creditcard", "ccErrors", "ccFormVars");

// Check if mandatory credit card expiry entered
if (checkMandatory("expirydate", "expiry date", 
              "ccErrors", "ccFormVars"))
   // Validate credit card expiry date
   checkExpiry("expirydate", "ccErrors", "ccFormVars");

// Now the script has finished the validation, 
// check if there were any errors
if (count($_SESSION["ccErrors"]) > 0)
{
    // There are errors.  Relocate back to step #1
    header("Location: " . S_ORDER_1);
    exit;
}
                      
// OK to update the order
$query = "UPDATE orders SET 
          creditcard = '{$_SESSION["ccFormVars"]["creditcard"]}',
          expirydate = '{$_SESSION["ccFormVars"]["expirydate"]}',
          instructions = '{$_SESSION["ccFormVars"]["instructions"]}'
          WHERE cust_id = -1 AND
                order_id = {$_SESSION["order_no"]}";

$result = $connection->query($query);

if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

// Clear the formVars so a future <form> is blank
unset($_SESSION["ccFormVars"]);
unset($_SESSION["ccErrors"]);

// Relocate to the order processing
header("Location: " . S_ORDER_3);
?>
