<?php
  //////////////// validator for existance of record
  function validate_npj_address( $data, &$rh )
  {
    $target  = &new NpjObject( &$rh, $data);
    $_data    = $target->Load(1);
    if (!is_array($_data))
     return "Неверный НПЖ-адрес или адрес несуществующего объекта"; //!!! to messageset
    return 0;
  }
  //////////////// validator for existance of record

  //////////////// validator for FM. !!!внести функциональность в сам FM
  function validate_fm( $data, &$rh )
  {
    if (!is_array($data) || count($data)==0)
     return "Вы не выбрали ни одного варианта."; //!!! to messageset
    die(print_r($data));
    return 0;
  }
  //////////////// validator for FM. 


  // форма 

  $rh->UseClass("ListSimple", $rh->core_dir);
  $rh->UseClass("Form"      , $rh->core_dir);
  $rh->UseClass("Field"     , $rh->core_dir);
  $rh->UseClass("FieldString" , $rh->core_dir);
  $rh->UseClass("FieldRadio"  , $rh->core_dir);
  $rh->UseClass("FieldMultiple"  , $rh->core_dir);
  $rh->UseClass("FieldDT"     , $rh->core_dir);
  $rh->UseClass("ButtonList"  , $rh->core_dir);
  $rh->UseClass("FieldCheckboxes"  , $rh->core_dir);

  ///////////////////////////
    $group1[] = &new FieldString( &$rh, array(
                          "field" => "record",
                          "db_ignore" => 1,
                          "default" => $record,
//                          "validator" => "validate_npj_address",
                           ) ); 
  ///////////////////////////

  $sql="SELECT u.user_id, u.login FROM ".$rh->db_prefix."groups as g, ".$rh->db_prefix."user_groups as gu, ".
  $rh->db_prefix."users as u WHERE gu.group_id=g.group_id AND g.user_id=u.user_id AND".
  " gu.user_id=".$db->Quote($principal->data["user_id"])." AND u.owner_user_id<>0".
  " AND g.group_rank>=".GROUPS_LIGHTMEMBERS." AND g.group_rank<".GROUPS_SELF." and g.is_system=1;"; 
  $debug->Trace($sql);
  $rs = $db->Execute($sql);
  $a=$rs->GetArray();
  $data4form=array();
  if (sizeof($a) > 0)
  { $c=0; $in=array(); $by_login = array();
    foreach ($a as $item) { $by_login[$item["login"]] = $item["user_id"]; 
                            $data4form[$item["user_id"]]=$item["login"]; $c++; }
/* to rewrite 4 edit
    if ($c>0)
    { $rs= $db->Execute( "select keyword_user_id from ".$rh->db_prefix."records_ref ".
                         "where keyword_user_id <> owner_id and record_id=".$db->Quote($data["record_id"]) );
      $a = $rs->GetArray();
      foreach($a as $item) $in[] = $item["keyword_user_id"];
    }
*/
    if (sizeof($in) == 0) $in = -10;
    $group2[] = &new FieldMultiple( &$rh, array(
                         "field" => "communities",
                         "maxsize" => 5,
                         "data_plain" => 1,
                         "default" => $in,
                         "data" => $data4form,
                         "db_ignore" => 1,
                         "validator" => "validate_fm",
                          ) ); 
  }
  else
  {
    $group2[] = &new FieldString( &$rh, array(
                      "field" => "cannot",
                      "db_ignore" => 1,
                      "default" => " ",
                      "readonly" => 1,
                       ) ); 
  }


  ///////////////////////////
      $form_config = array(
      "db_table"    => $rh->db_prefix."replica_dest_rules", 
      "message_set" => $rh->message_set."_form_ReplicationDest", 
      "group_state" => "0",
        );
      $form_buttons = array(
          array( "name" => $tpl->message_set["ButtonTextUpdate"],  
                 "tpl_name" => "forms/buttons.html:Insert", "handler" => "_nothing", "default"=>1 )
          );
      if ($reptype==REP_RECORDS)
       $form_fields = array(  
         &$group2, 
         );
      else
       $form_fields = array(  
         &$group1, 
         );

    $form = &new Form( &$rh, &$form_config, &$form_fields, &$form_buttons );

  ///////////////////////////


?>
