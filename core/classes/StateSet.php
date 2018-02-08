<?php
/*

    StateSet( &$config, $q="?", $s="&", $copy_from=NULL, $skip_char="_" )  -- persistent querystring & form generation 
      - $config -- ������ RequestHandler � ������������� �������
      - $q [quotation_mark] -- ������, ���������� ������ querystring
      - $s [separator_mark] -- ������, ����������� ���� key=value � querystring
      - $copy_from, $skip_char -- ���� ������ ������ ������ StateSet, �� ����������� ������ ��� �����. 
                                  ���� ������, �� ��������� ���������� ����������, ����� $key, ������������ �� $skip_char 

  ---------
  * Set( $key, $value, $weak=0 ) -- ���������� ���� � ��������
      - $key   -- ��� ���� (case-sensitive)
      - $value -- ��������������� ��������
      - $weak  -- ���� ���������� � �������, �� �� ����� �������������� ������������ ����

  * Get( $key ) -- �������� �������� ����
      - $key   -- ��� ���� (case-sensitive)

  * _Set( $key, &$value, $weak=0 ) -- ���������� ���� �������
  * &_Get( $key )                  -- �������� ������ �� ����

  * Free( $key=NULL ) -- �������� ����/�����
      - $key -- ���� ���������, �� ������� ���� �����, ����� ������ ��������������� ����

  * &Copy() -- ������� StateSet ����� �������

  * Pack( $method=MSS_GET, $bonus="", $only="" ) -- ��������� � ������ ��� GET/POST �������
      - $method -- ��� ������ "?key=value&key=value" ��� "<input type=hidden..."
      - $bonus  -- ���������� � ����� ������. ����� ��� MSS_GET, ������ ��� ����� ���� "?key=value&bonus", � ����� "?bonus"
      - $only   -- ������������ �������. ���� ������, �� �������� ������ �� ���� ������, ������� ���������� � only

  * Unpack( $from, $skip_char="_", $weak=0, $method=MSS_GET ) -- ����������� �� ������
      - $from      -- �� ����� ������
      - $skip_char -- ���������� ����, ������������ � ����� �������
      - $weak      -- ���� ���������� � �������, �� �� ����� �������������� ������������ ����
      - $method    -- ���� ����������� ������ ��� GET (ForR3)

  * Plus( $key, $value, $method=MSS_GET ) -- ��������� � ������, ������� ���� ���� (�� ��������� ���� � ������)
      - $key    -- ��� ������������ ���� (case-sensitive)
      - $value  -- ��������������� ��������
      - $method -- ��� ������ "?key=value&key=value" ��� "<input type=hidden..."

  * Minus( $key, $method=MSS_GET, $bonus="" ) -- ��������� � ������, ��������� ���� ����
      - $key    -- ��� ������������� ���� (case-sensitive)
      - $method -- ��� ������ "?key=value&key=value" ��� "<input type=hidden..."
      - $bonus  -- ���������� � ����� ������, ������������ $this->Plus. ����� �����������
      
  * Load( $keyset, $skip_char="_", $weak=0 ) -- ��������� ���� �� ������� ������ ���� �������
      - $keyset    -- ���-������ ��� StateSet
      - $skip_char -- ���������� ����, ������������ � ����� �������
      - $weak      -- ���� ���������� � �������, �� �� ����� �������������� ������������ ����

  * FormStart( $method=MSS_POST, $action="?", $form_bonus="", $only="" ) -- ��������� ��������� �����, ����� ���� ����
      - $method     -- ����� ������� ������������ �����/�������� �����
      - $action     -- �������� �������� <FORM ACTION=, ������ ���������� config->Href
      - $form_bonus -- ���������� ������ <FORM ....>
      - $only       -- ������������ �������. ���� ������, �� �������� ������ �� ���� ������, ������� ���������� � only

  * FormEnd() -- ��������� ����������� ��� "</FORM>"

  * SetWeak( $key, $value="" )          -- alias ���  Set(...$weak=1), ������������� ��� ������ �������������
  * LoadWeak( $keyset, $skip_char="_" ) -- alias ��� Load(...$weak=1), ������������� ��� ������ �������������

=============================================================== v.4 (Kuso)
*/
define ("MSS_GET",  0);
define ("MSS_POST", 1);

class StateSet
{
   var $q;
   var $s;
   var $_compiled;
   var $_ready;
   var $values;
   var $config, $rh;

   function StateSet( &$config, $q="?", $s="&", $copy_from=NULL, $skip_char="_" )
   {
     $this->config = &$config;
     $this->rh     = &$config;
     $this->q = $q; $this->s = $s;
     $this->_compiled = array("", "");
     $this->_ready = 1;
     
     $this->values = array();
     if ($copy_from) $this->Load($copy_from, $skip_char);
   }

   // ���������� ���� � ��������
   function Set( $key, $value, $weak=0 )
   {
     if ($weak) if (isset($this->values[$key])) return false;
     $this->_ready = 0;
     $this->values[$key] = $value;
     return true;
   }
   function _Set( $key, &$value, $weak=0 )
   {
     if ($weak) if (isset($this->values[$key])) return false;
     $this->_ready = 0;
     $this->values[$key] = &$value;
     return true;
   }

   // �������� �������� ����
   function Get( $key )
   { return $this->values[$key]; }
   function &_Get( $key )
   { return $this->values[$key]; }

   // �������� ���� ����� ��� ����
   function Free( $key=NULL )
   {
     if ($key) 
      if(is_array($key))
      {
        $kc = count($key);
        for($i=0; $i<kc; $i++) unset($this->values[$key[$i]]);     
      }
      else unset($this->values[$key]);
     else $this->values = array();
     $this->_ready = 0;
   }

   // �����������
   function &Copy()
   { $s = &new StateSet( &$this->config, $this->q, $this->s, $this, "" ); return $s; }

   // ��������� � ������
   function Pack( $method=MSS_GET, $bonus="", $only="" ) 
   {
     if (!$this->_ready) 
     {
      $this->compiled[MSS_GET ] = "";
      $this->compiled[MSS_POST] = "";

      $f=0;
      foreach($this->values as $k=>$v)
       if (($only == "") || (strpos($k, $only) === 0))
       {
          $v0 = htmlspecialchars($v); // !!! ���������, ���� �� ���, ������, ������ �� ����.
          $v1 = urlencode($v);        // !!! ���������, ���� �� ���, ������, ������ �� ����.
          if ($f) $this->compiled[MSS_GET ].=$this->s; else $f=1;
          $this->compiled[MSS_GET ] .= $k."=".$v1;
          $this->compiled[MSS_POST] .= "<input type='hidden' name='".$k."' value='".$v0."' />\n";
       }
      $this->_ready = 1;
     }
     $data = $this->compiled[$method];
     if ($method == MSS_POST) return $data.$bonus;

     if ($bonus != "") 
      if ($data != "") $data=$this->q.$data.$this->s.$bonus;
      else $data.=$this->q.$bonus;
     else if ($data != "") $data = $this->q.$data;
     
     return $data;
   }

   // ����������� �� GET-������
   function Unpack( $from, $skip_char="_", $weak=0, $method=MSS_GET ) // ForR2-3 ������� ��� � ����
   {
     if (strpos($from, $this->q) === 0) $from = substr( $from, strlen($this->q) );
     $data = explode( $this->s, $from );
     $to = array();
     foreach ($data as $v)
     {
       $a = explode("=", $v);
       $to[ $a[0] ] = $a[1];
     }
     $this->Load( $to, $skip_char, $weak );
   }

   // ����-�����
   function Plus( $key, $value, $method=MSS_GET )
   {
     $v0 = htmlspecialchars($value); // !!! ���������, ���� �� ���, ������, ������ �� ����.
     $v1 = urlencode($value);        // !!! ���������, ���� �� ���, ������, ������ �� ����.
     if ($method == MSS_GET) $bonus = $key."=".$v1; 
     else $bonus ="<input type='hidden' name='".$key."' value='".$v0."'>\n";      
     return $this->Minus( $key, $method, $bonus );
   }

   function Minus( $key, $method=MSS_GET, $bonus="" )
   {
     $data = "";
     $f=0;
     foreach($this->values as $k=>$v)
      if ($k != $key)
      {
         if ($method == MSS_GET) { if ($f) $data.=$this->s; else $f=1;
                                   $data .= $k."=".urlencode($v);  // !!! ���������, ���� �� ���, ������, ������ �� ����.
                                 } else
                                   $data .= "<input type='hidden' name='".$k."' value='".htmlspecialchars($v)."'>\n"; // !!! ���������, ���� �� ���, ������, ������ �� ����.     
      }
     if ($method == MSS_POST) return $data.$bonus;

     if ($bonus != "") 
      if ($data != "") $data=$this->q . $data . $this->s . $bonus;
      else $data = $this->q. $bonus;
     else  $data = $this->q. $data;

     return $data;
   }

   // load from stateset / array
   function Load( &$keyset, $skip_char="_", $weak=0 )
   {
     //if (is_a( $keyset, "StateSet" )) $data = &$keyset->values; 
     if (is_object( $keyset )) $data = &$keyset->values; 
     else $data = &$keyset;
     foreach ($data as $k=>$v)
      if ( (($skip_char == "") || ($k[0] != $skip_char)) && (($weak==0) || (!isset($this->values[$k]))) )
      { $this->values[$k] = $v; }
     $this->_ready = 0;
   }

   // form start/end
   function FormStart( $method=MSS_POST, $action="?", $form_bonus="", $only="" ) 
   {
     $m = array( "get", "post" );
     if ($this->config->rewrite_mode != 1) 
      { $bonus = $this->Plus( "page", $action, MSS_POST ); $action="index.php"; 
        // NB: [!!!!] �� ����������� $only. ���� ��� ����� �� ������������!
      }
    else
      { $bonus = $this->Pack( MSS_POST, "", $only ); }

     return "<form method='".$m[$method]."' action='".$this->config->Href($action,1)."' ".$form_bonus.">".
            $bonus; 
   }
   function FormEnd()
   { return "</form>"; }


   // aliases: ���������� ���� � ��������, ���� ��� ��� �� ���� �����������
   function SetWeak( $key, $value="" )
   { return $this->Set( $key, $value, 1 ); }

   function LoadWeak( &$keyset, $skip_char="_" )
   { $this->Load( &$keyset, $skip_char, 1 ); }



// EOC{ StateSet } 
}



?>