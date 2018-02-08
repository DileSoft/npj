<?php

/*
  * !!кажется, самое корявое место!!
  * создаёт все нужные поля
  * делает запросы к БД аклей/групп для определения списков доступа. имхо не страшно.

  - делает кучу запросов в БД рефов, чтобы создать поле с ключсловами и поле с постингом в сообщества 
    -- ??имхо надо как-то отрефакторить??
  - необходим рефакторинг сборки формы и полей формы, касающих классификации и публикации 
    (короче говоря, рефов.)
  - а также придумать способ включения произвольных групп форм, подготовленных для _save.

*/
  // kuso@npj 31.10.2004 02:21. следующее условие -- только для модуля-агрегатора.
  // если есть идеи как сделать лучше "блокировку контент-полей" -- подходите/пишите -- обсудим.
  if (($this->data["type"] == RECORD_POST) && 
      !is_new &&
      ($principal->data["user_id"] != $this->data["author_id"]))
     $block_content_fields=true;
  else 
     $block_content_fields=false;

  // kuso@npj 09.11.2004 18:55 experimental
  if  ($this->data["type"] == RECORD_POST) $textarea_mod = "_Medium";
  else                                     $textarea_mod = "_Big";



  // ---------- дерьмовый кусок кода. Надо бы его рефакторить
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_SELF." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_nobody = 1*$rs->fields["group_id"];
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_FRIENDS." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_friends = 1*$rs->fields["group_id"];
     $rs = $db->Execute( "select group_id from ".$rh->db_prefix."groups where is_system=1 and group_rank=".GROUPS_COMMUNITIES." and user_id=".
                         $db->Quote( $rh->account->data["user_id"] ) );
     $rh->account->group_communities = 1*$rs->fields["group_id"];
  // ------------------------


// ---------------------------------------------------------------------
// валидатор, не является ли это дело резервным словом, или не содержит ли.
function validate_tag( $data, $rh )
{
  // сначала проверим, не резервное ли это слово
  $result = $rh->object->validate_reserved_words($data, $rh);
  if ($result === 0)
  {
    // ежели всеж таки нет, то проверим, нет ли у нас уже готовой записи с таким тагом?
    $data = trim($data, "/");
    $npj_addr = $rh->object->_UnwrapNpjAddress( "/".$data );
    $r = &new NpjObject( &$rh, $npj_addr );
    $rdata = &$r->Load(2);
    if (!is_array($rdata)) $result = 0;
    else
     $result = $rh->tpl->message_set["Form.tag.Exist"];
  }
  return $result;
}
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
  $rh->UseClass("FieldPassword",      $rh->core_dir);
  $rh->UseClass("FieldMultiple" ,     $rh->core_dir);
  $rh->UseClass("FieldMultiplePlus" , $rh->core_dir);
  $rh->UseClass("FieldParameter" ,    $rh->core_dir);
  if ($rh->use_htmlarea_as_richedit)
   $rh->UseClass("FieldText"  , $rh->core_dir);


  // =================================================================================
  //  ФАЗА 1. Вспомогательные поля
  //          * data["formatting"]
  //          * can_metadata

  //определение верного форматтинга
  if ($data["formatting"]) $formatting = $data["formatting"];
  else if ($_POST["_formatting"]) $formatting = $_POST["_formatting"];
  else $formatting = $principal->data["_formatting"];

  $edits = array( "wacko" => "wikiedit", "simplebr" => "simpleedit", "rawhtml" => "richedit" );

  $debug->Trace("Handler record.edit - choose formatting ".$formatting);

  // флаг, по которому определяется, нужно ли цеплять группу с настройками доступа
  // её нужно цеплять при добавлении записи, если это постинг или если акл "акл_врайт" разрешает это делать
  $can_metadata = ($is_new || $type==RECORD_MESSAGE || $this->HasAccess( &$principal, "acl", "acl_write") );

  // =================================================================================
  //  ФАЗА 2. Сборка простых и незатейливых групп полей формы
  //          1 group_body
  //          2 group_options
  //          3 group_panels

  // 2.1. --------------------------------------------- GROUP BODY
  if ($show_tag)
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "tag",
                          "default" => $tag,
                          "tpl_row" => "form.html:Row_Span",
                          "nessesary" => 1,
                          "regexp" => "/^[A-Za-z\xc0-\xff\xa8\xb8\/][0-9A-Za-z\xc0-\xff\xa8\xb8\/\-]*$/",
                          "validator" => "validate_tag",
                          "validator_param" => &$rh, 
                          "regexp_help" => "Form.tag.RegexpHelp",
                           ) ); 
  if (!$is_new)
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "edited_datetime",
                          "tpl_data" => "field_string.html:Hidden",
                          "regexp" => "/^".$data["edited_datetime"]."$/",
                          "regexp_help_clean" => 1,
                          "regexp_help" => "Form.edited_datetime.RegexpHelp",
                          "tpl_row" => "form.html:Row_Span",
                           ) ); 

  if ($block_content_fields)
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "strict:body_post",
                          "tpl_row" => "form.html:Row_Hidden",
                          "readonly" => 1,
                          "db_ignore" => 1,
                          "default" => $data["body_post"],
                           ) ); 
  // --

  if ($is_new)
  if (($data["supertag"][strlen($data["supertag"])-1] != ":") &&
      ($data["supertag"] != $rh->node_user.":".$rh->default_node_homepage) 
     )
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "subject",
                          "default" => $data["subject"],
                          "tpl_row" => "form.html:Row_Span".($show_tag?"_Described":""),
                          "readonly" => $block_content_fields,
                           ) ); 

  $tpl->Assign("DeInit", 0);
  if ($formatting=="rawhtml") $tpl->Assign("DeInit", 1);
  //отображать soDynamic или только один редактор
  if ($principal->data["options"]["sodynamic_off"] || !$is_new)
  {
    if ($formatting=="simplebr") 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_simpleedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_SimpleEdit", 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
    if ($formatting=="wacko")     
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_wikiedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_WikiEdit", 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
    if ($formatting=="rawhtml")     
    if ($rh->use_htmlarea_as_richedit)
    $group1[] = &new FieldText( &$rh, array(
                          "field" => "body_richedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "readonly" => $block_content_fields,
                          "maxlen" => 64000,
                           ) ); 
    else
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_richedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => "form.html:Row_Dynamic_Vis",
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_RichEdit", 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
  }
  else
  {
//    $tpl->Assign("DeInit", 1);
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_simpleedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => ($formatting=="simplebr"?"form.html:Row_Dynamic_Vis":"form.html:Row_Dynamic_Hid"),
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_SimpleEdit", 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_wikiedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => ($formatting=="wacko"?"form.html:Row_Dynamic_Vis":"form.html:Row_Dynamic_Hid"),
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_WikiEdit", 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
    if ($rh->use_htmlarea_as_richedit)
    $group1[] = &new FieldText( &$rh, array(
                          "field" => "body_richedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => ($formatting=="rawhtml"?"form.html:Row_Dynamic_Vis":"form.html:Row_Dynamic_Hid"),
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
    else
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "body_richedit",
                          "db_ignore" => 1,
                          "default" => $data["body"],
                          "tpl_row" => ($formatting=="rawhtml"?"form.html:Row_Dynamic_Vis":"form.html:Row_Dynamic_Hid"),
                          "tpl_data" => "field_string.html:Textarea".$textarea_mod."_RichEdit".($formatting=="rawhtml"?"":"_Uninit"), 
                          "maxlen" => 64000,
                          "readonly" => $block_content_fields,
                           ) ); 
  }
  if (!$is_new)
  if (($data["supertag"][strlen($data["supertag"])-1] != ":") &&
      ($data["supertag"] != $rh->node_user.":".$rh->default_node_homepage) 
     )
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "subject",
                          "default" => $data["subject"],
                          "tpl_row" => "form.html:Row_Span".($show_tag?"_Described":""),
                          "readonly" => $block_content_fields,
                           ) ); 
  // 2.2. --------------------------------------------- GROUP OPTIONS
  if ($can_metadata)
  {
  //показываем выбор форматирования только при включеном soDynamic                          
  if ($is_new && !$principal->data["options"]["sodynamic_off"])
    $group2[] = &new FieldRadio( &$rh, array( 
                          "field" => "formatting",
                          "default" => $formatting,
                          "onchange" => "soDynamic",
                           ) ); 
  else
    $group2[] = &new FieldRadio( &$rh, array( 
                          "field" => "formatting",
                          "default" => $formatting,
                          "readonly" => 1,
                          "tpl_row" => "form.html:Row_Hidden",
                           ) ); 

    $group2[] = &new FieldDT( &$rh, array(
                          "field" => "user_datetime",
                          "time"=>1, "date"=>1,
                          "tpl_data" => "field_dt.html:DateTime_Calendar",
                          "readonly" => !$can_metadata || $block_content_fields,
                           ) );

    $group2[] = &new FieldRadio( &$rh, array( 
                          "field" => "pic_id",
                          "tpl_row" => "form.html:Row_Described",
                          "tpl_data" => "field_radio.html:Select",
                          "sql" => "select pic_id as id, description as value from ".$rh->db_prefix.
                                   "userpics where user_id=".$db->Quote($principal->data["user_id"]),
                          "default" => $principal->data["_pic_id"],
                           ) ); 
                             
    $disallow = array( "disallow_comments", "disallow_notify_comments", "disallow_replicate", );
    if ($type == RECORD_MESSAGE) $disallow[]="disallow_syndicate";
    $group2[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "disallow",
                          "fields" => $disallow,
                           ) );
  }                           
  // 2.2. --------------------------------------------- GROUP PANELS
    $group3[] = &new FieldParameter( &$rh, array(
                          "field" => "default_show_parameter",
                          "default" => array( "backlinks" ),
                          "only_more" => ($principal->data["user_id"] != $data["user_id"]),
                          "readonly"  => ($data["default_show_parameter_add"] == 2) &&
                                         ($principal->data["user_id"] != $data["user_id"]),
                           ) );

  // =================================================================================
  //  ФАЗА 3. Сборка группы с настройками доступа
  //          4 group_access
  //
  // 3.4. --------------------------------------------- GROUP ACCESS
  if ($is_new)
  if ($type == RECORD_POST)
  {
    $helper = &$this->SpawnHelper();
    $group4 = array();
    $group4 = &$helper->CreateAccessFields( &$group4, &$this, $is_new );
  }
  else
  { 
    $f=0; $acls = array(); foreach($this->acls as $ag) foreach($ag as $acl) $acls[]=$db->Quote($acl);
    $ff=0;
    $parent = $this->parent->data["supertag"];
    while ($f==0)
    { if ($parent[strlen($parent)-1] == ":") 
      if ($ff==0) $ff=1;
      else
      {
        $values = $rh->default_acls[ $rh->account->data["account_type"] ];
        // not tested patch ---
        if (isset($rh->account_classes[$rh->account->data["account_class"]]))
        {
          $target_class = $rh->account_classes[$rh->account->data["account_class"]];
          if (isset($target_class["acls"])) $values = $target_class["acls"];
        }
        // ---
        break;
      }
      $rs = $db->Execute( "select a.object_right, a.acl from ".$rh->db_prefix."acls as a, ".
                          $rh->db_prefix."records as r ".
                          " where a.object_type=".$db->Quote("record").
                          " and a.object_id=r.record_id".
                          " and r.supertag = ".$db->Quote($parent).
                          " and a.object_right in (".implode(",",$acls).")");
      if ($rs->RecordCount() == 0)
      {
        $f=0; $pos = strrpos($parent,"/");
        if ($pos === false) $pos = strrpos($parent,":")+1;
        $parent = substr($parent, 0, $pos);
      } else
      {
        $a = $rs->GetArray(); $values = array();
        foreach( $a as $item ) $values[$item["object_right"]] = $item["acl"];
        break;
      }
    }
    $group4 = array();
    foreach( $this->acls as $i=>$ag )
    foreach( $ag as $acl )
    
      $group4[] = &new FieldString( &$rh, array(
                            "field" => $acl,
                            "db_ignore" => 1,
                            "default" => $values[ $acl ],
                            "tpl_row"    => $i?"form_horizontal.html:Row_Hidden":"form_horizontal.html:Row_Described",
                            "tpl_data" => "field_string.html:TextareaSmall_Fixed",
                             ) ); 
  }


  // =================================================================================
  //  ФАЗА 4. Формирование массива групп
  //    * body    -- всегда
  //    * options -- только если можно метадату
  //    * panels  -- всегда
  //    * access  -- только для новых записей
    $group_state = ""; 
    $form_fields = array();

    // add body
    $group_state .=  "0"; 
    $form_fields["body"] = &$group1;

    //$group_state .=  "1";
    //$form_fields["panels"] = &$group3;
    if ($is_new)
    { $form_fields["access"] = &$group4; 
      if ($this->rh->hide_access_pane_in_new_record)
        $group_state .=  "1"; 
      else
      $group_state .=  "0"; 
    }
    if ($can_metadata) 
    { $form_fields["options"] = &$group2;
      $group_state .=  "1"; }

  // =================================================================================
  //  ФАЗА 5. Инвазия группы с классификацией
  //  ФАЗА 6. Инвазия групп со всякими прочими настройками кустомных подтипов (анонсы, дигесты)
  $helper      = &$this->SpawnHelper();
  if ($is_new) $helper->ParseRequest( $_REQUEST ); 
  $form_fields = &$helper->TweakForm( &$form_fields, &$group_state, !$is_new );

  // =================================================================================
  //  ФАЗА 7. Построение самой формы
    if (!$rh->use_htmlarea_as_richedit)
       $form_params = " onsubmit='if (as_update.length>0) for (var i in as_update) { as_update[i].updateRTEs(); }return true;' ";

      $form_config = array(
      "db_table"    => $rh->db_prefix."records", 
      "db_id"       => "record_id",
      "group_state" => $block_content_fields?"10":$group_state, 
      "message_set" => $rh->message_set."_form_RecordEdit",
      "buttons_small" => 1,
      "flip_one"    => (($data["is_digest"]==2) && $is_new),
      "focus_to"    => $block_content_fields?"":
                       ($edits[$formatting]=="richedit"?"":"body_".$edits[$formatting]),//"rte_id__":""
      "params"      => $block_content_fields?"":$form_params,
        );
      if ($is_new)
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextInsert"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      else
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );

      /*
          feature frozen by kuso@npj, 04042005
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCommentPreview"],  
                 "tpl_name" => "forms/buttons.html:Preview", "handler" => "_nothing", "default"=>0 );
      */
      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", "default"=>1 );


    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>