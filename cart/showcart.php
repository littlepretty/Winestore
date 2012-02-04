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

// This script shows the user the contents of their shopping cart

require_once "DB.php";
require_once "../includes/winestore.inc";
require_once "../includes/template.inc";

set_error_handler("customHandler");

// Show the user the contents of their cart
function displayCart($connection, &$template)
{
   // If the user has added items to their cart, then
   // the variable order_no will be registered
   if (isset($_SESSION["order_no"])) 
   {
      // Set the action of the <form>
      $template->setVariable("S_UPDATECART", S_UPDATECART);

      // Find the items in the cart
      $cartQuery = "SELECT qty, price, wine_id, item_id 
                    FROM items WHERE cust_id = -1
                    AND order_id = {$_SESSION["order_no"]}";
      $result = $connection->query($cartQuery);
      if (DB::isError($result))
         trigger_error($result->getMessage(), E_USER_ERROR); 

      $cartAmount = 0;
      $cartCount = 0;

      // Go through each of the wines in the cart
      while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
      {
         // Keep a running total of the number of items
         // and dollar-value of the items in the cart
         $cartCount += $row["qty"];
         $lineTotal = $row["price"] * $row["qty"];
         $cartAmount += $lineTotal;

         $template->setCurrentBlock("item");
         $template->setVariable("QUANTITY_NAME", $row["item_id"]);
         $template->setVariable("QUANTITY_VALUE", $row["qty"]);
         $template->setVariable("WINE", 
                                showWine($row["wine_id"], $connection));
         $template->setVariable("ITEM_PRICE", 
                                sprintf("%-.2f", $row["price"]));
         $template->setVariable("TOTAL_VALUE", 
                                sprintf("%-.2f", $lineTotal));
         $template->parseCurrentBlock("item");
      }
      $template->setCurrentBlock("cart");
      $template->setVariable("TOTAL_ITEMS", $cartCount);
      $template->setVariable("TOTAL_COST", sprintf("%-.2f", $cartAmount));
      $template->parseCurrentBlock("cart");
   } 
   else
   {
      // The user has not put anything in the cart
      $template->setCurrentBlock("emptycart");
      $template->setVariable("TEXT", "Your cart is empty");
      $template->parseCurrentBlock("emptycart");
   }
}

session_start();    

$template = new winestoreTemplate(T_SHOWCART);

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
  trigger_error($connection->getMessage(), E_USER_ERROR); 

// Show the contents of the shopping cart
displayCart($connection, $template);

$template->showWinestore(SHOW_ALL, B_ALL & ~B_SHOW_CART & 
                         ~B_PASSWORD & ~B_DETAILS);
?>
