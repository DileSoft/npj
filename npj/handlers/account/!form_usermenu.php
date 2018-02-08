<?php

  // форма регистрации нового пользователя

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);

  ///////////////////////////
    $group0[] = &new FieldString( &$rh, array( "field" => "npj_address", "nessesary" => 1 ) );
    $group0[] = &new FieldString( &$rh, array( "field" => "title",  ) );
    $group0[] = &new FieldString( &$rh, array( "field" => "user_id", "default"=> $object->data["user_id"],
                                               "readonly"=>1, "tpl_row"=>"form.html:Row_Hidden" ) );
    $group0[] = &new FieldString( &$rh, array( "field" => "pos", "default"=> sizeof($object->data["user_menu"])+1,
                                               "readonly"=>1, "tpl_row"=>"form.html:Row_Hidden" ) );
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."user_menu", 
      "db_id"       => "item_id",
      "message_set" => $rh->message_set."_form_Usermenu", 
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_insert", "default"=>1 )
          );
      $form_fields = array( &$group0 );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>