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

// This script updates quantities in the cart
// It expects parameters of the form XXX=YYY
// where XXX is a wine_id and YYY is the new
// quantity of that wine that should be in the
// cart

require_once "DB.php";
require_once "../includes/winestore.inc";

set_error_handler("customHandler");

session_start();    

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

// Clean up the data, and save the results in an array
foreach($_GET as $varname => $value)
   $parameters[$varname] = pearclean($_GET, $varname, 4, $connection);

// Did they want to update the quantities?
// (this should be true except if the user arrives here unexpectedly)
if (empty($parameters["update"]))
{
   $_SESSION["message"] = "Incorrect parameters to " . S_UPDATECART;
   header("Location: " . S_SHOWCART);
   exit;
}      

// If the user has added items to their cart, then
// the session variable order_no will be registered

// Go through each submitted value and update the cart
foreach($parameters as $itemName => $itemValue)
{
   // Ignore the update variable
   if ($itemName != "update")
   {
      // Does this item's name look like a wine_id?
      if (ereg("^[0-9]{1,4}$", $itemName))
      {
         // Is the update value a number?
         if (ereg("^[0-9]{1,3}$", $itemValue))
         {
            // If the number is zero, delete the item
            if ($itemValue == 0)
               $query = "DELETE FROM items WHERE cust_id = -1
                         AND order_id = {$_SESSION["order_no"]}
                         AND item_id = {$itemName}";
            else
              // otherwise, update the value
              $query = "UPDATE items SET qty = {$itemValue}
                        WHERE cust_id = -1
                        AND order_id = {$_SESSION["order_no"]}
                        AND item_id = {$itemName}";
            $result = $connection->query($query);
            if (DB::isError($result))
               trigger_error($result->getMessage(), E_USER_ERROR); 

         } // if (ereg("^[0-9]{1,3}$", $itemValue))
         else
           $_SESSION["message"] = 
             "A quantity is non-numeric or an incorrect length.";
      } // if (ereg("^[0-9]{1,4}$", $itemName))
      else
        $_SESSION["message"] = 
          "A wine identifier is non-numeric or an incorrect length.";
   } // if ($itemName != "update") 
} // foreach($parameters as $itemName => $itemValue)

// The cart may now be empty. Check this.
$query = "SELECT count(*) FROM items WHERE cust_id = -1
          AND order_id = {$_SESSION["order_no"]}";
          
$result = $connection->query($query);

if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

$row = $result->fetchRow(DB_FETCHMODE_ASSOC);

// Are there no items left?
if ($row["count(*)"] == 0)
{
   // Delete the order
   $query = "DELETE FROM orders WHERE cust_id = -1
             AND order_id = {$_SESSION["order_no"]}";         
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   unset($_SESSION["order_no"]);
}

// Go back to the cart
header("Location: " . S_SHOWCART);
?>
