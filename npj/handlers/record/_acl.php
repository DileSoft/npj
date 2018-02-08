<?php

  $data = &$this->Load( 2 );

  if ($data && $data!="empty") 
  {
   if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]] )) 
    return $this->Forbidden("RecordForbiddenACL");
   // !!!! не забыть почекать read_acl
   // !!!! не забыть почекать write_acl и поинклюдить _acl_view, если нельзя

   // обработчик формы
   include( $dir."/!form_acls.php" );
   if (!isset($_POST["__form_present"])) 
   { 
     $form->ResetSession();
   }

 $debug->Milestone( "Starting form handler" );
 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
 $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

  if ($form->success)
  {
  // ==== проставление ACLs ===============================================================================
    { $acls = array(); foreach($this->acls as $acl_group) foreach($acl_group as $acl) 
      { $acls[]=$db->Quote($acl); $_acls[] = $acl; }
      $db->Execute( "delete from ".$rh->db_prefix."acls where object_type=".$db->Quote("record").
                          " and object_id=".$data["record_id"]." and object_right in (".
                          implode(",",$acls).")");
      $sql = ""; $f=0;
      foreach( $_acls as $acl )
      { if ($f) $sql.=","; else $f=1;
        $sql.="(".$db->Quote("record").",".$data["record_id"].",".$db->Quote($acl).",".
                  $db->Quote($form->hash[$acl]->data).")";
      }
      if ($sql != "")
       $db->Execute("insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".$sql);
    }
  // ==== сохранение банлиста ===============================================================================
  if ($object->name == "")
    {
      $acl = $form->hash["global_access_acl"]->data;
      $db->Execute( "delete from ".$rh->db_prefix."acls where ".
                    "object_type = ".$db->Quote("account")." and ".
                    "object_id   = ".$db->Quote($data["user_id"])." and ".
                    "object_right= ".$db->Quote("banlist") );
      $db->Execute( "insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".
                    "(".$db->Quote("account").", ".
                        $db->Quote($data["user_id"]).", ".
                        $db->Quote("banlist").", ".
                        $db->Quote($acl).")" );
    }

    // редирект на record/show
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $object->npj_object_address, 0 ),1) );
  }
  } 
  else return $this->NotFound("RecordNotFound");

  return GRANTED;

?>

