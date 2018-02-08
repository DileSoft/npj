<?php
/*
    FieldString( &$rh, $config ) -- работаем со строками
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * maxlen
  * min
  * regexp, regexp_help
  * unique_sql
  * email
  * http
  * is_numeric
  * check, check_md5
  * lowercase
  * omit
  * replaceEmpty
  * notrim
  * regexp_help_clean
  * validador, validator_params -- внешний валидатор, возвращает ===0 или сообщение об ошибке. Может быть использован как преобразователь
  * tpl_readonly
  * postfix
  * after_formatter -- преформат "после контрола" до записи в БД

  * onchange  -- для всех типов полей
  * css       -- для всех типов полей

  - quick* (login, name, email)
  - formatter, formatto (for R3)

=============================================================== v.4 (Kuso)
*/

class FieldString extends Field
{

  function FieldString( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if ($this->config["check_md5"] || $this->config["check"]) 
      $this->config["db_ignore"] = 1;
    if (!isset($this->config["tpl_data"])) 
      if ($this->config["is_numeric"]) 
        $this->config["tpl_data"] = "field_string.html:Plain";
      else
        $this->config["tpl_data"] = "field_string.html:Wide";
    if (!isset($this->config["maxlen"]))   $this->config["maxlen"] = 250;
    if (!isset($this->config["notrim"])) 
     if (!(($this->config["tpl_data"] == "field_string.html:Plain") ||
          ($this->config["tpl_data"] == "field_string.html:Wide")) )
        $this->config["notrim"] = 1;
  }

  // проверка на различные ошибки
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;
    Field::Validate();
    // 1. min/max
    if (isset($config["maxlen"]) && ($config["maxlen"] < strlen($data)))
     $this->invalidReasons["FormError_TooLong"] = "Слишком длинное значение, нужно не более ".$config["maxlen"]." символов";
    if (isset($config["min"]) && ($config["min"] > strlen($data)))
     if (!isset($this->invalidReasons["FormError_Nessesary"]))
      $this->invalidReasons["FormError_TooShort"] = "Слишком короткое значение, нужно не менее ".$config["min"]." символов";
    // 2. regexp
    if (isset($config["regexp"]) && !preg_match($config["regexp"], $data))
    {
     if (isset($this->form->form_config["message_set"]) && 
         isset($this->rh->tpl->message_set[$config["regexp_help"]])) 
       $config["regexp_help"] = $this->rh->tpl->message_set[$config["regexp_help"]];

     if ($config["regexp_help_clean"]) 
       $this->invalidReasons["FormError_Regexp"] = $config["regexp_help"];
     else
       $this->invalidReasons["FormError_Regexp"] = "Неверный формат значения (нужный: ".$config["regexp_help"].")";
    }
    // 2a. is_numeric
    if (isset($config["is_numeric"]) && !preg_match("/^[0-9]*$/", $data))
     $this->invalidReasons["FormError_IsNumeric"] = "Значение должно быть числом!";
    // 3. email, http
    if( $data !== "")
    {
      $email = false; $http = false;
      if (isset($config["email"]) && preg_match("/^(([a-z\.\-\_0-9]+)@([a-z\.\-\_0-9]+\.[a-z]+))$/i", $data)) $email=true;
      if (isset($config["http"]) && preg_match("/^((ht|f)tp(s?):\/\/)?(([!a-z\-_0-9]+)\.)+([a-z0-9]+)(:[0-9]+)?(\/[=!~a-z\.\-_0-9\/?&%#]*)?$/i", $data)) $http=true;
  
      if (isset($config["email"]) && isset($config["http"]) && !$email && !$http)
       $this->invalidReasons["FormError_EmailHttp"] = "Значение должно быть email- или интернет-адресом";
      else
      if (isset($config["email"]) && !$email)
       $this->invalidReasons["FormError_Email"] = "Значение должно быть адресом электронной почты";
      else
      if (isset($config["http"]) && !$http)
       $this->invalidReasons["FormError_Http"] = "Значение должно быть интернет-адресом";

    }
    // 4. unique_sql
    if (isset($config["unique_sql"]))
    {
      $query = str_replace("[name]", $config["field"], $config["unique_sql"]);
      $query = str_replace("[value]", $this->rh->db->Quote($this->data), $query);
      $rs = $this->rh->db->SelectLimit( $query, 1 );

      if ($rs->RecordCount() && $rs->fields["id"] != $this->form->data_id)
       $this->invalidReasons["FormError_NotUnique"] = "К сожалению, это имя неуникально, пожалуйста, придумайте другое";
    }
    // 5. check for value
    if ($this->config["check"])
      if ($this->data != $this->config["check"])
       $this->invalidReasons["FormError_Check"] = "Значение введено неверно!";
    if ($this->config["check_md5"])
      if (md5($this->data) != $this->config["check_md5"])
       $this->invalidReasons["FormError_Check"] = "Значение введено неверно!";

    if ($this->config["validator"])
    { $result = call_user_func( $this->config["validator"], &$this->data, &$this->config["validator_param"] );
      if ($result !== 0)
      {
       $this->invalidReasons["FormError_Foreign"] = $result;
      }
    }
  
    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // получение из формы/бд
  function _Load( &$data ) { $this->_RestoreFromDb( &$data, "_" ); 
                             if ($this->config["after_formatter"])
                              $this->data = $this->rh->tpl->Format( $this->data, $this->config["after_formatter"] );
                           }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    $this->data = &$data[ $skip_char.$this->config["field"]]; 
    if (isset($this->config["lowercase"])) $this->data = strtolower($this->data);
    if (isset($this->config["omit"])) $this->data = preg_replace($this->config["omit"], "", $this->data);
    if (!isset($this->config["notrim"])) $this->data = trim($this->data);
    if (isset($this->config["replaceEmpty"]) && ($this->data=="")) $this->data = $this->config["replaceEmpty"];
  }


  function _Preparse( &$tpl, $tpl_prefix )
  {
    $tpl->Assign("_MaxLen", $this->config["maxlen"] );
    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Value", htmlspecialchars($this->data) );
    $tpl->Assign("_Postfix", $this->config["postfix"] );
  }
  function _Format() 
  { 
    $result = $this->data;
    if ($this->data == "")
     if (isset($this->rh->tpl->message_set["Form._StringEmpty"])) return $this->rh->tpl->message_set["Form._StringEmpty"];
     else $result = "<small>(не указано)</small>";
    else 
      if ($this->config["is_http"]) $result = $this->rh->Link( $result, $result, "", " target='_blank' " );

    if (isset($this->config["tpl_readonly"]))
    {
      $this->rh->tpl->Assign("_Value", $result );
      $result = $this->rh->tpl->Parse( $this->form->form_config["tpl_prefix"].$this->config["tpl_readonly"] );
    }

    return $result; 
  }

// EOC { FieldString }
}


?>