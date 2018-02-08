<?php

 if (version_compare(PHP_VERSION, "4.3.0", "<"))
 {
  header("HTTP/1.1 503 Service Unavailable");
  die (str_repeat(" ",500)."\nSorry, NNS Server require PHP 4.3+");
 }

 if ($rh->node->data["is_nns"]==0)
 {
  header("HTTP/1.1 503 Service Unavailable");
  die (str_repeat(" ",500)."\nSorry, NNS Server isn't running here");
 }
 //!!! MOVE ALL DIEs TO FORBIDDEN


 $method = $_SERVER["REQUEST_METHOD"];

 $debug->Trace("Method: ".$method);

 $remote = $_SERVER["REMOTE_ADDR"];
 /*!!!! this code MUST be commented in RELEASE*/
 if ($remote==$rh->node->data["alternate_ip"])
  $remote = $rh->node->data["ip"];
 /*/!!!! */

 if ($method=="GET")
 {
  if ($params[0]) //somenode
  {
   $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($params[0]));
  }
  else //all nodes
  {
   $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes");
  }

  if ($rs->RecordCount()>=0)
  {
    $LoggedIn = 0;
    $rstmp = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($_GET["node"]));
    if ($rstmp->RecordCount()>0)
      if ($rstmp->fields["passwd"]==$_GET["password"])
        $LoggedIn = 1;

    $rh->UseClass("ListObject", $rh->core_dir);
    $a = $rs->GetArray();
    $list = &new ListObject( &$rh, &$a );
    $tpl->Assign("LoggedIn", $LoggedIn);
    $tpl->theme = $rh->theme;
    $result = $list->Parse("nns.xml:List");
    $tpl->theme = $rh->skin;
   
    while (ob_get_level()) {
     ob_end_clean();
    }
    ob_start("ob_gzhandler");
    header ("Content-Type: text/xml");
    echo $result;
    die();  // !!! REMOVE ALL THIS CRAP
  }
 }
 else if ($method=="DELETE")
 {
   if ($params[0] && $_GET["password"])
   {
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($params[0]));
     if ($rs->RecordCount()>0)
     {
       if ($rs->fields["passwd"]==$_GET["password"])
       {
        $rs = $db->Execute("DELETE FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($params[0]));
        header("HTTP/1.1 200 Ok");
        die (str_repeat(" ",500)."\nRemoved succesfully");
       }
       else
       {
        header("HTTP/1.1 401 Unauthorized");
        die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because authorisation failed");
       }
     }
     else
     {
      header("HTTP/1.1 412 Precondition Failed");
      die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because it is not full [2]");
     }

   }
   else
   {
    header("HTTP/1.1 412 Precondition Failed");
    die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because it is not full [1]");
   }
 }
 else if ($method=="POST")
 {
   $xmlstring = file_get_contents("php://input");
   $id       = $this->untag($xmlstring, "id");
   $title    = $db->Quote($this->untag($xmlstring, "title"));
   $url      = $db->Quote($this->untag($xmlstring, "url"));
   $can_nns  = $db->Quote($this->untag($xmlstring, "can_nns"));
   $created  = $db->Quote($this->untag($xmlstring, "created"));
   $ip       = $db->Quote($remote);
   $email    = $db->Quote($this->untag($xmlstring, "email"));
   $password = $db->Quote($this->untag($xmlstring, "password"));
   $user_pictures_dir = $db->Quote($this->untag($xmlstring, "user_pictures_dir"));
   if ($id && preg_match("/^[a-z][a-z0-9]+$/", $id))
   {
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($params[0]));
     if ($rs->RecordCount()==0)
     {
      $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."nodes (node_id, title, url, can_nns, is_nns, created_datetime, passwd, ip, email, user_pictures_dir) ".
                       "VALUES (".$db->Quote($id).",".$title.",".$url.",".$can_nns.",0,".$created.",".$password.",".$ip.",".$email.",".$user_pictures_dir.")");
      header("HTTP/1.1 201 Created");
      die (str_repeat(" ",500)."\nCreated succesfully");
     }
     else
     {
      header("HTTP/1.1 409 Conflict");
      die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because there is such ID already");
     }
   }
   else
   {
    header("HTTP/1.1 412 Precondition Failed");
    die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because ID is not fulfil requirements");
   }
 }
 else if ($method=="PUT")
 {
  $xmlstring = file_get_contents("php://input");
  $id       = $this->untag($xmlstring, "id");
  $title    = $db->Quote($this->untag($xmlstring, "title"));
  $url      = $db->Quote($this->untag($xmlstring, "url"));
  $can_nns  = $db->Quote($this->untag($xmlstring, "can_nns"));
  $ip       = $db->Quote($remote);
  $email    = $db->Quote($this->untag($xmlstring, "email"));
  $password = $this->untag($xmlstring, "password");
  $user_pictures_dir = $db->Quote($this->untag($xmlstring, "user_pictures_dir"));
  if ($params[0] && $password)
  {
    $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$db->Quote($id));
    if ($rs->RecordCount()>0)
    {
      if ($rs->fields["passwd"]==$password)
      {
       $rs = $db->Execute("UPDATE ".$rh->db_prefix."nodes SET title=".$title.", url=".$url.", ".
                        "ip=".$ip.", email=".$email.", can_nns=".$can_nns.", user_pictures_dir=".$user_pictures_dir.
                        " WHERE node_id=".$db->Quote($id));
       header("HTTP/1.1 205 Reset Content");
       die (str_repeat(" ",500)."\nUpdated succesfully");
      }
      else
      {
       header("HTTP/1.1 401 Unauthorized");
       die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because authorisation failed");
      }
    }
    else
    {
     header("HTTP/1.1 412 Precondition Failed");
     die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because it is not full [2]");
    }

  }
  else
  {
   header("HTTP/1.1 412 Precondition Failed");
   die (str_repeat(" ",500)."\nSorry, NNS Server cannot proceed your request because it is not full [1]");
  }
 }
 else 
 {
  header("HTTP/1.1 501 Not Implemented");
  die (str_repeat(" ",500)."\nSorry, NNS Server cannot determine what you mean");
 }
/*
 echo $method."<hr><pre>";
 print_r($request);
 echo "</pre>";
*/
// phpinfo();
 die("Successful ".$method);

?>