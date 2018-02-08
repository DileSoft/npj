<?php
/*
  object -- ссылка на объект, из которого вызывается функция
  data   -- массив подготовленных итемов фида
  params -- параметры action (relevant: style, show_next_prev)
*/
 function &npj_object_action_feed( &$object, &$data, &$params )
 {
   $rh    = &$object->rh;
   $tpl   = &$object->rh->tpl;
   $debug = &$object->rh->debug;

  // 0. limit templates
  $templates = array("full", "userpics", "friends", "members", "authors", "poloskuns", 
  //                 "context", "wacko_digest", "html_digest", // !!! not ready yet
                    );
  if (!in_array($params["style"], $templates)) unset($params["style"]);
  if (!isset($params["style"])) $params["style"] = "full";

  // 1. choose template
  foreach( $templates as $v )
   if ($params["style"] == $v)
    { $tplt = "List_".$v; break; }

  // 2. parse feed
  $list = &new ListObject( &$rh, &$data );
  $result = "<!--notoc-->".$list->Parse( "actions/_feed.html:".$tplt )."<!--/notoc-->";
  return $result;

 }


?>