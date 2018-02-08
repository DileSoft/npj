<?php
/*
    FieldStringIframe( &$rh, $config ) -- работаем со строками
  ---------

  // overridden:
  * _Preparse( &$tpl_engine )

  // options
  * iframe_target
  * interface_params
    - deploy_text
    - collapse_text
    - add_mode
  * additional

=============================================================== v.0 (Kuso)
*/

class FieldStringIframe extends FieldString
{

  function FieldStringIframe( &$rh, $config )
  {
    // assigning defaults
    if (!isset($config["tpl_data"])) 
      $config["tpl_data"] = "field_iframe.html:Plain";

    FieldString::FieldString(&$rh, $config);
  }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    $tpl->Assign("_IframeTarget", $this->config["iframe_target"] );
    $tpl->Assign("_Additional", "_".$this->config["additional"] );

    return FieldString::_Preparse( &$tpl, $tpl_prefix );
  }

// EOC { FieldStringIframe }
}


?>