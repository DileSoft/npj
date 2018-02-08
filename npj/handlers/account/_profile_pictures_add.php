<?php

 // получаем данные
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )
     && 
     !($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && 
       (($this->npj_account == $rh->node_user) || ($this->npj_account == $rh->guest_user)))
    ) 
      return $this->Forbidden("UsepicsTune");

 // обработчик формы
 include( $dir."/!form_pictures_add.php" );
 if (!isset($_POST["__form_present"])) 
   $form->ResetSession();

 $debug->Milestone( "Starting form handler" );

 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
 $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

  if ($form->success)
  {
    // переименовать картинки
    $dir = $_SERVER["DOCUMENT_ROOT"]."/".$rh->user_pictures_dir;
    $filename1 = $dir.$form->hash["have_big"]->data;
    $filename2 = $dir.$form->hash["have_small"]->data;
    $ext1 = strrchr($form->hash["have_big"]->data, ".");
    $ext2 = strrchr($form->hash["have_small"]->data, ".");
    if ($form->hash["have_big"]->data)
     if (!rename( $filename1, $dir.$this->data["user_id"]."_big_".$form->data_id.$ext1 ))
      $debug->Error( " user pic failed: $filename1 -> ".$this->data["user_id"]."_big_".$form->data_id.$ext1 );
    if ($form->hash["have_small"]->data)
     if (!rename( $filename2, $dir.$this->data["user_id"]."_small_".$form->data_id.$ext2 ))
      $debug->Error( " user pic failed: $filename2 -> ".$this->data["user_id"]."_small_".$form->data_id.$ext2 );

    $sql = "update ".$form->form_config["db_table"]." set have_big=".
          $db->Quote($form->hash["have_big"]->data?$ext1:"").", have_small=".
           $db->Quote($form->hash["have_small"]->data?$ext2:"")." where pic_id=".$db->Quote( $form->data_id );
    $db->Execute( $sql );
 
    // сохранить то, что сохраняется не в основную таблицу
    if ($form->hash["is_default"]->data)
    {
      $sql = "update ".$rh->db_prefix."users set _pic_id=".$db->Quote( $form->data_id ).
             " where user_id=".$db->Quote($form->hash["user_id"]->data);
      $db->Execute($sql);
    }

    // редирект на profile/edit/ok
    $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "profile/pictures", 1 ),1) );
  }

?>