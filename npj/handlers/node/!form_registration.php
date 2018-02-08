<?php

// ---------------------------------------------------------------------
// валидатор, не является ли это дело резервным словом, или не содержит ли.
function validate_reserved_words( $data, $rh )
{
  return $rh->object->validate_reserved_words($data, $rh);
}
// валидатор, не является ли это дело резервным словом, или не содержит ли.
// ??????? NOT USED WIDELY YET. THEREFORE NOT TESTED
function validate_reserved_words_and_unique( $data, $rh )
{
  $valid = $rh->object->validate_reserved_words($data, $rh);
  if ($valid) return $valid;
  $db = &$rh->db;
  $sql = "select login from ".$rh->db_prefix."users where user_id=".$db->Quote($rh->__parent_id);
  $rs  = $db->Execute( $sql );
  $acc = $rs->GetArray();
  $sql = "select login from ".$rh->db_prefix."users where login=".$db->Quote($acc[0]["login"]."-".$data);
  $rs  = $db->Execute( $sql );
  $acc = $rs->GetArray();
  if (sizeof($acc))
    return "Это имя уже занято. Попробуйте другое"; 
  else 
    return 0;
}
//
function validate_store_parent_id( $data, $rh )
{
  $rh->__parent_id = $data;
  return 0;
}
// ---------------------------------------------------------------------

  // форма регистрации нового пользователя
  if (!$db) $db = &$rh->db;

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldPassword", $rh->core_dir );
  $rh->UseClass("FieldCheckboxes", $rh->core_dir );

  $account_type = ACCOUNT_USER;
  if ($params[0] == "community") $account_type = ACCOUNT_COMMUNITY;
  if ($params[0] == "workgroup") $account_type = ACCOUNT_WORKGROUP;


  ///////////////////////////
   if ($rh->alert_npjnet && $rh->node->data["created_datetime"]=="0000-00-00 00:00:00")
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "alert",
                          "default" => $tpl->message_set["AlertNNS"],
                          "readonly" => 1,
                          "db_ignore" => 1,
                           ) ); 
    // разные account_class
    if ($this->_target_class) //  appropriate hash-array from $rh->account_classes
    {
      $key = $this->_target_class["supertag"];
      $val = "<h1>".$this->_target_class["name"]."</h1>";
      $tpl->message_set[ "Form.account_class.Data" ] = array( $key => $val );
      $group0[] = &new FieldRadio( &$rh, array(
                            "field" => "account_class",
                            "default" => $this->_target_class["supertag"],
                            "data"  => array( $key => $val, ),
                            "readonly" => 1,
                            "db_ignore" => 1,
                             ) );

      if ($this->_target_class["parent_class"])
      {
        $sql = "select user_id, login, user_name from ".$rh->db_prefix."users ".
               " where alive=1 and node_id=". $db->Quote($rh->node_name).
               " and account_class=". $db->Quote($this->_target_class["parent_class"]).
               " order by login asc";
        $rs  = $db->Execute( $sql );
        $a   = $rs->GetArray();
        $data = array();
        foreach( $a as $k=>$v)
         $data[ $v["user_id"] ] = $v["login"]." &mdash; &laquo;".$v["user_name"]."&raquo;";
        $tpl->message_set[ "Form.parent_id.Data" ] = $data;
        $group0[] = &new FieldRadio( &$rh, array(
                              "field" => "parent_id",
                              "validator" => "validate_store_parent_id",
                              "validator_param" => &$rh, 
                              "nessesary" => 1,
                               ) );
      }
                             
    }

    $group0[] = &new FieldString( &$rh, array(
                          "field" => "login",
                          "nessesary" => 1,
                          "maxlen" => 20,  "min" => 3,
                          "db_ignore" => 1,
                          "regexp" => "/^[a-z][a-z0-9\-]+$/",
                          "regexp_help" => "Form.login.Help",
                          "unique_sql" => "select [name] from ".$rh->db_prefix."users where [name]=[value] and node_id=".
                                          $db->Quote($rh->node_name),
                          "validator" => "validate_reserved_words",
                          "validator_param" => &$rh, 
                          "lowercase" => 1,
                           ) ); 
    if ($account_type != ACCOUNT_USER)
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "password",
                          "nessesary" => 0,
                          "db_ignore" => 1,
                          "default" => "doesnt matter",
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    else
    $group0[] = &new FieldPassword( &$rh, array(
                          "field" => "password",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                           ) ); 
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "user_id",
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "advanced",
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 
    $group0[] = &new FieldDT( &$rh, array(
                          "field" => "creation_date",
                          "nessesary" => 0,
                          "date" => 1, "time" => 1,
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 

    $group0[] = &new FieldString( &$rh, array(
                          "field" => "user_name",
                          "regexp" => "/^[^;]*$/",
                          "regexp_help" => "Form.user_name.Help",
                          "nessesary" => 1,
                          "db_ignore" => 1,
                           ) ); 
    if ($account_type == ACCOUNT_USER)
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "email",
                          "email" => 1,
                          "tpl_row"  => "form.html:Row_Described",
                           ) ); 
  
  ///////////////////////////
    if (($account_type == ACCOUNT_COMMUNITY) ||
        ($account_type == ACCOUNT_WORKGROUP) )
    {                                       
      if ($this->_target_class)
       $_def_security = $this->_target_class["security"];
       
      $group0[] = &new FieldRadio( &$rh, array(
                            "field" => "security_type",
                            "default" => $_def_security,
                             ) ); 
      $group05[] = &new FieldRadio( &$rh, array(
                            "field" => "default_membership",
                            "default" => GROUPS_POWERMEMBERS,
                             ) ); 
      $group05[] = &new FieldRadio( &$rh, array(
                            "field" => "post_membership",
                            "default" => GROUPS_POWERMEMBERS,
                             ) ); 
      $group05[] = &new FieldRadio( &$rh, array(
                            "field" => "announce_membership",
                            "default" => GROUPS_MODERATORS,
                             ) ); 
    }
  ///////////////////////////
    $group0[] = &new FieldString( &$rh, array(
                          "field" => "helpful",
                          "default" => $tpl->message_set["AlertRegistration"],
                          "readonly" => 1,
                          "db_ignore" => 1,
                          "tpl_row"  => "form.html:Row_Span",
                           ) ); 
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
  ///////////////////////////
    $group2[] = &new FieldString( &$rh, array(
                          "field" => "icq_uin",
                          "maxlen" => 15,
                          "regexp" => "/^[0-9]*$/",
                          "regexp_help" => "Form.icq.Help",
                           ) ); 
  ///////////////////////////
    if ($account_type == ACCOUNT_USER) 
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

  if ($object->params[0] == "ok")           return $object->Handler( "_registration_ok", array(), &$principal );
  ///////////////////////////
  $ms = $rh->message_set."_form_Profile";
  if ($account_type == ACCOUNT_COMMUNITY) $ms = $rh->message_set."_form_Community";
  if ($account_type == ACCOUNT_WORKGROUP) $ms = $rh->message_set."_form_WorkGroup";

      $form_config = array(
      "db_table"    => $rh->db_prefix."profiles", 
      "db_id"       => "user_id",
      "message_set" => $ms, 
      "group_state" => "01111111",
      "on_before_action" => "node/_registration_insert",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_insert", "default"=>1 )
          );
    if (($account_type == ACCOUNT_COMMUNITY) ||
        ($account_type == ACCOUNT_WORKGROUP) )
        $form_fields = array( "Users"  => &$group0, &$group05, &$group1,  &$group15, &$group2, &$group3, &$group4, );
      else
        $form_fields = array( "Users"  => &$group0, &$group1, &$group15, &$group2, &$group3, &$group4, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>