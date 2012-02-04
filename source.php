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

require_once "includes/winestore.inc";
require_once "includes/template.inc";

function clean($input, $maxlength)
{
  $input = substr($input, 0, $maxlength);
  $input = EscapeShellCmd($input);
  return ($input);
}

if (isset($_GET["source"]))
{
   $source = clean($_GET["source"], 60);

   $template = new winestoreTemplate(T_SOURCE);

   if ((eregi("^" . D_WEB_PATH . "[a-z0-9]*[.]php$", $source) ||
       eregi("^" . D_WEB_PATH . "templates/[a-z0-9]*[.]tpl$", $source) ||
       $source == D_WEB_PATH . "includes/winestore.inc" ||
       $source == D_WEB_PATH . "includes/customHandler.inc" ||
       $source == D_WEB_PATH . "includes/authenticate.inc" ||
       $source == D_WEB_PATH . "includes/template.inc" ||
       $source == D_WEB_PATH . "includes/validate.inc" || 
       eregi("^" . D_WEB_PATH . "customer/[a-z0-9]*[.]php$", $source) ||
       eregi("^" . D_WEB_PATH . "auth/[a-z0-9]*[.]php$", $source) ||
       eregi("^" . D_WEB_PATH . "order/[a-z0-9-]*[.]php$", $source) ||
       eregi("^" . D_WEB_PATH . "search/[a-z0-9]*[.]php$", $source) ||
       eregi("^" . D_WEB_PATH . "cart/[a-z0-9]*[.]php$", $source)) &&
       file_exists(D_INSTALL_PATH . $source))
         $file = D_INSTALL_PATH . $source;

   $template->setVariable("PAGE", $source);

   if (isset($file)) 
   {
      $contents = highlight_file($file, true);
      $contents = str_replace("{", "&#123", $contents);
      $contents = str_replace("}", "&#125", $contents);
      $template->setVariable("SOURCE", $contents);
   }
   else 
      $template->setVariable("SOURCE", "Filename Not Found or Not Permitted.");

   $template->setCurrentBlock();
   $template->parseCurrentBlock();
   $template->show();
}
else
   trigger_error("source parameter must be provided", E_USER_ERROR);
?>
