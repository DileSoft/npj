<?php
/*

    Демо "аккаунт-модуль"

    ModuleDemoAccount( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- какой присоединить набор с сообщениями для вывода?
      - $section_id -- идентификатор гигантского раздела сайта (не группы внутри модуля)
      - $handlers_dir, $messageset_dir -- в замену стандартным из $rh->..

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