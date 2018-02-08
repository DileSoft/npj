<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldPassword", $rh->core_dir );


  $can_write = $this->HasAccess( &$principal, "owner" ) || $this->HasAccess( &$principal, "acl", "acl_write") ;


  $groups = array();
  foreach( $this->acls as $acl_group )
  {
    if (!$is_new)
    { $acls = array(); foreach($acl_group as $acl) $acls[]=$db->Quote($acl);
      $rs = $db->Execute( "select object_right, acl from ".$rh->db_prefix."acls where object_type=".$db->Quote("record").
                          " and object_id=".$this->data["record_id"]." and object_right in (".
                          implode(",",$acls).")");
      $a = $rs->GetArray(); $values = array();
      foreach( $a as $item ) $values[$item["object_right"]] = $item["acl"];
    }

    $group = array();
    foreach( $acl_group as $acl )
    {
      $group[] = &new FieldString( &$rh, array(
                            "field" => $acl,
                            "db_ignore" => 1,
                            "default" => $values[ $acl ],
                            "tpl_row"    => "form_horizontal.html:Row_Described",
                            "tpl_data" => "field_string.html:TextareaSmall_Fixed",
                            "readonly" => !$can_write,
                            "tpl_readonly" => "field_string.html:TextareaSmall_Readonly",
                             ) ); 
    }
    $groups[] = $group;
  }
  if ($can_write && $object->name == "") // “ќЋ№ ќ ƒЋя ∆”–ЌјЋј
  {
     // получаем банлист
     $banlist = $rh->cache->Restore( "account_acl_banlist", $data["user_id"], 2 );
     if ($banlist === false)
     {
        $rs = $db->Execute( "select acl from ".$rh->db_prefix."acls where ".
                            "object_type = ".$db->Quote("account")." and ".
                            "object_id   = ".$db->Quote($data["user_id"])." and ".
                            "object_right= ".$db->Quote("banlist") );
        if ($rs->RecordCount() > 0) $banlist = $rs->fields["acl"];
        else $banlist = "";
     }

    $group1[] =  &new FieldString( &$rh, array(
                            "field" => "global_access_acl",
                            "db_ignore" => 1,
                            "default" => $banlist,
                            "tpl_row"    => "form_horizontal.html:Row_Described",
                            "tpl_data" => "field_string.html:TextareaSmall_Fixed",
                             ) ) ; 
    $groups[] = $group1;
  }
  ///////////////////////////
  // ??? если у нас таки когда-нибудь будет смена владельца, то еЄ вписать сюда.
  //      усо пока не видит реальной пользы от такой пр€мой реализации, думает, что
  //     это нужно в переименование отнести. јдрес же изменитс€.

  ///////////////////////////
      $form_config = array(
      "tpl_name"    => "form_horizontal.html:Form",
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "group_state" => $can_write?"0111":"0",
      "message_set" => $rh->message_set."_form_RecordAcls",
      "critical"    => $can_write?$rh->critical_forms:false,
        );
      if ($can_write)
      {
        $form_buttons = array(
            array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                   "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
            );
      } else $form_buttons = array();
      $form_fields = &$groups;

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>