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

// This script finalises a purchase
// It expects that a cart has contents and that the
// user is logged in

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";

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

// Several tables must be locked to finalise a purchase.
$query = "LOCK TABLES inventory WRITE, 
                      orders WRITE, 
                      items WRITE, 
                      users READ, 
                      customer READ";

$result = $connection->query($query);
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

// Process each wine in the cart and find out if there is sufficient 
// stock available in the inventory

$query = "SELECT * FROM items 
          WHERE cust_id = -1 
          AND order_id = {$_SESSION["order_no"]}";

// Initialise an empty error message
$_SESSION["message"] = "";

$result = $connection->query($query);
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

// Get the next wine in the cart         
for ($winesInCart = 0; 
     $winesInCart < $result->numRows(); 
     $winesInCart++)
{
   $cartRow[$winesInCart] = $result->fetchRow(DB_FETCHMODE_ASSOC);  

   // Is there enough of this wine on hand?
   $query = "SELECT COUNT(on_hand), SUM(on_hand)
             FROM inventory 
             WHERE wine_id = {$cartRow[$winesInCart]["wine_id"]}";

   $stockResult = $connection->query($query);
   if (DB::isError($stockResult))
      trigger_error($stockResult->getMessage(), E_USER_ERROR); 
                                    
   $on_hand = $stockResult->fetchRow(DB_FETCHMODE_ASSOC);

   if ($on_hand["COUNT(on_hand)"] == 0)
      $available = 0;
   else
      $available = $on_hand["SUM(on_hand)"];
                   
   // Is there more wine in the cart than is for sale?
   if ($cartRow[$winesInCart]["qty"] > $available)
   {

      if ($available == 0)
         $_SESSION["message"] = "Sorry! We just sold out of " . 
                   showWine($cartRow[$winesInCart]["wine_id"], NULL) .
                   "\n<br>";
      else 
         $_SESSION["message"] .= "Sorry! We only have {$on_hand["SUM(on_hand)"]}  
                     bottles left of " . 
                     showWine($cartRow[$winesInCart]["wine_id"], NULL) .
                     "\n<br>";

      // Update the user's quantity to match the available amount
      $query = "UPDATE items
                SET qty = {$available}
                WHERE cust_id = -1
                AND order_id = {$_SESSION["order_no"]}
                AND item_id = {$cartRow[$winesInCart]["item_id"]}";

      $result = $connection->query($query);
      if (DB::isError($result))
         trigger_error($result->getMessage(), E_USER_ERROR); 
   }                                                           
} // for $winesInCart < $result->numRows()

// We have now checked if there is enough wine available.
// If there is, we can proceed with the order. If not, we
// send the user back to the amended cart to consider whether
// to proceed with the order.

if (empty($_SESSION["message"]))
{
   // Everything is ok - let's proceed then!         

   // First of all, find out the user's cust_id and
   // the next available order_id for this customer.
   $cust_id = getCust_id($_SESSION["loginUsername"], $connection);

   $query = "SELECT max(order_id) 
             FROM orders 
             WHERE cust_id = {$cust_id}";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   $newOrder_no = $row["max(order_id)"] + 1;
   
   // Now, change the cust_id and order_id of their cart!
   $query = "UPDATE orders SET
             cust_id = {$cust_id},
             order_id = {$newOrder_no}
             WHERE order_id = {$_SESSION["order_no"]}
             AND cust_id = -1";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $query = "UPDATE items SET
             cust_id = {$cust_id},
             order_id = {$newOrder_no}
             WHERE order_id = {$_SESSION["order_no"]}
             AND cust_id = -1";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Empty the cart
   unset($_SESSION["order_no"]);

   // Now we have to update the inventory. 
   // We do this one cart item at a time.
   // For all items, we know that there *is*
   // sufficient inventory, since we've checked earlier
   foreach($cartRow as $currentRow)
   {
      // Find the inventory rows for this wine, oldest first
      $query = "SELECT inventory_id, on_hand
                FROM inventory
                WHERE wine_id = {$currentRow["wine_id"]}
                ORDER BY date_added";

      $result = $connection->query($query);
      if (DB::isError($result))
         trigger_error($result->getMessage(), E_USER_ERROR); 

      // While there are still bottles to be deducted
      while($currentRow["qty"] > 0)
      {
         // Get the next-oldest inventory
         $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

         // Is there more wine in this inventory than the user wants?
         if ($row["on_hand"] > $currentRow["qty"])
         {
            // Reduce the inventory by the amount the user ordered
            $query = "UPDATE inventory SET 
                      on_hand = on_hand - {$currentRow["qty"]}
                     WHERE wine_id = {$currentRow["wine_id"]}
                     AND inventory_id = {$row["inventory_id"]}";

            // The user doesn't need any more of this wine
            $currentRow["qty"] = 0;
         }
         else
         {
            // Remove the inventory - we sold the remainder to
            // this user
            $query = "DELETE FROM inventory 
                      WHERE wine_id = {$currentRow["wine_id"]}
                      AND inventory_id = {$row["inventory_id"]}";

            // This inventory reduces the customer's required 
            // amount by at least 1, but we need to process more 
            // inventory
            $currentRow["qty"] -= $row["on_hand"];
         }   

         // UPDATE or DELETE the inventory
         $result = $connection->query($query);
         if (DB::isError($result))
            trigger_error($result->getMessage(), E_USER_ERROR); 
      }
   }
}
else
   $_SESSION["message"] .= 
     "\n<br>The quantities in your cart have been updated\n.";

// Last, UNLOCK the tables
$result = $connection->query("UNLOCK TABLES");
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR); 

// Redirect to the email confirmation page if everything is ok
// (supply the cust_id and order_id to the script)
// otherwise go back to the cart page and show a message
if (empty($_SESSION["message"]))
{
   header("Location: " . S_ORDER_4 . 
          "?cust_id={$cust_id}&order_id={$newOrder_no}");
   exit;
}
else
   header("Location: " . S_SHOWCART);
?>
