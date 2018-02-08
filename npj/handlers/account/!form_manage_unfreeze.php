<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldPassword", $rh->core_dir );

  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "alive",
                          "tpl_row" => "form.html:Row_Hidden",
                          "default" => 1,
                          "readonly" => 1,
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "old_password",
                          "check_md5" => $data["password"],
                          "tpl_data" => "field_string.html:SinglePassword",
                          "nessesary" => 1,
                           ) ); 
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."users", 
      "db_id"       => "user_id", 
      "message_set" => $rh->message_set."_form_Unfreeze"
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_update", "default"=>1 )
          );
      $form_fields = array(  &$group1, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>