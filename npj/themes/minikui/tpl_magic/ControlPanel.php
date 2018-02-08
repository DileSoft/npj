<?php

  $su = $rh->utility["skin"];

  if ($tpl->GetValue("Panel:Off"))  return;
  if ($tpl->GetValue("404"))  return;

  $hide = array(/* "delete"=>1 */);

  $panel = array();
  foreach ( $su->panel["panel"] as $k=>$v )
   if (!isset($hide[$k])) $panel[$k]=$v;

  if (sizeof($panel) == 0) { echo "<br />"; return GRANTED; }
  echo $su->ParsePanel( $su->panel["granted"], $panel, $su->panel["base"],
                        $su->panel["links"], $su->panel["method"], $su->panel["Name"] );

?>