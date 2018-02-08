<?php
/*
    Debug( $halt_level=0, $to_file=NULL )  -- ���� ������� ����� ��� �������
      - $halt_level -- ������� severity, ��� ������������� ������ ������ ��� �������� ������ 
                       ��������� ���������� ��������
      - $to_file    -- ����� ������� ��� �����, ����� ����� ����� �� � ����� ������, � � ����

  ---------
  * Flush( $prefix="<div>Trace log:</div><ul><li>", $separator="</li><li>", $postfix="<b>done.</b></li></ul>" ) --
                             ����� ���� � ������� echo(), ��� ���� ��� ��������������� ������� �����������
      - $prefix / $postfix -- ����������� � ������/����� ����
      - $separator         -- ����������� ����� ������� ����� �������� ����

  * Milestone( $what="" ) -- �������� � ��� �����, ��������� � ������� ����������� ������� Milestone() � ������������
      - $what -- ������������ �����������

  * Trace( $what, $flush=0 ) -- ������� ������ � ���, ���� �����
      - $what  -- ����� ������
      - $flush -- ���� �����, �� ����� ������ ��� ��������� � echo()

  * Trace_R( $what, $flush=0 ) -- ������� ������ � ��� � ���� ����������� �������
      - $what  -- ������, ������� ����� ��������
      - $flush -- ���� �����, �� ����� ������ ��� ��������� � echo()
      - �� �������� � ������� "&"
  * Error_R -- much like that

  * IsError( $error_level = 1 ) -- �� ��������� �� �� ����� ������ ������, true ���� ���������
      - $error_level -- ������� ����������������. ���� ���� ������ ������ �������� ������, ���������� ��

  * Error( $msg, $error_level=1 ) -- �������� � ��� ��������� �� ������
      - $msg         -- ���������������� ����� ������
      - $error_level -- ������� severity ������. ��� ������, ��� �������� ������. ������������� (0..5)

  * Halt( $flush = 1 ) -- ��������� ���������� �������
      - $flush -- ���� ���������� � �������, �� ��� � ������� ���

  // ���������� ������
  * _getmicrotime() -- ��� ����������� �������������, ����� �������

  // ����������
  * no_microtime -- �� ����������� ������� �������

=============================================================== v.6 (Kuso)
*/

class Debug
{
   var $halt_level;
   var $log;
   var $_milestone;
   var $milestone;
   var $is_error;
   var $to_file;
   var $to_file_level = 3; // ������ ����� ��������� ������ � ����
   var $no_microtime;
   var $kuso = false;

   function Debug( $halt_level=0, $to_file=NULL )
   {
     $this->halt_level = $halt_level;
     $this->log = array();
     $this->is_error = array();
     $this->milestone = $this->_getmicrotime();
     $this->_milestone = $this->milestone;
     $this->to_file = $to_file;
     $this->Trace("<b>log started.</b>",0);
   }

   // ����� ����
   function Flush( $prefix="<div>Trace log:</div><ul><li>", $separator="</li><li>", $postfix="</li></ul>" )
   {
     $this->Trace( "<b>log flushed.</b>",0);
     ob_start();
     echo $prefix;
     $f=0;
     foreach ($this->log as $item)
     {
      if (!$f) $f=1; else echo $separator;
      echo $item;
     }
     echo $postfix;
     if ($this->to_file && $this->is_error[$this->to_file_level]) 
     { 
        $data = ob_get_contents(); 
        //ob_end_clean(); 
        $fp = fopen( $this->to_file ,"w");
        fputs($fp,$data);
        fclose($fp);
        echo "<hr />LOG FLUSHED TO FILE: {".$this->to_file."}<hr />";
     }
     else ob_end_flush();
     $this->log = array();
   }

   // ������ � ���������� ���������
   function _getmicrotime() 
   { 
     if ($this->no_microtime) return 0;
     list($usec, $sec) = explode(" ",microtime());  return ((float)$usec + (float)$sec); 
   }
   function Milestone( $what="" )
   {
     if ($this->no_microtime) return 0;

     $m = $this->_getmicrotime();
     $diff = $m-$this->milestone;
     $this->Trace( "milestone (".sprintf("%0.4f",$diff)." sec): ".$what, 0 );
     $this->milestone = $m;
     return $diff;
   }

   // ����� � ���
   function Trace( $what, $flush=0 )
   {
     if ($this->no_microtime)
     {
       $this->log[] = "[tick] ".$what;
     }
     else
     {
       $m = $this->_getmicrotime();
       $diff = $m-$this->_milestone;
       $this->log[] = sprintf("[%0.4f] ",$diff).$what;
     }
     if ($flush) $this->Flush();
   }

   // ����� � ��� �������
   function Trace_R( $what, $flush=0 )
   {
     ob_start();
     if (is_array($what))
       foreach($what as $k=>$v) if ($k{0}!="&") $_what[$k]=$v;
     else $_what = $what;
     print_r($_what);
     $result = ob_get_contents();
     ob_end_clean();
     $this->Trace("<b><a href=# onclick='var a=document.getElementById(\"__tracediv".substr(md5($result),0,6)."\");a.style.display=(a.style.display==\"none\"?\"block\":\"none\"); return false;'>Trace recursive</a></b><div style='padding-left:60px; display:none' id='__tracediv".substr(md5($result),0,6)."'><pre style='color:#444444; font:11px Tahoma;margin:0'>".$result."</pre><b><a href=# onclick='var a=document.getElementById(\"__tracediv".substr(md5($result),0,6)."\");a.style.display=(a.style.display==\"none\"?\"block\":\"none\"); return false;'>Hide trace recursive</a></b></div>", $flush);
     return "<pre>".$result."</pre>";
   }

   // �� ���� �� ��� ������
   function IsError( $error_level = 1 )
   {
     if (isset($this->is_error[ $error_level ]))  return true;
     else return false;
   }

   // �������� � ��� ������ �� ������ � �������� �������
   function Error( $msg, $error_level=1 )
   {
     $this->Trace( "<span style='font-weight:bold; color:#ff4000;'>ERROR [".str_pad(str_repeat("!", $error_level ),5,".")."]: ".$msg."</span>", 0 );
     for ($e=$error_level; $e>=0; $e--)
       $this->is_error[ $e ]=1;
     if ($this->IsError($this->halt_level)) $this->Halt();
   }

   function Error_R( $what, $error_level=1 )
   {
     ob_start();
     if (is_array($what))
       foreach($what as $k=>$v) if ($k{0}!="&") $_what[$k]=$v;
     else $_what = $what;
     print_r($_what);
     $result = ob_get_contents();
     ob_end_clean();
     return $this->Error( "<pre>".htmlspecialchars($result)."</pre>", $error_level );
   }

   // �������, ���� ��� ������
   function Halt( $flush = 1 )
   {
     header("Content-Type: text/html; charset=windows-1251");
     if ($flush) $this->Flush();
     die("prematurely dying.");
   } 


// EOC{ Debug } 
}



?>
