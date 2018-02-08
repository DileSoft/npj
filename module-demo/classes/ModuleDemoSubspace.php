<?php
/*

    Демо "модуль субпространства"

    ModuleDemoSubspace( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- какой присоединить набор с сообщениями для вывода?
      - $section_id -- идентификатор гигантского раздела сайта (не группы внутри модуля)
      - $handlers_dir, $messageset_dir -- в замену стандартным из $rh->..

  ---------

========================================= v.1 (kuso@npj)
*/

class ModuleDemoSubspace extends NpjModule
{
  var $module_name = "ModuleDemoSubspace"; // for use in debug

  function Init( $rel_url )
  {
    $this->method = "default";
    $this->params = array();
    $parts = explode("/", trim($rel_url,"/"));

    if ($rel_url == "another")
    {
      $this->method = "another";
      $this->params = $parts;
      array_shift($this->params);
    }

  }

// EOC { ModuleDemoSubspace }
}


?>