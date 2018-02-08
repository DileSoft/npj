<?php
/*
    FieldWrapper( &$rh, $config ) -- обЄртка дл€ нескольких вложенных полей
      - &$rh -- ссылка на RequestHandler, в котором содержитс€ конфигураци€ проекта
      - $config -- хэш-массив с параметрами пол€ -- ((FieldConfig))

  ---------

  // пропускаютс€ вложенным пол€м просто
  * StoreToSession( $session_key )     -- сохранить в сессию
  * RestoreFromSession( $session_key ) -- восстановить из сессии
  * SetDefault()              -- установить "значение по умолчанию"
  * Load( &$data )            -- загрузить из массива, полученного из $_POST
  * RestoreFromDb( &$data )   -- загрузить из массива, полученного из Ѕƒ 
  * AfterHandler() -- вызываетс€ из формпроцессора после успешной отработки хандлера
  * CreateSELECT() -- сформировать часть строки дл€ SELECT-запроса
  * CreateUPDATE() -- сформировать часть строки дл€ UPDATE-запроса 
  * Validate()   -- проверка наличи€ ошибок в текущих данных формы
  * _StoreToDb() -- вызываетс€, когда происходит сохранение в Ѕƒ
  * _RestoreFromDb( &$data, $skip_char="" )  -- непосредственно загрузка "как бы из базы данных"
  * _Load( &$data )                          -- непосредственно загрузка "как бы из формы"

  // делаем ещЄ что-то своЄ (собираем их в массив, да)
  * _Preparse( &$tpl, $tpl_prefix )          -- заполнение домена TemplateEngine перед парсингом шаблона пол€
  * _Format( &$tpl, $tpl_prefix )            -- заполнение домена дл€ вывода значени€ пол€, а не полей ввода

  // options
  * tpl_row
  * tpl_data -- как оформл€ть список вложенных полей
  * tpl_fields_row -- если указано, то переписывает нахрен все tpl_row у вложенных
  * fields = array( Field ) -- собственно массив вложенных полей

  // ForR2
  * добавить ещЄ поле (типа селектор, показывать/не показывать попции


=============================================================== v.2 (Kuso)
*/

class FieldWrapper extends Field
{

  function FieldWrapper( &$rh, $config )
  {
    Field::Field( &$rh, $config );

    // assigning defaults
    if (!isset($this->config["tpl_data"]))  $this->config["tpl_data"] = "field_wrapper.html:Row";
    if (isset($this->config["tpl_fields_row"]))  
     foreach( $this->config["fields"] as $k=>$field) 
      $this->config["fields"][$k]->config["tpl_row"] = $this->config["tpl_fields_row"];
  }                              
  function StoreToSession( $session_key )
  { 
    foreach( $this->config["fields"] as $k=>$field) 
     $this->config["fields"][$k]->StoreToSession( $session_key );
  }
  function RestoreFromSession( $session_key )
  { 
    foreach( $this->config["fields"] as $k=>$field) 
     $this->config["fields"][$k]->RestoreFromSession( $session_key );
  }

  function AfterHandler()
  { 
    Field::AfterHandler();
    foreach( $this->config["fields"] as $k=>$field) 
     $this->config["fields"][$k]->AfterHandler();
  }

  function RestoreFromDb( &$data ) 
  { 
    $result = true;
    foreach( $this->config["fields"] as $k=>$field) 
     $result &= $this->config["fields"][$k]->RestoreFromDb( &$data );
    return $result;
  }

  // partially abstract
  function CreateSELECT() 
  { $result = array();
    foreach( $this->config["fields"] as $k=>$field) 
     if (!isset($field->config["db_ignore"]))
     { $r = $this->config["fields"][$k]->CreateSELECT();
       if ($r != "") $result[] = $r;
     }
    return implode(", ", $result);
  }
  function CreateUPDATE() 
  { $result = array();
    foreach( $this->config["fields"] as $k=>$field) 
     if (!isset($field->config["db_ignore"]))
     { $r = $this->config["fields"][$k]->CreateUPDATE();
       if ($r != "") $result[] = $r;
     }
    return implode(", ", $result);
  }
  function SetDefault() 
  { 
    $result = true;
    foreach( $this->config["fields"] as $k=>$field) 
     $result = $result && $this->config["fields"][$k]->SetDefault();
    return $result;
  }

  function Validate()   
  { 
    $this->invalidReasons = array(); 
    $result = true;
    foreach( $this->config["fields"] as $k=>$field) 
    {
      $r = $this->config["fields"][$k]->Validate();
      if (!$r) $this->rh->debug->Trace("invalid");
      $result &= $r;
    }
    $this->invalid = !$result;
    if ($this->invalid)
    {
      foreach( $this->config["fields"] as $field) 
       $this->invalidReasons = array_merge( (array)$this->invalidReasons, (array)$field->invalidReasons );
    }
    return !$this->invalid; 
  }
  function _StoreToDb() 
  { 
    foreach( $this->config["fields"] as $k=>$field) 
     if (!isset($field->config["db_ignore"]))
      $this->config["fields"][$k]->_StoreToDb();
  }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    foreach( $this->config["fields"] as $k=>$field) 
     if (!isset($field->config["db_ignore"]))
     {
       $this->config["fields"][$k]->_RestoreFromDb( &$data, $skip_char );
       $this->rh->debug->Trace(" WRAPPER restore from db : ".$field->config["name"]." = ".$field->data);
     }
  }
  function _Load( &$data )                         
  { 
    foreach( $this->config["fields"] as $k=>$field) 
    {
     $this->config["fields"][$k]->_Load( &$data );
     $this->rh->debug->Trace(" WRAPPER load : ".$field->config["name"]." = ".$field->data);
    }
  }
  
 // SPECIFIC 
  function _Preparse( &$tpl, $tpl_prefix )
  {
    if ($this->config["readonly"])
    {
      $this->rh->tpl->Assign("_Value", $this->_Format( &$this->rh->tpl, $tpl_prefix ));
    }
    else
    {
    // [] здесь надо в цикле отпарсить все пол€, вложенные в обложку
      $tpl->Assign("_FIELDS","");
      foreach ($this->config["fields"] as $k=>$field)
      {
        $this->rh->debug->Trace(" WRAPPER preparse : ".$field->config["name"]." = ".$field->data);
       $_field= &$this->config["fields"][$k];
       if ($this->form->blocked) $_field->config["readonly"] = 1;
       if (isset($_field->config["tpl_row"]))
       {
        $_field->ParseTo( $this->form->form_config["tpl_prefix"], "_FIELDS" );
        $result.= $_field->config["name"]." | ";
       }
      }
      $result .= $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_data"] );
      $tpl->Assign("_Value", $result );
    }
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign( "_Name", $this->config["name"] );
    $tpl->Assign( "_Desc", $this->config["desc"] );
  }
  function _Format( &$tpl, $tpl_prefix ) 
  { $result = array();
    foreach( $this->config["fields"] as $k=>$field) 
     $result[] = $this->config["fields"][$k]->_Format( &$tpl, $tpl_prefix );
    return implode(", ", $result);
  }

// EOC { FieldWrapper }
}


?>
