<?php
/*

    StateSet( &$config, $q="?", $s="&", $copy_from=NULL, $skip_char="_" )  -- persistent querystring & form generation 
      - $config -- объект RequestHandler с конфигурацией проекта
      - $q [quotation_mark] -- символ, означающий начало querystring
      - $s [separator_mark] -- символ, разделяющий пары key=value в querystring
      - $copy_from, $skip_char -- если указан объект класса StateSet, то конструктор создаёт его копию. 
                                  если массив, то заполняет начальными значениями, кроме $key, начинающихся со $skip_char 

  ---------
  * Set( $key, $value, $weak=0 ) -- установить поле в значение
      - $key   -- имя поля (case-sensitive)
      - $value -- устанавливаемое значение
      - $weak  -- если установить в единицу, то не будет перезаписывать существующие поля

  * Get( $key ) -- получить значение поля
      - $key   -- имя поля (case-sensitive)

  * _Set( $key, &$value, $weak=0 ) -- установить поле ссылкой
  * &_Get( $key )                  -- получить ссылку на поле

  * Free( $key=NULL ) -- очистить поле/набор
      - $key -- если пропущено, то очищает весь набор, иначе только соответствующее поле

  * &Copy() -- вернуть StateSet копию данного

  * Pack( $method=MSS_GET, $bonus="", $only="" ) -- упаковать в строку для GET/POST запроса
      - $method -- вид строки "?key=value&key=value" или "<input type=hidden..."
      - $bonus  -- дописывает в конце строки. важно для MSS_GET, потому что может быть "?key=value&bonus", а может "?bonus"
      - $only   -- опциональный префикс. если указан, то пакуются только те поля набора, которые начинаются с only

  * Unpack( $from, $skip_char="_", $weak=0, $method=MSS_GET ) -- разпаковать из строки
      - $from      -- из какой строки
      - $skip_char -- пропускать поля, начинающиеся с этого СИМВОЛА
      - $weak      -- если установить в единицу, то не будет перезаписывать существующие поля
      - $method    -- пока реализована только для GET (ForR3)

  * Plus( $key, $value, $method=MSS_GET ) -- упаковать в строку, добавив одно поле (не сохраняет поле в наборе)
      - $key    -- имя добавляемого поля (case-sensitive)
      - $value  -- устанавливаемое значение
      - $method -- вид строки "?key=value&key=value" или "<input type=hidden..."

  * Minus( $key, $method=MSS_GET, $bonus="" ) -- упаковать в строку, игнорируя одно поле
      - $key    -- имя игнорируемого поля (case-sensitive)
      - $method -- вид строки "?key=value&key=value" или "<input type=hidden..."
      - $bonus  -- дописывает в конце строки, используется $this->Plus. лучше пропускайте
      
  * Load( $keyset, $skip_char="_", $weak=0 ) -- загрузить поля из другого набора либо массива
      - $keyset    -- хэш-массив или StateSet
      - $skip_char -- пропускать поля, начинающиеся с этого СИМВОЛА
      - $weak      -- если установить в единицу, то не будет перезаписывать существующие поля

  * FormStart( $method=MSS_POST, $action="?", $form_bonus="", $only="" ) -- формирует заголовок формы, пакуя туда себя
      - $method     -- каким методом отправляется форма/пакуется набор
      - $action     -- значение атрибута <FORM ACTION=, внутри использует config->Href
      - $form_bonus -- дописывает внутрь <FORM ....>
      - $only       -- опциональный префикс. если указан, то пакуются только те поля набора, которые начинаются с only

  * FormEnd() -- формирует закрывающий тэг "</FORM>"

  * SetWeak( $key, $value="" )          -- alias для  Set(...$weak=1), рекомендуется для лучшей читабельности
  * LoadWeak( $keyset, $skip_char="_" ) -- alias для Load(...$weak=1), рекомендуется для лучшей читабельности

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

   // установить поле в значение
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

   // получить значение поля
   function Get( $key )
   { return $this->values[$key]; }
   function &_Get( $key )
   { return $this->values[$key]; }

   // очистить весь набор или поле
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

   // клонировать
   function &Copy()
   { $s = &new StateSet( &$this->config, $this->q, $this->s, $this, "" ); return $s; }

   // упаковать в строку
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
          $v0 = htmlspecialchars($v); // !!! проверить, надо ли это, узнать, почему не надо.
          $v1 = urlencode($v);        // !!! проверить, надо ли это, узнать, почему не надо.
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

   // распаковать из GET-строки
   function Unpack( $from, $skip_char="_", $weak=0, $method=MSS_GET ) // ForR2-3 сделать ещё и пост
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

   // плюс-минус
   function Plus( $key, $value, $method=MSS_GET )
   {
     $v0 = htmlspecialchars($value); // !!! проверить, надо ли это, узнать, почему не надо.
     $v1 = urlencode($value);        // !!! проверить, надо ли это, узнать, почему не надо.
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
                                   $data .= $k."=".urlencode($v);  // !!! проверить, надо ли это, узнать, почему не надо.
                                 } else
                                   $data .= "<input type='hidden' name='".$k."' value='".htmlspecialchars($v)."'>\n"; // !!! проверить, надо ли это, узнать, почему не надо.     
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
        // NB: [!!!!] не учитывается $only. Пока что нигде не используется!
      }
    else
      { $bonus = $this->Pack( MSS_POST, "", $only ); }

     return "<form method='".$m[$method]."' action='".$this->config->Href($action,1)."' ".$form_bonus.">".
            $bonus; 
   }
   function FormEnd()
   { return "</form>"; }


   // aliases: установить поле в значение, если оно ещё не было установлено
   function SetWeak( $key, $value="" )
   { return $this->Set( $key, $value, 1 ); }

   function LoadWeak( &$keyset, $skip_char="_" )
   { $this->Load( &$keyset, $skip_char, 1 ); }



// EOC{ StateSet } 
}



?>