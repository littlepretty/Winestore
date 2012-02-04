<?php
// Source code example for Web Database Applications with PHP and MySQL, 2nd Edition
// Author: Hugh E. Williams and David Lane, 2001-3
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

// This script logs a user out and redirects 
// to the calling page.

require_once '../includes/winestore.inc';
require_once '../includes/authenticate.inc';

set_error_handler("customHandler");

// Restore the session
session_start();

// Check they're logged in
sessionAuthenticate(S_LOGIN);

// Destroy the login and all associated data
session_destroy();

// Redirect to the main page
header("Location: " . S_MAIN);
exit;
?>
