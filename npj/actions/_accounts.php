<?php
/*
  object -- ������ �� ������, �� �������� ���������� �������
  data   -- ������ �������������� ������ ����
  params -- ��������� action (relevant: style)
*/
 function &npj_object_action_accounts( &$object, &$data, &$params )
 {
   $rh    = &$object->rh;
   $tpl   = &$object->rh->tpl;
   $debug = &$object->rh->debug;


  // 0. limit templates
  $templates = array("simple", "forum",
                    );
  if (!isset($params["style"])) $params["style"] = "simple";
  if (!in_array($params["style"],$templates)) $params["style"] = "simple";

  // 1. choose template
  foreach( $templates as $v )
   if ($params["style"] == $v)
    { $tplt = "List_".$v; break; }

  // 2. parse feed
  $list = &new ListObject( &$rh, &$data );
  return "<!--notoc-->".$list->Parse( "actions/_accounts.html:".$tplt )."<!--/notoc-->";

 }


?>