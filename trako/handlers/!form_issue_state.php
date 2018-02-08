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


  // msgset
  $tpl->MergeMessageSet( $rh->message_set."_form_Issue_State", $this->messagesets_dir );
  $msg = &$tpl->message_set;

  // =================================================================================
  //  ФАЗА 1. (none)

  $formatting = $principal->data["_formatting"];
  $edits = array( "wacko" => "wikiedit", "simplebr" => "simpleedit", "rawhtml" => "richedit" );
  $debug->Trace("issue_delete - choose formatting ".$formatting);

  // =================================================================================
  //  ФАЗА 2. Сборка простых и незатейливых групп полей формы

  // 2.0. --------------------------------------------- AVAILABLE STATUSES FOR NEW STATE
  // get available statuses
  $statuses = $trako->config["states"][$state_params["state"]]
                            ["statuses"];
  $status_data = array();                            
  foreach( $statuses as $status )
  {
    $status_data[ $status ] = $trako->config["statuses"][$status];
  }
  $msg["Form.issue_state_status.Data"] = $status_data;

  if ($this->HasAccess( &$principal, &$account, $issue, "status")) 
  $group1[] =  &new FieldRadio( &$this->rh, array(
                      "field" => "issue_state_status",
                      "default"   => $state_params["status"],
                      "db_ignore" => 1,
                      "tpl_data"  => "field_radio.html:Select",
                    ) );

  // 2.1. --------------------------------------------- GROUP MAIN
  $tpl->Assign("DeInit", 0);
  if ($formatting=="rawhtml") $tpl->Assign("DeInit", 1);
  //отображатьтолько один редактор
  {
    if ($formatting=="simplebr") 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_simpleedit",
//                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_SimpleEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="wacko")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_wikiedit",
//                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_WikiEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="rawhtml")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_richedit",
//                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => "",
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_RichEdit", 
                          "maxlen" => 64000,
                           ) ); 
  }


  // =================================================================================
  //  ФАЗА 4. Формирование массива групп
  //    * stat    -- всегда
  //    * main    -- всегда
    $group_state =  "00"; 
    $form_fields = array();
    $form_fields["main"] = &$group1;

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
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", );


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>