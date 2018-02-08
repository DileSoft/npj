<?php
// ====================================================================
  class Config 
  {
   function Config()
   {
    if(!@is_readable("../../config_db.php")) die("Cannot read local configuration.");
    require("../../config_db.php");
   }
  }
/////////////////////////////////////////////////////////////////////
  function read_from_db( $x )
  {
    GLOBAL $C;

    // mysql-sensitive code
    $sql = "select up.pic_id, up.have_big, up.have_small, up.user_id from ".$C->db_prefix."userpics as up, ".
           $C->db_prefix."users as u where ".
           "up.pic_id = u._pic_id and u.user_id = up.user_id and u.user_id = ".$x;
//die($sql);
    $db = mysql_connect( $C->db_host, $C->db_user, $C->db_password );
    $rs = mysql_db_query( $C->db_name, $sql );
    if ($rs) $a  = mysql_fetch_array( $rs ); else $a = false;
    if ($db) mysql_close($db);
    return $a;
  }

/////////////////////////////////////////////////////////////////////
  $C = &new Config();
  $f = ltrim(strrchr($_SERVER["REQUEST_URI"],"/"),"/");
  $e = explode(".",$f);
  $x = explode("_",$e[0]);
  check_pic( $x[0], $x[1], $x[2] );
  $x[0]*=1;

  $a = read_from_db( $x[0] );
  if ($a != false)  check_pic( $a["user_id"], $x[1], $a["pic_id"], $a["have_".$x[1]] );
  pass("z.gif");

// ====================================================================
  function check_pic( $user, $big, $pic, $ext=".gif" )
  {
    if ($ext != "")
    {
      $f1 = $user."_".$big."_".$pic.$ext;
      if (file_exists($f1)) pass( $f1 );
      if ($ext != ".jpg")
      {
        $f2 = $user."_".$big."_".$pic.".jpg";
        if (file_exists($f2)) pass( $f2 );
      }
    }
    if ($big != "small") check_pic( $user, "small", $pic );
  }

// ====================================================================
  function pass( $file )
  {
//   header("Content-Type: image/gif");
//   header("Content-Disposition: inline;filename=z.gif");
   header("HTTP/1.0 200 Ok");
   $f = fopen( $file, "rb" );
   header( "Content-Type: image/jpeg");
//   header( "Content-Disposition: inline; filename=\"mypic.jpg\"");
//   header( "Content-Length: ".(string)(filesize($file)) );
//   header( "Last-Modidfied: ".(string)(gmdate('D, d M Y H:i:s \G\M\T', filemtime($file))));
   fpassthru ($f);
   exit;
  }

?>