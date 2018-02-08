<?php
/*

    Трансляция, один канал File

    ChannelFile( &$module )
      - $module -- модуль ModuleChannels

  ---------
========================================= v.1 (kuso@npj)
*/

class ChannelFile extends ChannelAbstract
{
  var $more_splitter = ":"; 
  var $custom_errors = array(
              "5003" => "Could not delete file",
              "5004" => "Could not open dir",
                            );

  function &ComposeFormGroup()
  {
    $rh = &$this->rh;
    $channel = &$this;
    $group2 = array();

    if (!isset($channel->data["channel:formatting"]))
      $channel->data["channel:formatting"] = $this->rh->principal->data["_formatting"];
    if (!isset($channel->data["channel:access_more"]))
      $channel->data["channel:access_more"] = "----";

    $group2[] = &new FieldString( &$rh, array(
                          "field"   => "file_rel_dir",
                          "default" => $channel->data["channel:source"],
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Described",
                           ) ); 

    $group2[] = &new FieldRadio( &$rh, array(
                          "field"   => "file_format",
                          "default" => $channel->data["channel:formatting"],
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Described",
                           ) ); 
    $group2[] = &new FieldString( &$rh, array(
                          "field"   => "file_separator",
                          "default" => $channel->data["channel:access_more"],
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "tpl_data" => "field_string.html:TextareaSmall",
                          "tpl_row" => "form.html:Row_Described",
                           ) ); 

    // build form group
    return $group2;
  }

  // мапим поля из формы для сохранения в БД
  function MapFormToChannel (&$form, &$channel_data)
  {
    $channel_data[ "source" ] = rtrim($form->hash["file_rel_dir"]->data, "/");
    $channel_data[ "formatting"  ] = $form->hash["file_format"]->data;

    $channel_data[ "access_login" ] = "";
    $channel_data[ "access_pwd"   ] = "";
    $channel_data[ "access_more"  ] = $form->hash["file_separator"]->data;
  }

  // returns array(..) or ERROR-CONSTANT
  function _GetChannelContents()
  {

    // 1. get file listing
    $dir = $this->data["channel:source"];
    $handle=@opendir($dir);

    if ($handle === false) return 5004;

    $filelist = array();
    while (false!==($file = readdir($handle))) 
    {
      if ($file != "." && $file != ".." && !is_dir($dir."/".$file)) 
        $filelist[] = $file;
    }
    closedir($handle);

    // 2. foreach file -> get contents, then remove
    $result = array();
    foreach( $filelist as $file_name )
    {
      $ff_name = $dir."/".$file_name;

      $data = file( $ff_name );
      foreach($data as $k=>$v) $data[$k] = trim($v, "\n\r");

      $header_line = -1;
      foreach( $data as $no => $line )
        if (strpos( $line, $this->data["channel:access_more"] ) !== false)
        { $header_line = $no; break; }

      if ($header_line > 0)
        $header = implode(" ", array_slice( $data, 0, $header_line ));
      else  
        $header = "";
      $body   = implode("\n", array_slice( $data, $header_line+1 ));

      // 
      $result[] = array(
               "subject"        => $header,
               "body"           => $body,
               "description"    => "",
               "user_datetime"  => date("Y-m-d H:i:s", filemtime( $ff_name ) ),

               "author"         => $file_name,
               "guid_hash"      => md5( $file_name. filesize($ff_name) ),

               //specific fields for template =)
               "filename"       => $file_name,
                   );

      // 
      if (!unlink( $ff_name )) return 5003; 
    }

    return $result;
  }


// EOC { ChannelFile }
}


?>