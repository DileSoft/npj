<?php
// ---------------------------------------------------------------------
  $rh->UseClass("ListSimple",         $rh->core_dir);
  $rh->UseClass("Form"      ,         $rh->core_dir);
  $rh->UseClass("Field"     ,         $rh->core_dir);
  $rh->UseClass("FieldString" ,       $rh->core_dir);
  $rh->UseClass("FieldStringSelect" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  ,       $rh->core_dir);
  $rh->UseClass("FieldCheckboxes"  ,  $rh->core_dir);
  $rh->UseClass("FieldDT"     ,       $rh->core_dir);
  $rh->UseClass("ButtonList"  ,       $rh->core_dir);

  // ----> в переменной $issue лежит загруженная issue_data
  //  $debug->Error_R( $issue["RECORD"] );


  // =================================================================================
  //  ФАЗА 1. (none)

  $formatting = $principal->data["_formatting"];
  $edits = array( "wacko" => "wikiedit", "simplebr" => "simpleedit", "rawhtml" => "richedit" );
  $debug->Trace("issue_delete - choose formatting ".$formatting);

  // =================================================================================
  //  ФАЗА 2. Сборка простых и незатейливых групп полей формы

  // 2.1. --------------------------------------------- GROUP MAIN
  $tpl->Assign("DeInit", 0);
  if ($formatting=="rawhtml") $tpl->Assign("DeInit", 1);
  //отображатьтолько один редактор
  {
    if ($formatting=="simplebr") 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_simpleedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_SimpleEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="wacko")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_wikiedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_WikiEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="rawhtml")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_richedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_RichEdit", 
                          "maxlen" => 64000,
                           ) ); 
  }
  // =================================================================================
  //  ФАЗА 4. Формирование массива групп
  //    * main    -- всегда
    $group_state =  "0"; 
    $form_fields = array( "main"   => &$group1, );

  // msgset
  $tpl->MergeMessageSet( $rh->message_set."_form_Issue_Delete", $this->messagesets_dir );
  // =================================================================================
  //  ФАЗА 7. Построение самой формы
      $form_config = array( 
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "group_state" => $group_state, 
      "message_set" => $rh->message_set."_form_TrakoIssue",
      "buttons_small" => 0,
      "params"      => " onsubmit='if (as_update) as_update.updateRTEs();return true;' ",
        );

      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextDelete"],  
                 "tpl_name" => "forms/buttons.html:Delete", "handler" => "_nothing", "default"=>1 )
          );
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", );


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>