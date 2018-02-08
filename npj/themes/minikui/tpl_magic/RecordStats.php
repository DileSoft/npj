<?php

  $su = $rh->utility["skin"];

  if ($tpl->GetValue("Panel:Off"))  return;

  $security = "public";
  if ($rh->principal->data["options"]["record_stats"] == 1) ; else
  {
    if ($tpl->GetValue("NoRecordStats") == 1) return;
    if (($tpl->GetValue("NoRecordStats") == 2) || ($tpl->GetValue("404")))
    {  echo $tpl->Parse("record.stats.html:Title"); return; }

    if (($rh->object->class == "comments") && ($rh->object->params[0] != "ok") && ($rh->object->method == "add")) ; else
    if (      ($rh->object->class == "account") && ($rh->object->method == "show")  )
    { echo "<br />"; return;  }
    if (      ($rh->object->class == "record") && ($rh->object->method != "show")   )
    {  echo $tpl->Parse("record.stats.html:Title"); return; }
  }

  // всегда скрыта.
  if ($rh->principal->data["options"]["record_stats"] == 2)
  { echo $tpl->Parse("record.stats.html:Title"); return; }


  // Записать в домен статы
  $result = $su->AssignRecordStats( &$rh->object );

  // если панель выводить не получается, выходим
  if (!$result) { echo $tpl->Parse("record.stats.html:Title"); return; }

  // как-то здесь рубрикация ???
  if ($su->stats_type != "comment")
  {
     // вот здесь вот можно дописать про рубрикацию //
     $rh->utility["skin"]->ParseRecordRef( &$su->stats_object, true, true, false, false );
     // //
  }


//  $rh->object->_Trace("Recordstats"); 
//  $debug->Trace_R( $rh->object->data );
//  $debug->Error($rh->object->method);

    // ВЫВОД ПАНЕЛИ
    if ($tpl->GetValue("Access:Forbidden")) $security="forbidden";
    $tpl->Assign("Record.Stats.Sec", $security);
    $tpl->Assign("Record.Stats.Sec.Title", $tpl->message_set["Record.Stats.Sec"][$security]);

    $panel = array();
    foreach ( $su->panel["panel"] as $k=>$v )
     if (!isset($hide[$k])) $panel[$k]=$v;

    $tplt = "record.stats.html:Record.Panel_Item";
    $prefix = "";
    $postfix = "";
    $separator = "";

    if (sizeof($panel) == 0) { echo "<br />"; return GRANTED; }
    $result =  $su->ParsePanel( $su->panel["granted"], $panel, $su->panel["base"],
                                $su->panel["links"], 
                                $su->panel["method"], $su->panel["Name"],
                                $tplt, $prefix, $postfix, $separator );

    $tpl->Assign( "Record.Stats.Colspan", 2+2*sizeof($panel) );
    $tpl->Assign( "Record.Stats.Ctrls", $result );
    
    echo $tpl->Parse("record.stats.html:Record");

?>