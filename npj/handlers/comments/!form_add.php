<?php

  // форма регистрации нового пользователя

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes"  , $rh->core_dir);
  if ($rh->use_htmlarea_as_richedit)
   $rh->UseClass("FieldText"  , $rh->core_dir);

  //определение верного форматтинга
  $formatting = $principal->data["_formatting"];
  $edits = array( "wacko" => "wikiedit", "simplebr" => "simpleedit", "rawhtml" => "richedit" );

  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "subject",
                          "nessesary" => 0,
                          "tpl_row" => "form.html:Row_Span",
                           ) ); 
/*    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body",
                          "nessesary" => 1,
                          "maxlen" => 32000,
                          "tpl_row" => "form.html:Row_Span",
                          "tpl_data" => "field_string.html:Textarea"
                           ) );
*/
    $tpl->Assign("DeInit", 0);
    if ($formatting=="rawhtml") $tpl->Assign("DeInit", 1);
    if ($formatting=="simplebr") 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body",
                          "nessesary" => 1,
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea_SimpleEdit", 
                          "maxlen" => 32000,
                           ) ); 
    if ($formatting=="wacko")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body",
                          "nessesary" => 1,
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea_WikiEdit", 
                          "maxlen" => 32000,
                           ) ); 
    if ($formatting=="rawhtml")     
    if ($rh->use_htmlarea_as_richedit)
     $group1[] = &new FieldText( &$rh, array(
                           "field" => "body",
                           "nessesary" => 1,
                           "tpl_row" => "form.html:Row_Dynamic_Vis",
                           //"tpl_data" => "field_string.html:Textarea_RichEdit", 
                          "modals" => array(),
                           "maxlen" => 32000,
                            ) ); 
    else
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body",
                          "nessesary" => 1,
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea_RichEdit", 
                          "maxlen" => 32000,
                           ) ); 

    if ($principal->IsGrantedTo("noguests"))
    $group1[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "subscription",
                          "fields" => array(      "subscription_tree",
                                                  "subscription_childs",
                                           ),
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Described",
                           ) ); 
  ///////////////////////////
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "user_name",
                          "default" => $principal->data["user_name"],
                           ) ); 

    if ($principal->data["user_id"] > 1)
    $group2[] = &new FieldRadio( &$rh, array( 
                          "field" => "pic_id",
                          "tpl_row" => "form.html:Row_Described",
                          "tpl_data" => "field_radio.html:Select",
                          "sql" => "select pic_id as id, description as value from ".$rh->db_prefix.
                                   "userpics where user_id=".$db->Quote($principal->data["user_id"]),
                          "default" => $principal->data["_pic_id"],
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "user_login",
                          "default" => $principal->data["login"],
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "user_id",
                          "default" => $principal->data["user_id"],
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "user_node_id",
                          "default" => $principal->data["node_id"],
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldDT( &$rh, array(
                          "date" => 1, "time" => 1,
                          "field" => "created_datetime",
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
  ///////////////////////////
    $group2[] = &new FieldString( &$rh, array( 
                          "field" => "record_id",
                          "default" => $record->data["record_id"],
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array( 
                          "field" => "parent_id",
                          "default" => ($object->name==""?0:$comment->data["comment_id"]),
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array( 
                          "field" => "lft_id",
                          "default" => 0,
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array( 
                          "field" => "rgt_id",
                          "default" => 0,
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array( 
                          "field" => "ip_xff",
                          "default" => $_SERVER["REMOTE_ADDR"],
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 

  ///////////////////////////
  if (!$rh->use_htmlarea_as_richedit) $form_params = " onsubmit='if (as_update.length>0) for (var i in as_update) { as_update[i].updateRTEs(); }return true;' ";


      $form_config = array(
      "db_table"    => $rh->db_prefix."comments", 
      "message_set" => $rh->message_set."_form_Comment", 
      "group_state" => "10",
      "critical" => 0,
      "params"      => $form_params,
//      "focus_to" => "body",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextCommentAdd"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 ),
          array( "name" => $tpl->message_set["ButtonTextCommentPreview"],  
                 "tpl_name" => "forms/buttons.html:Preview", "handler" => "_nothing", "default"=>0 ),
          );
      $form_fields = array(  
        &$group2, &$group1, 
                          );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>