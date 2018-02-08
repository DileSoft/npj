<?php
/*
  object -- ссылка на объект, из которого вызывается функция
  data   -- массив подготовленных итемов фида
  params -- параметры action (relevant: style)
*/
 function &npj_object_action_forum( &$object, &$data, &$params )
 {
   $rh    = &$object->rh;
   $tpl   = &$object->rh->tpl;
   $debug = &$object->rh->debug;

  // 0. limit templates
  $templates = array("default", 
                    );
  if (!in_array($params["style"], $templates)) unset($params["style"]);
  if (!isset($params["style"])) $params["style"] = "default";

  // 1. choose template
  foreach( $templates as $v )
   if ($params["style"] == $v)
    { $tplt = "List_".$v; break; }

  // 2. parse feed
  $list = &new ListObject( &$rh, &$data );
  return "<!--notoc-->".$list->Parse( "actions/_forum.html:".$tplt )."<!--/notoc-->";

 }


?>