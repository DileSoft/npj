<?php

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes" , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);

  $sql = "select object_class, object_method from ".$rh->db_prefix."subscription ".
         " WHERE user_id=".$db->Quote($principal->data["user_id"]).
         " AND object_id=".$db->Quote($this->data["record_id"]);
  $rs = $db->Execute( $sql ); 
  $sub = $rs->GetArray();
  $subdata = array(); $values = array(); $fields = array();
  foreach($sub as $s)
   $subdata[$s["object_class"]."_".$s["object_method"]] = 1;

  if (($data["tag"] != "") && (!$data["is_keyword"]) || !$rh->disable_subscribe_documents)
  $fields[] = "record_comments";


  if ($rh->account->data["account_type"]!=ACCOUNT_COMMUNITY) 
  {
    if (($data["type"]==RECORD_DOCUMENT) && !$rh->disable_subscribe_documents)
    {
      $fields[] = "record_diff";
      $fields[] = "cluster_comments";
      $fields[] = "cluster_diff";
      $fields[] = "cluster_add";
    }
  }
  
  if ($data["is_keyword"])
  {
    if (!$rh->disable_subscribe_documents)
    {
    $fields[] = "facet_add";
    $fields[] = "facet_diff";
    }

    $fields[] = "facet_post";
    $fields[] = "facet_comments";
  }

  if ($rh->account->data["account_type"]>ACCOUNT_USER)
    $fields[] = "facet_comments2";

  
  $fields[] = "cluster_post";


  foreach($fields as $field)
  {
   if ($subdata[trim($field,"2")]) $values[] = 1;
   else $values[] = 0;
  }


    $group[] = &new FieldCheckboxes( &$rh, array(
                          "field" => "subscription",
                          "fields" => $fields,
                          "default" => $values,
                          "db_ignore" => 1,
                           ) ); 
    $groups[] = $group;
  ///////////////////////////

    $form_config = array(
    "tpl_name"    => "form_horizontal.html:Form",
    "db_table"    => $rh->db_prefix."records", 
    "db_id"       => "record_id",
    "group_state" => "0",
    "message_set" => $rh->message_set."_form_RecordSubscribe"
      );
    $form_buttons = array(
            array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                   "tpl_name" => "forms/buttons.html:Update", "handler" => "_nothing", "default"=>1 )
            );
    $form_fields = &$groups;

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////




?>