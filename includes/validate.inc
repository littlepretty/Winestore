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

// General-purpose validation functions

// Test if a mandatory field is empty
function checkMandatory($field, $errorString, $errors, $formVars)
{
   if (!isset($_SESSION["{$formVars}"]["{$field}"]) || 
       empty($_SESSION["{$formVars}"]["{$field}"])) 
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The {$errorString} field cannot be blank.";
      return false;
   }
   return true;
}

// Test if a field is less than a min or greater than a max length
function checkMinAndMaxLength($field, $minlength, $maxlength, 
                         $errorString, $errors, $formVars)
{
   if (isset($_SESSION["{$formVars}"]["{$field}"]) && 
      (strlen($_SESSION["{$formVars}"]["{$field}"]) < $minlength ||
       strlen($_SESSION["{$formVars}"]["{$field}"]) > $maxlength))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The {$errorString} field must be greater than or equal to" . 
         "{$minlength} and less than or equal to {$maxlength} " .
         "characters in length.";
      return false;
   }
   return true;
}

// Simple zipcode validator -- there's a better one in Chapter 9!
function checkZipcode($field, $errorString, $errors, $formVars)
{
   if (isset($_SESSION["{$formVars}"]["{$field}"]) && 
       !ereg("^([0-9]{4,5})$", $_SESSION["{$formVars}"]["{$field}"]))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The zipcode must be 4 or 5 digits in length";
      return false;
   }
   return true;
}

// Check a phone number
function checkPhone($field, $errorString, $errors, $formVars)
{
   $validPhoneExpr = "^([0-9]{2,3}[ ]?)?[0-9]{4}[ ]?[0-9]{4}$";

   if (isset($_SESSION["{$formVars}"]["{$field}"]) && 
       !ereg($validPhoneExpr, $_SESSION["{$formVars}"]["{$field}"]))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The {$field} field must be 8 digits in length, " .
         "with an optional 2 or 3 digit area code";
      return false;
   }
   return true;
}

// Check a birth date and that the user is 18+ years
function checkDateAndAdult($field, $errorString, $errors, $formVars)
{
   if (!ereg("^([0-9]{2})/([0-9]{2})/([0-9]{4})$", 
             $_SESSION["{$formVars}"]["{$field}"], $parts))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The date of birth is not a valid date " . 
         "in the format DD/MM/YYYY";
      return false;
   }

   if (!checkdate($parts[2],$parts[1],$parts[3]))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The date of birth is invalid. Please " .
         "check that the month is between 1 and 12, " .
         "and the day is valid for that month.";
      return false;
   }

   if (intval($parts[3]) < 1902 || 
       intval($parts[3]) > intval(date("Y")))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "You must be alive to use this service.";
      return false;
   }
   
   $dob = mktime(0, 0, 0, $parts[2], $parts[1], $parts[3]);

   // Check whether the user is 18 years old 
   // See Chapter 9 for an MS Windows version
   if ((float)$dob > (float)strtotime("-18years"))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "You must be 18+ years of age to use this service";
      return false;
   }
   return true;
}

// Check an email address
function emailCheck($field, $errorString, $errors, $formVars)
{
   // Check syntax
   $validEmailExpr =  "^[0-9a-z~!#$%&_-]([.]?[0-9a-z~!#$%&_-])*" .
                      "@[0-9a-z~!#$%&_-]([.]?[0-9a-z~!#$%&_-])*$";

   if (!eregi($validEmailExpr, $_SESSION["{$formVars}"]["{$field}"]))
   {
      $_SESSION["{$errors}"]["{$field}"] =  
         "The email must be in the name@domain format.";
      return false;
   }

   // See Chapter 7 for an MS Windows version
   if (function_exists("getmxrr") && 
       function_exists("gethostbyname"))
   {
     // Extract the domain of the email address
     $maildomain = 
        substr(strstr($_SESSION["{$formVars}"]["{$field}"], '@'), 1);

     if (!(getmxrr($maildomain, $temp) || 
           gethostbyname($maildomain) != $maildomain))
     {
       $_SESSION["{$errors}"]["{$field}"] = 
          "The email domain does not exist.";
       return false;
     }
   }

   return true;
}

// Check a credit card using Luhn's algorithm
function checkCard($field, $errors, $formVars)
{
  if (!ereg("^[0-9 ]*$", $_SESSION["{$formVars}"]["{$field}"]))
  {
    $_SESSION["{$errors}"]["{$field}"] = 
      "Card number must contain only digits and spaces.";
    return false;
  }

  // Remove spaces
  $_SESSION["{$formVars}"]["{$field}"] = ereg_replace('[ ]', '', $_SESSION["{$formVars}"]["{$field}"]);

  // Check first four digits
  $firstFour = intval(substr($_SESSION["{$formVars}"]["{$field}"], 0, 4));
  $type = "";
  $length = 0;

  if ($firstFour >= 8000 && $firstFour <= 8999)
  {
    // Try: 8000 0000 0000 1001
    $type = "SurchargeCard";
    $length = 16;
  }

  if (empty($type))
  {
    $_SESSION["{$errors}"]["{$field}"] = 
      "Please check your card details.";
    return false;
  }

  if (strlen($_SESSION["{$formVars}"]["{$field}"]) != $length)
  {
    $_SESSION["{$errors}"]["{$field}"] = 
      "Card number must contain {$length} digits.";
    return false;
  }

  $check = 0;

 // Add up every 2nd digit, beginning at the right end
  for($x=$length-1;$x>=0;$x-=2)
    $check += intval(substr($_SESSION["{$formVars}"]["{$field}"], $x, 1));

  // Add up every 2nd digit doubled, beginning at the right end - 1.
  // Subtract 9 where doubled value is greater than 10
  for($x=$length-2;$x>=0;$x-=2)
  {
    $double = intval(substr($_SESSION["{$formVars}"]["{$field}"], $x, 1)) * 2;
    if ($double >= 10)
      $check += $double - 9;
    else
      $check += $double;
  }

  // Is $check not a multiple of 10?
  if ($check % 10 != 0)
  {
    $_SESSION["{$errors}"]["{$field}"] = 
      "Credit card invalid. Please check number.";
    return false;
  }
  return true;
}

// Check a credit card expiry date
function checkExpiry($field, $errors, $formVars)
{
   if (!ereg("^([0-9]{2})/([0-9]{2})$", 
             $_SESSION["{$formVars}"]["{$field}"], $parts))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The expiry date is not a valid date " . 
         "in the format MM/YY";
      return false;
   }

   // Check the month
   if (!is_numeric($parts[1]) ||
       intval($parts[1]) < 1 || 
       intval($parts[1]) > 12)
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The month is invalid.";
      return false;
   }

   // Check the date
   if (!is_numeric($parts[2]) || 
       // Year has passed?
       intval($parts[2]) < intval(date("y")) || 
       // This year, but the month has passed?
       (intval($parts[2]) == intval(date("y")) && 
        intval($parts[1]) < intval(date("n"))) || 
       // More than 10 years in the future?
       intval($parts[2]) > (intval(date("y")) + 10))
   {
      $_SESSION["{$errors}"]["{$field}"] = 
         "The date is invalid.";
      return false;
   }
   
   return true;
}
?>
