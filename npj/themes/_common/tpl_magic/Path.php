<?php

  $su = &$rh->utility["skin"];

  // �������� � ����� �����
  $result = $su->AssignRecordStats( &$rh->object );

  // ���-�� ����� ���������� ???
  if ($su->stats_type != "unknown")
  if ($su->stats_type != "comment")
  {
     // ��� ����� ��� ����� �������� ��� ���������� //
     $rh->utility["skin"]->ParseRecordRef( &$su->stats_object, true, true, false, false );
     // //
  }

  echo $tpl->GetValue( "Record.Stats.Address" );



?>