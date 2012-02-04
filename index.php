<?php

// Source code example for Web Database Applications with PHP and MySQL, 2nd Edition
// Author: Hugh E. williams, 2001-3
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

// This is the home page of the online winestore
require_once "DB.php";
require_once "includes/winestore.inc";
require_once "includes/template.inc";

set_error_handler("customHandler");

function showPanel($connection, &$template)
{
   // Find the hot new wines
   $query = "SELECT  wi.winery_name, w.year, w.wine_name, w.wine_id, 
                     w.description
             FROM wine w, winery wi, inventory i
             WHERE w.winery_id = wi.winery_id
             AND w.wine_id = i.wine_id
             AND w.description IS NOT NULL
             GROUP BY w.wine_id
             ORDER BY i.date_added DESC LIMIT 3";

   // Run the query on the database through
   // the connection
   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Process the three new wines
   while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
   {
      // Add the wine details to the template
      $template->setCurrentBlock("row");
      $template->setVariable("YEAR", $row["year"]);
      $template->setVariable("WINERY", $row["winery_name"]);
      $template->setVariable("WINE", $row["wine_name"]);
      $template->setVariable("DESCRIPTION", $row["description"]);
      $template->setVariable("VARIETIES",
                             showVarieties($connection, $row["wine_id"]));
      $price = showPricing($connection, $row["wine_id"]);
      $template->setVariable("BOTTLE_PRICE", sprintf("%.2f", $price));
      $template->setVariable("DOZEN_PRICE", sprintf("%.2f", ($price*12)));

      // Add a link to add one wine to the cart
      $template->setCurrentBlock("link");
      $template->setVariable("SCRIPT", S_ADDTOCART);
      $template->setVariable("QTY", "1");
      $template->setVariable("WINE_ID", $row["wine_id"]);
      $template->setVariable("STRING", "Add a bottle to the cart");
      $template->parseCurrentBlock("link");

      // Add a link to add a dozen wines to the cart
      $template->setVariable("SCRIPT", S_ADDTOCART);
      $template->setVariable("QTY", "12");
      $template->setVariable("WINE_ID", $row["wine_id"]);
      $template->setVariable("STRING", "Add a dozen");
      $template->parseCurrentBlock("link");

      $template->setCurrentBlock("row");
      $template->parseCurrentBlock("row");
   }
}

// ---------

session_start();    

$template = new winestoreTemplate(T_HOME);

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

showPanel($connection, $template);

// Add buttons and messages, and show the page
$template->showWinestore(SHOW_ALL, B_ALL & ~B_UPDATE_CART & 
                         ~B_HOME & ~B_PASSWORD & 
                         ~B_PURCHASE & ~B_EMPTY_CART);
?>
