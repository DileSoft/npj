<?php

  /*
      Об автоматическом назначении свойств записям
  */

  // получаем данные по записи
  $data = &$object->Load(2);
  if (!is_array($data)) return $object->NotFound("AccountNotFound");

  if ($object->GetType() == RECORD_POST) return $object->Forbidden("AutomatePost");

  $account = &new NpjObject( &$rh, $object->npj_account );
  $adata = &$account->Load(2);

  // только владелец/модераторы могут это править
  if (!$account->HasAccess( &$principal, "owner" )) 
   if (!$account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS )) 
    return $this->Forbidden("AutomateOwnerOnly");


 // получаем все существующие правила
 $rs = $db->Execute("select * from ".$rh->db_prefix."records_ref_rules where keyword_id=".$db->Quote($data["record_id"]) );
 $old_rules = array(); $a = $rs->GetArray(); 
 foreach($a as $item) $old_rules[ $item["field"] ] = $item["value"];

 // обработчик формы
 include( $dir."/!form_automate.php" );
 if (!isset($_POST["__form_present"])) 
 { 
   $form->ResetSession();
 }

 $debug->Milestone( "Starting form handler" );

 $tpl->Skin( $rh->theme );
 $result= $form->Handle();
 $tpl->UnSkin();

 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
 $tpl->Assign("Preparsed:TITLE", $this->Format($data["subject_r"], $data["formatting"], "post") );
 if ($tpl->GetValue("Preparsed:TITLE") == "") $tpl->message_set["Form._Name"] = trim($tpl->message_set["Form._Name"], ": ");
 $tpl->Append("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
 $state->Free( "id" );

  if ($form->success)
  {
    $rules = array();
    // 1.1. перекачать все настройки без подчерка
    foreach($form->hash as $name=>$field)
     if ($name{0} != "_") 
      if (!is_array($field->data)) 
       if ($field->data != -1) $rules[$name] = $field->data; else;
      else
      foreach($field->data as $k=>$v) 
       if ($v>0) $rules[$field->config["fields"][$k]] = $v;

    // 1.2. перекачать _groups

    if ($form->hash["_groups"]->data[0]!=-1) // таки мы будем менять
    {
      if ($form->hash["_groups"]->data[0]==0) // никто (но в БД запишется "-1")
       $_groups = $rh->account->group_nobody.",-1";
      else if ($form->hash["_groups"]->data[0]==-2) // все конфиденты
       $_groups=$rh->account->group_friends.",-2";
      else if ($form->hash["_groups"]->data[0]==-3) // всем сообществам
       $_groups=$rh->account->group_communities.",-3,".(1*$form->hash["_groups"]->radio_data); 
      else
      { //[_items_in_groups] -- мы не работаем с постом бля
       $grps = $form->hash["_groups"]->data;
       $_groups = array();  
       for ($gnum=0; $gnum<4; $gnum++)
        if (!isset($grps[$gnum])) break;
        else $_groups[] = $grps[$gnum];
       $_groups = implode(",", $_groups);
      }
     $rules["_groups"] = $_groups;
    }

    // 1.3. перекачать _communities
   if ($rh->account->data["account_type"] == ACCOUNT_USER)
    if ($form->hash["_communities"]->data[0]) // таки будем перепубликовать
    {
      $userids = $form->hash["_communities"]->data;
      foreach ($userids as $k=>$v)
       $userids[$k] = $db->Quote($v);
      $rs = $db->Execute( "select root_record_id from ".$rh->db_prefix."users where user_id in (".
                          implode(",",$userids).")");
      $a = $rs->GetArray();
      $_communities = array();
      foreach( $a as $item ) $_communities[] = $item["root_record_id"];
      $rules["_communities"] = implode(",",$_communities);
    }

    // 2. стереть старые настройки
    $db->Execute( "delete from ".$rh->db_prefix."records_ref_rules where keyword_id=". $db->Quote($data["record_id"]) );

    // 3. записать новые все
    if (sizeof($rules) > 0)
    {
      $sql = array();
      foreach($rules as $field=>$value)
       $sql[] = "(".$db->Quote($data["record_id"]).",".$db->Quote($field).",".$db->Quote($value).")";
      $db->Execute( "insert into ".$rh->db_prefix."records_ref_rules (keyword_id, field, value) VALUES ".
                    implode (", ", $sql) );
    }

    // редирект на show
    $rh->Redirect( $object->Href("!/show", NPJ_RELATIVE, STATE_IGNORE ), STATE_IGNORE );
  }

 return GRANTED;

?>