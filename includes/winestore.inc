<?php
require_once 'db.inc';
require_once 'customHandler.inc';

// Choose or adjust one of the following
// NOTE: do not add a trailing slash
// define("D_INSTALL_PATH", "c:/progra~1/easyph~1/www");
// define("D_INSTALL_PATH", "/Library/WebServer/Documents");
//define("D_INSTALL_PATH", "/usr/local/apache2/htdocs");
define("D_INSTALL_PATH","/Applications/MAMP/htdocs");

// Paths -- for these, add trailing slash
define("D_WEB_PATH", "/winestore/");
define("D_CART", D_WEB_PATH . "cart/");
define("D_CARTIMAGES", D_CART . "images/");
define("D_CUSTOMER", D_WEB_PATH . "customer/");
define("D_AUTH", D_WEB_PATH . "auth/");
define("D_ORDER", D_WEB_PATH . "order/");
define("D_SEARCH", D_WEB_PATH . "search/");
define("D_TEMPLATES", D_INSTALL_PATH . D_WEB_PATH . "templates/");

// No slash at beginning
// S - scripts
define("S_MAIN", D_WEB_PATH . "index.php");
define("S_ADDTOCART", D_CART . "addtocart.php");
define("S_EMPTYCART", D_CART . "emptycart.php");
define("S_SHOWCART", D_CART . "showcart.php");
define("S_UPDATECART", D_CART . "updatecart.php");
define("S_ORDER_1", D_ORDER . "order-step1.php");
define("S_ORDER_2", D_ORDER . "order-step2.php");
define("S_ORDER_3", D_ORDER . "order-step3.php");
define("S_ORDER_4", D_ORDER . "order-step4.php");
define("S_ORDERRECEIPT", D_ORDER . "receipt.php");
define("S_SEARCH", D_SEARCH . "search.php");
define("S_SEARCHFORM", D_SEARCH . "searchform.php");
define("S_DETAILS", D_CUSTOMER . "details.php");
define("S_VALIDATE", D_CUSTOMER . "validate.php");
define("S_CUSTRECEIPT", D_CUSTOMER . "receipt.php");
define("S_LOGOUT", D_AUTH . "logout.php");
define("S_LOGIN", D_AUTH . "login.php");
define("S_LOGINCHECK", D_AUTH . "logincheck.php");
define("S_PASSWORD", D_AUTH . "password.php");
define("S_CHANGEPASSWORD", D_AUTH . "changepassword.php");
define("S_PASSWORDRECEIPT", D_AUTH . "receipt.php");

// T - templates
define("T_SKELETON", "winestore.tpl");
define("T_HOME", "index.tpl");
define("T_SHOWCART", "showcart.tpl");
define("T_DETAILS", "details.tpl");
define("T_CUSTRECEIPT", "custreceipt.tpl");
define("T_LOGIN", "login.tpl");
define("T_PASSWORD", "password.tpl");
define("T_PASSWORDRECEIPT", "passwordreceipt.tpl");
define("T_EMAIL", "email.tpl");
define("T_ORDERRECEIPT", "orderreceipt.tpl");
define("T_SEARCH", "search.tpl");
define("T_SOURCE", "source.tpl");

// I - images
define("I_CART_OFF", D_CARTIMAGES . "cart_off.jpg");
define("I_CART_ON", D_CARTIMAGES . "cart_on.jpg");

// B - Buttons
define("B_EMPTY_CART", 1);
define("B_SHOW_CART", 2);
define("B_UPDATE_CART", 4);
define("B_PURCHASE", 8);
define("B_SEARCH", 16);
define("B_HOME", 32);
define("B_DETAILS", 64);
define("B_LOGINLOGOUT", 128);
define("B_PASSWORD", 256);
define("B_ALL", 511);

// Show the cart icon?
define("NO_CART", 1);
define("SHOW_ALL", 2);

// Search rows per page
define("SEARCH_ROWS", 12);

// Custom error handler controls
// File to log errors to
define("ERROR_FILE", "/tmp/php_error_log");

// Save errors to a file?
define("FILE_ERRORS", false);

// Show errors to the screen?
define("SCREEN_ERRORS", true);

// The database connection string
$dsn = "mysql://{$username}:{$password}@{$hostname}/{$databasename}";

// Untaint user data
function pearclean($array, $index, $maxlength, $connection)
{
  if (isset($array["{$index}"]))
  {
    $input = trim(substr($array["{$index}"], 0, $maxlength));
    $input = mysql_real_escape_string($input);
    return ($input);
  }
  return NULL;
}

// Find the cust_id using the user_name
function getCust_id($user_name, $connection = null)
{
   global $dsn;

   // If a connection parameter is not passed, then 
   // use our own connection
   if (!isset($connection))
   {
      $connection = DB::connect($dsn, false);
      if (DB::isError($connection))
        trigger_error($connection->getMessage(), E_USER_ERROR); 
   }

   $query = "SELECT cust_id FROM users WHERE 
             user_name = '{$user_name}'";

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   return($row["cust_id"]);
}

// Show the user the details of one wine in their cart
function showWine($wineId, $connection = null)
{
   global $dsn;

   $wineQuery = "SELECT year, winery_name, wine_name
                 FROM winery, wine
                 WHERE wine.winery_id = winery.winery_id
                 AND wine.wine_id = {$wineId}";  

   // If a connection parameter is not passed, then 
   // use our own connection to avoid any locking problems
   if (!isset($connection))
   {
      $connection = DB::connect($dsn, false);
      if (DB::isError($connection))
        trigger_error($connection->getMessage(), E_USER_ERROR); 
   }

   $result = $connection->query($wineQuery);

   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   // Print the wine details
   $output = "{$row["year"]} {$row["winery_name"]} {$row["wine_name"]}";

   // Print the varieties for this wine
   $output .= showVarieties($connection, $wineId);     

   return $output;
}

  
// Find the varieties for a wineID
function showVarieties($connection, $wineID)
{
   // Find the varieties of the current wine,
   // and order them by id
   $query = "SELECT gv.variety
             FROM grape_variety gv, wine_variety wv, wine w
             WHERE w.wine_id = wv.wine_id
             AND wv.variety_id = gv.variety_id
             AND w.wine_id = {$wineID}
             ORDER BY wv.id";

   $result = $connection->query($query);

   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $varieties = "";

   // Retrieve and print the varieties
   while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
      $varieties .= " {$row["variety"]}";

   return $varieties;
}

// Find the cheapest bottle price for a wineID
function showPricing($connection, $wineID)
{
   // Find the price of the cheapest inventory
   $query = "SELECT min(cost) FROM inventory
             WHERE wine_id = {$wineID}";

   $result = $connection->query($query);

   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   // Retrieve the oldest price
   $row = $result->fetchRow(DB_FETCHMODE_ASSOC);

   $price = $row["min(cost)"];

   return $price;
}

// Lookup the country_id in the countries lookup table
// and return the country name
function showCountry($country_id, $connection)
{
  $query = "SELECT country FROM countries WHERE 
            country_id = {$country_id}";

  $result = $connection->query($query);

  if (DB::isError($result))
     trigger_error($result->getMessage(), E_USER_ERROR); 

  $countryRow = $result->fetchRow(DB_FETCHMODE_ASSOC);

  return($countryRow["country"]);
}

// Lookup the title in the titles lookup table
// and return the title string
function showTitle($title_id, $connection)
{
  $query = "SELECT title FROM titles WHERE 
            title_id = {$title_id}";

  $result = $connection->query($query);

  if (DB::isError($result))
     trigger_error($result->getMessage(), E_USER_ERROR); 

  $titleRow = $result->fetchRow(DB_FETCHMODE_ASSOC);

  return($titleRow["title"]);
}

?>
