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

// This script sends the user a confirmation email for their order
// and then redirects to an HTML receipt version

// By default, this script uses PEAR's Mail package.
// To use PHP's internal mail() function instead, change "true" to "false"
// in the following line
define("USE_PEAR", false);

require_once "DB.php";
require_once "HTML/Template/ITX.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";

// Use the PEAR Mail package if USE_PEAR is defined
if (USE_PEAR == true)
  require_once "Mail.php";

set_error_handler("customHandler");

// Send the user an email that summarises their purchase
function send_confirmation_email($custID, $orderID, $connection)
{
   $template = new HTML_Template_ITX(D_TEMPLATES);
   $template->loadTemplatefile(T_EMAIL, true, true);

   // Find customer information
   $query = "SELECT * FROM customer, users
            WHERE customer.cust_id = {$custID}
            AND users.cust_id = customer.cust_id";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 
   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   // Start by setting up the "To:" email address
   $to = "{$row["firstname"]} {$row["surname"]} <{$row["user_name"]}>";

   // Now setup all the customer fields
   $template->setVariable("TITLE", showTitle($row["title_id"], $connection));
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

   // Add each item to the email
   while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) 
   { 
     // Work out the cost of this line item
     $itemsPrice = $row["qty"] * $row["price"];

     $orderTotalPrice += $itemsPrice;
  
     $wineDetail = showWine($row["wine_id"], $connection);

     $template->setCurrentBlock("row");
     $template->setVariable("QTY", str_pad($row["qty"],9));
     $template->setVariable("WINE", 
                             str_pad(substr($wineDetail, 0, 53), 55));
     $template->setVariable("PRICE", 
                            str_pad(sprintf("$%4.2f" , 
                            $row["price"]), 11));
     $template->setVariable("TOTAL", 
                            str_pad(sprintf("$%4.2f", $itemsPrice), 12));
     $template->parseCurrentBlock("row");
   }

   $template->setCurrentBlock("items");
   $template->setVariable("ORDER_TOTAL", 
                          sprintf("$%4.2f\n", $orderTotalPrice));
   $template->parseCurrentBlock("items");
   $template->setCurrentBlock();
   $template->parseCurrentBlock();

   $out = $template->get();


   if (USE_PEAR == false)
   {
     // --------------------------------------------
     // The internal PHP mail() function is used only if USE_PEAR is false

     // Now, setup the "Subject:" line
     $subject = "Hugh and Dave's Online Wines: Order Confirmation";

     // And, last (before we build the email), set up some mail headers
     $headers  = "From: Hugh and Dave's Online Wines " . 
                 "<help@webdatabasebook.com>\r\n";
     $headers .= "X-Sender: <help@webdatabasebook.com>\r\n"; 
     $headers .= "X-Mailer: PHP\r\n"; 
     $headers .= "Return-Path: <help@webdatabasebook.com>\r\n";

     // Send the email!
     mail($to, $subject, $out, $headers);      
     // --------------------------------------------
   }
   else 
   {
     // --------------------------------------------
     // Use the PEAR Mail package and SMTP since USE_PEAR is true

     // Now, setup the "Subject:" line
     $headers["Subject"] = "Hugh and Dave's Online Wines: Order Confirmation";

     // And, last (before we build the email), set up some mail headers
     $headers["From"] = "Hugh and Dave's Online Wines " . 
                        "<help@webdatabasebook.com>";
     $headers["X-Sender"] = "<help@webdatabasebook.com>";
     $headers["X-Mailer"] = "PHP"; 
     $headers["Return-Path"] = "<help@webdatabasebook.com>";

     $smtpMail =& Mail::factory("smtp");
     $smtpMail->send($to, $headers, $out);
     // --------------------------------------------
   }
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

// Send the user a confirmation email
send_confirmation_email($cust_id, $order_id, $connection);

// Redirect to a receipt page (this can't be the receipt page,
// since the reload problem would cause extra emails).
header("Location: " . S_ORDERRECEIPT . 
       "?cust_id={$cust_id}&order_id={$order_id}");
?>
