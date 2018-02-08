<?php

  $su = &$rh->utility["skin"];

  if ($tpl->GetValue("Preparsed:ModRef")) { echo $tpl->GetValue("Preparsed:ModRef"); return; }

  // как-то здесь рубрикация ???
  if ($su->stats_type != "unknown")
  if ($su->stats_type != "comment")
  {
     // вот здесь вот можно дописать про рубрикацию //
     $su->ParseRecordRef( &$su->stats_object, false, false, true, false );
     // //
  }

  echo $tpl->GetValue( "Record.Stats.Ref" );
?>