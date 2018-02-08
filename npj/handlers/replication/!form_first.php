<?php

  // форма 

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes"  , $rh->core_dir);

  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "record",
                          "readonly" => 1,
                          "db_ignore" => 1,
                          "default" => $record,
                           ) ); 

    $group1[] = &new FieldRadio( &$rh, array(
                          "field" => "replica",
                          "default" => REP_RECORDS,
                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."comments", 
      "message_set" => $rh->message_set."_form_ReplicationAdd", 
      "group_state" => "0",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextNext"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      $form_fields = array(  
        &$group1, 
        );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>