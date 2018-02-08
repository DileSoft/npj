<?php
/*
   {{import
   }}

   http://npj.ru/kuso/myimport/imported --> {{import}}, to = "Тест".
   Импортировать в: kuso@npj:Тест/*
*/
//
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
                                            
 if (!$_POST["_form"])
 {
  // parse FORM 
  $tpl->LoadDomain( array(
     "to" => "",
     "Form:Import"    => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $object->npj_object_address )."/".$script_name , "name=\"import_form\" enctype=\"multipart/form-data\""),
     "/Form"          => $state->FormEnd(),
  ));
  return $tpl->Parse( "actions/import.html:Form");
 }
 else
 {
  if ($_FILES["_import"]["error"]==0)
  {
   //move_uploaded_file($_FILES['_import']['tmp_name'], "/place/to/put/uploaded/file"); 
   $fd = fopen($_FILES['_import']['tmp_name'], "r");
   $contents = fread ($fd, filesize ($_FILES['_import']['tmp_name'])); 
   fclose ($fd);
   $base_addr = $object->untag($contents, "title");

   $object->Load(2);

   $items = explode("<item>", $contents);
   array_shift($items);

   $pre_address = trim($this->_UnwrapNpjAddress( $_POST["_to"], NPJ_RECOVERABLE ), "/");
   $pre_address = $this->RipMethods( $pre_address, RIP_STRONG );     // свести до ближайшей записи
   if (substr($pre_address, strlen($pre_address)-1) != ":")
     $pre_address.="/";


   foreach ($items as $item)
   {
    $rel_tag = $object->untag($item, "guid");
    $title = $object->untag($item, "title");
    $body = str_replace("]]&gt;", "]]>", $object->untag($item, "description"));
    if (is_numeric($rel_tag{0})) continue;

    $address = trim($pre_address.$rel_tag, "/");
    $tag     = preg_replace("/^.*:/i","", $address);

    $record =& new NpjObject(&$rh, $address);
    $record->data["type"] = 2;
    $record->data["tag"] = $tag;
    $record->data["subject"] = $title;
    $record->data["formatting"] = "wacko";
    $record->data["body"] = $this->Format($body, $record->data["formatting"], "pre");
    $record->Save();

   }
  return $tpl->Parse( "actions/import.html:Success");
  }
  else
  {
   $debug->Trace_R($_FILES);
   $debug->Trace_R($_POST);
   $debug->Error("IMPORT: ".$script_name);
  }
 }

?>
