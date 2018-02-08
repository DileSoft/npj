<?php
/*

    ���� "�������-������"

    ModuleDemoAccount( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- ����� ������������ ����� � ����������� ��� ������?
      - $section_id -- ������������� ����������� ������� ����� (�� ������ ������ ������)
      - $handlers_dir, $messageset_dir -- � ������ ����������� �� $rh->..

  ---------

========================================= v.1 (kuso@npj)
*/

class ModuleDemoAccount extends NpjModule
{
  var $module_name = "ModuleDemoSubspace"; // for use in debug

  function Init( $rel_url )
  {
    $parts = explode("/", trim($rel_url,"/"));
    if ($rel_url == "accountee")
    {
      $this->method = "accountee";
      $this->params = $parts;
      array_shift($this->params);
    }
    else return NpjModule::Init( $rel_url ); // slip thru
     

  }

// EOC { ModuleDemoAccount }
}


?>