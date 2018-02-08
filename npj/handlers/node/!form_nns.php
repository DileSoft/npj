<?php

  // форма nns

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes", $rh->core_dir );

  $is_new=0;
  if ($rh->node->data["created_datetime"]=="0000-00-00 00:00:00") $is_new=1;


    $group0[] = &new FieldString( &$rh, array(
                          "field" => "about",
                          "default" => $tpl->message_set["AboutNNS"],
                          "readonly" => 1,
                          "db_ignore" => 1,
                           ) ); 
    $group0[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "options",
                          "fields" => array("allow_update"),
                          "default" => array(1),
                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////

      $form_config = array(
      "message_set" => $rh->message_set."_form_Nns", 
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextNNS".($is_new?"1":"2")],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      $form_fields = array( &$group0, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>