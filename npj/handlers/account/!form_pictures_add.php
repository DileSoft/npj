<?php
  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldUpload", $rh->core_dir );
  $rh->UseClass("ButtonList"  , $rh->core_dir);


  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array( 
                          "field" => "user_id",
                          "readonly"=>1,
                          "tpl_row" => "form.html:Row_Hidden",
                          "default" => $this->data["id"],
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "description",
                          "nessesary" => 1,
                           ) ); 
    $group1[] = &new FieldRadio( &$rh, array(
                          "field" => "is_default",
                          "db_ignore" => 1,
                           ) ); 
    $group2[] = &new FieldUpload( &$rh, array(
                          "field" => "have_big",
                          "save_dir" => $rh->user_pictures_dir,
                          "extensions" => array( "gif", "jpg", "jpeg", "jpe", "png" ),
                          "maxsize" => "100000",
                          "max_wh" => array( $rh->user_pictures_big_x, $rh->user_pictures_big_y ),
                          "db_ignore" => 1,
                           ) ); 
    $group2[] = &new FieldUpload( &$rh, array(
                          "field" => "have_small",
                          "save_dir" => $rh->user_pictures_dir,
                          "extensions" => array( "gif", "jpg", "jpeg", "jpe", "png" ),
                          "maxsize" => "100000",
                          "max_wh" => array( $rh->user_pictures_small_x, $rh->user_pictures_small_y ),
                          "db_ignore" => 1,
                          "nessesary" => array( "have_big" ),
                          "musthave" => 1,
                           ) ); 
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "upload"      => 1,
      "db_table"    => $rh->db_prefix."userpics", 
      "db_id"       => "pic_id",
      "message_set" => $rh->message_set."_form_UserPictures"
        );

      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_insert", "default"=>1 )
          );

      $form_fields = array(  &$group1 , &$group2 );


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>