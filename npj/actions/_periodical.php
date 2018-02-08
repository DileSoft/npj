<?php
/*
  object -- ссылка на объект, из которого вызывается функция
  data   -- массив подготовленных итемов фида
  params -- параметры action (relevant: style, show_next_prev)
*/
 function &npj_object_action_periodical( &$object, &$data, &$params )
 {
   $rh    = &$object->rh;
   $tpl   = &$object->rh->tpl;
   $debug = &$object->rh->debug;

  // 0. limit templates
  $templates = array( "plain",
                      "context", // "wacko_digest", "html_digest", // !!! not ready yet
                    );
  if (!in_array($params["style"], $templates)) unset($params["style"]);
  if (!isset($params["style"])) $params["style"] = "plain";
  // 0+. limit submode
  $periods = array( "none"=>0, "daily"=>1, "monthly"=>2 );
  if (!isset($periods[$params["period"]])) $params["period"] = "daily";
  $period = $periods[ $params["period"] ];

  // 1. choose template
  foreach( $templates as $v )
   if ($params["style"] == $v)
    { $tplt = "List_".$v; break; }

  // 1x. выбор тага
  if ($params["subject"])
   foreach ($data as $k=>$v) $data[$k]["_tag"] = $data[$k]["non_empty_subject"];
  else
   foreach ($data as $k=>$v) $data[$k]["_tag"] = $object->AddSpaces($data[$k]["tag"], " ");

  // 2. если period != "none", то нужно разбить массив по датам
  if ($period == "none") { $tplt.="_items"; $data2 = &$data; }
  else
  if ($params["style"] == "context") { $data2 = &$data; }
  else
  {
    $data2 = array();
    // 2a. разбить по датам
    foreach ($data as $k=>$v)
    {
      switch ($period)
      {
        case 1: $value = substr( $v["datetime"], 0, 10 ); break;
        case 2: $value = substr( $v["datetime"], 0, 7  ); break;
      }
      if (!isset($data2[ $value ]))
      {
        $data2[$value] = array( "items" => array());
      }
      $data2[ $value ]["items"][] = &$data[$k];
    }
    // 2б отпарсить по датам
    foreach ($data2 as $k=>$v)
    {
      $listDay = &new ListObject( &$rh, &$data2[$k]["items"] );
      $data2[$k]["items"] = $listDay->Parse("actions/_periodical.html:".$tplt."_items");
    }
    // 2в. прописать заголовки каждой дате
    switch ($period)
    {
      case 1: foreach($data2 as $k=>$v) $data2[$k]["date"] = $k;
              break;
      case 2: foreach($data2 as $k=>$v) 
              {
                $year  = 1*substr($k,0,4);
                $month = 1*ltrim(substr($k, 5, 2),"0");
                $data2[$k]["date"] = $tpl->message_set["Months"][$month]."&nbsp;".$year;
              }
              break;
    }

  }

  // 3. а теперь можно парсить по имеющемуся шаблону
  $list = &new ListObject( &$rh, &$data2 );
  return $list->Parse( "actions/_periodical.html:".$tplt );

 }
?>