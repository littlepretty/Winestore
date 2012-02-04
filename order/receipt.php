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

// This script shows the user an HTML receipt

require_once "DB.php";
require_once "../includes/template.inc";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";

set_error_handler("customHandler");

function show_HTML_receipt($custID, $orderID, $connection)
{
   $template = new winestoreTemplate(T_ORDERRECEIPT);

   // Find customer information
   $query = "SELECT * FROM customer, users
             WHERE customer.cust_id = {$custID}
             AND users.cust_id = customer.cust_id";
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   // Now setup all the customer fields
   $template->setVariable("CUSTTITLE", showTitle($row["title_id"], $connection));
   $template->setVariable("SURNAME", $row["surname"]);
   $template->setVariable("CUST_ID", $custID);
   $template->setVariable("ORDER_ID", $orderID);
   $template->setVariable("FIRSTNAME", $row["firstname"]);
   $template->setVariable("INITIAL", $row["initial"]);
   $template->setVariable("ADDRESS", $row["address"]);
   $template->setVariable("CITY", $row["city"]);
   $template->setVariable("STATE", $row["state"]);
   $template->setVariable("COUNTRY", showCountry($row["country_id"], $connection));
   $template->setVariable("ZIPCODE", $row["zipcode"]);
   
   $orderTotalPrice = 0;

   // list the particulars of each item in the order
   $query = "SELECT  i.qty, w.wine_name, i.price, 
                     w.wine_id, w.year, wi.winery_name
             FROM    items i, wine w, winery wi
             WHERE   i.cust_id = {$custID}
             AND     i.order_id = {$orderID}
             AND     i.wine_id = w.wine_id
             AND     w.winery_id = wi.winery_id
             ORDER BY item_id";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR);

   // Add each item to the page
   while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) 
   { 
     // Work out the cost of this line item
     $itemsPrice = $row["qty"] * $row["price"];

     $orderTotalPrice += $itemsPrice;
  
     $wineDetail = showWine($row["wine_id"], $connection);

     $template->setCurrentBlock("row");
     $template->setVariable("QTY", $row["qty"]);
     $template->setVariable("WINE", $wineDetail);
     $template->setVariable("PRICE", 
                            sprintf("$%4.2f" , $row["price"]), 11);
     $template->setVariable("TOTAL", sprintf("$%4.2f", $itemsPrice));
     $template->parseCurrentBlock("row");
   }

   $template->setCurrentBlock("items");
   $template->setVariable("ORDER_TOTAL", 
                          sprintf("$%4.2f\n", $orderTotalPrice));
   $template->parseCurrentBlock("items");
   $template->setCurrentBlock();

   $template->showWinestore(NO_CART, B_HOME);
}

// ----------

session_start();

// Connect to a authenticated session
sessionAuthenticate(S_SHOWCART);

// Check the correct parameters have been passed
if (!isset($_GET["cust_id"]) || !isset($_GET["order_id"]))
{
   $_SESSION["message"] = 
     "Incorrect parameters to order-step4.php";
   header("Location: " . S_SHOWCART);
   exit;
}      

// Check this customer matches the $cust_id
$connection = DB::connect($dsn, true);

if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

$cust_id = pearclean($_GET, "cust_id", 5, $connection);
$order_id = pearclean($_GET, "order_id", 5, $connection);

$real_cust_id = getCust_id($_SESSION["loginUsername"]);

if ($cust_id != $real_cust_id)
{
   $_SESSION["message"] = "You can only view your own receipts!";
   header("Location: " . S_HOME);
   exit;
}

// Show the confirmation HTML page
show_HTML_receipt($cust_id, $order_id, $connection);
?>
