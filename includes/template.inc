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

// ITX template class extensions for the winestore
// -- winestoreTemplate is a generic page
// -- winestoreFormTemplate is a <form> page (and extends winestoreTemplate)

require_once "DB.php";
require_once "HTML/Template/ITX.php";
require_once "winestore.inc";

define("P_TITLE", "Hugh and Dave's Online Wines");

// An extension of HTML_Template_ITX for the winestore pages
class winestoreTemplate extends HTML_Template_ITX
{
   // Class constructor
   // Loads the winestore.tpl skeleton, and a named page
   // Sets the page title
   function winestoreTemplate($pageBody, $pageTitle = P_TITLE)
   {
      $this->template = $this->HTML_Template_ITX(D_TEMPLATES);
      $this->loadTemplatefile(T_SKELETON, true, true);
      $this->setVariable("TITLE", $pageTitle);
      $this->addBlockFile("PAGE_BODY", "pageBody", "{$pageBody}");
   }

   // Completes the page, and outputs with show()
   function showWinestore($options = NO_CART, $buttons = B_HOME)
   {
      $this->setCurrentBlock();

      // Show the user login status
      $this->showLogin();

      if ($options & ~NO_CART)
         // Show the dollar and item total of the cart
         $this->showCart();

      // Display any messages to the user
      $this->showMessage();

      // Set up the buttons
      if ($buttons != 0)
         $this->showButtons($buttons);

      $this->setCurrentBlock();
      $this->parseCurrentBlock();
      $this->show();
   }

   // Show the total number of items and dollar value of the shopping cart,
   // as well as a clickable cart icon
   function showCart()
   {
      global $dsn;

      $connection = DB::connect($dsn, true);

      if (DB::isError($connection))
         trigger_error($connection->getMessage(), E_USER_ERROR); 

      // Initialise an empty cart
      $cartAmount = 0;
      $cartCount = 0;

      // If the user has added items to their cart, then
      // the variable order_no will be registered
      if (isset($_SESSION["order_no"]))
      {
         $cartQuery = "SELECT qty, price FROM items
                       WHERE cust_id = -1 
                       AND order_id = {$_SESSION["order_no"]}";

         // Find out the number and the dollar value of
         // the items in the cart. To do this, we run the
         // cartQuery through the connection on the database         
         $result = $connection->query($cartQuery);

         if (DB::isError($result))
            trigger_error($result->getMessage(), E_USER_ERROR);

         while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
         {
            $cartAmount += $row["price"] * $row["qty"];
            $cartCount += $row["qty"];          
         }
      }

      $this->setCurrentBlock("cartheader");
      $this->setVariable("I_CART_OFF", I_CART_OFF);
      $this->setVariable("I_CART_ON", I_CART_ON);
      $this->setVariable("S_SHOWCART", S_SHOWCART);   
      $this->setVariable("TOTAL", sprintf("%-.2f", $cartAmount));
      $this->setVariable("COUNT", sprintf("%d", $cartCount));
      $this->parseCurrentBlock("cartheader");
   }



   // Display any messages that are set, and then
   // clear the message
   function showMessage()
   {
      // Is there an error message to show the user?
      if (isset($_SESSION["message"]))
      {
         $this->setCurrentBlock("message");
         $this->setVariable("INFO_MESSAGE", $_SESSION["message"]);
         $this->parseCurrentBlock("message");

         // Clear the error message
         unset($_SESSION["message"]);
      }
   }

   // Show whether the user is logged in or not
   function showLogin()
   {
      // Is the user logged in?
      if (isset($_SESSION["loginUsername"]))
         $this->setVariable("LOGIN_STATUS", 
                     "You are currently logged in as {$_SESSION["loginUsername"]}");
      else
         $this->setVariable("LOGIN_STATUS", 
                     "You are currently not logged in");
   }

   // Output the buttons for a winestore page
   function showButtons($buttons)
   {
      $this->setCurrentBlock("buttons");

      // If the cart has contents, offer the opportunity to view the cart
      // or empty the cart. 
      if (isset($_SESSION["order_no"]))
      {
         if ($buttons & B_EMPTY_CART)
         {
            $this->setCurrentBlock("form");
            $this->setVariable("ACTION", S_EMPTYCART);
            $this->setVariable("NAME", "empty");
            $this->setVariable("VALUE", "Empty Cart");
            $this->parseCurrentBlock("form");
         }

         if ($buttons & B_SHOW_CART)
         {
            $this->setCurrentBlock("form");
            $this->setVariable("ACTION", S_SHOWCART);
            $this->setVariable("NAME", "view");
            $this->setVariable("VALUE", "View Cart");
            $this->parseCurrentBlock("form");
         }

         // Must be logged in and have items in cart
         if (($buttons & B_PURCHASE) && 
             isset($_SESSION["loginUsername"]) &&
             isset($_SESSION["order_no"]))
         {
            $this->setCurrentBlock("form");
            $this->setVariable("ACTION", S_ORDER_1);
            $this->setVariable("NAME", "purchase");
            $this->setVariable("VALUE", "Make Purchase");
            $this->parseCurrentBlock("form");
         }
      }

      if ($buttons & B_SEARCH)
      {
         $this->setCurrentBlock("form");
         $this->setVariable("ACTION", S_SEARCHFORM);
         $this->setVariable("NAME", "search");
         $this->setVariable("VALUE", "Search Wines");
         $this->parseCurrentBlock("form");
      }

      if ($buttons & B_HOME)
      {
         $this->setCurrentBlock("form");
         $this->setVariable("ACTION", S_MAIN);
         $this->setVariable("NAME", "home");
         $this->setVariable("VALUE", "Main Page");
         $this->parseCurrentBlock("form");
      }

      if ($buttons & B_DETAILS)
      {
         $this->setCurrentBlock("form");
         $this->setVariable("ACTION", S_DETAILS);
         if (isset($_SESSION["loginUsername"]))
         {
            $this->setVariable("NAME", "account");
            $this->setVariable("VALUE", "Change Details");
         }
         else
         {
            $this->setVariable("NAME", "account");
            $this->setVariable("VALUE", "Become a Member");
         }
         $this->parseCurrentBlock("form");
      }

      if ($buttons & B_LOGINLOGOUT)
      {
         $this->setCurrentBlock("form");
         if (isset($_SESSION["loginUsername"]))
         {
            $this->setVariable("ACTION", S_LOGOUT);
            $this->setVariable("NAME", "logout");
            $this->setVariable("VALUE", "Logout");
         }
         else
         {
            $this->setVariable("ACTION", S_LOGIN);
            $this->setVariable("NAME", "login");
            $this->setVariable("VALUE", "Login");
         }
         $this->parseCurrentBlock("form");
      }   

      if (($buttons & B_PASSWORD) && isset($_SESSION["loginUsername"]))
      {
         $this->setCurrentBlock("form");
         $this->setVariable("ACTION", S_PASSWORD);
         $this->setVariable("NAME", "password");
         $this->setVariable("VALUE", "Change Password");
         $this->parseCurrentBlock("form");
      }

      $this->setCurrentBlock("buttons");
      $this->parseCurrentBlock("buttons");
   }


}


// An extension of winestoreTemplate for pages that contain a form
class winestoreFormTemplate extends winestoreTemplate
{
   // The formVars array associated with this page of widgets
   var $formVars = null;

   // The errors array associated with this page of widgets
   var $formErrors = null;

   // Class constructor
   // Parameters:
   // (1) Heading in <h1> above the <form>
   // (2) Instructions in <b> above the <form>
   // (3) <form action=""> value
   // (4) formVars $_SESSION array name for storing widget values
   // (5) formErrors $_SESSION array name for storing widget errors
   // (6) [optional] form method type
   // (7) [optional] <title> for the page
   function winestoreFormTemplate($formHeading, $instructions, 
                                  $action, $formVars, $formErrors, 
                                  $method = "POST", $pageTitle = P_TITLE)
   {
      $this->template = $this->winestoreTemplate(T_DETAILS, $pageTitle);

      // Set up the <form> headings and target
      $this->setVariable("FORMHEADING", $formHeading);
      $this->setVariable("INSTRUCTIONS", $instructions);
      $this->setVariable("S_VALIDATE", $action);
      $this->setVariable("METHOD", $method);

      // Save formVars and formErrors
      $this->formVars = $formVars;
      $this->formErrors = $formErrors;
   }

   // Produces a mandatory <form> widget
   // Parameters are:
   // (1) The HTML widget name and matching table attribute name
   // (2) The text to show next to the widget
   // (3) The size of the widget
   function mandatoryWidget($name, $text, $size)
   {
      // Are there any errors to show for this widget?
      // If so, show them above the widget
      if (isset($_SESSION["{$this->formErrors}"]["{$name}"]))
      {
         $this->setCurrentBlock("mandatoryerror");
         $this->setVariable("MERRORTEXT", 
           $_SESSION["{$this->formErrors}"]["{$name}"]);
         $this->parseCurrentBlock("mandatoryerror");
      }

      // Setup the widget
      $this->setCurrentBlock("mandatoryinput");
      $this->setVariable("MINPUTNAME", "{$name}");
      if (isset($_SESSION["{$this->formVars}"]["{$name}"]))
         $this->setVariable("MINPUTVALUE", 
           $_SESSION["{$this->formVars}"]["{$name}"]);
      $this->setVariable("MINPUTTEXT", "{$text}");
      $this->setVariable("MINPUTSIZE", $size);
      $this->parseCurrentBlock("mandatoryinput");
      $this->setCurrentBlock("widget");
      $this->parseCurrentBlock("widget");
   }

   // Produces an optional <form> widget
   // Parameters are:
   // (1) The HTML widget name and matching table attribute name
   // (2) The text to show next to the widget
   // (3) The size of the widget
   function optionalWidget($name, $text, $size)
   {
      // Are there any errors to show for this widget?
      // If so, show them above the widget
      if (isset($_SESSION["{$this->formErrors}"]["{$name}"]))
      {
         $this->setCurrentBlock("optionalerror");
         $this->setVariable("OERRORTEXT", 
           $_SESSION["{$this->formErrors}"]["{$name}"]);
         $this->parseCurrentBlock("optionalerror");
      }

      // Setup the widget
      $this->setCurrentBlock("optionalinput");
      $this->setVariable("OINPUTNAME", "{$name}");
      if (isset($_SESSION["{$this->formVars}"]["{$name}"]))
         $this->setVariable("OINPUTVALUE", 
           $_SESSION["{$this->formVars}"]["{$name}"]);
      $this->setVariable("OINPUTTEXT", "{$text}");
      $this->setVariable("OINPUTSIZE", $size);
      $this->parseCurrentBlock("optionalinput");
      $this->setCurrentBlock("widget");
      $this->parseCurrentBlock("widget");
   }

   // Produces a password <form> widget
   // Parameters are:
   // (1) The HTML widget name and matching table attribute name
   // (2) The text to show next to the widget
   // (3) The size of the widget
   function passwordWidget($name, $text, $size)
   {
      // Are there any errors to show for this widget?
      // If so, show them above the widget
      if (isset($_SESSION["{$this->formErrors}"]["{$name}"]))
      {
         $this->setCurrentBlock("passworderror");
         $this->setVariable("PERRORTEXT", 
           $_SESSION["{$this->formErrors}"]["{$name}"]);
         $this->parseCurrentBlock("passworderror");
      }

      // Setup the widget
      $this->setCurrentBlock("passwordinput");
      $this->setVariable("PINPUTNAME", "{$name}");
      if (isset($_SESSION["{$this->formVars}"]["{$name}"]))
         $this->setVariable("PINPUTVALUE", 
           $_SESSION["{$this->formVars}"]["{$name}"]);
      $this->setVariable("PINPUTTEXT", "{$text}");
      $this->setVariable("PINPUTSIZE", $size);
      $this->parseCurrentBlock("passwordinput");
      $this->setCurrentBlock("widget");
      $this->parseCurrentBlock("widget");
   }

   // Produces a <select> <form> widget.
   // Unlike others, this doesn't support error display
   // Parameters are:
   // (1) The table attribute that fills the <option> value. Also used as
   //     the <select> name 
   // (2) The text to show next to the widget
   // (3) The table attribute that is displayed with the <option>
   // (3) The PEAR DB Result to get the data from
   function selectWidget($name, $text, $optiontext, $data)
   {
      while ($row = $data->fetchRow(DB_FETCHMODE_ASSOC))
      {
         $this->setCurrentBlock("option");
         $this->setVariable("OPTIONTEXT", $row["{$optiontext}"]);
         $this->setVariable("OPTIONVALUE", $row["{$name}"]);
         if (isset($_SESSION["{$this->formVars}"]["{$name}"]) && 
             $_SESSION["{$this->formVars}"]["{$name}"] == $row["{$name}"])
            $this->setVariable("SELECTED", " selected");  
         $this->parseCurrentBlock("option");
      }
      $this->setCurrentBlock("select");
      $this->setVariable("SELECTNAME", "{$name}");
      $this->setVariable("SELECTTEXT", "{$text}");
      $this->parseCurrentBlock("select"); 
      $this->setCurrentBlock("widget");
      $this->parseCurrentBlock("widget"); 
   }
}
?>
