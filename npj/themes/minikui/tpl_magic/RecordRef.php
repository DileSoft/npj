<?php

//    if ($tpl->GetValue("NoRecordStats")) return;
    if ($tpl->GetValue("404")) return;
    if ($tpl->GetValue("Panel:Off"))  return;

    if ($rh->object->class == "record") 
    {
      // ��� ����� ��� ����� �������� ��� ���������� //
      $rh->utility["skin"]->ParseRecordRef( &$rh->object  );
      echo $tpl->GetValue( "Record.Stats.Ref" );
    }

?>