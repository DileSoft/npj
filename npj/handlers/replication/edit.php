<?php

  // проверка на банлист
  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  switch ($params[0])
  {
   case "dest":
     $node    = $params[1];
     $repid   = $params[2];
     $reptype = $params[3];
     $account = $params[4];
     $record  = $params[5];
     // обработчик формы
     include( $dir."/!form_dest.php" );
     if (!isset($_POST["__form_present"])) 
       $form->ResetSession();
     $debug->Milestone( "Starting form handler" );
     $tpl->theme = $rh->theme;
     $result= $form->Handle();
     $tpl->theme = $rh->skin;
     if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
     $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
     if ($form->success)
     {
      /////// save to DB
      if ($reptype==REP_RECORDS)
      {
       if (is_array($form->hash["communities"]->data)) 
       {
        $db->Execute("INSERT INTO ".$rh->db_prefix."replica_dest_rules ".
         "(rep_rule_id, node_id, owner_id) VALUES (".$db->Quote($repid).", ".
         $db->Quote($node).", ".$db->Quote($principal->data["user_id"]).")");
        $id = $db->Insert_ID();

        foreach( $form->hash["communities"]->data as $user_id )
        {
         $db->Execute("INSERT INTO ".$rh->db_prefix."replica_dests ".
          "(dest_rule_id, keyword_id) VALUES (".$db->Quote($id).", ".$db->Quote($user_id).")");
        }
       }
      }
      else
      {
       //из-за валидатора код дублируется !!!
       $target  = &new NpjObject( &$rh, $form->hash["record"]->data);
       $data    = $target->Load(1);
       if (is_array($data))
       {
        $db->Execute("INSERT INTO ".$rh->db_prefix."replica_dest_rules ".
         "(rep_rule_id, node_id, owner_id, record_id) VALUES (".$db->Quote($repid).", ".
         $db->Quote($node).", ".$db->Quote($principal->data["user_id"]).", ".
         $db->Quote($data["record_id"]).")");
       }
      }
      /////// redirect to foreign node
      $nodeobject = &new NpjObject( &$rh, "show@".$node );
      $nodeobject->Load(1);
      $rh->Redirect($nodeobject->data["url"]."foreign/".$rh->node_name."/".
                    $principal->data["login"]."/replication/edit/final/".$repid);
     }
   break;

   case "final":
     $repid   = $params[1];

     /////// save "valid"
     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."replica_rules WHERE rep_rule_id = ".$db->Quote($repid));
     if ($principal->data["user_id"] != $rs->fields["owner_id"]) return $this->Forbidden("ReplicationForbiddenEdit");
     $db->Execute("UPDATE ".$rh->db_prefix."replica_rules SET valid = 1 WHERE rep_rule_id = ".$db->Quote($repid));

     /////// save to subscription
     if ($rs->fields["reptype"]==REP_RECORD_COMMENTS)
     {
      $class = "record";
      $oid = $rs->fields["record_id"];
      $method = "commentreplica";
     }
     else 
     {
      $target  = &new NpjObject( &$rh, $rh->node_user);
      $data    = $target->_LoadById($rs->fields["record_id"],1,"record");
      $data    = $target->_LoadById($data["user_id"],1,"account");
      if ($data["account_type"]==0)
       $class = "cluster";
      else 
       $class = "facet";
      $oid = $rs->fields["record_id"];
      $method = ($rs->fields["reptype"]==REP_RECORDS?"replica":"commentreplica");
     }

     $db->Execute("INSERT INTO ".$rh->db_prefix."subscription (object_class, object_id, object_method, method_option, user_id) ".
                  "VALUES (".$db->Quote($class).", ".$db->Quote($oid).", ".$db->Quote($method).", ".
                  $db->Quote($repid).", ".$db->Quote($principal->data["user_id"]).")");
     $tpl->Assign("Preparsed:CONTENT", ($_SESSION["rep_back"]?"<a href='".$this->Href($_SESSION["rep_back"])."'>Вернуться</a>.":"Ура!"));
     $tpl->Assign("Preparsed:TITLE", "Успешно сохранено.");
   break;

   case "rule":
   default:
     $reptype = $params[1];
     $account = $params[2];
     $record  = $params[3];
     $trgt    = $account.":".($reptype==REP_RECORD_COMMENTS?$record:"");
     $target  = &new NpjObject( &$rh, $trgt );
     $data    = $target->Load(1);
     $url     = $rh->url;

     if (is_array($data))
     {
      // обработчик формы
      include( $dir."/!form_rule.php" );
      if (!isset($_POST["__form_present"])) 
        $form->ResetSession();
      $debug->Milestone( "Starting form handler" );
      $tpl->theme = $rh->theme;
      //hack ???
      $rh->url = "foreign/".$principal->data["node_id"]."/".$principal->data["login"]."/replication/edit/rule/".$params[1]."/".$params[2]."/".$params[3];
      $result= $form->Handle();
      $rh->url = url;
      $tpl->theme = $rh->skin;
      if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
      $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
      if ($form->success)
      {
       /////// save to DB
       foreach( $form->hash as $k=>$v )
       {                                                                                     
         // создаём db_data на основе data (важно для сложных полей, хотя здесь таких вроде и нет) 
         $form->hash[$k]->_StoreToDb(); 
         $this->data[$k] = $form->hash[$k]->db_data;
         if (is_array($form->hash[$k]->db_data))
         {
           $debug->Trace_R( $form->hash[$k]->db_data );
           foreach ($form->hash[$k]->db_data as $field=>$value)
             $this->data[ $form->hash[$k]->config["fields"][$field] ] = $value;
         }
       }
       $this->data["owner_id"]   = $principal->data["user_id"];
       $this->data["node_id"]    = $principal->data["node_id"];
       $this->data["record_id"]  = $target->data["record_id"];
       $this->data["reptype"]    = $reptype;

       $fields = array("owner_id", "node_id", "record_id", "date_from", "date_to", "dont_doublereplicate", "maxperday", "maxdepth", "authors_white", "authors_black", "topic_white", "topic_black", "facet_white", "facet_black", "reptype");

       $add="";
       foreach( $fields as $f )
       {
         $query1.= $add.$f;
         $query2.= $add.$db->Quote( $this->data[$f] );
         if (!$add) $add=", "; 
       }
       $db->Execute("INSERT INTO ".$rh->db_prefix."replica_rules (".$query1.") VALUES (".$query2.")");
       $this->data["rep_rule_id"]=$db->Insert_ID();
       /////// redirect to foreign node
       $nodeobject = &new NpjObject( &$rh, "show@".$principal->data["node_id"] );
       $nodeobject->Load(1);
       $rh->Redirect($nodeobject->data["url"]."replication/edit/dest/".$rh->node_name.
              "/".$this->data["rep_rule_id"]."/".$reptype."/".$account."/".$record);
      }
    }
    else return $this->NotFound("RecordNotFound");
    
  }



//  $debug->Trace_R($_POST);
//  $debug->Error(4545);
//    return $this->Handler( "edit", array($form->hash["replica"]->data), &$principal );
    // редирект на /add/ok
    //$rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $this->npj_address."/ok/".$new_comment->name, 1 ),1) );


  return GRANTED;
?>