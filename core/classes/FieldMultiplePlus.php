<?php
/*
    FieldMultiplePlus( &$rh, $config ) -- выбор до нескольких элементов, или ниодного или все.
                                          при этом есть пресет, когда собирается дополнительная радиоинформация
                                      
    наследует от FieldMultiple.
    разрабатывалась для НПЖ, поэтому немного кастрированная
  ---------

  // options
  * see <FieldMultiple>

  * radio_data ( id => name )
  * radio_default

  [to be supplied.]

=============================================================== v.0 (Kuso)
*/

class FieldMultiplePlus extends FieldMultiple
{

  function FieldMultiplePlus( &$rh, $config )
  {
    // preassigning defaults
    if (!isset($config["tpl_data"])) $config["tpl_data"] = "field_multiple_plus.html:Plain";

    FieldMultiple::FieldMultiple(&$rh, $config);
    // assigning defaults
    
  }

  // установка значения по-умолчанию
  function SetDefault() 
  { 
    $this->radio_data = $this->config["radio_default"]; 
    return FieldMultiple::SetDefault();
  }

  // запись/получение из формы/бд
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    FieldMultiple::_RestoreFromDb( &$data, $skip_char );
    
    if ($this->config["db_ignore"] && ($skip_char == "")) return;

    $radio_preset_field = $skip_char.$this->config["field"]."_radio";
    $this->radio_data = &$data[ $radio_preset_field ];
  }

  function _Preparse( &$tpl, $tpl_prefix )
  { $tpl = &$this->rh->tpl;
    if ($this->config["readonly"]) return;

    // -- precompiling groups
    if (sizeof( $this->config["radio_data"] ) == 0) return;

    $this->rh->UseClass("ListCurrent", $this->rh->core_dir );

    // -- copypasted from FM::_Preparse
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Size", $this->config["size"] );
    $tpl->Assign("_MaxSize", $this->config["maxsize"] );
    $tpl->Assign("_Type", "in");
    $tpl->Assign("_Type", "out");
    $tpl->Assign("_Size", $this->config["size_all"] );
    // -- end copypaste

    if (!isset($this->config["radio_preset"]))
     $radio_id = $this->rh->tpl->message_set[ "Form.".$this->config["field"].".RadioPreset" ];
    else
     $radio_id = $this->config["radio_preset"];
    if (is_array($this->data)) $_data2 = -10;
    else $_data2 = $this->data;
    $tpl->Assign("_IsCurrent", $radio_id == $_data2 );

    $list = &new ListCurrent( &$this->rh, $this->config["radio_data"], "", $this->radio_data );
    $list->Parse($tpl_prefix.$this->config["tpl_data"]."_RadioPreset", "PRESET_RADIO", 0);

    // -- patching input data
    FieldMultiple::_Preparse( &$tpl, $tpl_prefix );
  
  }

  // refactored part of FM.
  function __BuildPresetsArray()
  {
    if (is_array($this->data)) $_data2 = -10;
    else $_data2 = $this->data;
    if (!isset($this->config["presets"]))   
      $data2 = $this->rh->tpl->message_set[ "Form.".$this->config["field"].".Presets" ];
    else 
      $data2 = $this->config["presets"];
    $data21 = array();

    if (is_array($this->config["presets_block"]))
      foreach($this->config["presets_block"] as $v)
      {
        unset($data2[$v]);
      }

    if (!isset($this->config["radio_preset"]))
     $radio_id = $this->rh->tpl->message_set[ "Form.".$this->config["field"].".RadioPreset" ];
    else
     $radio_id = $this->config["radio_preset"];

    foreach( $data2 as $k=>$v)
    {
         $o = array(
                "href" => $k,
                "text" => $v,
                "title" => ($k==-10?"block":"none"),

                "_PresetRadio" => ($k==$radio_id?$this->rh->tpl->GetValue("PRESET_RADIO"):""),
                    );
         $data21[] = $o; 
    }

    $list2 = new ListCurrent( &$this->rh, $data21, "href", $_data2, NULL, array( "_PresetRadio" ) );

    return array( "list2"  => $list2,
                  "data21" => $data21, 
                  "_data2" => $_data2, 
                  "data2"  => $data2 );
  }


// EOC { FieldMultiplePlus }
}


?>