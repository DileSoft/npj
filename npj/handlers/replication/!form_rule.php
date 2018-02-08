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
                          "field" => "account",
                          "db_ignore" => 1,
                          "default" => $account,
                          "readonly" => 1,
                           ) ); 
  if ($reptype==REP_RECORD_COMMENTS)
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "record",
                          "db_ignore" => 1,
                          "default" => $record,
                          "readonly" => 1,
                           ) ); 


    $group1[] = &new FieldDT( &$rh, array(
                          "field" => "date_from",
                          "time"=>1, "date"=>1,
//                          "db_ignore" => 1,
                          "tpl_data" => "field_dt.html:DateTime_Calendar",
                           ) ); 
    $group1[] = &new FieldDT( &$rh, array(
                          "field" => "date_to",
                          "time"=>1, "date"=>1,
//                          "db_ignore" => 1,
                          "default"=>date("Y-m-d H:i:s", time()+60*60*24*365),
                          "tpl_data" => "field_dt.html:DateTime_Calendar",
                           ) ); 
    $group1[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "options",
                          "fields" => array("dont_doublereplicate"),
                          "default" => array(1),
                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
  if ($reptype==REP_RECORDS)
  {
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "facet_white",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "facet_black",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
  }
  ///////////////////////////
    $group3[] = &new FieldString( &$rh, array(
                          "field" => "maxperday",
                          "is_numeric" => 1,
//                          "db_ignore" => 1,
                           ) ); 
  if ($reptype!=REP_RECORDS)
    $group3[] = &new FieldString( &$rh, array(
                          "field" => "maxdepth",
                          "is_numeric" => 1,
//                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
    $group4[] = &new FieldString( &$rh, array(
                          "field" => "authors_white",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
    $group4[] = &new FieldString( &$rh, array(
                          "field" => "authors_black",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
    $group5[] = &new FieldString( &$rh, array(
                          "field" => "topic_white",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
    $group5[] = &new FieldString( &$rh, array(
                          "field" => "topic_black",
                          "tpl_row"    => "form_horizontal.html:Row_Described",
                          "tpl_data" => "field_string.html:TextareaSmall_Fixed",
//                          "db_ignore" => 1,
                           ) ); 
  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."replica_rules", 
      "message_set" => $rh->message_set."_form_ReplicationEdit", 
      "group_state" => "01111",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextNext"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      if ($reptype==REP_RECORDS)
       $form_fields = array(  
         "0"=>&$group1, "1"=>&$group2, "2"=>&$group3, "3"=>&$group4, "4"=>&$group5, 
         );
      else
       $form_fields = array(  
         "0"=>&$group1, "2"=>&$group3, "3"=>&$group4, "4"=>&$group5, 
         );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>
