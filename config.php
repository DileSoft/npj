<?php
  // top level configuration 
  include("core/config.php");
  include("npj/config.php");


  // $this->debug_file_name = dirname(__FILE__)."/files/debug_".rand(0, 65535).".log" ;

  // concrete configuration
  if (!file_exists("config_tunes.php")) 
  {
    $uri  = preg_replace("/\?.*$/", "",$_SERVER["REQUEST_URI"]);
    $page = $_REQUEST["page"];
    $uri  = substr( $uri, 0, strlen($uri)-strlen($page) );
    $uri  = rtrim( $uri, "/" )."/setup";
    die ("Please, <a href='".$uri."'>run installer</a>!");
  }
  include("config_tunes.php");
  include("config_db.php");
  if (file_exists("config_modules.php")) include("config_modules.php");
  $this->npj_version = "R1.9";
?>