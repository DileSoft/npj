<?php

//    if ($tpl->GetValue("NoRecordStats")) return;
    if ($tpl->GetValue("404")) return;
    if ($tpl->GetValue("Panel:Off"))  return;

    if ($rh->object->class == "record") 
    {
      // вот здесь вот можно дописать про рубрикацию //
      $rh->utility["skin"]->ParseRecordRef( &$rh->object  );
      echo $tpl->GetValue( "Record.Stats.Ref" );
    }

?>