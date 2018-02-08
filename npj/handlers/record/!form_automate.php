<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes", $rh->core_dir );
  $rh->UseClass("FieldMultiple", $rh->core_dir );
  $rh->UseClass("FieldMultiplePlus", $rh->core_dir );

  // $old_rules[field]=value -- содержит текущее состояние


   /////////////////////////// (copied from !form_record)
   // add for communities
      // create helper-post by hands
      $this->rh->UseClass("HelperAbstract"); 
      $this->rh->UseClass("HelperRecord");
      $this->rh->UseClass("HelperPost");
      $helper = &new HelperPost( &$this->rh, &$this );
      $group2 = array();
      $is_new = false;
      $helper->CreateAccessFields( &$group2, &$this, $is_new, "automate", $old_rules["_groups"] );
  /////////////////////////// (copied from HelperPost)
    // 1.1. Получим список всех сообществ, в которых уже состоит пользователь
    $sql= "SELECT u.user_id, u.login FROM ".
            $rh->db_prefix."groups as g, ".
            $rh->db_prefix."user_groups as gu, ".
            $rh->db_prefix."users as u WHERE ".
            "gu.group_id = g.group_id AND g.user_id = u.user_id AND ".
            "gu.user_id = ".$db->Quote($rh->account->data["user_id"])." AND ".
            "u.owner_user_id <> 0 AND g.group_rank >= ".GROUPS_LIGHTMEMBERS." AND ".
            "g.group_rank < ".GROUPS_SELF." and g.is_system=1;"; 
    $rs = $db->Execute($sql);
    $a=$rs->GetArray();
    $by_login = array(); // communities[login] = account_id
    $data4form=array();  // communities[account_id] = login
    $in=array();         // my_communities[] = account_id

    if (sizeof($a) > 0)
    { 
      // 1.2. подготавливаем списки "логин-идшник"
      foreach ($a as $item) { $by_login[$item["login"]] = $item["user_id"]; 
                              $data4form[$item["user_id"]] = $item["login"]; $c++; }
      // 1.3. если редактируем, то заполняем эти поля из массива нашего
      if (sizeof($old_rules["_communities"]))
      {
        $rs= $db->Execute( "select user_id from ".$rh->db_prefix."records ".
                           "where record_id in (".$old_rules["_communities"].")" );
        $a = $rs->GetArray();
        foreach($a as $item) $in[] = $item["user_id"];
      }
      // 1.4. если добавляем, то надо откуда-то прочитать , ???
      else
      if (is_array( $obj->data["communities"] ))
      {
        foreach( $obj->data["communities"] as $name=>$value )
         if (($value != "post") && isset($by_login[ strtolower($value) ])) 
          $in[] = $by_login[ strtolower($value) ];
      }
    }
    if (sizeof($in) == 0) $in = 0;

    // 2. добавим поле
    $group3[] = &new FieldMultiple( &$rh, array(
                           "field" => "_communities",
                           "maxsize" => 5,
                           "data_plain" => 1,
                           "default" => $in,
                           "data" => $data4form,
                           "db_ignore" => 1,
                           "tpl_data" => "field_multiple_post.html:Plain",
                           "size" => 5,
                           "size_all" => 7,
                            ) ); 
  
  ///////////////////////////
    $group1[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "disallow",
                          "db_ignore" => 1,
                          "fields" => ($rh->account->data["account_type"] == ACCOUNT_USER)?
                                      array(      "disallow_comments",
                                                  "disallow_notify_comments",
                                                  "disallow_syndicate",
                                                  "disallow_replicate",
                                           ):
                                      array(      "disallow_comments",
                                                  "disallow_replicate",
                                           ),
                           "default" => ($rh->account->data["account_type"] == ACCOUNT_USER)?
                                      array(
                                                  $old_rules["disallow_comments"],
                                                  $old_rules["disallow_notify_comments"],
                                                  $old_rules["disallow_syndicate"],
                                                  $old_rules["disallow_replicate"],
                                           ):
                                      array(   
                                                  $old_rules["disallow_comments"],
                                                  $old_rules["disallow_replicate"],
                                             ),
                           ) );
  ///////////////////////////
   $tpl->MergeMessageset( "std_form_RecordAutomate" );
 
   if ($rh->account->data["account_type"] == ACCOUNT_USER)
   {
     $sql = "select pic_id as id, description as value from ".$rh->db_prefix.
            "userpics where user_id=".$db->Quote($principal->data["user_id"]);
     $rs = $db->Execute( $sql );
     $a = $rs->GetArray();
     $userpics = array( -1 => $tpl->message_set[ "Form.pic_id.NoChange" ] );
     foreach( $a as $item ) $userpics[ $item["id"] ] = $item["value"];
     $tpl->message_set["Form.pic_id.Data"] = $userpics;

     $group1[] = &new FieldRadio( &$rh, array( 
                           "field" => "pic_id",
                           "tpl_data" => "field_radio.html:Select",
                           "default" => $old_rules["pic_id"]?$old_rules["pic_id"]:"-1",
                           "db_ignore" => 1,
                            ) ); 
  }                           
  ///////////////////////////

  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."rules", 
      "db_id"       => "user_id",
      "message_set" => "empty",
      "buttons_small" => 1,
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
          );

      $form_buttons[] = 
          array( "name" => $tpl->message_set["ButtonTextCancel"],  
                 "tpl_name" => "forms/buttons.html:Cancel", "handler" => "_cancel", "default"=>0 );
      
   if ($rh->account->data["account_type"] == ACCOUNT_USER)
      $form_fields = array(  &$group2, &$group3, &$group1, );
   else
      $form_fields = array(  0=>&$group2, 2=>&$group1, );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>