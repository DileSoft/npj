<?php
/*
    FieldText( &$rh, $config ) -- работаем с большими текстами
  ---------

  // overridden:
  * _Preparse( &$tpl_engine )
  * ParseTo( &$tpl_engine )
  * сохранение в БД

  // options (see FieldString)
  * templates -- "field_text.html:HtmlArea"/ "form.html:Row_Span"
  - modals = array( item => { module, [title], [icon] } ) 
  * height = 400px
  * rows, cols
  * post_format = array( postfix => formatter )

=============================================================== v.3 (Kuso)
*/

class FieldText extends FieldString
{

  function FieldText( &$rh, $config )
  {
    if (!isset($config["tpl_data"])) $config["tpl_data"] = "field_text.html:HtmlArea";
    if (!isset($config["tpl_row"]))  $config["tpl_row" ] = "form.html:Row_Span";
    if (!isset($config["maxlen"]))   $config["maxlen"]   = 100000;

    FieldString::FieldString(&$rh, $config);

    // re assigning defaults
    if (!isset($this->config["after_formatter"])) $this->config["after_formatter"] = "wysiwyg_after_htmlarea";
    if (!isset($this->config["modals"])) $this->config["modals"] = array( "pictures" => "Вставить изображение" );
    if (!isset($this->config["height"])) $this->config["height"] = "400px";
    if (!isset($this->config["rows"]))   $this->config["rows"] = "5";
    if (!isset($this->config["cols"]))   $this->config["cols"] = "40";
    if (!isset($this->config["post_format"])) $this->config["post_format"] = array();
  }

  function CreateUPDATE() 
  { $this->_StoreToDb(); 
    $result = $this->config["field"]."=". $this->rh->db->Quote($this->db_data);
    foreach( $this->config["post_format"] as $postfix=>$formatter)
    {
      $result.= ", ".$this->config["field"]."_".$postfix."=". 
                     $this->rh->db->Quote(  $this->rh->tpl->Format($this->db_data,$formatter)  );
    }
    return $result;
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    // add more tpl vars
    $tpl->Assign("__height_px", $this->config["height"]) ;
    $tpl->Assign("__rows", $this->config["rows"]) ;
    $tpl->Assign("__cols", $this->config["cols"]) ;
    $tpl->Assign("cms/", $this->rh->cms_url) ;

    $list = new ListSimple(&$tpl->config, $this->config["modals"]);
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_modal", "__modals");

    return FieldString::_Preparse( &$tpl, $tpl_prefix );
  }


// EOC { FieldText }
}


?>