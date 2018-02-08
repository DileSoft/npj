<?php


 if ($this->data["text"])
 {
   $text = $this->data["text"];
 }
 else 
 {
   $text = $this->Format($this->data["html"], "after_htmlmail");
 }
 preg_match("/^\s*([a-z0-9\-]+)\:([a-z0-9]+)\s*(!f)?\s*(.*)$/is",$text,$matches);
 $user = $matches[1];
 $password = $matches[2];
 $access = $matches[3];
 $text = $matches[4];
 $debug->Trace($text."==".$user."==".$password."==".$text);

 if ($user && $password)
 {
   $rs = $db->Execute("SELECT user_id, user_name, node_id, _pic_id, _formatting, password, login FROM ".$rh->db_prefix.
                      "users where login=".$db->Quote($user)." AND node_id=".$db->Quote($rh->node_name));
   if (!$rs->fields)
    $this->data["error"] = "POSTLITE: No such user";
   else
   {
     if ($password!=$rs->fields["password"] && md5($password)!=$rs->fields["password"])
       $this->data["error"] = "POSTLITE: Incorrect password";
     else
     {
       $principal->MaskById( $rs->fields["user_id"] );
       $record =& new NpjObject(&$rh, $rs->fields["login"]."@".$rs->fields["node_id"].":1");
       if (is_array($this->data["headers"]["received"]))
       $date = $this->data["headers"]["received"][count($this->data["headers"]["received"])-1];
       else
         $date = $this->data["headers"]["received"];
       $pos = strrpos($date, ";"); 
       $date = substr($date, $pos+1);
       $record->data["user_datetime"] = date("Y-m-d H:i:s", strtotime($date));
       $record->data["type"] = 1;
       $record->data["user_id"] = $rs->fields["user_id"];
       //$record->data["pic_id"]  = $rs->fields["_pic_id"];
       $record->data["tag"] = "1";
       $record->data["subject"] = "";//$headers["subject"]?$headers["subject"]:$this->data["headers"]["subject"];
       $formatting = $rs->fields["_formatting"];
       //!!! пока нет форматтера htmlmail
       if ($formatting=="rawhtml") $formatting="simplebr";

       $record->data["formatting"] = $formatting;
       $record->data["body"] = $this->Format(trim($text), $formatting, "pre");

       $rs2 = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_FRIENDS.
                            " and user_id=".$db->Quote( $rs->fields["user_id"] ));
       $group_friends = 1*$rs2->fields["group_id"];

       if ($access=="!f") // все конфиденты
       {
        $record->data["group1"]=$group_friends;
        $record->data["group2"]=-2; $record->data["group3"]=0; $record->data["group4"]=0;
       }
       else  // все 
       {
        $record->data["group1"]=0; $record->data["group2"]=0; 
        $record->data["group3"]=0; $record->data["group4"]=0;
       }
//       $debug->Trace_R($record->data);
//       $debug->Trace($record->npj_account);
       $record->Save();
       $principal->UnMask();
     }
   }
 }
 else
  $this->data["error"] = "POSTLITE: User/Password omitted";

 if ($this->data["error"]) $this->Save();

?>