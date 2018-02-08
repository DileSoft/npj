<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" ,       $rh->core_dir);
  $rh->UseClass("FieldStringSelect" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes", $rh->core_dir );


  ///////////////////////////
    $group1[] = &new FieldRadio( &$rh, array( 
                          "field" => "_formatting",
                          "db_ignore" => 1,
                           ) ); 
    $group1[] = &new FieldRadio( &$rh, array( 
                          "field" => "_notify_comments",
                           ) ); 
    $group1[] = &new FieldRadio( &$rh, array( 
                          "field" => "_replication_allowed",
                           ) ); 
  // moresettings (see that!)
  {
    // где вносить изменения, чтобы сеттинги сохранялись -- see [*****] in settings.php

    $options = $principal->DecomposeOptions($this->data["more"]);
    $radios = array( /*"user_menu", "novice_panel",*/ "double_click", "edit_simple", "sodynamic_off" );
    $defaults = array();  foreach( $radios as $field ) $defaults[] = $options[$field];
    $radios2 = array( "comments_always" );
    $defaults2 = array();  foreach( $radios2 as $field ) $defaults2[] = $options[$field];
    $group1[] = &new FieldCheckboxes( &$rh, array( 
                          "field" => "more_options",
                          "fields" => $radios,
                          "default" => $defaults,
                          "db_ignore" => 1 
                             ) ); 

    $group1[] = &new FieldStringSelect( &$rh, array( 
                          "field" => "keywords_auto",
                          "default" => $options[ "keywords_auto" ],
                          "db_ignore" => 1 
                             ) ); 

    $classification = &new FieldRadio( &$rh, array( 
                          "field" => "classification",
                          "default" => $options[ "classification" ]*1,
                          "db_ignore" => 1
                           ) ); 

    if ($this->data["account_type"] == ACCOUNT_USER)
      $group15[] = &$classification;
    else
      $group1[] = &$classification;

    $group15[] = &new FieldRadio( &$rh, array( 
                          "field" => "record_stats",
                          "default" => $options[ "record_stats" ],
                          "db_ignore" => 1
                           ) ); 

    $group15[] = &new FieldRadio( &$rh, array( 
                          "field" => "comments",
                          "default" => $options[ "comments" ],
                          "db_ignore" => 1
                           ) ); 
    $group15[] = &new FieldCheckboxes( &$rh, array( 
                          "field" => "more_comments",
                          "fields" => $radios2,
                          "default" => $defaults2,
                          "db_ignore" => 1 
                             ) ); 

    $skins = array( ""=>"Form.skin_override.0" );
    $tpl->message_set["Form.skin_override.0"] = "в их собственном стиле";
    foreach( $rh->skins as $skin ) 
    { $skins[$skin] = "Form.skin_override.$skin"; $tpl->message_set["Form.skin_override.$skin"] = $skin; }
    $group15[] = &new FieldRadio( &$rh, array(
                          "field" => "skin_override",
                          "data"  => $skins,
                          "default" => $data["skin_override"],
                          "db_ignore" => 1,
                          "tpl_data" => "field_radio.html:Select",
                           ) ); 
    $group15[] = &new FieldRadio( &$rh, array(
                          "field" => "group_versions_override",
                          "default" => $data["group_versions_override"],
                          "db_ignore" => 1,
                           ) ); 
    $group15[] = &new FieldRadio( &$rh, array(
                          "field" => "post_supertag_override",
                          "default" => isset($options["post_supertag_ovr"])?$options["post_supertag_ovr"]:-1,
                          "db_ignore" => 1,
                           ) ); 
    $group15[] = &new FieldRadio( &$rh, array(
                          "field" => "post_date_override",
                          "default" => isset($options["post_date_ovr"])?$options["post_date_ovr"]:-1,
                          "db_ignore" => 1,
                           ) ); 
  }
  // ---
  ///////////////////////////
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "_personal_page_size",
                          "is_numeric" => 1,
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "_friends_page_size",
                          "is_numeric" => 1,
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "_recentchanges_size",
                          "is_numeric" => 1,
                           ) ); 
  ///////////////////////////
    $group3[] = &new FieldString( &$rh, array(
                          "field" => "password",
                          "db_ignore" => 1,
                          "default" => $this->_NpjAddressToUrl($this->npj_account.":settings/password"),
                          "tpl_data" => "field_link.html:Button",
                           ) ); 
    $group3[] = &new FieldString( &$rh, array(
                          "field" => "freeze",
                          "db_ignore" => 1,
                          "default" => $this->_NpjAddressToUrl($this->npj_account.":manage/freeze"),
                          "tpl_data" => "field_link.html:Button",
                           ) ); 
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."profiles", 
      "db_id"       => "user_id",
      "message_set" => $rh->message_set."_form_UserSettings"
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_update", "default"=>1 )
          );
      $form_fields = array(  &$group1, &$group15, &$group3, &$group2, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>