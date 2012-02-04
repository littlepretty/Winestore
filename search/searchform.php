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

// This is the script that allows the to search and browse wines, and
// to select wines to add to their shopping cart

require_once "../includes/template.inc";
require_once "../includes/winestore.inc";

set_error_handler("customHandler");

session_start();    

// Takes <form> heading, instructions, action, formVars name, and 
// formErrors name as parameters
$template = new winestoreFormTemplate("Search", 
                "Choose regions and wine types to browse.", 
                S_SEARCH, "searchFormVars", NULL, "GET");

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

// Create the drop-down search widgets for the page

// Load the regions from the region table
$regionResult = $connection->query("SELECT * FROM region");
if (DB::isError($regionResult))
   trigger_error($regionResult->getMessage(), E_USER_ERROR); 

// Load the wine types from the wine_type table
$wineTypeResult = $connection->query("SELECT * FROM wine_type");
if (DB::isError($wineTypeResult))
   trigger_error($wineTypeResult->getMessage(), E_USER_ERROR); 

$template->selectWidget("region_name", "Region name:", 
                        "region_name", $regionResult);

$template->selectWidget("wine_type", "Wine type:",
                        "wine_type", $wineTypeResult);

$template->showWinestore(NO_CART, B_HOME | B_SHOW_CART | B_LOGINLOGOUT);
?>
