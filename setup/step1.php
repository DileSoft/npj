<?php
?>
<form action="<?php echo myLocation() ?>?installAction=step2" name="form1" method="POST">
<input type="hidden" name="config[message_set]" value="<?php echo $config["message_set"];?>">
<table>

 <tr><td></td><td><h1><?php echo $lang["title"];?>: <?php echo $lang["step"];?> 1</h1></td></tr>

<?php
   print("<tr><td></td><td>".$lang["fresh"].NPJ_VERSION.". ".$lang["pleaseConfigure"]."</td></tr>\n");
?>

 <tr><td></td><td><br /><?php echo $lang["note"];?></td></tr>

 <tr><td></td><td><br />
<?php

print("<strong>".$lang["Testing Configuration"]."</strong><br />\n");
test($lang["TestPHPVer"], version_compare(PHP_VERSION, "4.1.0", ">="), $lang["TestPHPVerFailed"], 1);

//require('optimizer.php');

session_start();
test($lang["TestPHPSessions"], ($_SESSION["npj_sess_works"] == "works"), $lang["TestPHPSessionsFailed"], 1);
test($lang["TestLocale"], (strtoupper("àáâÿ") == "ÀÁÂß"), $lang["TestLocaleFailed"], 0);
//unset($_SESSION["npj_sess_works"]);
    
?>
  </td></tr>

 <tr><td></td><td><br /><strong><?php echo $lang["databaseConf"];?></strong></td></tr>
 <tr><td></td><td><?php echo $lang["mysqlHostDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["mysqlHost"];?>:</td><td><input type="text" size="50" name="config[db_host]" value="<?php echo $wConfig["db_host"] ?>" /></td></tr>
 <tr><td></td><td><?php echo $lang["dbDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["db"];?>:</td><td><input type="text" size="50" name="config[db_name]" value="<?php echo $wConfig["db_name"] ?>" /></td></tr>
 <tr><td></td><td><?php echo $lang["mysqlPasswDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["mysqlUser"];?>:</td><td><input type="text" size="50" name="config[db_user]" value="<?php echo $wConfig["db_user"] ?>" /></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["mysqlPassw"];?>:</td><td><input type="password" size="50" name="config[db_password]" value="<?php echo $wConfig["db_password"] ?>" /></td></tr>
 <tr><td></td><td><?php echo $lang["prefixDesc"];?></td></tr>
 <tr><td align="right" nowrap><?php echo $lang["prefix"];?>:</td><td><input type="text" size="50" name="config[db_prefix]" value="<?php echo $wConfig["db_prefix"] ?>" /></td></tr>

 <tr><td></td><td><input 
    class="InsertBtn" 
    onmouseover='this.className="InsertBtn_";' 
    onmouseout ='this.className="InsertBtn";' 
                   type="submit" value="<?php echo $lang["Continue"];?>" /></td></tr>
</table>
</form>