<h1><?php echo $lang["title"];?>: <?php echo $lang["step"];?> 3</h1>
<?php

session_start();
$config = array_merge((array)$config, (array)$_SESSION["ses_config"]);
$config2 = array_merge((array)$config2, (array)$_SESSION["ses_config"]);
$_SESSION["ses_config"] = $config;

$dblink = mysql_connect($config2["db_host"], $config2["db_user"], $config2["db_password"]);
mysql_select_db($config2["db_name"], $dblink);

// do installation stuff
if (!$version = trim($wakkaConfig["wakka_version"])) $version = "0";
if (trim($wakkaConfig["wacko_version"])) $version = trim($wakkaConfig["wacko_version"]);
switch ($version)
{
// new installation
case "0":

  $name = "full.sql";
  $fd = @fopen ($name, "r");
  $sql = @fread ($fd, filesize ($name));
  @fclose ($fd);
  $sql = str_replace('%%NODE_ID%%', strtolower($config2["npj_name"]), $sql);
  $sql = str_replace('%%NODE_TITLE%%', $config2["npj_title"], $sql);
  $sql = str_replace('%%NODE_URL%%', $config2["base_url"], $sql);
  $sql = str_replace('%%NODE_PREF%%', $config2["db_prefix"], $sql);
  $sql = str_replace('%%NODE_MAIL%%', $config2["node_mail"], $sql);
  $sqls = explode("# %%@%%",$sql);

  print("<strong>".$lang["Installing Stuff"]."</strong><br />\n");
  //print_r($sqls);
  foreach($sqls as $sql)
   if (trim($sql)!="")
   {
    $res = @mysql_query(rtrim($sql, "\n\r ;"), $dblink);
    if ($res) echo ".";
    else echo "<span class=\"failed\" title=\"".$sql."\n give error \n".mysql_error()."\">!</span>";
   }

  $name = "admin.sql";
  $fd = @fopen ($name, "r");
  $sql = @fread ($fd, filesize ($name));
  @fclose ($fd);
  $sql = str_replace('%%NODE_ID%%', $config2["npj_name"], $sql);
  $sql = str_replace('%%NODE_PREF%%', $config2["db_prefix"], $sql);
  $sql = str_replace('%%ADMIN_NAME%%', strtolower($config["admin_name"]), $sql);
  $sql = str_replace('%%ADMIN_PASSWORD%%', md5($_POST["password"]), $sql);
  $sql = str_replace('%%ADMIN_EMAIL%%', $config["admin_email"], $sql);
  $sqls = explode("# %%@%%",$sql);
  echo ("<p>".$lang["adding admin"]);
  foreach($sqls as $sql)
   if (trim($sql)!="")
   {
    $res = @mysql_query(rtrim($sql, "\n\r ;"), $dblink);
    if ($res) echo ".";
    else echo "<span class=\"failed\" title=\"".$sql."\n give error \n".mysql_error()."\">!</span>";
   }

}
?>

<p>
<?php echo $lang["NextStep"];?> <tt><?php echo "config_tunes.php" ?></tt>.
<?php echo $lang["MakeWrite"];?>.
<?php echo $lang["ForDetailsSee"];?>.
</p>

<form action="<?php echo myLocation(); ?>?installAction=step4" method="POST">
<input 
  class="InsertBtn" 
    onmouseover='this.className="InsertBtn_";' 
    onmouseout ='this.className="InsertBtn";' 
        type="submit" value="<?php echo $lang["Continue"];?>" />
</form>
