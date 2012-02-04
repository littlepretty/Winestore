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

// Show the login page

require_once "../includes/template.inc";
require_once "../includes/winestore.inc";
require_once "../includes/validate.inc";

set_error_handler("customHandler");

session_start();

// Takes <form> heading, instructions, action, formVars name, and 
// formErrors name as parameters
$template = new winestoreFormTemplate("Login", 
                "Please enter your username and password.",
                S_LOGINCHECK, "loginFormVars", "loginErrors");


$template->mandatoryWidget("loginUsername", "Username/Email:", 50);
$template->passwordWidget("loginPassword", "Password:", 8);

// Add buttons and messages, and show the page
$template->showWinestore(NO_CART, B_HOME);
?>
