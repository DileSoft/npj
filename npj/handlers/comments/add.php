<?php



  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $record->Load( 2 ); // без блобов
  if (!is_array($record->data)) return $this->NotFound("RecordNotFound");
  // комментарий к анонсу:
  if ($record->data["rare"]["announced_supertag"]) 
  {
    $rh->Redirect( $record->Href( $record->data["rare"]["announced_supertag"]."/comments/add" )."#comments_add" );
    // return $this->Forbidden("CouldNotCommentAnnounce");
  }
  // -- 
  if ($record->data["disallow_comments"]) return $this->Forbidden("CouldNotComment");
  $object->record = &$record;
  $tpl->Assign( "Href:Record", $record->Href($record->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE) );

  // проверка на банлист

  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) 
    return $this->Forbidden("YouAreInBanlist");

  if (!$record->HasAccess( &$principal, $record->security_handlers[$record->GetType()], "comment" )) 
    return $this->Forbidden("CommentForbidden");

  $comment = &$object;
  $data = $comment->Load( 3 ); // полностью
  if (!is_array($data)) return $this->NotFound("CommentNotFound");

  // limitation based on parent`s state
  if ($object->name == "")
  {
  } 
  else
  {
    if ($data["active"] != 1)
      return $this->Forbidden("CommentDeletedNoAdd");
    if ($data["frozen"] != 0)
      return $this->Forbidden("CommentFrozenNoAdd");
  }

  // !!!! здесь стоит проверить, можно ли 
  // 1. (?) комментировать этот журнал
  // 2. (+) комментировать эту запись
  // 3. (+) комментировать этому пользователю
  // 4. (+) банлист
  // 5. (+) замороженность комментария

  // уход в подфункцию 
  if ($object->params[0] == "ok")           return $object->Handler( "_add_ok", &$params, &$principal );

   // обработчик формы
   include( $dir."/!form_add.php" );
   if (!isset($_POST["__form_present"])) 
   { 
     $form->ResetSession();
   }

   $debug->Milestone( "Starting form handler" );

   // отображаем собственно контекст комментирования
    if ($data["is_tree_only"])
    {
      $tpl->Assign( "Active", 1 );
      $record->Handler( "show", "", &$principal );
      $tpl->Assign( "Preparsed:COMMENTS", "" );
    }
    else
    {
      if ($data["active"]<=0)   return $this->Forbidden("CouldNotCommentDeleted");

      // сформировать $this->_b, $this->comment_mode
      $this->comment_mode = -1;

      // enhance $data
      $data["Link:user"] = $object->Link( $data["user_login"]."@".$data["user_node_id"] );
      if ($data["user_id"] == 1) 
      { 
        $data["user_name"] = $data["Link:user"];
        $data["Link:user"] = "[&nbsp;".$data["ip_xff"]."&nbsp;]";
      }
      $data["dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($data["created_datetime"]));
      $data["userpic"] = "<img border=\"0\" src=\"".$rh->user_pictures_dir.$data["user_id"]."_small_".
              $data["pic_id"].".gif\" />"; 
      if (trim($data["subject"]) == "") $data["_subject"] = "(без заголовка)";  // !!! to messageset
      else $data["_subject"] = $data["subject"];

      $this->_b = array( $data );

      $params["dummy"] = 1;
      $params["insert"] = 1;

      $tpl->Assign( "CommentID", $data["comment_id"] );

      include( $dir."/_show.php" );

    }

   $tpl->Skin($rh->theme);
   $result= $form->Handle();
   $tpl->UnSkin();

   if ($data["is_tree_only"]) $data["number_comments"]=$record->data["number_comments"];

   if ($data["number_comments"] == 0)
    $tpl->Parse( "comments.html:AddNone", "Preparsed:COMMENTS", TPL_APPEND );
   else
   {
    $tpl->Assign( "CommentCount", $data["number_comments"] );
    $tpl->Parse( "comments.html:CommentAdd", "Preparsed:COMMENTS", TPL_APPEND );
   }

  if ($form->success)
  {

    //определяем форматтинг
    $formatting = $principal->data["_formatting"];

    $_body_post = $this->Format($form->hash["body"]->data, $formatting, "pre");
    $_body_post = $this->Format($_body_post, $formatting);
    $_body_post = $this->Format($_body_post, "paragrafica");
    $_body_post = $this->Format($_body_post, $formatting, "post");

    $_subject_post = $this->Format(
                         $this->Format($form->hash["subject"]->data, $formatting."_subject"),
                       $formatting, array("default"=>"post","feed"=>1));

    // типографирование
    $principal->data["advanced_options"] = $principal->DecomposeOptions( $principal->data["advanced"] );
    if ($principal->data["advanced_options"]["typografica"])
    {
      $_body_post = $this->Format($_body_post, "typografica");
      $_subject_post   = $this->Format($_subject_post,"typografica");
    }

    if ($tpl->message_set["ButtonTextCommentPreview"] == $_POST["__button"])
    {
      $tpl->Assign("Preview", $_body_post );
      $tpl->Parse( "comments.html:Preview", "Preparsed:COMMENTS" );

      // стараемся уменьшить количество дерьма вокруг
      $tpl->Assign("Preparsed:CONTENT", "<br />".$record->Link( $record->npj_address, "", $tpl->message_set["CommentPreviewRecord"]) );
      $tpl->Assign("Panel:Off", 1);
  
      // отрабатываем preview
      $form->invalid = true;
      $tpl->theme = $rh->theme;
       $result = $form->Parse();
      $tpl->theme = $rh->skin;
    }
    else
    {
/*
    // нужно изменить коммент-каунт
    $sql = "select count(*) as result from ".$rh->db_prefix."comments where active=1 and record_id=".
           $db->Quote( $form->hash["record_id"]->data );
    $rs = $db->Execute( $sql );
    $sql = "update ".$rh->db_prefix."records set number_comments=".
           $db->Quote( $rs->fields["result"] )." where record_id=".
           $db->Quote( $form->hash["record_id"]->data );
    $db->Execute( $sql );
*/

      // нужно создать затычку под комментарий, перегнав из формы данные, 
      $new_comment = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context."/comments" );
      foreach( $form->hash as $field )
      {
        $field->_StoreToDb(); 
         if (!is_array($field->db_data))
          $new_comment->data[ $field->config["field"] ] = $field->db_data;
         else 
          foreach ($field->db_data as $f=>$v)
           $new_comment->data[ $field->config["fields"][$f] ] = $v;
      }

      $new_comment->data["body_post"] = $_body_post;
      $new_comment->data["subject"] =   $_subject_post;
      $new_comment->record = & $record;
  
      // сохраняем комментарий в БД
      $new_comment->Save();
  
      // делаем отсылку почты в сейве, делаем подписку в сейве
      $rh->absolute_urls = 0;
  
      // редирект на comments/add/ok
      $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( $this->npj_address."/ok/".$new_comment->name, 1 ),1) );
    }
  }


  // присоединение формы книзу
   if ($result !== false) 
   {
     $tpl->Assign("Preparsed:COMMENTS/Before", $tpl->GetValue("Preparsed:COMMENTS"));
     $tpl->Assign("Preparsed:COMMENTS/Form", $result);
     $tpl->Append("Preparsed:COMMENTS", $result);
   }

?>
