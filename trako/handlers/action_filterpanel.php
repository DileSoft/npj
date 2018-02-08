<?php

  // "FILTER+PANEL" action
  //
  //  (+) [show_panel=0,1,2] 
  //

  // =================================================================================
  // Âûחמג הויסעגטי
  if (!isset($params["show_panel"])) $params["show_panel"]=2;
  if (!$params["for"]) $params["for"] = $this->object->npj_object_address; 
  $action1  = $this->Action("filter", $params, &$principal );
  $content1 = $tpl->GetValue("Preparsed:CONTENT");

  if ($_GET["method"] && ($params["show_panel"]==1)) $params["show_panel"]=2;
  if ($params["show_panel"] == 2) 
  {
    $params   = $tpl->GetValue("Action:PARAMS");
    $action2  = $this->Action("panel", $params, &$principal );
    $content2 = $tpl->GetValue("Preparsed:CONTENT");
  }

  $tpl->Assign("Preparsed:CONTENT", $content1.$content2 );

  return GRANTED;

?>