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

require_once "DB.php";
require_once "../includes/template.inc";
require_once "../includes/winestore.inc";

set_error_handler("customHandler");

// Construct the query
function setupQuery($region_name, $wine_type)
{
   // Show the wines stocked at the winestore that match
   // the search criteria
   $query = "SELECT DISTINCT wi.winery_name, 
                     w.year, 
                     w.wine_name, 
                     w.wine_id
             FROM wine w, winery wi, inventory i, region r, wine_type wt
             WHERE w.winery_id = wi.winery_id
             AND w.wine_id = i.wine_id";

   // Add region_name restriction if they've selected anything
   // except "All"
   if ($region_name != "All")
      $query .= " AND r.region_name = '{$region_name}'
                  AND r.region_id = wi.region_id";
      
   // Add wine type restriction if they've selected anything
   // except "All"
   if ($wine_type != "All")
      $query .= " AND wt.wine_type = '{$wine_type}'
                  AND wt.wine_type_id = w.wine_type";

   // Add sorting criteria
   $query .= " ORDER BY wi.winery_name, w.wine_name, w.year";

   return ($query);
}

// Show the user the wines that match their query
function showWines($connection, &$template)
{
   // Produce a heading for the top of the page
   $template->setCurrentBlock();
   $template->setVariable("SEARCHCRITERIA", 
              "Region: {$_SESSION["searchFormVars"]["region_name"]} " .
              "Wine type: {$_SESSION["searchFormVars"]["wine_type"]}");

   // Encode the search parameters for embedding in links to other pages 
   // of results
   $browseString = "wine_type=" . 
                   urlencode($_SESSION["searchFormVars"]["wine_type"]) .  
                   "&amp;region_name=" . 
                   urlencode($_SESSION["searchFormVars"]["region_name"]);

   // Build the query using the search criteria
   $query = setupQuery($_SESSION["searchFormVars"]["region_name"], 
                       $_SESSION["searchFormVars"]["wine_type"]);

   $result = $connection->query($query);
   if (DB::isError($result))
      trigger_error($result->getMessage(), E_USER_ERROR); 

   $numRows = $result->numRows();

   // Is there any data?
   if ($numRows > 0)
   {
      // Yes, there is data.

      // Check that the offset is sensible and, if not, fix it.

      // Offset greater than the number of rows? 
      // Set it to the number of rows LESS SEARCH_ROWS
      if ($_SESSION["searchFormVars"]["offset"] > $numRows)
        $_SESSION["searchFormVars"]["offset"] = $numRows - SEARCH_ROWS;

      // Offset less than zero? Set it to zero
      if ($_SESSION["searchFormVars"]["offset"] < 0)
        $_SESSION["searchFormVars"]["offset"] = 0;

      // The "Previous" page begins at the current 
      // offset LESS the number of SEARCH_ROWS per page
      $previousOffset = 
        $_SESSION["searchFormVars"]["offset"] - SEARCH_ROWS;

      // The "Next" page begins at the current offset
      // PLUS the number of SEARCH_ROWS per page
      $nextOffset = $_SESSION["searchFormVars"]["offset"] + SEARCH_ROWS;

      // Fetch one page of results (or less if on the
      // last page, starting at $_SESSION["searchFormVars"]["offset"])
      for ( $rowCounter = 0;
            $rowCounter < SEARCH_ROWS &&
            $rowCounter + $_SESSION["searchFormVars"]["offset"] < 
            $result->numRows() && 
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC, 
                   $_SESSION["searchFormVars"]["offset"] + $rowCounter);
            $rowCounter++)
      {
         $template->setCurrentBlock("row");
         $template->setVariable("YEAR", $row["year"]);
         $template->setVariable("WINERY", $row["winery_name"]);
         $template->setVariable("WINE", $row["wine_name"]);
         $template->setVariable("VARIETIES", 
                    showVarieties($connection, $row["wine_id"]));

         $price = showPricing($connection, $row["wine_id"]);
         $template->setVariable("BOTTLE_PRICE", 
                                sprintf("$%4.2f", $price));
         $template->setVariable("DOZEN_PRICE", 
                                sprintf("$%4.2f", ($price*12)));

         $template->setVariable("ONEHREF", S_ADDTOCART . 
                                "?qty=1&amp;wineId={$row["wine_id"]}");
         $template->setVariable("DOZENHREF", S_ADDTOCART . 
                                "?qty=12&amp;wineId={$row["wine_id"]}");
         $template->parseCurrentBlock("row");
      } // end for rows in the page

      // Show the row numbers that are being viewed
      $template->setCurrentBlock();
      $template->setVariable("BEGINROW", 
                             $_SESSION["searchFormVars"]["offset"] + 1);
      $template->setVariable("ENDROW", $rowCounter + 
                             $_SESSION["searchFormVars"]["offset"]); 
      $template->setVariable("ROWS", $result->numRows());

      // Are there any previous pages?
      if ($_SESSION["searchFormVars"]["offset"] >= SEARCH_ROWS)
      {
        // Yes, so create a previous link
         $template->setCurrentBlock("link");
         $template->setVariable("HREF", S_SEARCH . "?offset=" . 
                                rawurlencode($previousOffset) . 
                                "&amp;{$browseString}");
         $template->setVariable("HREFTEXT", "Previous");
         $template->parseCurrentBlock("link");
      }
      else
      {
         // No, there is no previous page so don't 
         // print a link
         $template->setCurrentBlock("outtext");
         $template->setVariable("OUTTEXT", "Previous");
         $template->parseCurrentBlock("outtext");
      }

      $template->setCurrentBlock("links");
      $template->parseCurrentBlock("links");

      // Output the page numbers as links
      // Count through the number of pages in the results
      for($x=0, $page=1; $x<$result->numRows(); $x+=SEARCH_ROWS, $page++)
      {
         // Is this the current page?
         if ($x < $_SESSION["searchFormVars"]["offset"] || 
             $x > ($_SESSION["searchFormVars"]["offset"] + 
                   SEARCH_ROWS - 1))
         {
            // No, so print a link to that page
            $template->setCurrentBlock("link");
            $template->setVariable("HREF", 
              S_SEARCH . "?offset=" . rawurlencode($x) .
              "&amp;{$browseString}");
            $template->setVariable("HREFTEXT", $page);
            $template->parseCurrentBlock("link");
         }
         else
         {
            // Yes, so don't print a link
            $template->setCurrentBlock("outtext");
            $template->setVariable("OUTTEXT", $page);
            $template->parseCurrentBlock("outtext");
         }

         $template->setCurrentBlock("links");
         $template->parseCurrentBlock("links");
      }

      // Are there any Next pages?
      if (isset($row) && ($result->numRows() > $nextOffset))
      {
         // Yes, so create a next link
         $template->setCurrentBlock("link");
         $template->setVariable("HREF", 
             S_SEARCH . "?offset=" . rawurlencode($nextOffset) .
             "&amp;{$browseString}");
         $template->setVariable("HREFTEXT", "Next");
         $template->parseCurrentBlock("link");
      }
      else
      {
         // No, there is no next page so don't 
         // print a link
         $template->setCurrentBlock("outtext");
         $template->setVariable("OUTTEXT", "Next");
         $template->parseCurrentBlock("outtext");
      }

      $template->setCurrentBlock("links");
      $template->parseCurrentBlock("links");
   } // end if numRows()
   else
   {
      $template->setCurrentBlock("outtext");
      $template->setVariable("OUTTEXT", 
                             "No wines found matching your criteria.");
      $template->parseCurrentBlock("outtext");
      $template->setCurrentBlock("links");
      $template->parseCurrentBlock("links");
   }
}


// ---------

session_start();    

$template = new winestoreTemplate(T_SEARCH);

$connection = DB::connect($dsn, true);
if (DB::isError($connection))
   trigger_error($connection->getMessage(), E_USER_ERROR); 

// Store the search parameters so the <form> redisplays the
// previous search
$_SESSION["searchFormVars"]["region_name"] = 
   pearclean($_GET, "region_name", 100, $connection);

$_SESSION["searchFormVars"]["wine_type"] = 
   pearclean($_GET, "wine_type", 32, $connection);

// If an offset isn't provided, set it to 0
if (isset($_GET["offset"]))
   $_SESSION["searchFormVars"]["offset"] = 
     pearclean($_GET, "offset", 5, $connection);
else
   $_SESSION["searchFormVars"]["offset"] = 0;
   
// Show the user their search
showWines($connection, $template);

$template->showWinestore(SHOW_ALL, B_HOME | B_SHOW_CART | B_SEARCH |
                         B_LOGINLOGOUT);
?>
