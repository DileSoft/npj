<?php

  $data = &$this->Load( 2 );

  if ($data && $data!="empty") 
  {
   if (!$this->HasAccess( &$principal, "owner" )) return $this->Forbidden("RecordForbiddenACL");

   // обработчик формы
   include( $dir."/!form_groups.php" );
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
   { // ==== проставление group1..4
    if ($form->hash["groups"]->data[0]==-1) // все
    {
     $data["group1"]=0; $data["group2"]=0; 
     $data["group3"]=0; $data["group4"]=0;
    }
    else if ($form->hash["groups"]->data[0]==0) // никто
    {
     $data["group1"]=$rh->account->group_nobody;
     $data["group2"]=-1; $data["group3"]=0; $data["group4"]=0;
    }
    else if ($form->hash["groups"]->data[0]==-2) // все конфиденты
    {
     $data["group1"]=$rh->account->group_friends;
     $data["group2"]=-2; $data["group3"]=0; $data["group4"]=0;
    }
    else if ($form->hash["groups"]->data[0]==ACCESS_GROUP_COMMUNITIES) // всем сообществам
    {
     $data["group1"]=$rh->account->group_communities;
     $data["group2"]=ACCESS_GROUP_COMMUNITIES; 
     $data["group3"]=$form->hash["groups"]->radio_data; 
     $data["group4"]=0;
    }
    else
    { //[_items_in_groups] -- мы не работаем с постом бля
     $grps = $form->hash["groups"]->data;
     for ($gnum=0; $gnum<4; $gnum++)
      if (!isset($grps[$gnum])) $data["group".($gnum+1)] = 0;
      else $data["group".($gnum+1)] = $grps[$gnum];
    }
   }
    $db->Execute( "update ".$rh->db_prefix."records SET ".    
                  "group1 = ".$db->Quote($data["group1"]).",".
                  "group2 = ".$db->Quote($data["group2"]).",".
                  "group3 = ".$db->Quote($data["group3"]).",".
                  "group4 = ".$db->Quote($data["group4"]).
                  " where record_id=".$db->Quote($data["record_id"]) );
    // then refs
    $db->Execute( "update ".$rh->db_prefix."records_ref SET ".    
                  "group1 = ".$db->Quote($data["group1"]).",".
                  "group2 = ".$db->Quote($data["group2"]).",".
                  "group3 = ".$db->Quote($data["group3"]).",".
                  "group4 = ".$db->Quote($data["group4"]).
                  " where record_id=".$db->Quote($data["record_id"]) );
    // редирект на record/show
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $object->npj_object_address, 0 ),1) );
  }
  } 
  else return $this->NotFound("RecordNotFound");

  return GRANTED;

?>

