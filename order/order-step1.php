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

// This script allows a user to enter their credit card number
// and delivery instructions.
// The user must be logged in to view it.

require_once "../includes/template.inc";
require_once "../includes/winestore.inc";
require_once "../includes/authenticate.inc";

set_error_handler("customHandler");

session_start();    

// Check the user is properly logged in
sessionAuthenticate(S_SHOWCART);

// Takes form heading, instructions, action, formVars name, and 
// formErrors name as parameters
$template = new winestoreFormTemplate("Finalise Your Order", 
            "Please enter your SurchargeCard details " . 
            "(Try: 8000000000001001 ) and delivery instructions.",
            S_ORDER_2, "ccFormVars", "ccErrors");

// Create the credit card widgets
$template->mandatoryWidget("creditcard", "SurchargeCard:", 16);
$template->mandatoryWidget("expirydate", "Expiry Date (mm/yy):", 5);
$template->optionalWidget("instructions", "Delivery Instructions:", 128);

$template->showWinestore(SHOW_ALL, B_SHOW_CART | B_HOME);
?>
