<?php
/*
  object -- ������ �� ������, �� �������� ���������� �������
  data   -- ������ �������������� ������ ����
  params -- ��������� action (relevant: style, show_next_prev)
*/
// ----------------------------------------------------------------------------------- ��������������� ������
if (!function_exists( "print_tree" ) )
{
  function print_tree( &$rh, &$subtree, $tplt, $subject )
  {
     $from =  "non_empty_subject";
     if ($subject == 0) $from = "tag2";
     if ($subject == 2) $from = "tag1";

    foreach( $subtree as $k=>$v )
    { // �������� �� �����
      if (isset($subtree[$k]["__childs"]) && (sizeof($subtree[$k]["__childs"]) > 0))
        $subtree[$k]["childs"] = print_tree( &$rh, &$subtree[$k]["__childs"], $tplt, $subject );
      else
        $subtree[$k]["childs"] = "";
      // ������ non_empty_subject �� tag, tag1
      $subtree[$k]["non_empty_subject"] = $subtree[$k][$from];
    }
    // �������
    $list = &new ListObject( &$rh, &$subtree );
    return $list->Parse( $tplt );
  }
}
// ---------------------------------------------------------------------------------------------- ����� �����
 
 function &npj_object_action_list( &$object, &$data, &$params )
 {
   $rh    = &$object->rh;
   $tpl   = &$object->rh->tpl;
   $debug = &$object->rh->debug;

  // 0. limit templates
  $templates = array( "indent", "ul", "ol", "js", "br",
                      "context", 
                      //"wacko_digest", "html_digest", // !!! not ready yet
                    );
  if (!in_array($params["style"], $templates)) unset($params["style"]);
  if (!isset($params["style"])) $params["style"] = "ul";

  // 1. choose template
  foreach( $templates as $v )
   if ($params["style"] == $v)
    { $tplt = "List_".$v; break; }


  // 2. ������������� ������, ���������� �� ���� �������
  // +
  // 3. --if index, compose & tpl->Assign "TreeIndex"-- deprecated for now ???
  $index=""; $hash = array(); $letters = array();
  if ($params["index"])
  {
    foreach( $data as $k=>$v )
    {
      if ($v["tag2"])
       $sup = strtoupper( $v["tag2"][0]);
      else
       $sup = strtoupper( $v["tag"][0]);
      if (!isset($letters[ $sup ]) )
      {
         $data[$k]["letter"] = "<div class=\"index-letter\">".$sup."</div>";
         $letters[ $sup ] = $v["record_id"];
      } else $data[$k]["letter"] = "";
    }

    foreach( $letters as $letter=>$id )
      $index.="&nbsp;<a href=\"#letter_".$id."\">".$letter."</a> ";
    $index = "<div class=\"tree-index\">".$index."</div>";
  }

  // 4. parse tree in recursion
  return $index.print_tree( &$rh, $data, "actions/_list.html:".$tplt, $params["subject"] ).$index;
 }
?>