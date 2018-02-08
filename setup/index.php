<?php

  // change THIS to "https"
  define( "SCHEME", "http" );


  if (file_exists("../config_tunes.php") && (filesize("../config_tunes.php")>10)) die("NPJ already installed<br>NPJ уже установлен.");

  ob_start();
  define('NPJ_VERSION', 'R1.9');
  setlocale(LC_CTYPE, array("ru_RU.CP1251","ru_SU.CP1251","ru_RU.KOI8-r","ru_RU","russian","ru_SU","ru"));  
  error_reporting (E_ALL ^ E_NOTICE );

  $pos = strpos($_SERVER["REQUEST_URI"],"/setup/");
  if ($pos===false)
   die('Erroneous request.');
  else
   $baseurl = SCHEME."://".$_SERVER["HTTP_HOST"].substr($_SERVER["REQUEST_URI"], 0, $pos+1);

  $base_domain    = preg_replace("/^www\./i", "", $_SERVER["HTTP_HOST"]);
  $cookie_domain = ".".$base_domain;
  $_domains = explode(".", $cookie_domain);
  if (count($_domains)<=2 || (count($_domains)==3  && strlen($_domains[1])<=2))
    $cookie_domain = "";
  session_set_cookie_params(0, "/", $cookie_domain);


  if (!$installAction = trim($_REQUEST["installAction"])) 
  {
    session_start();
    $_SESSION["npj_sess_works"] = "works";
    header("Location: ".$baseurl."setup/?installAction=step1");
  }

  header("Content-Type: text/html; charset=windows-1251");

  // stuff
  function test($text, $condition, $errorText = "", $stopOnError = 1) {
  GLOBAL $lang;
    print("$text ");
    if ($condition)
    {
      print("<span class=\"ok\">OK</span><br />\n");
    }
    else
    {
      print("<span class=\"failed\">".$lang["failed"]."</span>");
      if ($errorText) print(": ".$errorText);
      print("<br />\n");
      if ($stopOnError) exit;
    }
  }

  function myLocation() {
    list($url, ) = explode("?", $_SERVER["REQUEST_URI"]);
    return $url;
  }

  // fetch configuration
  $config = $_POST["config"];
  $config2 = array_merge((array)$wConfig, (array)$_POST["config"]);


  if (!isset($config["message_set"]) || !@file_exists("messageset/".$config["message_set"]."_Installer.php")) $config["message_set"]="std";
  require_once("messageset/".$config["message_set"]."_Installer.php");

  if (!$wConfig["base_url"]) $wConfig["base_url"] = $baseurl; 

?>
<html>
<head>
  <title><?php echo $lang["title"];?></title>
  <style>
body, table, td, div, p {  font-family: Arial, Tahoma, Verdana, sans; color: #444444; font-size:14px; line-height:1.3em }
h1 { padding:0 5px 0 0; font-size:18px; color:#0064D8; margin:0; } 
h2 { padding:0; margin: 1em 0 0.5em; line-height:1.2em; font-size:17px; color:#444444} 
h3 { padding:0; margin: 1em 0 0.5em; line-height:1.2em; font-size:14px; color:#0064D8} 
h4 { padding:0; margin: 1em 0 0.5em; line-height:1.2em; font-size:13px; color:#444444} 
h5 { padding:0; margin: 1em 0 0.5em; line-height:1.2em; font-size:12px; color:#666666} 
h6 { padding:0; margin: 1em 0 0.5em; line-height:1.2em; font-size:11px; color:#444444} 
tt { color:#666600; background:#ffffcc; padding: 0 2px } 
.InsertBtn  { color:#002000; cursor:hand; background:#dbffdb; height:40px; width:210px; }
.InsertBtn_ { color:#002000; cursor:hand; background:#fff6bc; height:40px; width:210px; }

    .ok { color: #008800; font-weight: bold; }
    .failed { color: #880000; font-weight: bold; }
    A { color: #0000FF; }
  </style>
</head>

<body>
<?php
  if (@file_exists("".$installAction.".php")) include("".$installAction.".php"); else print("<em>Invalid action</em>");
?>
</body>
</html>
