<!DOCTYPE HTML PUBLIC
                 "-//W3C//DTD HTML 4.01 Transitional//EN"
                 "http://www.w3.org/TR/html401/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>{TITLE}</title>
</head>
<body bgcolor="white">
<p align="right"><b>{LOGIN_STATUS}</b></p>
<!-- BEGIN cartheader -->
<table>
  <tr>
    <td><a href="{S_SHOWCART}" onMouseOut="cart.src='{I_CART_OFF}'"
                               onMouseOver="cart.src='{I_CART_ON}'">
        <img src="{I_CART_OFF}" vspace=0 border=0 
             alt="cart picture" name="cart"></a>
    </td>

    <td>Total in cart: ${TOTAL} ({COUNT} items)
     </td>

   </tr>
</table>
<!-- END cartheader -->
<!-- BEGIN message -->
<br><b><font color="red">{INFO_MESSAGE}</font></b>
<!-- END message -->
{PAGE_BODY}
<!-- BEGIN buttons -->
<table>
<tr>
<!-- BEGIN form -->
  <td><form action="{ACTION}" method="GET">
    <input type="submit" name="{NAME}" value="{VALUE}">
  </form></td>
<!-- END form -->
</tr>
</table>
<!-- END buttons -->
<br><a href="http://validator.w3.org/check/referer">
  <img src="http://www.w3.org/Icons/valid-html401" height="31" width="88" 
        align="right" border="0" alt="Valid HTML 4.01!"></a>
</body>
</html>
