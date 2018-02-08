<?php

// служебный хандлер. нужен для того, чтобы запускать всякие клёвые пхп-скрипты разовые.
  $tpl->Assign("Preparsed:TITLE", "Backyard");

  if (!$this->HasAccess( &$principal, "acl_text", $rh->node_admins ))
   return $this->Forbidden("NotAnAdmin");

  /*
  // 0. ---- прописывание root_record_id в юзерс ==============================================================================================
  if ($params[0] == 0)
  {
    $rs = $db->Execute("select user_id, login, node_id from ".$rh->db_prefix."users where root_record_id=0");
    $users = $rs->GetArray();
    foreach( $users as $user )
    {
      $rs = $db->Execute("select record_id from ".$rh->db_prefix."records where user_id = ".$user["user_id"].
                         " and supertag = ".$db->Quote( $user["login"]."@".$user["node_id"].":") );
      $record = $rs->fields;
      $db->Execute( "update ".$rh->db_prefix."users set root_record_id = ". $record["record_id"].
                    " where user_id = ". $user["user_id"] );
    }
    $tpl->Append( "Preparsed:CONTENT", "<p>Прописан <b>root_record_id</b> в таблицу <b>users</b></p>" );
    return true;
  }

  // 1. ---- зачистка таблицы "Сообщества в которых я" ============================================================================================
  if ($params[0] == 1)
  {
    // этот патч нужно применять только на поживших узлах, не при инсталляции
    $rs = $db->Execute("select group_id from ".$rh->db_prefix."groups where group_rank=9");
    $already = array(-185); $a = $rs->GetArray();
    foreach( $a as $v ) $already[] = $v["group_id"];
    $db->Execute("delete from r1_user_groups where group_id in (".implode(",",$already).")");
    $tpl->Append( "Preparsed:CONTENT", "<p>Очищены все группы <b>Сообщества в которых я состою</b> (надо выполнить patch2.sql)</p>" );
    return true;
  }

  // 2. ---- удаление дупов, появившихся в результате работы кривого кода ===========================================================================
  if ($params[0] == 2)
  {
    // 1. получить всех дубликатов
    $rs = $db->Execute("select r1.ug_id from ".$rh->db_prefix."user_groups as r1, ".
                                               $rh->db_prefix."user_groups as r2 ".
                       "where r1.group_id = r2.group_id and r1.user_id = r2.user_id and r1.ug_id > r2.ug_id ");
    $already = array(-185); $a = $rs->GetArray();
    foreach( $a as $v ) $already[] = $v["ug_id"];
    // 2. удалить всех дубликатов
    $db->Execute("delete from ".$rh->db_prefix."user_groups where ug_id in (".implode(",",$already).")" );

    $tpl->Append( "Preparsed:CONTENT", "<p><b>Удалены дубликаты списка конфидентов</b></p>" );
    return true;
  }

  // 3. ---- прописывание себя в свою группу "состою в сообществах" ===========================================================================
  if ($params[0] == 3)
  {
    $rs = $db->Execute("select g.group_id from ".$rh->db_prefix."groups as g, ".
                                                            $rh->db_prefix."user_groups as ug ".
                       "where g.group_rank = ".GROUPS_COMMUNITIES.
                       " and g.user_id = ug.user_id and g.group_id = ug.group_id ");
    $already = array(-185); $a = $rs->GetArray();
    foreach( $a as $v ) $already[] = $v["group_id"];

    $rs = $db->Execute("select u.user_id, u.root_record_id, g.group_id from ".$rh->db_prefix."groups as g, ".
                       $rh->db_prefix."users as u where g.user_id = u.user_id and group_rank = ".GROUPS_COMMUNITIES.
                       " and g.group_id not in (". implode(",",$already).")" );
    $groups = $rs->GetArray();
    foreach( $groups as $group )
    {
      $rs = $db->Execute("insert into ".$rh->db_prefix."user_groups (group_id, user_id, keyword_id) VALUES (".
                         $group["group_id"].", ".$group["user_id"].", ".$group["root_record_id"].")" );
    }
    $tpl->Append( "Preparsed:CONTENT", "<p>Все пользователи занесены в собственные группы <b>Сообщества в которых я состою</b></p>" );
    return true;
  }
  */

  // 4. ---- прописывание отрицательного syndicate в рефы существующим документам ===========================================================================
  if ($params[0] == 4)
  {
    $rs = $db->Execute( "select record_id from ".$rh->db_prefix."records where type=".RECORD_DOCUMENT );
    $a = $rs->GetArray();
    $b = array();
    foreach( $a as $v ) $b[]=$v["record_id"];
    $db->Execute( "update ".$rh->db_prefix."records_ref set syndicate=-1 where record_id in (".implode(",",$b).")");
    $tpl->Append( "Preparsed:CONTENT", "<p>Во все рефы документов прописан <b>syndicate=-1</b></p>" );
    return true;
  }

  // stats. ---- инкремент счётчика статистики ===========================================================================
  if ($params[0] == "stats")
  {
    $db->Execute( "update ".$rh->db_prefix."usage_stats set already_processed=already_processed+1" );
    $tpl->Append( "Preparsed:CONTENT", "<p>Инкрементированы счётчики &laquo;этапа&raquo; статистики.</p>" );
    return true;
  }
  
  $tpl->Append( "Preparsed:CONTENT", "<p>Зря вы сюда, нет тут ничто</p>" );

?>