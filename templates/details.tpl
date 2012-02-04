<!-- BEGIN inputform -->
<form method="{METHOD}" action="{S_VALIDATE}">
<h1>{FORMHEADING}</h1>
<b>{INSTRUCTIONS}  Fields shown in <font color="red">red</font> 
   are mandatory.</b>
<p>
<table>
<col span="1" align="right">
<!-- BEGIN widget -->
<!-- BEGIN select -->
   <tr>
      <td><font color="red">{SELECTTEXT}</font></td>
      <td><select name="{SELECTNAME}">
<!-- BEGIN option -->
      <option{SELECTED} value="{OPTIONVALUE}">{OPTIONTEXT}
<!-- END option -->
      </select></td>
   </tr>
<!-- END select -->
<!-- BEGIN mandatoryinput -->
   <tr>
      <td><font color="red">{MINPUTTEXT}</font>
      </td>
      <td>
<!-- BEGIN mandatoryerror -->
      <font color="red">{MERRORTEXT}</font><br>
<!-- END mandatoryerror -->      
         <input type="text" name="{MINPUTNAME}" 
          value="{MINPUTVALUE}" size={MINPUTSIZE}>
      </td>
  </tr>
<!-- END mandatoryinput -->
<!-- BEGIN optionalinput -->
   <tr>
      <td>{OINPUTTEXT}
      </td>
      <td>
<!-- BEGIN optionalerror -->
      <font color="red">{OERRORTEXT}</font><br>
<!-- END optionalerror -->      
         <input type="text" name="{OINPUTNAME}" 
          value="{OINPUTVALUE}" size={OINPUTSIZE}>
      </td>
  </tr>
<!-- END optionalinput -->
<!-- BEGIN passwordinput -->
   <tr>
      <td><font color="red">{PINPUTTEXT}</font>
      </td>
      <td>
<!-- BEGIN passworderror -->
      <font color="red">{PERRORTEXT}</font><br>
<!-- END passworderror -->      
         <input type="password" name="{PINPUTNAME}" 
          value="{PINPUTVALUE}" size={PINPUTSIZE}>
      </td>
  </tr>
<!-- END passwordinput -->
<!-- END widget -->
<tr>
   <td><input type="submit" value="Submit"></td>
</tr>
</table>
</form>
<!-- END inputform -->
