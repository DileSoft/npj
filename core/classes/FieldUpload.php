<?php
/*
    FieldUpload( &$rh, $config ) -- работаем с файлами
  ---------

  // overridden:
  * Validate()
  * _Preparse( &$tpl_engine )
  * Format()

  // options
  * maxlen
  * maxsize
  * extensions[]
  * save_dir
  * thumbnails[x,y,..]
  * thumb_quality
  * preview_thumb (#)
  * max_wh[x,y]
  ? unique_sql

=============================================================== v.4 (Kuso)
*/

class FieldUpload extends Field
{

  function FieldUpload( &$rh, $config )
  {
    Field::Field(&$rh, $config);
    // assigning defaults
    if (!isset($this->config["tpl_data"])) $this->config["tpl_data"] = "field_upload.html:Picture";
  }

  // проверка на различные ошибки
  function Validate()   
  { 
    $config = &$this->config;
    $data = &$this->data;

    Field::Validate();

    if ((($data == "") || ($data == "(clear)")) && !$config["musthave"]) 
    {
      $this->invalid = false;
      return true;
    }

    // checkz
    if (!isset($this->config["save_dir"]))
     $this->invalidReasons[] = "Кое-кто забыл указать, куда сохранять картинки!";


    // 1. maxlen
    if (isset($config["maxlen"]) && ($config["maxlen"] < strlen($data)))
     $this->invalidReasons["FormError_TooLong"] = "Слишком длинное имя у файла, нужно не более ".$config["maxlen"]." символов";

    // 2. maxsize
    if (isset($config["maxsize"]) && ($config["maxsize"] < $this->file_size))
     $this->invalidReasons["FormError_MaxSize"] = "Слишком большой размер файла &#151; ".sprintf("%.2f",$this->file_size/1024).
                               "&nbsp;Кб, нужно не более ".sprintf("%.2f",$config["maxsize"]/1024)."&nbsp;Кб";

    // 3. extensions
    if ($data != "")
    if (isset($config["extensions"]))
    {
       $_data = explode(".", $data);
       if (!in_array( strtolower($_data[sizeof($_data)-1]), $config["extensions"] ))
         $this->invalidReasons["FormError_WrongExtension"] = "Недопустимый тип файла (можно: ".implode(", ",$config["extensions"]).")";
    }

    // 4. unique_sql
    if (isset($config["unique_sql"]))
    {
      $query = str_replace("[name]", $config["field"], $config["unique_sql"]);
      $query = str_replace("[value]", $this->rh->db->Quote($this->data), $query);
      $rs = $this->rh->db->SelectLimit( $query, 1 );

      if ($rs->RecordCount() && $rs->fields["id"] != $this->form->data_id)
       $this->invalidReasons["FormError_NotUnique"] = "К сожалению, это имя неуникально, а следовало бы придумать что-нибудь эдакое!";
    }
  
    // 5. max Width x Height
    if (isset($this->config["max_wh"]))
    {
      if ($this->picture_size)
      {
        if (($this->config["max_wh"][0] < $this->picture_size[0]) ||
            ($this->config["max_wh"][1] < $this->picture_size[1]) )
         {
           $this->invalidReasons["FormError_MaxWH"] = "Геометрические размеры картинки слишком велики &#151; нужно не более ".
                                                      implode("x",$this->config["max_wh"]);
           $this->rh->debug->Trace("invalid ".$config["field"] );
         }
      }
    }


    $this->invalid = sizeof($this->invalidReasons) != 0;
    return !$this->invalid; 
  }

  // получение из формы/бд
  function _Load( &$data ) 
  { 
    if ($data["_".$this->config["field"]."_clear"])
    {
      $this->data="(clear)";
      return;
    }
    if (is_uploaded_file($_FILES[ "_".$this->config["field"] ]["tmp_name"]))
    {
      // 1. check out $data
      $_data = explode(".", $_FILES[ "_".$this->config["field"] ]["name"] );
      $ext  = strtolower($_data[ sizeof($_data)-1 ]);
      unset( $_data[ sizeof($_data)-1 ] );
      $name = implode( ".", $_data );
      $name = $this->rh->tpl->Format( $name, "translit" );
      $_name = $name;
      $count = 1;
      while (file_exists($_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].$name.".".$ext))
      {
        if ($name === $_name) $name = $_name.$count;
        else $name = $_name.(++$count);
      }
      $this->data = $name.".".$ext;
      $this->file_size = $_FILES["_".$this->config["field"]]['size'];
      // 1.5 get image size if asked for
      if (isset($this->config["max_wh"]))
      {
        $src = $_FILES[ "_".$this->config["field"] ]["tmp_name"];
        $size = @getimagesize( $src ); 
        $this->picture_size = $size;
      }
      // 2. validate
      if (!$this->Validate()) return $this->invalidReasons=array();

      // 3. save to permanent location
      move_uploaded_file($_FILES["_".$this->config["field"]]["tmp_name"], $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].$this->data); 
      chmod( $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].$this->data, 0744 );

      // 5. make thumbs if asked for
      if (isset($this->config["thumbnails"]))
      {
        $thumbs = &$this->config["thumbnails"]; $tl = sizeof($thumbs);
        $src = $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].$this->data;
        $size = getimagesize( $src ); 
        $this->rh->debug->Trace( "picture: ".$size[2]." = ".$size[0]." x ".$size[1]);
        if ($size[2] == 1) $img = imagecreatefromgif( $src ); else
        if ($size[2] == 2) $img = imagecreatefromjpeg( $src ); else
        if ($size[2] == 3) $img = imagecreatefrompng( $src ); else return;
        for ($i=0; $i<$tl; $i+=2)
        {
          if (!is_numeric($thumbs[$i]))   $thumbs[$i]   = floor($size[0]*$thumbs[$i+1]/$size[1]);
          if (!is_numeric($thumbs[$i+1])) $thumbs[$i+1] = floor($size[1]*$thumbs[$i]  /$size[0]);
          
          $this->rh->debug->Trace( $thumbs[$i]." x ".$thumbs[$i+1]);

          $thumb = imagecreatetruecolor( $thumbs[$i], $thumbs[$i+1] ); // !!! truecolor ???
          imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbs[$i], $thumbs[$i+1], $size[0], $size[1]);
          imagejpeg($thumb, $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].
                            $name."_".($i/2+1).".jpg", $this->config["thumb_quality"]);
          imagedestroy($thumb);
        }
        imagedestroy($img);
      }

    } else $this->data = "";
  }
  function _RestoreFromDb( &$data, $skip_char="" ) 
  { 
    $this->data = &$data[ $skip_char.$this->config["field"]]; 
    $this->db_loaded_data = $this->data; 
  }

  function CreateUPDATE() { $this->_StoreToDb(); 
                            if ($this->data == "") return "";
                            if ($this->data == "(clear)")
                            {
                              $this->data = "";
                              return Field::CreateUPDATE();
                            }
                            return Field::CreateUPDATE(); }


  function _Preparse( &$tpl, $tpl_prefix )
  {
    $tpl->Assign("_Field", "_".$this->config["field"] );
    if (!$this->data) { $tpl->Assign("_Value", ""); $tpl->Assign("_Preview", ""); }
    else
    {
      $src = $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].$this->data;
      if (!file_exists($src)) $this->file_size = "(картинка не&nbsp;загружена)";
      else $this->file_size = filesize( $src );

      $tpl->Assign("_Value", "<b>".$this->data."</b><br /><small>(".sprintf("%.2f",$this->file_size/1024)."&nbsp;Кб)</small>" );
      $tpl->Assign("_Filename", $this->data);
      $tpl->Assign("_Filesize", sprintf("%.2f",$this->file_size/1024)."&nbsp;Кб");
      $tpl->Assign("_Href", $this->config["save_dir"].$this->data );

      if (isset($this->config["preview_thumb"]))
      {                               
        $_data = explode(".", $this->data);
        unset( $_data[ sizeof($_data)-1 ] );
        $thumb = $_SERVER["DOCUMENT_ROOT"].$this->config["save_dir"].implode( ".", $_data )."_".($this->config["preview_thumb"]).".jpg";
        if (!file_exists($thumb)) $tpl->Assign("_Preview","(нет&nbsp;картинки)");
        else
        {
          $size = getimagesize( $thumb ); 
          $tpl->Assign("_Preview", "<img src='".$this->config["save_dir"].implode( ".", $_data )."_".($this->config["preview_thumb"]).".jpg"."' border=0 ".$size[3]." alt='' />");
        }
      } else
        $tpl->Assign("_Preview", "<img src='".$this->config["save_dir"].$this->data."' border=0 ".$size[3]." alt='' />");

    }
  }
  function _Format( &$tpl, $tpl_prefix ) 
  { 
    $this->_Preparse( &$tpl, $tpl_prefix );
    return $this->rh->tpl->Parse( $tpl_prefix.$this->config["tpl_data"]."_Readonly" );
  }


// EOC { FieldUpload }
}


?>