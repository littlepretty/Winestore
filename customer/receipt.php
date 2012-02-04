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

// This script shows the user a receipt for their customer
// UPDATE or INSERT. It carries out no database writes and
// can be bookmarked.
// The user must be logged in to view it.

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";
require_once "../includes/template.inc";

set_error_handler("customHandler");

// Show the user a customer INSERT or UPDATE receipt
function show_HTML_receipt($cust_id, $connection, &$template)
{
  // Retrieve the customer details
  $query = "SELECT * FROM customer WHERE cust_id = {$cust_id}";
  $result = $connection->query($query);
  if (DB::isError($result))
     trigger_error($result->getMessage(), E_USER_ERROR); 
  $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

  // Is there an optional phone field? If so, add it to the output
  if (!empty($row["phone"]))
  {
     $template->setCurrentBlock("phone");
     $template->setVariable("PHONE", $row["phone"]);
     $template->parseCurrentBlock("address");
  }

  // Now, add all the mandatory fields to the output
  $template->setCurrentBlock();
  $template->setVariable("EMAIL", $_SESSION["loginUsername"]);
  $template->setVariable("FIRSTNAME", $row["firstname"]);
  $template->setVariable("SURNAME", $row["surname"]);
  $template->setVariable("INITIAL", $row["initial"]);
  $template->setVariable("ADDRESS", $row["address"]);
  $template->setVariable("CITY", $row["city"]);
  $template->setVariable("STATE", $row["state"]);
  $template->setVariable("ZIPCODE", $row["zipcode"]);
  $template->setVariable("DOB", $row["birth_date"]);
  $template->setVariable("CUSTTITLE", showTitle($row["title_id"],
                         $connection));
  $template->setVariable("COUNTRY", showCountry($row["country_id"],
                         $connection));
  
}

// -----

session_start();    

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

// Check the user is properly logged in
sessionAuthenticate(S_MAIN);

// Find out the cust_id of the user
$cust_id = getCust_id($_SESSION["loginUsername"]);

// Start a new page
$template = new winestoreTemplate(T_CUSTRECEIPT);

// Show the customer confirmation 
show_HTML_receipt($cust_id, $connection, $template);

// Add buttons and messages, and show the page
$template->showWinestore(NO_CART, B_HOME);
?>
