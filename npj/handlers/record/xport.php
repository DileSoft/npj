<?php

  if (!$this->HasAccess( &$principal, "noguests" )) return $this->Forbidden("NoGuests");


  $data = $this->Load(3);
  if (!is_array($data)) return $this->NotFound();

  header("Content-type: text/xml");

  $rh->absolute_urls = 1; // !!!!!!!

  $xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n";
  $xml .= "<rss version=\"2.0\">\n";
  $xml .= "<channel>\n";
  $xml .= "<title>".$this->data["tag"]."</title>\n";
  $xml .= "<link>".$this->Href("!")."</link>\n";
  $xml .= "<description>Export of entire cluster</description>\n";
  $xml .= "<language></language>\n";
  $xml .= "<generator>NPJ ".$rh->npj_version."</generator>\n";

  if ($this->HasAccess( &$principal, $this->security_handlers[$this->data["type"]] ))
  {
     $__tag = rtrim("/".$this->data["tag"], "/");


     $numOfSlashes = substr_count($__tag, "/");


     $sql = "select * from ".$rh->db_prefix."records where (supertag =".$db->Quote($this->data["supertag"]).
            " or supertag like ".$db->Quote($this->data["supertag"]."%").")";
     $rs  = $db->Execute($sql);
     $pages = $rs->GetArray();

     foreach ($pages as $num=>$page)
     {
       $o = &new NpjObject( &$rh, $page["supertag"] );
       $o->Load(2);
        
       // check ACLS
       if (!$o->HasAccess( &$principal, "acl", "write" )) continue;

       // output page
       $tag = rtrim("/".$page["tag"], "/");
       if ($numOfSlashes == substr_count($tag, "/")) $tag = "";
       else
       {
         $_tag = explode("/", $tag);
         $tag = "";
         for ($i=0; $i<count($_tag); $i++)
         {
           if ($i>$numOfSlashes) $tag .= $_tag[$i]."/";
         }
       }


      $authordata = &$this->_LoadById( $page["author_id"], 3, "account" );
      $author = $authordata["login"]."@".$authordata["node_id"]." - ".$authordata["user_name"];
      $author = htmlspecialchars( $author );


      $xml .= "<item>\n";
      $xml .= "<guid>".rtrim($tag, "/")."</guid>\n";
      $xml .= "<title>".htmlspecialchars( $page["subject"] )."</title>\n";
      $xml .= "<link>".$this->Href( $page["supertag"] )."</link>\n";
      $xml .= "<description><![CDATA[".str_replace("]]>","]]&gt;",$page["body"])."]]></description>\n";
      $xml .= "<author>".$author."</author>\n";
      $xml .= "<pubDate>".gmdate('D, d M Y H:i:s \G\M\T', strtotime($page["created_datetime"]))."</pubDate>\n";
      $xml .= "</item>\n";
     }

  }
  else
  {
   $xml .= "<item>\n";
   $xml .= "<title>Error</title>\n";
   $xml .= "<link>".$this->href("!")."</link>\n";
   $xml .= "<description>You're not allowed to access this information.</description>\n";
   $xml .= "</item>\n";
  }

  $xml .= "</channel>\n";
  $xml .= "</rss>\n";

  return $xml;

?>
