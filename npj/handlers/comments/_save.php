<?php

/*
  unlike RECORD/_SAVE эта функция НЕ ОБНОВЛЯЕТ КОММЕНТАРИЙ. Она его, понимаешь, ДОБАВЛЯЕТ.

  function Save() 
  работаем с $this->data
  секьюрити не проверяем - оно проверено в edit ????
*/
  $this->data_before_save = $this->data; // сохраняем то, что пришло -- чтобы не потерять

  $fields = array(
            "pic_id", "subject", "body_post", 
            "user_id", "user_login", "user_name", "user_node_id", 
            "created_datetime", "ip_xff", "frozen", "active",
            "record_id", "parent_id", "lft_id", "rgt_id",
                  );
  //0, subject, body, user etc, created, "[mail]", record_id, 0/comment_id, 0, 0

  // #0. загрузим родителя и проверим, не заморожен/удалён ли он?
  // замороженные можно добавлять, но только "замороженными" же.
  // это чтобы программным путём сыпать комментарии or something
  if ($this->data["parent_id"])
  {
    $parent = $this->_LoadById( $this->data["parent_id"], 2 );
    if ((($parent["active"] == 0) && ($parent["active"] != $this->data["active"]))
        || 
        (($parent["frozen"] != 0) && ($parent["frozen"] != $this->data["frozen"]))
       )
       return DENIED;  // а вот здесь бы высылать почтой !!!!!!!

  }
  // это, чтобы можно было добавлять изначально "удалённые" комментарии
  if (!isset($this->data["active"])) $this->data["active"] = 1;

  // #1. найти нужные lft_id / rgt_id
    // Находим куда приклеить 
    $sql = "select comment_id, rgt_id from ".$rh->db_prefix."comments where record_id=".
          $db->Quote($this->data["record_id"])." and parent_id=".
          $db->Quote($this->data["parent_id"])."order by rgt_id desc";
    $rs = $db->SelectLimit( $sql, 1);
    if ($rs->RecordCount() == 0) 
    if ($this->data["parent_id"] == 0) $left = 1; 
    else 
    {
      $sql = "select rgt_id from ".$rh->db_prefix."comments where comment_id=".
             $db->Quote($this->data["parent_id"]);
      $rs = $db->SelectLimit( $sql, 1);
      if ($rs->RecordCount() == 0) $left = 1;
      else $left = $rs->fields["rgt_id"];
    }
    else $left = $rs->fields["rgt_id"]+1;
    // сдвигаем дерево вправо
    if ($left > 1)
    {
      $sql1 = "update ".$rh->db_prefix."comments set lft_id=lft_id+2 where record_id=".
              $db->Quote($this->data["record_id"])." and lft_id>=".$db->Quote($left);
      $sql2 = "update ".$rh->db_prefix."comments set rgt_id=rgt_id+2 where record_id=".
              $db->Quote($this->data["record_id"])." and rgt_id>=".$db->Quote($left);
      $db->Execute( $sql1 );
      $db->Execute( $sql2 );
    }
    // запоминает левое/правое нашей записи
    $this->data["lft_id"]= $left;
    $this->data["rgt_id"]= $left+1;

  // #2. сформировать запрос в БД
    $sql = ""; $f=0;
    foreach( $fields as $field )
    { if ($f) $sql.=", "; else $f=1;
      $sql.= $db->Quote( $this->data[$field] );
    }
    $sql = "insert into ".$rh->db_prefix."comments (".implode(", ",$fields).") VALUES (".$sql.")";

  // #3. выполнить запрос в БД
    $db->Execute( $sql );
    $comment_id = $db->Insert_ID();
    $this->npj_object_address .= "/".$comment_id;
    $this->name = $comment_id;
    $this->Load(3);

  // #3.5. community filter -- надо унаследовать настройки от родителя, если есть
    if ($rh->community_filter && $this->data["parent_id"])
    {
      $sql = "select * from ".$rh->db_prefix."comments_filtered where ".
             " comment_id = ".$db->Quote($this->data["parent_id"]);
      $rs  = $db->Execute($sql);
      $_a  = $rs->GetArray();
      if (sizeof($_a))
      {
        $rows = array();
        foreach( $_a as $k=>$v )
        {
          $row = array();
          foreach( $v as $kk=>$vv )
            if (!is_numeric($kk) && ($kk != "_id") && ($kk != "comment_id"))
              $row[$kk] = $db->Quote($vv);
          $_flag = true;
          $row["comment_id"] = $db->Quote($this->name);
          $rows[] = "(".implode(",", $row).")";
        }
        $_a = array();
        foreach( $row as $k=>$v ) $_a[] = $k;
        $sql = "insert into ".$rh->db_prefix."comments_filtered ".
               "(".implode(",", $_a).") values ".implode(",", $rows);
        $db->Execute( $sql );
      }
    }

  // #4. делаем отсылку почты

    $this->Handler( "mail", array(), &$principal );

  // #5. подписываем себя (принципала, от имени которого действуем) на комментарии, если надо
    $p = array("by_script" => 1);
    if ($this->data_before_save["subscription_tree"]) $p["option"] = "tree";  
    if ($this->data_before_save["subscription_tree"] || 
        $this->data_before_save["subscription_childs"])
      $this->Handler( "subscribe", &$p, &$principal );

  // #6. обновляем comment-count
    $sql = "select count(*) as result from ".$rh->db_prefix."comments where active=1 and record_id=".
           $db->Quote( $this->data["record_id"] );
    $rs = $db->Execute( $sql );
    $record_id = $this->data["record_id"];
    $sql = "update ".$rh->db_prefix."records set ".
           "commented_datetime=".$db->Quote( $this->data["created_datetime"] ).", ".
           "last_comment_id   =".$db->Quote( $this->data["comment_id"] ).", ".
           "number_comments=".$db->Quote( $rs->fields["result"] ).
           " where record_id=".
           $db->Quote( $record_id );
    $db->Execute( $sql );
    $sql = "update ".$rh->db_prefix."records_ref set ".
           "commented_datetime=".$db->Quote( $this->data["created_datetime"] ).", ".
           "last_comment_id   =".$db->Quote( $this->data["comment_id"] )." ".
           " where record_id=".
           $db->Quote( $this->data["record_id"] );
    $db->Execute( $sql );

  // #6,5 <--- module call
    // third part module call
    $parent_data = $this->_LoadById( $this->data["record_id"], 2, "record" );
    $parent_record = &new NpjObject( &$rh, $parent_data["supertag"] );
    if ($rh->modules && isset($rh->modules[$parent_record->class]))
      $this->PassToModule( $parent_record->class, "on_comment", 
                           array( "comment_id"=>$this->data["comment_id"],
                                  "record_id" =>$this->data["record_id"]  ), 
                           &$principal );
  // ^6,5 done

  // #7. обновляем comment-count в анонсах документов
    $sql = "update ".$rh->db_prefix."records_rare set announced_comments=".
           $db->Quote( $rs->fields["result"] )." where announced_id=".
           $db->Quote( $this->data["record_id"] );
    $db->Execute( $sql );

  // #7 bis. обновление records.last_comment_id &etc. у анонсов.
    $sql = "select record_id from ".$rh->db_prefix."records_rare where ".
           "announced_id=".$db->Quote( $this->data["record_id"] );
    $rs = $db->Execute( $sql );
    $a = $rs->GetArray();
    $r_ids_q = array();
    foreach($a as $k=>$v) $r_ids_q[] = $db->Quote( $v["record_id"] );
    if (sizeof($r_ids_q))
    {
       $sql = "update ".$rh->db_prefix."records ".
              " set ".
              "commented_datetime=".$db->Quote( $this->data["created_datetime"] ).", ".
              "last_comment_id   =".$db->Quote( $this->data["comment_id"] )." ".
              " where record_id in (".implode(",", $r_ids_q).")";
       $db->Execute( $sql );
       $sql = "update ".$rh->db_prefix."records_ref set ".
              "commented_datetime=".$db->Quote( $this->data["created_datetime"] ).", ".
              "last_comment_id   =".$db->Quote( $this->data["comment_id"] )." ".
              " where record_id in (".implode(",", $r_ids_q).")";
       $db->Execute( $sql );
    }

//  $debug->Trace_R( $this->data );
//  $debug->Error( $sql );

?>