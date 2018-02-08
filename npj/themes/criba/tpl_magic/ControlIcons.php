<?php


  if ($tpl->GetValue("Preparsed:PRINT")) return;

  if (!$rh->principal->IsGrantedTo("noguests")) return;

    // ÂÛÂÎÄ ÏÀÍÅËÈ
    $su = &$rh->utility["skin"];

    $panel = array();
    foreach ( $su->panel["panel"] as $k=>$v )
     if (!isset($hide[$k])) $panel[$k]=$v;

    $tplt = "control_icons.html:Panel_Item";
    $prefix = "";
    $postfix = "";
    $separator = "";

    if (sizeof($panel) == 0) { echo "&nbsp;"; return GRANTED; }
    $result =  $su->ParsePanel( $su->panel["granted"], $panel, $su->panel["base"],
                                $su->panel["links"], 
                                $su->panel["method"], $su->panel["Name"],
                                $tplt, $prefix, $postfix, $separator );

    echo $result;

?>
