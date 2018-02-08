<?php

  if ($text{0} == ">")
  {
    $wysiwyg = true;
    $text = substr($text, 1);
  }
  if ($text{0} == "<")
  {
    $striptags = true;
    $text = substr($text, 1);
  }

  $_text = strtolower( $text );

  $result = $cache->Restore( "texts_".$rh->messageset_no, $_text, 2 );

  if ($result === false)
  {
    $sql = "select body_r from ".$rh->db_prefix."texts where LOWER(supertag)=LOWER(".$db->Quote($_text).
           ") and section_id=".$db->Quote( $rh->messageset_no );


    $rs = $db->SelectLimit( $sql, 1 );
    if ($rs && $rs->RecordCount() > 0)
     $result = $rs->fields["body_r"];
    else
    {
     $debug->Trace("Formatter[texts] : '$text' not found. sorrei."); 
     $result = "";
    }

    $cache->Store( "texts_".$rh->messageset_no, $_text, 2, $result );
  }

  if ($wysiwyg)   $result = $this->Format( $result, "wysiwyg");
  if ($striptags) $result = preg_replace("/<.*?>/i", "", $result);

  echo trim($this->Format( $result, "typografica" ));

?>