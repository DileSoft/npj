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
  //  ФАЗА 1. Вспомогательные поля
  //          * data["formatting"]
  //          * can_metadata

  if ($issue["RECORD"]["formatting"]) $formatting = $issue["RECORD"]["formatting"];
  else if ($_POST["_formatting"]) $formatting = $_POST["_formatting"];
  else $formatting = $principal->data["_formatting"];

  $edits = array( "wacko" => "wikiedit", "simplebr" => "simpleedit", "rawhtml" => "richedit" );
  $debug->Trace("issue_add - choose formatting ".$formatting);

  // =================================================================================
  //  ФАЗА 2. Сборка простых и незатейливых групп полей формы

  // 2.1. --------------------------------------------- GROUP MAIN
    $group1[] = &new FieldString( &$rh, array(
                          "field"   => "subject",
                          "default" => $issue["RECORD"]["subject"],
                          "nessesary" => 1,
                           ) ); 


  $tpl->Assign("DeInit", 0);
  if ($formatting=="rawhtml") $tpl->Assign("DeInit", 1);
  //отображатьтолько один редактор
  {
    if ($formatting=="simplebr") 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_simpleedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => $issue["RECORD"]["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_SimpleEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="wacko")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_wikiedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => $issue["RECORD"]["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis_Small",
                          "tpl_data" => "field_string.html:Textarea_WikiEdit", 
                          "maxlen" => 64000,
                           ) ); 
    if ($formatting=="rawhtml")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_richedit",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "default" => $issue["RECORD"]["body"],
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


  // =================================================================================
  //  ФАЗА 5. Инвазия группы с классификацией
  //  ФАЗА 6. Инвазия групп со всякими прочими настройками кустомных подтипов (анонсы, дигесты)

  $this->rh->UseClass("HelperAbstract");
  $this->rh->UseClass("HelperRecord");
  $this->rh->UseClass("HelperTrakoIssue", $this->classes_dir);

  $o = &new NpjObject( &$this->rh, $this->object->npj_account.":trako/dummy" );

  // Автопост в рубрику
  if ($section->tag != "") $o->post_from = $section;

  $o->owner = &new NpjObject( &$this->rh, $this->object->npj_account );
  $o->owner->Load(2);
  $o->data  = $issue;
  $helper = &new HelperTrakoIssue( &$rh, &$o );
  $helper->ParseRequest( $_REQUEST ); 
  $form_fields = &$helper->TweakForm( &$form_fields, &$group_state, $issue["issue_no"]?true:false );

  // msgset
  $tpl->MergeMessageSet( $rh->message_set."_form_Issue", $this->messagesets_dir );
  // =================================================================================
  //  ФАЗА 7. Построение самой формы
      $form_config = array( 
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "group_state" => $group_state, 
      "message_set" => $rh->message_set."_form_TrakoIssue",
      "buttons_small" => 0,
      "params"      => ($formatting=="rawhtml")?" onsubmit='if (as_update) as_update.updateRTEs();return true;' "
                                               :"",
        );

      if (!isset($issue["issue_no"]))
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      else
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );

      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel",  );


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>