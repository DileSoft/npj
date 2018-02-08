<?php

  // Показать список аккаунтов
  //
  /*
     {{Channels>List
         (+) [ type = "rss|file|mailbox" ]
         (+) [ state = "valid" ]
         (+) [ page_size = 20 ]
         (-) [ my   = 1 ]
     }}
  */
  //$debug->Trace_R( $params );
  $rh->UseClass( "ListObject", $rh->core_dir );
  $rh->UseClass( "Arrows", $rh->core_dir );
  $rh->UseClass( "ChannelAbstract", $this->classes_dir);

  $channels = array();

  // ===========================================================
  // Параметры по-умолчанию
  if (!$params["page_size"]) $params["page_size"] = 20;
  $page_size = $params["page_size"];
  $page_frame_size = false;
  $order = "u.login asc";

  // ===========================================================
  // Кусочки запроса
  if ($params["state"] == "valid") 
    $where_valid = "and state = ".$db->Quote( CHANNELS_STATE_OK );
  if ($params["type"]) 
    $where_type = "and channel_type = ".$db->Quote( $params["type"] );
  // ===========================================================
  // "Стрелочки" 
  $table = "channels as c,".$rh->db_prefix."users as u";
  $where = "c.user_id = u.user_id ".$where_valid.$where_type;

  $arrows = &new Arrows( &$state, $where, $table, $page_size, $page_frame_size );

  // ===========================================================
  // Строим запрос на каналы и получаем их
  $sql = "select c.* from ".$rh->db_prefix.$table.
         " where ".$where.
         " order by ".$order;
  //$debug->Error($sql);

  $rs  = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
  $a   = $rs->GetArray();
  $user_ids_q = array();
  $channels   = array();
  foreach( $a as $k=>$v ) 
  {
    $channels[ $v["channel_id"] ] = $a[$k];
    $user_ids_q[] = $db->Quote( $v["user_id"] );
  }

  // ===========================================================
  // Втупую создаём каналы =)
  $ch = array();
  foreach( $channels as $k=>$v ) 
  {
    $ch[$k] = &ChannelAbstract::Factory( &$this, $v["channel_type"], 
                                         $v["channel_id"], CHANNELS_ID );
  }

  // ===========================================================
  // Препарсинг (пдготовка к выводу,собственно)
  foreach ($channels as $k=>$item)
  {
    $channels[$k] = $this->object->_PreparseAccount($ch[$k]->data);
    $channels[$k]["more"] = "";
    $channels[$k]["is_managed"] = 
         $principal->IsGrantedTo( "owner", "account", $ch[$k]->data["user_id"] );
  }

  // ===========================================================
  // Парсинг результата
  // 0. spawn TE
  $TE = &$this->GenerateTemplateEngine();

  // 1. parse Arrows
  $arrows->tpl = &$TE;
  $arrows->Parse( "arrows.html", "ARROWS"  );

  // 4. parse list
  $list = &new ListObject( &$rh, &$channels );
  $list->tpl = &$TE;
  $result = "<!--notoc-->".$list->Parse( "list.html:List" )."<!--/notoc-->";

  // ============================================================
  // Сохранение результата
  $tpl->Assign("Preparsed:TITLE", "Каналы агрегации".
          ($params["type"]?(" типа &laquo;".$params["type"]."&raquo;"):""));
  $tpl->Assign("Preparsed:CONTENT", $result);


  return GRANTED;

?>