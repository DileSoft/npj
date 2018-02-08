<?php
/*
    Field( &$rh, $config ) -- абстрактный класс для поля формы
      - &$rh -- ссылка на RequestHandler, в котором содержится конфигурация проекта
      - $config -- хэш-массив с параметрами поля -- ((FieldConfig))
      - служит базой для многих типов полей

  ---------

  * StoreToSession( $session_key )     -- сохранить в сессию
      - $session_key -- ключ, по которому ищем данные о поле
  * RestoreFromSession( $session_key ) -- восстановить из сессии
      - $session_key -- ключ, по которому ищем данные о поле

  * SetDefault()              -- установить "значение по умолчанию"
  * Load( &$data )            -- загрузить из массива, полученного из $_POST
      - $data -- хэш-массив
  * RestoreFromDb( &$data )   -- загрузить из массива, полученного из БД 
      - $data -- хэш-массив
      - два метода часто совпадают, но могут и отличаться, например, при загрузке картинок

  * ParseTo( $tpl_prefix, $target_var ) -- отпарсить шаблон для данного поля
      - $tpl_prefix -- с чего начинаются шаблоны для этого поля
      - $target_var -- куда помещать результат (в домене)

  * AfterHandler() -- вызывается из формпроцессора после успешной отработки хандлера

  // Для работы с базой данных
  * CreateSELECT() -- сформировать часть строки для SELECT-запроса
  * CreateUPDATE() -- сформировать часть строки для UPDATE-запроса 

  // Как правило, происходит override следующих методов
  * Validate()   -- проверка наличия ошибок в текущих данных формы
      - для этого класса проверяет только nessesary (поле обязательно для заполнения)

  * _StoreToDb() -- вызывается, когда происходит сохранение в БД
      - можно перегружать, если при сохранении в БД с этим полем нужно выполнить какие-то действия
      - вызывается в CreateUPDATE, если не запрещён при перегрузке

  * _RestoreFromDb( &$data, $skip_char="" )  -- непосредственно загрузка "как бы из базы данных"
      - $skip_char -- доп. параметр, который часто использует _Load для вызова
  * _Load( &$data )                          -- непосредственно загрузка "как бы из формы"
  * _Preparse( &$tpl, $tpl_prefix )          -- заполнение домена TemplateEngine перед парсингом шаблона поля
      - $tpl -- сам TemplateEngine, домен которого заполняется
      - $tpl_prefix -- префикс шаблонов
  * _Format( &$tpl, $tpl_prefix )            -- заполнение домена для вывода значения поля, а не полей ввода
      - $tpl -- сам TemplateEngine, домен которого заполняется
      - $tpl_prefix -- префикс шаблонов

=============================================================== v.4cms/vl (Kuso)
*/

class Field
{
  var $config;
  var $rh;
  var $data;
  var $invalidReasons;
  var $invalid;
  var $form;

  function Field( &$rh, $config )
  {
    $this->config = &$config;
    $this->rh     = &$rh;

    // assigning defaults
    if (!isset($this->config["tpl_row"]))  $this->config["tpl_row"] = "form.html:Row";
    if (!isset($this->config["name"])) $this->config["name"] = "Form.".$this->config["field"];
    if (!isset($this->config["desc"])) $this->config["desc"] = "Form.".$this->config["field"].".Desc";

    if ($this->config["db_ignore"]) $this->SetDefault();
  }
  function StoreToSession( $session_key )
  { 
    $_SESSION[ $session_key ][ $this->config["field"] ] = &$this->data;
  }
  function RestoreFromSession( $session_key )
  { 
    if (isset($_SESSION[ $session_key ][ $this->config["field"] ]))
    {
      $this->data = &$_SESSION[ $session_key ][ $this->config["field"] ];
      return $this->Validate();
    }
    else
     return $this->SetDefault();
  }

  function AfterHandler()
  { }

  function Load( &$data ) { $this->data_before_load = $this->data;
                            $this->_Load(&$data); return $this->Validate(); }
  function RestoreFromDb( &$data ) { $this->previous_data = $this->data;
                                     $this->db_data = &$data[ $this->config["field"] ]; 
                                     $this->_RestoreFromDb( &$data ); return $this->Validate(); }
  function ParseTo( $tpl_prefix, $target_var ) 
  { 
    $this->rh->tpl->Assign( "_Name", $this->config["name"] );
    $this->rh->tpl->Assign( "_Desc", $this->config["desc"] );
    if ($this->config["nessesary"]) $this->rh->tpl->Assign( "_Nessesary", $this->rh->tpl->message_set["form_nessesary"] );
    else $this->rh->tpl->Assign( "_Nessesary", "" );

    if ($this->invalid)
    {
      foreach ($this->invalidReasons as $k=>$v)
       if (isset( $this->rh->tpl->message_set[$k] ))
        $this->invalidReasons[$k] = $this->rh->tpl->message_set[$k];

      $errors = &new ListSimple( $this->rh, $this->invalidReasons );
      $errors->Parse( $tpl_prefix."errors.html:List", "_Error", 0 );
    } else $this->rh->tpl->Assign( "_Error", "" );

    if (isset($this->config["onchange"]))
     $this->rh->tpl->Assign("_OnChange", " onclick=\"return ".$this->config["onchange"]."('".
              $this->rh->tpl->GetValue("FormName").
              "', '_".$this->config["field"]."', this);\" ");
    else
     $this->rh->tpl->Assign("_OnChange", "");
    
    if (isset($this->config["css"]))
     $this->rh->tpl->Append("_OnChange", " class=\"".$this->config["css"]."\"");

    if (is_array($this->config["interface_params"]))
    {
      foreach($this->config["interface_params"] as $k=>$v)
        $this->rh->tpl->Assign( "params_".$k, $v );
    }

    $this->_Preparse( &$this->rh->tpl, $tpl_prefix );

    if ($this->config["readonly"])
    {
      $this->rh->tpl->Assign("_Data", $this->_Format( &$this->rh->tpl, $tpl_prefix ));
    }
    else
      $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_data"], "_Data"     );
    $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_row" ], $target_var, 1 );
  }

  // partially abstract
  function CreateSELECT() { return $this->config["field"]; }
  function CreateUPDATE() { $this->_StoreToDb(); 
                            return $this->config["field"]."=". $this->rh->db->Quote($this->db_data); }
  function SetDefault() { $this->rh->debug->Trace( "set default[ ". $this->config["field"] ." ]= ".$this->config["default"]);
                          $this->data = $this->config["default"]; return true; }


  // fully abstract
  function Validate()   
  { 
    $this->invalidReasons = array(); 
    if (isset($this->config["nessesary"]) && ($this->data === "") && ($this->config["nessesary"]==1))
      $this->invalidReasons["FormError_Nessesary"] = "Поле должно быть обязательно заполнено!";
    if (isset($this->config["nessesary"]) && ($this->data === "") && (is_array($this->config["nessesary"])))
    {
      $f=0;
      foreach ($this->config["nessesary"] as $field)
       if ($this->form->hash[ $field ]->data != "") { $f=1; break; }
      if (!$f)
        $this->invalidReasons["FormError_NessesaryGroup"] = "Одно из этих полей должно быть заполнено!";
    }
    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }
  function _StoreToDb() { $this->db_data = $this->data; }
  function _RestoreFromDb( &$data, $skip_char="" ) { $this->data = &$data[ $this->config["field"]];     }
  function _Load( &$data )                         { $this->data = &$data[ "_".$this->config["field"]]; }
  function _Preparse( &$tpl, $tpl_prefix )
  {
    $tpl->Assign("_Field", "_".$this->config["field"] );
    if (!is_array($this->data))
     $tpl->Assign("_Value", htmlspecialchars($this->data) );
  }
  function _Format( &$tpl, $tpl_prefix ) { return $this->data; }

// EOC { Field }
}


?>
