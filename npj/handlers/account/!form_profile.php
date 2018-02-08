<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldPassword", $rh->core_dir );
  $rh->UseClass("FieldCheckboxes", $rh->core_dir );


  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "user_name",
                          "nessesary" => 1,
                          "default" => $data["user_name"],
                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
    if (($object->data["account_type"] == ACCOUNT_COMMUNITY) ||
        ($object->data["account_type"] == ACCOUNT_WORKGROUP) )
    {
      $group1[] = &new FieldRadio( &$rh, array(
                            "field" => "security_type",
                             ) ); 
      $group1[] = &new FieldRadio( &$rh, array(
                            "field" => "default_membership",
                            "default" => GROUPS_LIGHTMEMBERS,
                             ) ); 
      $group1[] = &new FieldRadio( &$rh, array(
                            "field" => "post_membership",
                            "default" => GROUPS_LIGHTMEMBERS,
                             ) ); 
      $group1[] = &new FieldRadio( &$rh, array(
                            "field" => "announce_membership",
                            "default" => GROUPS_POWERMEMBERS,
                             ) ); 
    }
  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "bio",
                          "maxlen" => 4000,
                          "tpl_data" => "field_string.html:Textarea"
                           ) );
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "website_url",
                          "http" => 1,
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "website_name",
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "interests",
                          "maxlen" => 4000,
                          "tpl_data" => "field_string.html:TextareaSmall",
                          "tpl_row"  => "form.html:Row_Described",
                          ) );
  ///////////////////////////
    $group15[] = &new FieldString( &$rh, array(
                          "field" => "journal_name",
                           ) ); 
    $group15[] = &new FieldString( &$rh, array(
                          "field" => "journal_desc",
                          "maxlen" => 1000,
                          "tpl_data" => "field_string.html:TextareaSmall"
                           ) ); 
    $group15[] = &new FieldString( &$rh, array(
                          "field" => "file_url_prefix",
                          "http" => 1,
                          "tpl_row"  => "form.html:Row_Described",
                           ) );   ///////////////////////////
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "email",
                          "email" => 1,
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "icq_uin",
                          "maxlen" => 15,
                          "regexp" => "/^[0-9]*$/",
                          "regexp_help" => "Form.icq.Help",
                           ) ); 
  ///////////////////////////
    if ($object->data["account_type"] == ACCOUNT_USER) 
    $group3[] = &new FieldRadio( &$rh, array( 
                          "field" => "sex",
                           ) ); 
    $group3[] = &new FieldDT( &$rh, array(
                          "field" => "birthday",
                           )
                     ); 
  ///////////////////////////
    $group4[] = &new FieldString( &$rh, array(
                          "field" => "country",
                          "tpl_data" => "field_string.html:Plain"
                           ) ); 
    $group4[] = &new FieldString( &$rh, array(
                          "field" => "region",
                          "tpl_data" => "field_string.html:Plain"
                           ) ); 
    $group4[] = &new FieldString( &$rh, array(
                          "field" => "city",
                          "tpl_data" => "field_string.html:Plain"
                           ) ); 
  ///////////////////////////
  // moresettings (see that!)
  {
    $options = $this->data["advanced_options"];
    $radios = array( "typografica", "hide_email", "group_versions" );
    $defaults = array();  foreach( $radios as $field ) $defaults[] = $options[$field];
    $group5[] = &new FieldCheckboxes( &$rh, array( 
                          "field" => "advanced_options",
                          "fields" => $radios,
                          "default" => $defaults,
                          "db_ignore" => 1 
                             ) ); 
    $radios = array( "post_supertag", "post_date" );
    $defaults = array();  foreach( $radios as $field ) $defaults[] = $options[$field];
    $group5[] = &new FieldCheckboxes( &$rh, array( 
                          "field" => "advanced_post_options",
                          "fields" => $radios,
                          "default" => $defaults,
                          "db_ignore" => 1 
                             ) ); 

    $skins = array();
    foreach( $rh->skins as $skin ) 
    { $skins[$skin] = "Form.skin.$skin"; $tpl->message_set["Form.skin.$skin"] = $skin; }
    $group5[] = &new FieldRadio( &$rh, array(
                          "field" => "skin",
                          "data"  => $skins,
                          "tpl_data" => "field_radio.html:Select",
                           ) ); 

    $friends_templates = array();
    foreach( $rh->friends_templates as $ft ) 
    { $friends_templates[$ft] = "Form.friends_template.$ft"; $tpl->message_set["Form.friends_template.$ft"] = $ft; }
    $group5[] = &new FieldRadio( &$rh, array(
                          "field" => "friends_template",
                          "data"  => $friends_templates,
                          "tpl_data" => "field_radio.html:Select",
                           ) ); 

    $group5[] = &new FieldString( &$rh, array(
                          "field" => "template_announce",
                          "tpl_row" => "form.html:Row_Span",
                           ) ); 
    $group5[] = &new FieldString( &$rh, array(
                          "field" => "template_digest",
                          "tpl_row" => "form.html:Row_Span",
                           ) ); 
  }
  // ---

  ///////////////////////////
  $ms = $rh->message_set."_form_Profile";
  if ($object->data["account_type"] == ACCOUNT_COMMUNITY) $ms = $rh->message_set."_form_Community";
  if ($object->data["account_type"] == ACCOUNT_WORKGROUP) $ms = $rh->message_set."_form_WorkGroup";

      $form_config = array(
      "db_table"    => $rh->db_prefix."profiles", 
      "db_id"       => "user_id",
      "message_set" => $ms, 
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_update", "default"=>1 )
          );
      $form_fields = array( &$group1, &$group15, &$group2, &$group3, &$group4, &$group5 );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>