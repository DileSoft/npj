<?php
define(FS_INFO,1);         // FilterState
define(FS_CONTENT,2);
define(MT_TXT,3);          // MimeType
define(MT_MULTIPART,4);
define(MT_OTHER,5);
define(MS_FISRTINF,6);     // multiState
define(MS_INFOBLOCK,7);
define(MS_TXTBLOCK,8);
define(MS_OTHERBLOCK,9);
define(TF_8BIT,10);        // transfer
define(TF_PRINTABLE,11);
define(TF_BASE64,12);
define(TF_OTHER,13);

class MailParse
{
var $state; //FilterState
var $mt;    //MimeType
var $mtt; 
var $ms;     //multiState
var $tr;     //transfer

var $error; 
var $html;
var $result;

function MailParse($message = "") 
{
 $this->message = $message;
}

function parse($message = "") 
{
  $this->error = "";
  $this->result = array("text"=>"", "html"=>"");
  if (!$message) $message = $this->message;
  $this->html = false;
  $this->recode = "w";
  $this->state = FS_INFO;
  $this->mt = MT_OTHER;
  $this->ms = MS_FIRSTINF;
  $this->tr = TF_OTHER;
  $message = str_replace("\r", "", $message); 
  $lines = explode("\n",$message);
  $num = count($lines);
  for ($i=0;$i<=$num;$i++)
  {
    $line = $lines[$i];
    if ($this->state==FS_INFO) 
    { 
      if ($line!="")
      while ($lines[$i+1]{0}=="\t" || $lines[$i+1]{0}==" ")
      {
        $i++;
        $line = $line." ".trim($lines[$i]);
      }
      $this->processInfo($line);
    }
    else
    {
      $line = $this->processStr($line);
      if ($this->writeIt) 
        if ($this->html) 
          $this->result["html"] .= $line;
        else 
          $this->result["text"] .= $line;
    }
    if ($this->error) return false;
  }
  return $this->result;
}

function error($message = "") 
{
 $this->error=$message;
}

function Decode($s)
{
  $temp=$s;
  if ($this->tr==TF_PRINTABLE)    $temp=quoted_printable_decode($s);
  else if ($this->tr==TF_BASE64)  $temp=base64_decode($s);
  if ($this->tr!=TF_BASE64)       $temp=$temp."\n";
  if ($this->recode!="w")         $temp=convert_cyr_string($temp,$this->recode,"w");
  //if ($this->html)                $temp=striptags($temp);  //!!!
  return $temp;
}


function IsBound($s)
{
  $s = str_replace("-", "", $s);
  $i = strpos($s,$this->bound);
  if ($i===false || $i>0) return false;
//  if (str_replace("-","",substr($s,0,$i))!="") return false;
  return true;
}

function ProcessInfo($s)
{
//  echo "0".$s;
  if ($s=="")
  {
    if ($this->mt==MT_OTHER) $this->error('Unknown mime type');
    if ($this->mt==MT_TXT)
    {
      $this->state=FS_CONTENT;
      return;
    }
    if ($this->ms==MS_FIRSTINF);
    else 
      if ($this->mtt==MT_TXT) $this->ms=MS_TXTBLOCK;
      else $this->ms=MS_OTHERBLOCK;
    $this->state=FS_CONTENT;
    return;
  }
  $l=strlen($s);
  $i = strpos($s, ":");
  if ($i===false) 
  {
   if ($s{0}=="\t" || $s{0}==" ") return;                     //multiline header
   else $this->error('Incorrect format: '.$s); 
  }
  else
  {
    $s1=strtoupper(substr($s,0,$i));
    if ($s1=='FROM')
      $this->from=substr($s,$i+1);
    else if ($s1=='CONTENT-TYPE')
    { 
      $j = strpos($s, "/", $i+2);
      if ($j===false) $this->error('Incorrect mime format'); 
      $s2=strtoupper(substr($s,$i+2,$j-$i-2));
      $i = strpos($s, ";", $j+1);
      if ($i===false) $s3="";
      else $s3=strtoupper(substr($s,$j+1,$i-$j-1));

      if ($s2=='TEXT')
      {
        if ($s3=='HTML;' || $s3=='HTML') $this->html=true;

        $j = strpos($s, "=", $i+2);
        $s3=trim(strtoupper(substr($s,$i+2)));

        if (stristr($s3,"WINDOWS-1251")) $this->recode="w"; 
        else if (stristr($s3,"KOI8-R")) $this->recode="k"; 
        if ($this->ms==MS_FIRSTINF) $this->mt=MT_TXT;
        else $this->mtt=MT_TXT;
      }
      else if ($s2=='MULTIPART')
      {
        $this->mt=MT_MULTIPART;
        $j = strpos($s, "=", $i+2);
        if ($j===false) $this->error("Incorrect multipart format");
        $s2=strtoupper(substr($s,$i+2,$j-$i-2));

        if ($s2!='BOUNDARY') $this->error("Incorrect multipart format 2(boundary): ".$s);
        $i = strpos($s, "\"", $j+2);
        if ($i===false) $this->error("Incorrect miultipart format 3(\"): ".$s);
        $s2=str_replace("-", "", substr($s,$j+2,$i-$j-2));
        $this->bound=$s2;
      }
      else if ($this->ms==MS_FIRSTINF) $this->mt=MT_OTHER;
      else $this->mtt=MT_OTHER;
      //echo "<b>mp</b>:".$s2.";$i;$j<br>";
    }
    else if ($s1=='CONTENT-TRANSFER-ENCODING')
    {
      $s2=strtoupper(substr($s,$i+2));
      if ($s2=='QUOTED-PRINTABLE') $this->tr=TF_PRINTABLE;
      else if ($s2=='8BIT') $this->tr=TF_8BIT; 
      else if ($s2=='BASE64') $this->tr=TF_BASE64; 
      else $this->tr=TF_OTHER;
    }
  /*  if s1='Content-Type' then begin //Это кодировка KOI\WIN?
      s2:='';
      i:=i+2;
      while i<=l do begin
        s2:=s2+S[i];
        i:=i+1;
      end;
      write(LOG, ' Type:'+s2);
      if (s2='Content-Type: text/html; charset=windows-1251')
      or (s2='Content-Type: text/plain; charset=windows-1251')
      then tr:=TF_PRINTABLE else
      if s2='8bit' then tr:=TF_8BIT else
      if s2='base64' then tr:=TF_BASE64 else tr:=TF_OTHER;
      return;
    end;*/
  }
}


function processStr($s)
{
  $this->writeIt=true;
  if ($this->mt==MT_TXT) $result=$this->decode($s);
  else 
  {
    //echo "s: $s<br>";
    if ($this->IsBound($s)) 
    {
       $this->ms=MS_INFOBLOCK;
       $this->mtt=MT_OTHER;
       $this->state=FS_INFO;
       $result='';
    }
    else 
      if ($this->ms==MS_TXTBLOCK) $result=$this->decode($s);
      else $this->writeIt=false;
  }
 return $result;
}


function um_decode($string)
{
 if (strpos($string, "=?")===false)
  $result = ($this->recode=="w"?$string:convert_cyr_string($string,$this->recode,"w"));
 else 
 {
  $star = explode("?", $string);
  for ($i=0;$i<count($star);$i++) 
  {
   if ($star[$i]!="=") 
   {
    if ($star[$i]=="U" || $star[$i]=="B") $method="base64_decode";
    else if ($star[$i]=="Q") $method="quoted_printable_decode";
    else if (strtolower($star[$i])=="koi8-r") $decode = "k";
    else if (strtolower($star[$i])=="windows-1251") $decode = "";
    else 
    {
     $t = $method?$method($star[$i]):$star[$i];
     $result .= str_replace("=","",$decode?convert_cyr_string($t,$decode,"w"):$t);
    }
   }
  }
 }
 //echo ($result)."<br>";
 return $result;
}

function explodeHeaders($text)
{
 $raw_headers = trim($text);
 $raw_headers = preg_replace("/\n[ \t]+/", ' ', $raw_headers); // Unfold headers
 $raw_headers = explode("\n", $raw_headers);
 $headers = array();
 foreach ($raw_headers as $value) 
 {
     $name  = strtolower(substr($value, 0, $pos = strpos($value, ':')));
     $value = ltrim(substr($value, $pos + 1));
     if (isset($headers[$name]) AND is_array($headers[$name])) {
         $headers[$name][] = $value;
     } elseif (isset($headers[$name])) {
         $headers[$name] = array($headers[$name], $value);
     } else {
         $headers[$name] = $value;
     }
 }
 return $headers;
}

}

?>