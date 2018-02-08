<?php
  $data = &$this->Load( 2 );

  if ($data && $data!="empty") 
  {
   // проверка на банлист
   if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) 
    return $this->Forbidden("YouAreInBanlist");

   if (!$this->HasAccess(&$principal, "noguests")) 
    return $this->Forbidden("NoGuests");

   if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]], "read" )) 
    return $this->Forbidden("RecordForbidden");

   // обработчик формы
   include( $dir."/!form_subscribe.php" );
   if (!isset($_POST["__form_present"])) 
   { 
     $form->ResetSession();
   }

   $tpl->theme = $rh->theme;
   $result= $form->Handle();
   $tpl->theme = $rh->skin;
   if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
   $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

   if ($form->success)
   {
   // ==== сохраняемся ===========================================================================
     $sql = "delete from ".$rh->db_prefix."subscription ".
            " WHERE user_id=".$db->Quote($principal->data["user_id"]).
            " AND object_id=".$db->Quote($this->data["record_id"]);
     $rs = $db->Execute( $sql ); 

     foreach( $form->hash["subscription"]->data as $k=>$v )
     {
       $debug->Trace($form->hash["subscription"]->config["fields"][$k]."=>".$v);
       $omg = explode("_",trim($form->hash["subscription"]->config["fields"][$k],"2"));
       if ($v && $omg[1])
       {
        $sql = "insert into ".$rh->db_prefix."subscription ".
               "(object_class, object_id, object_method, method_option, user_id) ".
               "VALUES (".$db->Quote($omg[0]).",".$db->Quote($this->data["record_id"]).
               ",".$db->Quote($omg[1]).",".$db->Quote("").",".
               $db->Quote($principal->data["user_id"]).")";
        $db->Execute($sql);
        //if ($debug->kuso) $debug->Error($sql);
       }
     }

    // редирект на record/show
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $object->npj_object_address, 0 ),1) );
   }
  } 
  else return $this->NotFound("RecordNotFound");

  return GRANTED;

?>