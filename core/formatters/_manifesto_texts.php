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

  $result = $cache->Restore( "texts".$options["db"]."_".$rh->messageset_no, $_text, 2 );

  if ($result === false)
  {
    $sql = "select id, body_r from ".$rh->db_prefix."texts".$options["db"]." where LOWER(supertag)=LOWER(".$db->Quote($_text).
           ")";
    if ($this->config->lang) $sql.=" and lang=".$this->config->db->Quote($this->config->lang);


    $rs = $db->SelectLimit( $sql, 1 );
    if ($rs && $rs->RecordCount() > 0)
    {
     $result = $rs->fields["body_r"];
     $edit = "редактировать";
     $id     = "_edit=1&id=".$rs->fields["id"];
     $_id = $rs->fields["id"];
    }
    else
    {
     $debug->Trace("Formatter[texts] : '$text' not found. sorrei."); 

     //
     if ($rh->principal->IsGrantedTo( "role", "editor" )) $result = $text;
     else                                                 $result = "";
     
     $edit = "создать";
     $id     = "_add=1"."&__form_present=1&_supertag=".$text;

     if ($this->config->lang) $id.="&_lang=".$this->config->lang;

     $_id = 0;
    }

    $cache->Store( "texts".$options["db"]."_".$rh->messageset_no, $_text, 2, $result );
  }

  if ($wysiwyg)   $result = $this->Format( $result, "wysiwyg");
  if ($striptags) $result = preg_replace("/<.*?>/i", "", $result);

  $result = trim($this->Format( $result, "typografica" ));

  // проверка на едит-ин-плаце ---------------------------
  if (($rh->cms_url != "") && $tpl->GetValue("M:cms.Show") && $rh->principal->IsGrantedTo( "role", "editor" ))
  {
    $width="650";  if ($options["width"])  $width =$options["width"]; 
    $height="400"; if ($options["height"]) $height=$options["height"];
    $result = "<div class=\"edit-in-place\">".$result.
              "<div class=\"edit-in-place-link-\">".
              "<a target=_blank ".
              " onclick=\"NewWindow(this.href,'texts_".$_id."','$width','$height','no');return false\" ".
              " href=\"/".$rh->cms_url."form/texts".$options["db"]."?".$id."&close=1\">".$edit."</a>".
              "</div></div>";
  }
  // -----------------------------------------------------

  echo $result;

?>