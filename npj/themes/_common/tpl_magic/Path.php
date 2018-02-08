<?php

  $su = &$rh->utility["skin"];

  // Записать в домен статы
  $result = $su->AssignRecordStats( &$rh->object );

  // как-то здесь рубрикация ???
  if ($su->stats_type != "unknown")
  if ($su->stats_type != "comment")
  {
     // вот здесь вот можно дописать про рубрикацию //
     $rh->utility["skin"]->ParseRecordRef( &$su->stats_object, true, true, false, false );
     // //
  }

  echo $tpl->GetValue( "Record.Stats.Address" );



?>