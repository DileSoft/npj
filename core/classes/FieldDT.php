<?php
/*
    FieldDT( &$rh, $config ) -- ğàáîòàåì ñ äàòîé/âğåìåíåì. ÍÅ ÏÅĞÅÄÅËÛÂÀË ÅØ¨
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * date(Y/N) -- âîñïğèíèìàòü ëè áëîê ñ äàòîé     {{_ValueDate}}
  * time(Y/N) -- âîñïğèíèìàòü ëè áëîê ñî âğåìåíåì {{_ValueTime}}
  * format(d.m.Y) -- ôîğìàò, â êîòîğîì âûâîäèòü Readonly çíà÷åíèå
  * optional -- ïóñòîå çíà÷åíèå ñîõğàíÿåòñÿ êàê ïóñòîå çíà÷åíèå.

=============================================================== v.4 (Kuso)
*/

class FieldDT extends Field
{
  function FieldDT( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_dt.html:Date_Calendar";
    if (!isset($this->config["date"])) $this->config["date"] = 1; 
    if (!isset($this->config["default"])) 
      if ($this->config["optional"])
      {
        $this->config["default"] = "";
      }
      else
    {
      if ($this->config["date"]) $date = date("Y-m-d"); else $date="1900-00-00";
      if ($this->config["time"]) $time = date("H:i:s"); else $time="00:00";
      $this->config["default"] = $date." ".$time;
    }
  }

  // ïğîâåğêà íà ğàçëè÷íûå îøèáêè
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;
    Field::Validate();
  
    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // ïîëó÷åíèå èç ôîğìû/áä
  function _Load( &$data ) 
  { 
    if (isset($data[ "_".$this->config["field"]."_Date" ]) && $this->config["date"]) 
         $date = &$data[ "_".$this->config["field"]."_Date" ]; 
    else $date="01.01.1980";
    if (isset($data[ "_".$this->config["field"]."_Time" ]) && $this->config["time"]) 
         $time = &$data[ "_".$this->config["field"]."_Time" ]; 
    else $time="00:00";

    $_date = explode(".", $date);
    $_time = explode(":", $time);

    $this->data = date("Y-m-d H:i:s" , mktime( $_time[0], $_time[1], 0, $_date[1], $_date[0], $_date[2] ) );
  }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    $this->data = &$data[ $skip_char.$this->config["field"]]; 
    if (strpos($this->data," ") === false)
    {
      $this->data = date( "Y-m-d H:i:s", $this->data );
    }
  }


  function _Preparse( &$tpl, $tpl_prefix )
  {
    $date = date( "d.m.Y", strtotime($this->data)  );
    $time = date( "H:i", strtotime($this->data)  );

    if ($this->config["optional"] && 
        (($this->data == "") || date("Y",strtotime($this->data)) < 1980))
    {
      $date = "";
      $time = "";
    }

    $tpl->Assign("_Field", "_".$this->config["field"] );
    $tpl->Assign("_Value_Date", $date );
    $tpl->Assign("_Value_Time", $time );
  }
  function _Format() 
  { if (!$this->format) $this->format = "d.m.Y @ H:i";
    return date( $this->format, strtotime($this->data) ); 
  }

// EOC { FieldDT }
}


?>