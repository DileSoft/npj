<?php

// ToDo:
//  * записывать is_parent в records

/*
    {{Tree
           page="Kuso@NPJ:PageAddress"
           [mode=, style= -- по общей спецификации]
           [depth="full|1|2"]
           [filter="keywords|all|digests"]
           [subject="tag|1|short"]
           [index="1"]
           }}
*/
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
  $rh->UseClass( "ListObject", $rh->core_dir );

  $tpl->Assign( "show_past",     $params["show_past"]     );
  $tpl->Assign( "show_future",   $params["show_future"]   );
  $tpl->Assign( "show_timeline",        $params["show_future"] || $params["show_past"] );
  $tpl->Assign( "show_past_and_future", $params["show_future"] && $params["show_past"] );

  if (!isset($params["subject"])) $params["subject"] = 2;
  if ($params["subject"] == "tag") $params["subject"] = 0;
  if ($params["subject"] == "short") $params["subject"] = 2;


  if ($params["index"])
  { $params["subject"]=0;
    if (!isset($params["style"])) $params["style"] = "br";
    if (($params["style"] == "ol") || ($params["style"] == "ul"))
      $params["style"]   ="indent";
  }

  // 0. unwrap
  if ($params[0] == "") $params[0] = $this->npj_object_address;
  $supertag = $this->_UnwrapNpjAddress( $params[0] );
  $supertag = $this->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи

  $dataobject = &new NpjObject( &$rh, $supertag );
  $dataobject->Load(2);

  $supertag = rtrim( $supertag, "/" );
  $dotpos = strpos( $supertag, ":" );
  if ($dotpos !== strlen($supertag)-1) // если супертаг не оканчивается на ":"
  {
    if ($dotpos === false) { $supertag.=":"; $dotpos = strlen($supertag)-1; }
    else $supertag.="/";
  }
  // длина обрезаемого тага
  if ($dotpos === strlen($supertag)-1) $root_tagparts = array();
  else
  {
    if (is_array($dataobject->data))
     $tag = $dataobject->data["tag"];
    else
     $tag = substr($supertag, strlen($dataobject->npj_account)+1 );
    $root_tagparts = explode( "/", $tag );
  }

  // 1. attach morecase & other sql specific params
  $morecase = "";
  if ($params["filter"] == "keywords") $morecase .= " AND (r.is_keyword=1) ";
  if ($params["filter"] == "digests")  $morecase .= " AND (r.is_digest>0) ";
  if ($params["filter"] == "clusters") $morecase .= " AND r.is_parent=1 ";
//  [??] кто такой $depth и откуда он пришел ??
//  kuso@: is should be start parameter, noting depth of tree`s root. "depth" seems to be not supported yet.
  if (is_numeric($params["depth"]))    $morecase .= " AND r.depth<=".$db->Quote($depth+$params["depth"]);
  $order = "UPPER(r.tag)"; if ($params["subject"]) $order="UPPER(r.subject)";

  // 1а. только для ключслов и кластеров
  if ($params["filter"] == "keywords")
  {
    $count = "COUNT(*) as content_count, ";
    $join  = ", ".$rh->db_prefix."records_ref as ref";
    $where_group = " AND ref.keyword_id=r.record_id GROUP BY (ref.keyword_id) ";
  }


  // 2. load recent by subtree [???] Стоит вынести это в константу?
  // << 2004-12-02 max@ >>
  // fields must present:
  // type, group1, group2, group3, group4, record_id. record_id as id, user_id - is
  $sql = "SELECT ".$count.
         " 0 as is_empty, "." 0 as is_empty1, ".
         " r.record_id, r.record_id as id, ".
         " user_id, ".
         " r.group1, r.group2, r.group3, r.group4, ".
         " type ".
         " FROM ".
         $rh->db_prefix."records as r ".$join." WHERE r.type=2 AND r.supertag LIKE ".$db->Quote( $supertag."%" ).
         $morecase.
         " ".$where_group." ORDER BY r.depth, ".$order." ASC";
 $rs = $db->Execute( $sql );

  // 3. filter them out
  $found = 0; $data = array();
  $hash = array();

  // << 2004-12-02 max@  >>
  $q_data = $rs->GetArray();
  $record_ids = array();
  foreach ($q_data as $fields)
  {
    // проверить права и сложить id-шники с отфильтрованными записями про запас. пригодятся
    $cache->Store( "record", $fields["record_id"], 1, $fields );
    if ($principal->IsGrantedTo(  $this->security_handlers[$fields["type"]],
                                    "record", $fields["record_id"]))
       $record_ids[] = $fields["record_id"];
       $filtered[$fields["record_id"]] = $fields; // нужно для мёржа с массивом "тел" -- $qb_data
  }
  // а теперь получаем бодисы  << max@ >>
  $pagesize = count($record_ids); // все -- одной портянкой
  $order_issue = $order." ASC";

  $uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе.
  $qb_data = $uactn->GetRecordBodies ( &$record_ids, $params, 0, false, $order_issue, $pagesize ); 

  $qf_data = array();
  foreach ($qb_data as $v)
      $qf_data[] = array_merge( (array) $filtered[$v["record_id"]], (array) $v);

  // formatting
  foreach ($qf_data as $fields)
  {
    if ($fields["tag"] == "") continue;

    $found++;
    $fields["datetime"] = $fields["user_datetime"]; // здесь главная дата -- какая написана
    if ($fields["content_count"] == 0) $fields["content_count"] = "";
    else $fields["content_count"] = "<span class=\"count-\">(записей:&nbsp;".$fields["content_count"].")</span>";

    $hash[ $fields["supertag"] ] = $fields;
    // tag2 -- обрезок ТАГА (обрезается слева на длину тага корня)
    // _tag -- дописываем к этому слэш
    $tagparts = explode("/", $hash[ $fields["supertag"] ]["tag"] );
    if (sizeof($root_tagparts))
      $new_tagparts = array_slice( $tagparts, sizeof($root_tagparts) );
    else
      $new_tagparts = $tagparts;
    $hash[ $fields["supertag"] ]["tag2"] = implode("/", $new_tagparts); 
    $hash[ $fields["supertag"] ]["_tag"] = $hash[ $fields["supertag"] ]["tag2"]."/";
    //$debug->Trace( "[$found]=".$hash[ $fields["supertag"] ]["tag"]." ==== ".$hash[ $fields["supertag"] ]["_tag"] );

    $hash[ $fields["supertag"] ]["_childs"] = array();
    $data[] = &$hash[ $fields["supertag"] ];

  }

  //$debug->Trace_R( $data );

  // ----- завершение первой стадии -----------------------
  // преформат полей (стадия 2) -----------
  foreach ($data as $k=>$item)
  {
    $data[$k] = $object->_PreparseArray( &$data[$k] );
  }

  // RSS-a нет для дерева (нет стадии 3) --
  // Точнее, она совсем другая! простраиваем дерево, деревце
  $defaults = array(
         "is_empty1" => ($params["filter"] == "digests")?0:1,
         "is_empty"  => 1,
         "is_not_digest" => ($params["filter"] == "digests")?1:0,
         "record_id" => 0, "id"=> 0, "body_post" => "",
         "user_id"   => $object->data["user_id"],
         "edited_user_name"   => $object->data["edited_user_name"],
         "edited_user_login"   => $object->data["edited_user_login"],
         "edited_user_node_id"   => $object->data["edited_user_node_id"],
         "created_datetime" => "", "edited_datetime" => "", "user_datetime" => "", "datetime" => "",
         "number_comments" => 0,
         "disallow_comments" => 1,
         "group1" => 0, "group2" => 0, "group3" => 0, "group4" => 0,
         "type" => 2,
         "is_digest" => 0,
         "version_tag" => "",
         "is_parent" => 0,
         "depth" => 0,
         "disallow_replicate" => 0,
         "pic_id" => 0,
                    );
  $defaults = $object->_PreparseArray( &$defaults );
  $data2 = $uactn->BuildTree( &$object, $supertag, &$defaults, &$hash, &$data );

  // --------------- заполняем Action:TITLE
   if ($title == "")
   {
     $rdata = $object->_Load( rtrim($supertag, "/"), 2);
     if (is_array($rdata))
      if ($rdata["tag"] == "") $title = substr($rdata["supertag"],0,strpos($rdata["supertag"],":"));
      else  $title = substr($rdata["supertag"],0,strpos($rdata["supertag"],":")).":".$rdata["tag"];
   }
   $tpl->Append("Action:TITLE", " ".$object->Link(rtrim($supertag, ":/"), "", $title) );

  // вызов алгоритма вывода (стадия 4) ----
  if (sizeof($data2) == 0)
  {
    // кажется, здесь нужно придумать, что бы такого выводить, когда пусто
    // !!! -> messageset
    return ""; // "нет подходящих записей";
  }
  else
  return $object->_ActionOutput( &$data2, &$params, "list" );

?>