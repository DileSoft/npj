<?php
  $data = &$this->Load( 4 );

  if ($data && $data!="empty") 
  {
   
    // проверка прав доступа
   if (!$this->HasAccess( &$principal, $this->security_handlers[$data["type"]] )) 
    if (!$this->HasAccess( &$principal, "owner" ) && !($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && $this->npj_account==$rh->node_user ))
     return $this->Forbidden("RecordForbidden");

    // проверка соответствия фильтра
    if ($rh->community_filter)
      if (!$this->IsCommunityFilterOk( $rh->object->npj_filter ))
      { 
          if ($this->data["filter:exploded"][0]) $f = "in/".$this->data["filter:exploded"][0]."/by/";
          else                                   $f = "";
          return $rh->Redirect( $this->Href( $f.$rh->object->npj_address, 
              STATE_USE ), STATE_IGNORE );
      }

    // если у принципала есть настройка "всегда показывать с комментами", то и надо вызвать этот хандлер
   if ($principal->data["options"]["comments_always"] && ($data["number_comments"] > 0) && !$params["_comments"])
    if (!$data["disallow_comments"]) 
   {
     $comments = &new NpjObject( &$rh, $object->npj_object_address."/comments" );
     return $comments->Handler( "show", &$params, &$principal );
   }
   
   // определяем, надо ли вызывать обновление полей. -----------------------------------------------------------------------------
   $update=0;
   if (($this->GetType()==RECORD_POST) && 
       ($data["body"] != "") && ($data["body_post"] == ""))     $update=1;
   if (($data["subject"] != "") && ($data["subject_r"] == ""))  $update=1;
   // ============================================== -----------------------------------------------------------------------------

   $data["subject_post"] = $this->Format($data["subject_r"], $data["formatting"], "post");
   $data["body_post"] = $this->Format($data["body_r"], $data["formatting"], "post");

   // refactored: $data["body_post"] = $this->NumerateToc( $data["body_post"] ); //  numerate toc if needed
   $uactn = &$rh->UtilityAction(); // actions теперь живут в отдельном классе. << max@jetstyle 2004-11-18 >>
   $data["body_post"] = $uactn->NumerateToc( $data["body_post"], &$this ); //  numerate toc if needed

   // запись в body_post для лент -------------------------------------------------------------------------------------------------
   if ($update)
   {
     $subject_r =     $this->Format($data["subject"], $data["formatting"]."_subject");
     $data["subject_r"] = $subject_r;
     if ($this->GetType()==RECORD_POST)
     {
       $body_post = $this->Format($data["body_r"], $data["formatting"], array("default"=>"post","feed"=>1));
       $body_post = $this->Format($body_post, "cut", array("supertag"=>$_supertag ));

       $data["subject_post"] = $this->Format($subject_r, $data["formatting"], 
                                             array("default"=>"post","feed"=>1,"subject"=>1, "stripnotypo"=>1));
       $subject_post =  $data["subject_post"];
     } else 
     {
       $body_post=$subject_post="";
       $data["subject_post"] = $this->Format($data["subject_r"], $data["formatting"], "post");
     }

     $db->Execute( "update ". $rh->db_prefix."records set body_post=".
                   $db->Quote( $body_post    ).", subject_r=".
                   $db->Quote( $subject_r    ).", subject_post=".
                   $db->Quote( $subject_post )." where record_id = ". 
                   $db->Quote( $data["record_id"]    ) );
   }
   if ($data["crossposted"] == "!") $this->CompileCrossposted( $data["record_id"] );
   // ========================== -------------------------------------------------------------------------------------------------

//!!!! URLs <= to kukutz: что бы это могло значить?
   $edit = $this->href("!/edit")."#form";
   $themeurl = $tpl->GetValue("theme");
   $dclick = '<script type="text/javascript" language="javascript">var edit = "'.$edit.'";</script>'.
             '<script type="text/javascript" language="javascript" src="'.$themeurl.'/js/dclick.js"></script>';
   if (!$rh->admins_only_documents || $this->HasAccess( &$principal, "acl_text", $rh->node_admins))
     if ($principal->data["options"]["double_click"]) $tpl->Assign( "DCLICK", $dclick);

   $tpl->Assign( "Preparsed:TITLE", $data["subject_post"] );
   if ($data["subject_post"] == "") $data["subject_post"] = $rh->account->data["journal_name"];
   $tpl->Assign( "Html:TITLE", $data["subject_post"] );
   $tpl->Assign( "Preparsed:CONTENT", $data["body_post"] );
   $tpl->Assign( "Preparsed:CONTENT>raw", $data["body_post"] );

   if (($this->GetType() == RECORD_POST) && 
       $data["rare"]["announced_supertag"] && !$data["rare"]["announced_disallow_comments"])
   {
      $tpl->Assign("Href:Announced", $this->Href( $data["rare"]["announced_supertag"], NPJ_ABSOLUTE, STATE_IGNORE ) );
      if ($data["rare"]["announced_comments"] == 0)
       $tpl->Parse( "comments.html:Announce_HiddenNone", "Preparsed:COMMENTS" );
      else
      {
       $tpl->Assign( "CommentCount", $data["rare"]["announced_comments"] );
       $tpl->Parse( "comments.html:Announce_Hidden", "Preparsed:COMMENTS" );
      }
   }
   else
     if ($data["disallow_comments"] || $data["rare"]["announced_disallow_comments"])
        $tpl->Assign( "Preparsed:COMMENTS", "" );
     else
      if (!$rh->admins_only_documents || $this->GetType() == RECORD_POST)
     {
       if ($data["number_comments"] == 0)
        $tpl->Parse( "comments.html:HiddenNone", "Preparsed:COMMENTS" );
       else
       {
        $tpl->Assign( "CommentCount", $data["number_comments"] );
        $tpl->Parse( "comments.html:Hidden", "Preparsed:COMMENTS" );
       }
     }

   if ($data["is_keyword"] && ($data["tag"] !== ""))
   { 
     $account = &new NpjObject( &$rh, $this->npj_account );
     $account->Load(3);

     if (!$this->_category_mode) $this->_category_mode = $account->data["options"]["keywords_auto"];;

     if ($state->Get("roubric")) $this->_category_mode = $state->Get("roubric");
     $action = preg_replace( "/[^0-9a-z_\-\\\\>]/i", "", strtolower($this->_category_mode));

     if ($action == "") $action = "feed";

     $params = $rh->keywords_auto_params["_"];
     if (isset($rh->keywords_auto_params[$action]))
      $params = $rh->keywords_auto_params[$action];


     $contents = $this->Action( $action, $params, &$principal );
     if ($contents == "" && ($action != "nothing"))
     {
       $contents = $tpl->Parse("roubric.html:Empty");
     }

     // вывод
     { 
       $tpl->Assign( "Preparsed:TITLE", $data["subject_post"] );
       if ($data["subject_post"] == "") $data["subject_post"] = $rh->account->data["journal_name"];
       $tpl->Assign( "Html:TITLE", $data["subject_post"] );
       $tpl->Assign( "Preparsed:CONTENT", $data["body_post"] );
       $tpl->Assign( "Preparsed:FACET", $contents );
       $tpl->Parse ( "roubric.html:Wrapper", "Preparsed:CONTENT" );
       $tpl->Assign( "NoRecordStats", 2 );
     }
   }

  } 
  else return $this->NotFound("RecordNotFound");

  return GRANTED;

?>
