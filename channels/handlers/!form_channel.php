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

  $tpl->MergeMessageSet( $rh->message_set."_form_Channel", $this->messagesets_dir );

// ---------------------------------------------------------------------
// валидатор, не является ли это дело резервным словом, или не содержит ли.
if (!function_exists(validate_reserved_words))
{
  function validate_reserved_words( $data, $rh )
  {
    return $rh->object->validate_reserved_words($data, $rh);
  }
}
// ---------------------------------------------------------------------

  // =================================================================================
  //  Сборка простых и незатейливых групп полей формы
  $channel = &$params["&channel"];
  // 1. --------------------------------------------- GROUP MAIN
  $group1 = array();
  $group2 = array();
  $group3 = array();

  $group1[] = &new FieldRadio( &$rh, array(
                        "field" => "account_class",
                        "default" => $channel->GetAccountClass(),
                        "data" => array( $channel->GetAccountClass() =>
                                         "<h1>".$tpl->message_set["Channels.types"][$params["type"]]."</h1>" ),
                        "readonly" => 1,
                        "db_ignore" => 1,
                         ) );
  $group1[] = &new FieldString( &$rh, array(
                        "field"   => "login",
                        "default" => $channel->data["login"],
                        "nessesary" => 1,
                        "readonly"  => ($params["mode"] != "add"), // only if adding
                        "db_ignore" => 1,

                        "maxlen" => 20,  "min" => 3,
                        "regexp" => "/^[a-z][a-z0-9\-]+$/",
                        "regexp_help" => "Form.login.Help",
                        "unique_sql" => "select [name] from ".$rh->db_prefix."users where [name]=[value] and node_id=".
                                        $db->Quote($params["type"]),
                        "validator" => "validate_reserved_words",
                        "validator_param" => &$rh, 
                        "lowercase" => 1,
                        "tpl_data" => "field_string.html:Plain",
                        "tpl_row"  => ($params["mode"] == "add")?"form.html:Row_Described":"form.html:Row",

                        "postfix" => "<b style='font:18px Verdana'>@".$params["type"]."/".$rh->node_name."</b>",
                         ) ); 
  $group1[] = &new FieldString( &$rh, array(
                        "field"   => "user_name",
                        "default" => $channel->data["user_name"],
                        "nessesary" => 1,
                        "db_ignore" => 1,
                         ) ); 
  $group1[] = &new FieldString( &$rh, array(
                        "field" => "bio",
                        "default" => $channel->data["bio"],
                        "maxlen" => 4000,
                        "db_ignore" => 0,
                        "tpl_data" => "field_string.html:TextareaSmall",
                         ) );

  // 2. --------------------------------------------- CHANNEL SPECIFIC
  $group2 = &$channel->ComposeFormGroup();

  // 3. --------------------------------------------- GROUP TEMPLATES
  foreach ($channel->templates as $t=>$value)
    if (!isset($channel->data["channel:template_".$t]))
      $channel->data["channel:template_".$t] = $value;

  $group3[] = &new FieldString( &$rh, array(
                        "field" => "template_subject",
                        "default" => $channel->data["channel:template_subject"],
                        "maxlen" => 250,
                        "db_ignore" => 0,
                        "nessesary" => 1,
                         ) );
  $group3[] = &new FieldString( &$rh, array(
                        "field" => "template_body",
                        "default" => $channel->data["channel:template_body"],
                        "maxlen" => 8000,
                        "db_ignore" => 0,
                        "nessesary" => 1,
                        "tpl_data" => "field_string.html:TextareaSmall",
                        "tpl_row"  => "form.html:Row_Described",
                         ) );
  $group3[] = &new FieldString( &$rh, array(
                        "field" => "template_body_post",
                        "default" => $channel->data["channel:template_body_post"],
                        "maxlen" => 8000,
                        "db_ignore" => 0,
                        "tpl_data" => "field_string.html:TextareaSmall",
                        "tpl_row"  => "form.html:Row_Described",
                         ) );

  $group_state =  "00"; 
  $form_fields = array( "common"   => &$group1, 
                        "custom"   => &$group2,
                        "tpls"     => &$group3,
                      );

  // =================================================================================
  //  ФАЗА 7. Построение самой формы
      $form_config = array( 
      "db_table"    => $rh->db_prefix."channels", 
      "db_id"       => "user_id",
      "group_state" => $group_state, 
      "message_set" => $rh->message_set."_form_Channel",
      "buttons_small" => 0,
        );

      if ($params["mode"] == "add")
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      else
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );

      /* no cancel indeed
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel",  );
      */


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>