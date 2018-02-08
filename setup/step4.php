<h1><?php echo $lang["title"];?>: <?php echo $lang["step"];?> 4</h1>
<?php

session_start();
$config = $_SESSION["ses_config"];

// merge existing configuration with new one
$config = array_merge((array)$wConfig, (array)$config);

$config["cookie_prefix"] = $config["db_prefix"];
$config["node_admins"] = strtolower($config["admin_name"]);
$config["base_url"] = str_replace( SCHEME."://".$_SERVER["HTTP_HOST"]."/","",$config["base_url"]);
$config["node_secret_word"] = rand().rand().rand();
$config["scheme"] = SCHEME;


test($lang["apply rights"]."_templates...",      @chmod ("../_templates", 0777),      $lang["apply rights yourself"]."_templates", 0);
test($lang["apply rights"]."rss...",             @chmod ("../rss", 0777),             $lang["apply rights yourself"]."rss", 0);
test($lang["apply rights"]."images/userpics...", @chmod ("../images/userpics", 0777), $lang["apply rights yourself"]."images/userpics", 0);

$htaccess = "RewriteEngine off\nErrorDocument 404 /".$config["base_url"]."images/userpics/_404_mysql.php\n";
test($lang["adjust htaccess"], $fp = @fopen("../images/userpics/.htaccess", "w"), 
      $lang["adjust htaccess yourself"]."<br /><div style=\"background-color: #EEEEEE; padding: 10px 10px;\">\n<pre>".
      $htaccess."</pre>\n</div>\n", 0);
if ($fp)
{
  fwrite($fp, $htaccess);
  fclose($fp);
}


function array_to_str ($arr, $name="") {
 foreach ($arr as $k => $v)
 {
   $entries .= "  \$this->".$k." = \"".str_replace("\n","\\n",$v)."\";\n";
 }
 $str .= $entries;
 return $str;
}

$dbconfig["db_host"]     = $config["db_host"];
$dbconfig["db_name"]     = $config["db_name"];
$dbconfig["db_user"]     = $config["db_user"];
$dbconfig["db_password"] = $config["db_password"];
$dbconfig["db_prefix"]   = $config["db_prefix"];
$dbconfig["db_al"]       = "adodb";
$dbconfig["db_al_type"]  = "mysql";

unset($config["admin_name"]);
unset($config["admin_email"]);
unset($config["db_host"]);
unset($config["db_name"]);
unset($config["db_user"]);
unset($config["db_password"]);
unset($config["db_prefix"]);
unset($config["npj_name"]);
unset($config["npj_title"]);

// set version to current version, yay!
$config["npj_version"] = NPJ_VERSION;

// convert config array into PHP code
$configCode = "<?php\n// config_tunes.php ".$lang["writtenAt"].strftime("%c")."\n// ".$lang["dontchange"]."\n\n";
$configCode .= array_to_str($config)."\n";

$name = "default.txt";
$fd = @fopen ($name, "r");
$other = @fread ($fd, filesize ($name));
@fclose ($fd);
$configCode .= $other;

// convert dbconfig array into PHP code
$dbConfigCode = "<?php\n// config_db.php ".$lang["writtenAt"].strftime("%c")."\n// ".$lang["dontchange"]."\n\n";
$dbConfigCode .= array_to_str($dbconfig)."\n?>";


// try to write configuration file
echo $lang["writing"];
test($lang["writing2"]." <tt>config_tunes.php</tt>...", $fp = @fopen("../config_tunes.php", "w"), "", 0);

if ($fp)
{
  fwrite($fp, $configCode);
  // write
  fclose($fp);

  test($lang["writing2"]." <tt>config_db.php</tt>...", $fp = @fopen("../config_db.php", "w"), "", 0);

  if ($fp)
  {
    fwrite($fp, $dbConfigCode);
    // write
    fclose($fp);
    echo $lang["ready"]." <a href=\"/".$config["base_url"]."\">".$lang["return"]."</a>. ".$lang["SecurityRisk"]."</p>";
  }
  else $error = "config_db.php";
}
else $error = "config_tunes.php";

if ($error)
{
  // complain
  print("<p>".$lang["warning"]." <tt>".$error."</tt> ".$lang["GivePrivileges"].".</p>\n");
  ?>
  <form action="<?php echo myLocation() ?>?installAction=step4" method="POST">
  <input type="submit" value="<?php echo $lang["try again"];?>" />
  </form>
  <strong>config_tunes.php</strong>
  <?php
  print("<div style=\"background-color: #EEEEEE; padding: 10px 10px;\">\n<pre>".str_replace("<", "&lt;", $configCode)."\n</pre></div>\n");
  ?>
  <strong>config_db.php</strong>
  <?php
  print("<div style=\"background-color: #EEEEEE; padding: 10px 10px;\">\n<pre>".str_replace("<", "&lt;", $dbConfigCode)."\n</pre></div>\n");

  echo $lang["willready"]." <a href=\"/".$config["base_url"]."\">".$lang["return"]."</a>. ".$lang["SecurityRisk"]."</p>";

}  

?>