<?php
/*
    FieldModule( &$rh, $config ) -- ссылочка на попап другого модуля
  ---------

  // overridden:
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * module -- например "news"
  * item   -- например 31
  * type   = "item" // order, list
  * text   = "редактировать"
  * w, h
  * scroll    = "yes" // "no"
  * resizable = "yes" // "no"
  * mode = "edit" // "add"
  * add_text 
  * no_form
  * modal

=============================================================== v.0 (Kuso)
*/

class FieldModule extends Field
{
  function FieldModule( &$rh, $config )
  {
    $config["readonly"]  = 1; // не пробовать загружать из формы
    $config["db_ignore"] = 1; // не пробовать загружать из БД (и сохранять)
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_module.html:Button";
    if (!isset($this->config["type"])) $this->config["type"] = "item";
    if (!isset($this->config["text"])) $this->config["text"] = "редактировать";
    if (!isset($this->config["scroll"])) $this->config["scroll"] = "yes";
    if (!isset($this->config["resizable"])) $this->config["resizable"] = "yes";
    if (!isset($this->config["mode"])) $this->config["mode"] = "edit";
  }

  // вывод
  function _Format( &$tpl, $tpl_prefix ) 
  { 
    $this->rh->UseClass("Helper", $this->rh->core_dir);
    $helper = &new Helper( &$this->rh );
    $helper->hide_edit = false;
    return $helper->Edit1Click(
        $this->config["module"], $this->config["item"], $this->config["type"], 
        $this->config["tpl_data"], $this->config["text"], 
        $this->config["w"], $this->config["h"], 
        $this->config["scroll"], $this->config["resizable"], 
        $this->config["mode"], $this->config["add_text"], $this->config["no_form"], 
        $this->config["modal"],  $tpl_prefix );
  }

// EOC { FieldModule }
}


?>