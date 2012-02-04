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

// This script empties the cart and deletes the session variable

require_once "DB.php";
require_once "../includes/winestore.inc";

set_error_handler("customHandler");

// Initialise the session - this is needed before
// a session can be destroyed
session_start();

// Is there a cart in the database?
if (isset($_SESSION["order_no"]))
{
   $connection = DB::connect($dsn, true);
   if (DB::isError($connection))
     trigger_error($connection->getMessage(), E_USER_ERROR); 

   // First, delete the order
   $query = "DELETE FROM orders WHERE cust_id = -1
             AND order_id = {$_SESSION["order_no"]}";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Now, delete the items
   $query = "DELETE FROM items WHERE cust_id = -1
             AND order_id = {$_SESSION["order_no"]}";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Finally, destroy the session variable
   unset($_SESSION["order_no"]);
}
else
   $_SESSION["message"] = "There is nothing in your cart.";

// HTTP_REFERER isn't set by some browsers. If it isn't, then 
// redirect to the main page.
if (isset($_SERVER["HTTP_REFERER"]))
  header("Location: {$_SERVER["HTTP_REFERER"]}");
else 
  header("Location: " . S_MAIN);
?>
