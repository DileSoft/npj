<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes"  , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);

    $group1[] = &new FieldString( &$rh, array(
                          "field" => "tag",
                          "tpl_row" => "form.html:Row_Span_Described",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "desc",
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Span_Described",
                          "tpl_data" => "field_string.html:TextareaSmall",
                          "maxlen" => 64000,
                           ) ); 

  ///////////////////////////
    $f=0; $acls = array(); foreach($this->acls as $ag) foreach($ag as $acl) $acls[]=$db->Quote($acl);
    $parent = $this->npj_account.":";
    $rs = $db->Execute( "select a.object_right, a.acl from ".$rh->db_prefix."acls as a, ".
                         $rh->db_prefix."records as r ".
                         " where a.object_type=".$db->Quote("record").
                         " and a.object_id=r.record_id".
                         " and r.supertag = ".$db->Quote($parent).
                         " and a.object_right in (".implode(",",$acls).")");
    if ($rs->RecordCount() == 0) 
    {
      $values = $rh->default_acls[ $rh->account->data["account_type"] ];
      // not tested patch ---
      if (isset($rh->account_classes[$rh->account->data["account_class"]]))
      {
        $target_class = $rh->account_classes[$rh->account->data["account_class"]];
        if (isset($target_class["acls"])) $values = $target_class["acls"];
      }
      // ---
    }
    else
    {
      $a = $rs->GetArray(); $values = array();
      foreach( $a as $item ) $values[$item["object_right"]] = $item["acl"];
    }
    $group3 = array();
    foreach( $this->acls as $i=>$ag )
    foreach( $ag as $acl )
      $group3[] = &new FieldString( &$rh, array(
                            "field" => $acl,
                            "db_ignore" => 1,
                            "default" => $values[ $acl ],
                            "tpl_row"    => $i?"form_horizontal.html:Row_Hidden":"form_horizontal.html:Row_Described",
                            "tpl_data" => "field_string.html:TextareaSmall_Fixed",
                             ) ); 

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "group_state" => "01",
      "message_set" => $rh->message_set."_form_Keyword",
      "buttons_small" => 1,
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 ),
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", "default"=>1 ),
                          );
      $form_fields = array(  &$group1, &$group3 );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>