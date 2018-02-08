<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldPassword", $rh->core_dir );

  ///////////////////////////
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "old_password",
                          "check" => $data["temporary_password"],
                          "default" => $params[2],
                          "tpl_data" => "field_string.html:Wide",
                          "nessesary" => 1,
                           ) ); 
  ///////////////////////////
    $group1[] = &new FieldPassword( &$rh, array(
                          "field" => "password",
                          "nessesary" => 1,
                           ) ); 
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."users", 
      "db_id"       => "user_id", 
      "message_set" => $rh->message_set."_form_Password_New"
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_update", "default"=>1 )
          );
      $form_fields = array(  &$group0, &$group1, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>