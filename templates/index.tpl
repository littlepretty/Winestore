<h1>Here are some Hot New Wines!</h1>
<table width="60%">
  <tr>
    <td><i>Hugh and Dave's Online Wines is not really a winestore. 
    It's an application that demonstrates the concepts of web database
    applications, and is downloadable source code that you can use freely 
    under this <a href="license.txt">license</a>. It pretends to
    give customers from around the world the opportunity to buy over
    1000 wines that come from more than 300 wineries throughout
    Australia.</i>
    </td>
  </tr>
</table>
<table border=0>
<!-- BEGIN row -->
  <tr>
    <td bgcolor="maroon"><b><font color="white">
    {YEAR} {WINERY} {WINE} {VARIETIES}</font></b>
    </td>
  </tr>

  <tr>
    <td bgcolor="silver"><b>Review: </b>{DESCRIPTION}
    </td>
  </tr>

  <tr>
    <td bgcolor="gray">
    <b>Our price: </b>${BOTTLE_PRICE} (${DOZEN_PRICE} a dozen)
    </td>
  </tr>

  <tr>
    <td align="right">
<!-- BEGIN link -->
    <a href="{SCRIPT}?qty={QTY}&amp;wineId={WINE_ID}">{STRING}</a>&nbsp;
<!-- END link -->
    </td>
  </tr>
  <tr>
    <td></td>
  </tr>
<!-- END row -->
</table>
