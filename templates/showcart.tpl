<!-- BEGIN cart -->
<h1>Your Shopping Cart</h1>
<form action="{S_UPDATECART}" method="GET">
<table border="0" cellpadding="0" cellspacing="5">
   <tr>
      <th>Quantity</th>
      <th>Wine</th>
      <th>Unit Price</th>
      <th>Total</th>
   </tr>
<!-- BEGIN item -->
   <tr>
       <td><input type="text" size=3 name="{QUANTITY_NAME}" 
            value="{QUANTITY_VALUE}"></td>
       <td>{WINE}</td>
       <td>${ITEM_PRICE}</td>
       <td>${TOTAL_VALUE}</td>
   </tr>
<!-- END item -->
   <tr></tr>
   <tr>
      <td><b>{TOTAL_ITEMS} items</b></td>
      <td></td>
      <td></td>
      <td><b>${TOTAL_COST}</b></td>
   </tr>
</table>
<input type="submit" name="update" value="Update Quantities">
</form>
<!-- END cart -->
<!-- BEGIN emptycart -->
<h1><font color="red">{TEXT}</font></h1>
<!-- END emptycart -->
