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

require_once "winestore.inc";

// Back trace an error
function backTrace($context)
{
   $calls = "";

   // Get a backtrace of the function calls
   $trace = debug_backtrace();

   $calls = "\nBacktrace:";

   // Start at 2 -- ignore this function (0) and the customHandler() (1)
   for($x=2; $x < count($trace); $x++)
   {
     $callNo = $x - 2;
     $calls .= "\n  {$callNo}: {$trace[$x]["function"]} ";
     $calls .= "(line {$trace[$x]["line"]} in {$trace[$x]["file"]})";
   }

   $calls .= "\nVariables in {$trace[2]["function"]} ():";

   // Use the $context to get variable information for the function
   // with the error
   foreach($context as $name => $value)
   {
     if (!empty($value))
       $calls .= "\n  {$name} is {$value}";
     else
       $calls .= "\n  {$name} is NULL";
   }
   return ($calls);
}

// Custom error handler function -- reproduced from Chapter 12
function customHandler($number, $string, $file, $line, $context)
{
  $error = "";

  switch ($number)
  {
     case E_USER_ERROR:
       $error .= "\nERROR on line {$line} in {$file}.\n";
       $stop = true;
       break;
     case E_WARNING:
     case E_USER_WARNING:
       $error .= "\nWARNING on line {$line} in {$file}.\n";
       $stop = false;
       break;
     case E_NOTICE:
     case E_USER_NOTICE:
       $error .= "\nNOTICE on line {$line} in {$file}.\n";
       $stop = false;
       break;
     default:
       $error .= "UNHANDLED ERROR on line {$line} in {$file}.\n";
       $stop = false;
  }
  $error .= "Error: \"{$string}\" (error #{$number}).";
  $error .= backTrace($context);
  $error .= "\nClient IP: {$_SERVER["REMOTE_ADDR"]}";

  $prepend = "\n[PHP Error " . date("YmdHis") . "]";
  $error = ereg_replace("\n", $prepend, $error);

  if (SCREEN_ERRORS)
     print "<pre>{$error}</pre>";

  if (FILE_ERRORS)
     error_log($error, 3, ERROR_FILE);

  if ($stop == true)
  {
    if (isset($_SESSION["order_no"]))
       unset($_SESSION["order_no"]);

   if (isset($_SESSION["loginUsername"]))
      unset($_SESSION["loginUsername"]);

   if (isset($_SESSION["loginIP"]))
      unset($_SESSION["loginIP"]);

    die(); 
  }
}

?>
