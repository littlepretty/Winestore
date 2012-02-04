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

// This script adds an item to the shopping cart
// It expects a WineId of the item to add and a 
// quantity (qty) of the wine to be added

require_once "DB.php";
require_once "../includes/winestore.inc";

set_error_handler("customHandler");

// Have the correct parameters been provided?
if (empty($_GET["wineId"]) || empty($_GET["qty"]))
{
   $_SESSION["message"] = "Incorrect parameters to addtocart.php";
   header("Location: {$_SERVER["HTTP_REFERER"]}");
   exit;
}   

session_start();    

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

$wineId = pearclean($_GET, "wineId", 5, $connection);
$qty = pearclean($_GET, "qty", 3, $connection);

$update = false;

// If the user has added items to their cart, then
// the variable $_SESSION["order_no"] will be registered

// First, decide on which tables to lock
// We don't touch orders if the cart already exists
if (isset($_SESSION["order_no"])) 
   $query = "LOCK TABLES inventory READ, items WRITE";
else
   $query = "LOCK TABLES inventory READ, items WRITE, orders WRITE";

// LOCK the tables
$result = $connection->query($query);
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR);

// Second, create a cart if we don't have one yet
// or investigate the cart if we do
if (!isset($_SESSION["order_no"])) 
{
   // Find out the maximum order_id, then
   // register a session variable for the new order_id
   // A cart is an order for the customer with cust_id = -1
   $query = "SELECT max(order_id) FROM orders WHERE cust_id = -1";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);

   // Save the cart number as order_no
   // This is used in all cart scripts to access the cart
   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
   $_SESSION["order_no"] = $row["max(order_id)"] + 1;

   // Now, create the shopping cart
   $query = "INSERT INTO orders SET cust_id = -1, 
             order_id = {$_SESSION["order_no"]}";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);
         
   // Default the item_id to 1
   $item_id = 1;
}
else
{
   // We already have a cart. Check if the customer already
   // has this item in their cart
   $query = "SELECT item_id, qty FROM items WHERE cust_id = -1
             AND order_id = {$_SESSION["order_no"]} 
             AND wine_id = {$wineId}";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);

   // Is the item in the cart already?
   if ($result->numRows() > 0)
   {
      $update = true;
      $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

      // Save the item number
      $item_id = $row["item_id"];
   }
      
   // If this is not an update, find the next available item_id
   if ($update == false)
   {
      // We already have a cart, find the maximum item_id
      $query = "SELECT max(item_id) FROM items WHERE cust_id = -1
                AND order_id = {$_SESSION["order_no"]}";
      $result = $connection->query($query);
      if (DB::isError($result))
         trigger_error($result->getMessage(), E_USER_ERROR);

      $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

      // Save the item number of the new item
      $item_id = $row["max(item_id)"] + 1;
   }   
}
   
// Third, add the item to the cart or update the cart
if ($update == false)
{
   // Get the cost of the wine
   // The cost comes from the cheapest inventory
   $query = "SELECT count(*), min(cost) FROM inventory 
             WHERE wine_id = {$wineId}";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   // This wine could have just sold out - check this
   // (this happens if another user buys the last bottle
   //  before this user clicks "add to cart")
   if ($row["count(*)"] == 0)
      // Register the error as a session variable
      // This message will then be displayed back on 
      // page where the user adds wines to their cart
      $_SESSION["message"] = 
        "Sorry! We just sold out of this great wine!";
   else
   { 
      // We still have some of this wine, so save the
      // cheapest available price
      $cost = $row["min(cost)"];
      $query = "INSERT INTO items SET cust_id = -1, 
                order_id = {$_SESSION["order_no"]},
                item_id = {$item_id}, wine_id = {$wineId}, qty = {$qty},
                price = {$cost}";
   }
}
else
   $query = "UPDATE items SET qty = qty + {$qty} 
                          WHERE cust_id = -1
                          AND order_id = {$_SESSION["order_no"]} 
                          AND item_id = {$item_id}";

// Either UPDATE or INSERT the item
// (Only do this if there wasn't an error)
if (empty($_SESSION["message"]))
{
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);
}

$result = $connection->query("UNLOCK TABLES");
if (DB::isError($result))
   trigger_error($result->getMessage(), E_USER_ERROR);

// HTTP_REFERER isn't set by some browsers. If it isn't, then 
// redirect to the main page.
if (isset($_SERVER["HTTP_REFERER"]))
  header("Location: {$_SERVER["HTTP_REFERER"]}");
else 
  header("Location: " . S_MAIN);

?>
