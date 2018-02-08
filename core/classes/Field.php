<?php
/*
    Field( &$rh, $config ) -- ����������� ����� ��� ���� �����
      - &$rh -- ������ �� RequestHandler, � ������� ���������� ������������ �������
      - $config -- ���-������ � ����������� ���� -- ((FieldConfig))
      - ������ ����� ��� ������ ����� �����

  ---------

  * StoreToSession( $session_key )     -- ��������� � ������
      - $session_key -- ����, �� �������� ���� ������ � ����
  * RestoreFromSession( $session_key ) -- ������������ �� ������
      - $session_key -- ����, �� �������� ���� ������ � ����

  * SetDefault()              -- ���������� "�������� �� ���������"
  * Load( &$data )            -- ��������� �� �������, ����������� �� $_POST
      - $data -- ���-������
  * RestoreFromDb( &$data )   -- ��������� �� �������, ����������� �� �� 
      - $data -- ���-������
      - ��� ������ ����� ���������, �� ����� � ����������, ��������, ��� �������� ��������

  * ParseTo( $tpl_prefix, $target_var ) -- ��������� ������ ��� ������� ����
      - $tpl_prefix -- � ���� ���������� ������� ��� ����� ����
      - $target_var -- ���� �������� ��������� (� ������)

  * AfterHandler() -- ���������� �� �������������� ����� �������� ��������� ��������

  // ��� ������ � ����� ������
  * CreateSELECT() -- ������������ ����� ������ ��� SELECT-�������
  * CreateUPDATE() -- ������������ ����� ������ ��� UPDATE-������� 

  // ��� �������, ���������� override ��������� �������
  * Validate()   -- �������� ������� ������ � ������� ������ �����
      - ��� ����� ������ ��������� ������ nessesary (���� ����������� ��� ����������)

  * _StoreToDb() -- ����������, ����� ���������� ���������� � ��
      - ����� �����������, ���� ��� ���������� � �� � ���� ����� ����� ��������� �����-�� ��������
      - ���������� � CreateUPDATE, ���� �� �������� ��� ����������

  * _RestoreFromDb( &$data, $skip_char="" )  -- ��������������� �������� "��� �� �� ���� ������"
      - $skip_char -- ���. ��������, ������� ����� ���������� _Load ��� ������
  * _Load( &$data )                          -- ��������������� �������� "��� �� �� �����"
  * _Preparse( &$tpl, $tpl_prefix )          -- ���������� ������ TemplateEngine ����� ��������� ������� ����
      - $tpl -- ��� TemplateEngine, ����� �������� �����������
      - $tpl_prefix -- ������� ��������
  * _Format( &$tpl, $tpl_prefix )            -- ���������� ������ ��� ������ �������� ����, � �� ����� �����
      - $tpl -- ��� TemplateEngine, ����� �������� �����������
      - $tpl_prefix -- ������� ��������

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
      $this->invalidReasons["FormError_Nessesary"] = "���� ������ ���� ����������� ���������!";
    if (isset($this->config["nessesary"]) && ($this->data === "") && (is_array($this->config["nessesary"])))
    {
      $f=0;
      foreach ($this->config["nessesary"] as $field)
       if ($this->form->hash[ $field ]->data != "") { $f=1; break; }
      if (!$f)
        $this->invalidReasons["FormError_NessesaryGroup"] = "���� �� ���� ����� ������ ���� ���������!";
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
