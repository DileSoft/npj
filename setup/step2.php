<?php 
session_start();
$_SESSION["ses_config"] = $config;

?>
<script language="JavaScript">
function check() {
var f = document.forms.form1;
var re;
if (f.elements["config[npj_name]"].value.length<3) {
 alert('<?php echo $lang["name too short"];?>');
 return false;
}
re = new RegExp("^[a-z][a-z0-9]+$");
if (f.elements["config[npj_name]"].value.search(re)==-1) {
 alert('<?php echo $lang["incorrect name"];?>');
 return false;
}
re = new RegExp("^[A-Za-z0-9]+$");
if (f.elements["config[admin_name]"].value.search(re)==-1) {
 alert('<?php echo $lang["incorrect adminname"];?>');
 return false;
}
re = new RegExp("[a-z0-9\-\.]+@[a-z0-9\-]+\.[a-z]+", "i");
if (f.elements["config[admin_email]"].value.search(re)==-1) {
 alert('<?php echo $lang["incorrect email"];?>');
 return false;
}
if (f.elements["password"].value.length<5) {
 alert('<?php echo $lang["password too short"];?>');
 return false;
}
if (f.elements["password"].value!=f.elements["password2"].value) {
 alert('<?php echo $lang["passwords don't match"];?>');
 return false;
}
return true;
}
</script>
<form action="<?php echo myLocation() ?>?installAction=step3" name="form1" method="POST">
<table>

 <tr><td></td><td><h1><?php echo $lang["title"];?>: <?php echo $lang["step"];?> 2</h1></td></tr>

 <tr><td></td><td><br />
<?php

print("<strong>".$lang["Testing Configuration"]."</strong><br />\n");
test($lang["TestSql"], $dblink = @mysql_connect($config2["db_host"], $config2["db_user"], $config2["db_password"]));
test($lang["Looking for database..."], @mysql_select_db($config2["db_name"], $dblink), $lang["DBError"]);
print("<br />\n");

?>
  </td></tr>

 <tr><td></td><td><br /><strong><?php echo $lang["SiteConf"];?></strong></td></tr>

 <tr><td></td><td><?php echo $lang["nameDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["name"];?>:</td><td><input type="text" size="50" name="config[npj_name]" value="<?php echo $wConfig["npj_name"] ?>" /></td></tr>

 <tr><td></td><td><?php echo $lang["npjtitleDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["npjtitle"];?>:</td><td><input type="text" size="50" name="config[npj_title]" value="<?php echo $wConfig["npj_title"] ?>" /></td></tr>

 <tr><td></td><td><?php echo $lang["npjmailDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["npjmail"];?>:</td><td><input type="text" size="50" name="config[node_mail]" value="<?php echo $wConfig["node_mail"] ?>" /></td></tr>

 <tr><td></td><td><?php echo $lang["baseDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["base"];?>:</td><td><input type="text" size="50" name="config[base_url]" value="<?php echo $wConfig["base_url"] ?>" /></td></tr>


 <tr><td></td><td><br /><strong><?php echo $lang["AdminConf"];?></strong></td></tr>

 <tr><td></td><td><?php echo $lang["adminDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["admin"];?>:</td><td><input type="text" size="50" name="config[admin_name]" value="<?php echo $wConfig["admin_name"] ?>" /></td></tr>

 <tr><td></td><td><?php echo $lang["passwDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["password"];?>:</td><td><input type="password" size="50" name="password" value="" /></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["password2"];?>:</td><td><input type="password" size="50" name="password2" value="" /></td></tr>

 <tr><td></td><td><?php echo $lang["mailDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["mail"];?>:</td><td><input type="text" size="50" name="config[admin_email]" value="<?php echo $wConfig["admin_email"] ?>" /></td></tr>


 <tr><td></td><td><input type="submit" 
    class="InsertBtn" 
    onmouseover='this.className="InsertBtn_";' 
    onmouseout ='this.className="InsertBtn";' 
    value="<?php echo $lang["Continue"];?>" onclick="return check();" /></td></tr>
</table>
</form>