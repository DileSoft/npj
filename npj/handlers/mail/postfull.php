<?php

/*
+  type                                          ?banned:                        
+  keywords   //string                             version_tag,                  
+  user_id                                         disallow_replicate,           
p  group1..4                                       disallow_notify_comments,     
+  communities //array                             is_digest,                    
+  body                                            is_announce,                  
+  formatting                                      is_keyword,                   
+  tag                                             disallow_comments,            
+  subject,                                        disallow_syndicate            
+d pic_id,                                         default_show_parameter*,      
   user_datetime,                                t акли                          
                                                   depth,                        
principal


*/

 if ($this->data["text"])
 {
   $text = $this->data["text"];
 }
 else 
 {
   $text = $this->Format($this->data["html"], "after_htmlmail");
 }
 preg_match( "/^(.*?)\n\s*\n(.*)$/s", $text, $matches ); 
 $text = $matches[2];
 $headers = $this->parser->explodeHeaders($matches[1]);
 if (!$headers["user"] && $headers["login"]) $headers["user"] = $headers["login"];

 if ($headers["user"] && ($headers["password"] || $headers["spassword"]))
 {
   $rs = $db->Execute("SELECT user_id, user_name, node_id, _pic_id, _formatting, password, login FROM ".$rh->db_prefix.
                      "users where login=".$db->Quote($headers["user"])." AND node_id=".$db->Quote($rh->node_name));
   if (!$rs->fields)
    $this->data["error"] = "POSTFULL: No such user";
   else
   {
     if ($headers["spassword"]!=$rs->fields["password"] && md5($headers["password"])!=$rs->fields["password"])
       $this->data["error"] = "POSTFULL: Incorrect password";
     else
     {
       $principal->MaskById( $rs->fields["user_id"] );

       $tag = trim($headers["document"]);
       if (is_numeric($tag{0})) $tag = "";

       $record =& new NpjObject(&$rh, $rs->fields["login"]."@".$rs->fields["node_id"].":".($tag?$tag:"1"));

       if (is_array($this->data["headers"]["received"]))
       $date = $this->data["headers"]["received"][count($this->data["headers"]["received"])-1];
       else
         $date = $this->data["headers"]["received"];
       $pos = strrpos($date, ";"); 
       $date = substr($date, $pos+1);
       $record->data["user_datetime"] = $headers["date"]?date("Y-m-d H:i:s", strtotime($headers["date"])):
                                        date("Y-m-d H:i:s", strtotime($date));
       $debug->Trace("d1:".$date.";d2:".$headers["date"]);
       $record->data["type"] = $tag?2:1;
       $record->data["keywords"] = $headers["keywords"];
       $record->data["user_id"] = $rs->fields["user_id"];

       // !!! this line now possible obsolete (take a look after "post in subroubrics of communities")
//       $record->data["communities"] = $headers["journals"]?explode(",",$headers["journals"]):"";
//       foreach ($AA as $user_id)
//         $record->data["communities"][] = $user_id; 

// how to convert sobaka OR sobaka@npj to uid of sobaka???
       if ($headers["journals"])
       {
        $debug->Trace("hurra");
        $comms = explode(",",$headers["journals"]);
        if (is_array($comms))
        foreach ($comms as $comm)
        {
        $debug->Trace($comm);
          $comm = strtolower(trim($comm));
          if (strpos($comm, "@")===false) $comm = $comm."@".$rh->node_name;
          $commdata =& $record->_Load($comm, 0, "account");
        $debug->Trace($commdata["user_id"]);
          $record->data["communities"][] = $commdata["user_id"];
        }
       }
       
       $record->data["tag"] = $tag?$tag:"1";
       $record->data["subject"] = $headers["subject"]?$headers["subject"]:$this->parser->um_decode($this->data["headers"]["subject"]);
       //$record->data["pic_id"]  = $headers["picture"]?$headers["picture"]:$rs->fields["_pic_id"];
       if ($headers["picture"])
         $record->data["pic_id"]  = $headers["picture"];
       $formatting = $headers["formatting"]?$headers["formatting"]:$rs->fields["_formatting"];

       //!!! пока нет форматтера htmlmail
       if ($formatting=="rawhtml") $formatting="simplebr";

       $record->data["formatting"] = $formatting;
       $record->data["body"] = $this->Format(trim($text), $formatting, "pre");
       if ($record->data["type"]==1)
       {
         $rs2 = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_SELF." and user_id=".
                             $db->Quote( $rs->fields["user_id"] ));
         $group_nobody = 1*$rs2->fields["group_id"];
         $rs2 = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_FRIENDS." and user_id=".
                             $db->Quote( $rs->fields["user_id"] ));
         $group_friends = 1*$rs2->fields["group_id"];
         if ($headers["security"]=="public") // все
         {
          $record->data["group1"]=0; $record->data["group2"]=0; 
          $record->data["group3"]=0; $record->data["group4"]=0;
         }
         else if ($headers["security"]=="private") // никто
         {
          $record->data["group1"]=$group_nobody;
          $record->data["group2"]=-1; $record->data["group3"]=0; $record->data["group4"]=0;
         }
         else if ($headers["security"]=="protected") // все конфиденты
         {
          $record->data["group1"]=$group_friends;
          $record->data["group2"]=-2; $record->data["group3"]=0; $record->data["group4"]=0;
         }
       }
       $debug->Trace_R($record->data);
       $debug->Trace($record->npj_account);
       $record->Save();
       $principal->UnMask();
     }
   }
 }
 else
  $this->data["error"] = "POSTFULL: User/Password omitted";

 if ($this->data["error"]) $this->Save();

?>