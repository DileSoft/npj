<?php
/*
    FieldPassword( &$rh, $config ) -- �������� �� ��������-��������. ���������� ������� ������, ���������� ������
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()
  * CreateUPDATE

  // options
  * maxlen
  * min
  * regexp, regexp_help
  * plain

=============================================================== v.1 (Kuso)
*/

class FieldPassword extends Field
{

  function FieldPassword( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_string.html:Password";
    if (!isset($this->config["min"]))    $this->config["min"] = 5; 
    if (!isset($this->config["maxlen"])) $this->config["maxlen"] = 250; 
  }

  // �������� �� ��������� ������
  function Validate()   
  { 
    $config = &$this->config;
    if (!isset($this->data)) $this->data = "";
    $data = &$this->data;
    Field::Validate();
    // 1. min/max
    if (isset($config["maxlen"]) && ($config["maxlen"] < strlen($data)))
     $this->invalidReasons["FormError_TooLong"] = "������� ������� ��������, ����� �� ����� ".$config["maxlen"]." ��������";
    if ($data && isset($config["min"]) && ($config["min"] > strlen($data)))
     if (!isset($this->invalidReasons["FormError_Nessesary"]))
      $this->invalidReasons["FormError_TooShort"] = "������� �������� ��������, ����� �� ����� ".$config["min"]." ��������";
    // 2. regexp
    if (isset($config["regexp"]) && !preg_match($config["regexp"], $data))
     $this->invalidReasons["FormError_Regexp"] = "�������� ������ �������� (������: ".$config["regexp_help"].")";

    // 3. not equal
    if ($this->data != $this->data2)
     $this->invalidReasons["FormError_PasswordsNotMatch"] = "�������� �������� �� ���������. � ������ ��.";
  
    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // ��������� �� �����/��
  function _Load( &$data ) 
  { if ($data[ "_".$this->config["field"]])
    {
     $this->data = &$data[ "_".$this->config["field"]];
     $this->data2 = &$data[ "_".$this->config["field"]."_Dupe"];
    }
  }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { $this->data = ""; }

  function CreateUPDATE() { $this->_StoreToDb(); 
                            if ($this->data == "") return "";
                            return $this->config["field"]."=". $this->rh->db->Quote(
                                $this->config["plain"]?$this->db_data:md5($this->db_data)); }

  function _Preparse( &$tpl, $tpl_prefix )
  {
    $tpl->Assign("_MaxLen", $this->config["maxlen"] );
    $tpl->Assign("_Field", "_".$this->config["field"] );
  }
  function _Format() { return "***********"; }

// EOC { FieldPassword }
}


?>