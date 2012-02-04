<h1>Your order (reference # {CUST_ID} - {ORDER_ID}) has
been dispatched</h1>
Thank you {CUSTTITLE} {SURNAME}, 
your order has been completed and dispatched.
Your order reference number is {CUST_ID} - {ORDER_ID}. 
Please quote this number in any correspondence.
<br>
<p>If it existed, the order would have been shipped to: 
<br><b>
{CUSTTITLE} {FIRSTNAME} {INITIAL} {SURNAME}
<br>
{ADDRESS}
<br>{CITY} {STATE}
<br>{COUNTRY} {ZIPCODE}
</b>
<br>
<br>
<p>We have billed your fictional credit card.
<!-- BEGIN items -->
<table border=0 width=70% cellpadding=0 cellspacing=5>
<tr>
   <td><b>Quantity</b></td>
   <td><b>Wine</b></td>
   <td align="right"><b>Unit Price</b></td>
   <td align="right"><b>Total</b></td>
</tr>
<!-- BEGIN row -->
<tr>
   <td>{QTY}</td>
   <td>{WINE}</td>
   <td align="right">{PRICE}</td>
   <td align="right">{TOTAL}</td>
</tr>
<!-- END row -->
<tr></tr>
<tr>
   <td colspan=2 align="left"><i><b>Total of this order</b></td>
   <td></td>
   <td align="right"><b><i>{ORDER_TOTAL}</b></td>
</tr>
</table>
<!-- END items -->
<p><i>An email confirmation has been sent to you.
Thank you for shopping at Hugh and Dave's Online Wines.</i>
