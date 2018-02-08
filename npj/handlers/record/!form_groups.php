<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldMultiple" , $rh->core_dir);
  $rh->UseClass("FieldMultiplePlus" , $rh->core_dir);

  ///////////////////////////

  // ---------- дерьмовый кусок кода. Надо бы его рефакторить!!!!!!! СКОПИРОВАН ИЗ !form_record.php
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_SELF." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_nobody = 1*$rs->fields["group_id"];
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_FRIENDS." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_friends = 1*$rs->fields["group_id"];
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_COMMUNITIES." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_communities = 1*$rs->fields["group_id"];
  // ------------------------

    $helper = &$this->SpawnHelper();
    $group3 = array();
    $is_new = false;
    $group3 = &$helper->CreateAccessFields( &$group3, &$this, $is_new );


  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "message_set" => $rh->message_set."_form_RecordGroups",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", "default"=>1 );

      $form_fields = array(  &$group3, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>