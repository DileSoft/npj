<?php

 $text = $this->data["text"];
 $text = str_replace( "\r", "", $text );
 $text = str_replace( "\n", "", $text );
 $text = str_replace("\t2", "\n", $text);
 $text = str_replace("\t3", "\r", $text);
 $text = str_replace("\t1", "\t", $text);
 $struct = unserialize($text);
 $debug->Trace_R($this->data);
 $debug->Trace_R($struct);
// $debug->Error("reprecieve camed", 4); // breaking up!

/// !!!! откуда пришла посылка, как это узнать? пусть это ПОКА лежит в struct["node"]


 //// Пучим юзеров --------------------------------------------------------------------
 foreach ($struct["users"] as $u)
 {
  //check node_id of that user
  if ($u["node_id"]!=$rh->node_name)
  {
   $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));
   if ($rs->RecordCount()==0)
   {
    $sql = "INSERT INTO ".$rh->db_prefix."users (login,node_id,user_name,alive,_formatting,_pic_id,theme,lang,more,email) VALUES (".
      $db->Quote($u["login"]).", ".$db->Quote($u["node_id"]).", ".$db->Quote($u["user_name"]).", ".
      $db->Quote($u["alive"]).", ".$db->Quote($u["_formatting"]).", ".$db->Quote($u["_pic_id"]).", ".
      $db->Quote($u["theme"]).", ".$db->Quote($u["lang"]).", ".$db->Quote($u["more"]).", ".
      $db->Quote($u["email"]).")";
    $db->Execute($sql);

   //популяция
    $account = &new NpjObject( &$rh, $u["login"]."@".$u["node_id"] );
    $node_principal = &new NpjPrincipal( &$rh );
    $rh->principal->MaskById(2);
    $account->Handler( "populate", array("foreign"=>1,), &$node_principal );
    $rh->principal->UnMask();

   }
   else
   {
    $sql = "UPDATE ".$rh->db_prefix."users SET user_name=".$db->Quote($u["user_name"]).
      ", alive=".$db->Quote($u["alive"]).",     _formatting=".$db->Quote($u["_formatting"]).
      ", _pic_id=".$db->Quote($u["_pic_id"]).", theme=".$db->Quote($u["theme"]).
      ", lang=".$db->Quote($u["lang"]).",       more=".$db->Quote($u["more"]).
      ", email=".$db->Quote($u["email"]).
      " WHERE node_id=".$db->Quote($u["node_id"])." AND login=".$db->Quote($u["login"]);
    $db->Execute($sql);
   }
  }
 }


 //// Пучим правила --------------------------------------------------------------------
 $fields_record  = array("tag","user_datetime",
                    "disallow_comments","disallow_notify_comments","disallow_syndicate","formatting",
                    "pic_id","subject","body","rare");
 $fields_comment = array("pic_id", "subject", "body_post", "user_id", "user_login", "user_name",
                    "user_node_id", "created_datetime", "rep_original_id", "rep_node_id", 
                    "replicator_user_id");
 $fields_reprule = array("object_id","object_class","node_id","datetime","rep_rule_id");

 
 foreach ($struct["rules"] as $_rule)
 {
//  if ($died==2) $debug->Error("Uff");
  $debug->Trace("Choose rule: id ".$_rule["rep_rule_id"].", total ".count($struct["rules"]));

  $ruls = $db->Execute("SELECT * FROM ".$rh->db_prefix."replica_dest_rules ".
            "WHERE rep_rule_id=".$db->Quote($_rule["rep_rule_id"])." AND node_id=".$db->Quote($struct["node"]));
  // Удостовериться что логика заполнения node_id в replica_dest_rules такая же, как тут ???
  if ($ruls->fields)
  {
   if (isset($ruls->fields["touched_datetime"]))
   {
      $db->Execute("update ".$rh->db_prefix."replica_dest_rules ".
                " SET touched_datetime=".$db->Quote(date("Y-m-d H:i:s")).
                " WHERE dest_rule_id=".$db->Quote($ruls->fields["dest_rule_id"]));
   }

   if ($_rule["object_class"]=="record")
   {
     $_record = &$struct["records"][$_rule["object_id"]];
     
     $debug->Trace("Let battle begins: ".$_record["id"]."//".$_rule["object_id"]);
     
     $u = &$struct["users"][$_record["user_id"]];
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));

     $debug->Trace("SQL:"."SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));

     $tag = $_record["tag"];
     $type = is_numeric($tag{0})?RECORD_POST:RECORD_DOCUMENT; // !!!!!! refactor to some NpjObject (or better) function

     if ($rs->fields && $rs->fields["node_id"]!=$rh->node_name //check node_id of that user
         && $type==RECORD_POST) //only posts!
     {

       $principal->MaskById( $rs->fields["user_id"] );

       $debug->Trace("We at mask: {".$rs->fields["user_id"]."}".$rs->fields["login"]."@".$rs->fields["node_id"]."/".$rh->node_name.":".$tag);

       $record =& new NpjObject(&$rh, $rs->fields["login"]."@".$rs->fields["node_id"]."/".$rh->node_name.":".$tag);
/*
Записи

"supertag"                   x
"tag"                        "tag"                                 +
"depth"                      x
"id"                         x
"record_id"                  rare.rep_original_id
"user_id"                    f($u)                                 +
"type"                       кролик                                +
"created_datetime"           x
"user_datetime"              "user_datetime"                       +
"disallow_comments"          "disallow_comments"                   +
"disallow_notify_comments"   "disallow_notify_comments"            +
"disallow_syndicate"         "disallow_syndicate"                  +
"formatting"                 "formatting"                          +
"is_keyword"                 ? \
"is_announce"                ?  - пока бл не реплицируем. !!!
"is_digest"                  ? /
"pic_id"                     "pic_id"                              +
"subject"                    "subject"                             +
"body"                       "body"                                +
"rare"                       "rare"                                +
--------
"_r", "_post" !!!
rare.rep*                                                          +

Каменты
"ip_xff"
"record_id"
*/
       foreach ($fields_record as $fld)
         $record->data[$fld] = $_record[$fld];

       $record->data["type"] = $type;
       $record->data["user_id"] = $rs->fields["user_id"];

       if (!$record->data["rare"]["rep_original_id"])
       {
         $record->data["rare"]["rep_original_id"] = $_record["record_id"];
         $record->data["rare"]["rep_node_id"] = $struct["node"];
       }

       $record->data["rare"]["replicator_user_id"] = $ruls->fields["owner_id"];

       if ($record->data["type"]==RECORD_POST)
       {
         $record->data["group1"]=0; $record->data["group2"]=0; 
         $record->data["group3"]=0; $record->data["group4"]=0;
       }

       //КАММУНИТИЗ СОГЛАСНО ПРАВИЛА ЗАПОЛНИТЬ
       // !!! this code will obsolete когда мы поменяем работу контрола выбора сообщества
       $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."replica_dests WHERE dest_rule_id=".$db->Quote($ruls->fields["dest_rule_id"]));
       $dests = $rs->GetArray();
       $record->data["communities"] = array();
       foreach ($dests as $dest)
         $record->data["communities"][] = $dest["keyword_id"]; 
         //оторвать ноги мудаку кукуцу, который обозвал юзерид кейвордидом в августе`03 or even `02

       $debug->Trace_R($record->data);
       //$debug->Error($record->npj_account);
       $died++;
       $record->Save();
       $principal->UnMask();
     }
   }
   else if ($_rule["object_class"]=="comment")
   {
     $_comment = &$struct["comments"][$_rule["object_id"]];
     
     $debug->Trace("Let <b>joke</b> begins: ".$_comment["comment_id"]."//".$_rule["object_id"]);
     
     $u = &$struct["users"][$_comment["user_id"]];
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));

     $debug->Trace("SQL:"."SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($u["login"])." AND node_id=".$db->Quote($u["node_id"]));

     if ($rs->fields && $rs->fields["node_id"]!=$rh->node_name) //check node_id of that user
     {
       $record_id = $ruls->fields["record_id"];

       $rs2 = $db->Execute("SELECT supertag FROM ".$rh->db_prefix."records where record_id=".$db->Quote($record_id));
       if ($rs2->fields)
       {
        $supertag = $rs2->fields["supertag"];

        $principal->MaskById( $rs->fields["user_id"] );

        $debug->Trace("We at mask: ".$rs->fields["login"]."@".$rs->fields["node_id"]."/".$rh->node_name.":");

        $comment =& new NpjObject(&$rh, $supertag."/comments");

        foreach ($fields_comment as $fld)
          $comment->data[$fld] = $_comment[$fld];

        $comment->data["user_id"] = $rs->fields["user_id"];
        $comment->data["record_id"] = $record_id;
        $comment->data["parent_id"] = 0;
        $comment->data["lft_id"] = 0;
        $comment->data["rgt_id"] = 0;

        if (!$comment->data["rep_original_id"])
        {
          $comment->data["rep_original_id"] = $_comment["comment_id"];
          $comment->data["rep_node_id"] = $struct["node"];
        }

        $comment->data["replicator_user_id"] = $ruls->fields["owner_id"];

        $debug->Trace_R($comment->data);
        //$debug->Error($comment->npj_account);
        $died++;
        $comment->Save();
        $principal->UnMask();
       }
     }
   }
   else
   {
    $debug->Trace("<b>Strange class:</b> ".$_rule["object_class"]);
   }
  }//if
 }//foreach

  //$debug->Error("REPLICATION done ---------", 4);


?>