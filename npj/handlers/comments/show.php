<?php

  $record = &new NpjObject( &$rh, $object->npj_account.":".$object->npj_context );
  $data = $record->Load( 3 ); // надо будет показать и запись
  if (!is_array($record->data)) return $this->NotFound("RecordNotFound");
  // комментарий к анонсу:
  if ($data["rare"]["announced_supertag"]) 
  {
    $rh->Redirect( $record->Href( $data["rare"]["announced_supertag"]."/comments#comments" ) );
    // return $this->Forbidden("CouldNotCommentAnnounce");
  }
  // -- 
  if ($data["disallow_comments"]) return $this->Forbidden("CouldNotComment");

  if (!$record->HasAccess( &$principal, $record->security_handlers[$data["type"]] )) 
    return $this->Forbidden("RecordForbidden");

  $object->record = &$record;
  $tpl->Assign( "Href:Record", $record->Href($record->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE) );
  
  if ($this->name == "no")
  {
     $tpl->Assign( "Href:Object", $record->Href( $record->npj_object_address, NPJ_ABSOLUTE, STATE_IGNORE ));
     return $record->Handler( "show", array("_comments"=>1), &$principal );
  }
  

  // выбор, как будем показывать радость, радость.
  $comment_mode = $principal->data["options"]["comments"];
  $cm = $state->Get("comments");
  $state->Free("comments");
  $modes = array( COMMENTS_TREE => "tree", 
                  COMMENTS_FULL => "full", 
                  COMMENTS_PLAIN=> "plain" );
  foreach ($modes as $k=>$mode) 
   if ($cm == $mode) $comment_mode = $k;

  foreach ($modes as $k=>$mode) 
   if ($comment_mode != $k) $tpl->Assign( "Href:Comments/".$mode, 
      $rh->Href($rh->url,STATE_IGNORE).$state->Plus("comments", $mode) );
  
  $data = &$this->Load( 3 );

  if ($data !== "empty") 
  {
    // community-filter
    $cf = $rh->community_filter && ($rh->object->npj_filter != "");
    if ($cf)
    {
      $filter_object = &new NpjObject( &$rh, $rh->object->npj_filter."@".$rh->node_name );
      $filter_data   = $filter_object->Load(2);
      $filter_link   = $filter_object->Link( $filter_object->npj_object_address );
      if (!is_array($filter_data)) $cf=false;
      $filter_panel  = 0;
      if ($rh->principal->IsGrantedTo( "rank_greater", "account", $filter_data["user_id"], GROUPS_MODERATORS ))
        $filter_panel = 1;
    }

    $sql = "select c.*, ".
           ($cf?"cf.created_datetime as filtered_datetime, ":"").
           "FLOOR((rgt_id-lft_id-1)/2) as number_comments ".
           " from ".$rh->db_prefix."comments as c ".
           ($cf
             ?" left outer join ".$rh->db_prefix."comments_filtered as cf on c.comment_id=cf.comment_id ".
              " and cf.filter_user_id=".$db->Quote($filter_data["user_id"])
             :""
           ).
           " where ".
           " record_id=".$db->Quote($record->data["record_id"])." and ".
           " lft_id BETWEEN ".$db->Quote($data["lft_id"])." AND ".$db->Quote($data["rgt_id"]).
           ($comment_mode == COMMENTS_PLAIN ? " ORDER BY created_datetime ASC" : " ORDER BY lft_id ASC" );
    $rs = $db->Execute( $sql );  // !!! Arrows needed
    $a = $rs->GetArray();
    $depth=-1; $prev_r=0; // подсчёт _depth
    foreach( $a as $k=>$v)
    {
      // разрешаем автору комментария удалять
      if (  ($v["user_id"] == $principal->data["user_id"]) && 
             $record->HasAccess(&$principal, "noguests")
         )
            $a[$k]["control_panel"] = 1;
      else  $a[$k]["control_panel"] = 0;

      // разрешаем автору записи удалять и морозить
      if (  (  $record->HasAccess(&$principal, "owner") &&
               $record->HasAccess(&$principal, "noguests")
            )  ||
            $rh->account->HasAccess(&$principal, "rank_greater", GROUPS_MODERATORS)
         )  
         {
            $a[$k]["control_panel"] = 1;
            $a[$k]["freeze_panel"] = 1;
         }
         else
         {
            $a[$k]["freeze_panel"] = 0;
         }

      // Заморозка работает только 0,1. Всё остальное блокирует панель.
      if (($a[$k]["frozen"] != 0) && ($a[$k]["frozen"] != 1))
         $a[$k]["freeze_panel"] = 0;
            
      // Community-filter:
      if( $a[$k]["active"]  && $cf )
      {
        $a[$k]["filter_panel"] = $filter_panel;
        $a[$k]["filtered"] = $a[$k]["filtered_datetime"]?1:0;
        if ($a[$k]["filtered"] && !$state->Get("cfilter"))
        {
          $a[$k]["active"] = 0;
          $a[$k]["Link:filter"] = $filter_link;
        }
      }

      // подсчёт глубины (использовать стандартный в ListObjectTree нельзя, потому что комменты можно 
      //                  как бы удалять, хехе)
      $distance = $v["lft_id"] - $prev_r -1;
      if ($depth<0) $depth=0; else
      if ($distance < 0) $depth ++; else
       if ($distance > 0) $depth -= $distance; 
      $prev_r = $v["rgt_id"];                                     
      $a[$k]["_depth"] = ($comment_mode == COMMENTS_PLAIN ? 0 : $depth );
      

      $a[$k]["Link:user"] = $object->Link( $v["user_login"]."@".$v["user_node_id"] );
      if ($a[$k]["user_id"] == 1) 
      { 
        //$a[$k]["user_name"] = $a[$k]["Link:user"];
        $a[$k]["Link:user"] = "[&nbsp;".$a[$k]["ip_xff"]."&nbsp;]";
        //if ($debug->kuso) $debug->Error_R($a[$k]);
      }
      $a[$k]["dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($v["created_datetime"]));
      $a[$k]["userpic"] = "<img border=\"0\" src=\"".$rh->user_pictures_dir.$v["user_id"]."_small_".
                           $v["pic_id"].".gif\" />"; 
      if (trim($v["subject"]) == "") $a[$k]["_subject"] = "(без заголовка)";  // !!! to messageset
      else $a[$k]["_subject"] = $v["subject"];

      if ($a[$k]["active"])
        if ($rh->rss) 
        {
          $href_record = $record->Href($record->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE);
          // guid,      link,       author,   title, description, pubDate,   comments
          // supertag   Href:tag,   edited*,  (?),   body_post,   datetime,  (?)
          $rss_entry = array(
            "supertag" => $record->npj_object_address."/comments/".$a[$k]["comment_id"],
            "Href:tag"      => $href_record."/comments/".$a[$k]["comment_id"],
            "Href:comments" => $href_record."/comments/".$a[$k]["comment_id"],
            "tag"      => $a[$k]["_subject"],
            "body_post"=> $a[$k]["body_post"],
            "datetime" => $a[$k]["created_datetime"],
            "edited_user_name"    => $a[$k]["user_name"],
            "edited_user_login"   => $a[$k]["user_login"],
            "edited_user_node_id" => $a[$k]["user_node_id"],
                            );
          $rh->rss->AddEntry( $rss_entry, RSS_COMMENTS );
        }
    }

    // сокрытие и удаление "удалённых" комментариев
    $hide_hash = array();
    $is_owner = $record->HasAccess(&$principal, "owner");
    for($i=sizeof($a)-1; $i>=0; $i--)
    {
      if (($a[$i]["active"] == 1)
      /*
          ||
          (($a[$i]["active"] == 2)
           &&
           ($is_owner ||
            ($a[$i]["user_login"]."@".$a[$i]["user_node_id"] == 
             $principal->data["login"]."@".$principal->data["node_id"])
           )
          )
      */
         )
      {
        $hide_hash[ $a[$i]["comment_id"] ] = 1;
        $hide_hash[ $a[$i]["parent_id"] ] = 1;
      }
      if ($hide_hash[ $a[$i]["comment_id"] ]) $hide_hash[ $a[$i]["parent_id"] ] = 1;
    }
    $b = array();
    foreach($a as $k=>$v) if ($hide_hash[ $v["comment_id"] ]) $b[] = $a[$k];
    
    if (!$data["is_tree_only"] && ($data["active"]<=0))
     return $this->NotFound("CommentDeleted"); //!!! нужна тут ссылка на все комментарии к этой записи

    // отображение контекста записи
    {
      $tpl->Assign( "Active", 1 );
      $this->_b = &$b;
      $this->_comment_mode = $comment_mode;

      if ($data["parent_id"] != 0)
       $params["dummy"] = 1;
      else
       $record->Handler( "show", array("_comments"=>1), &$principal ); 

      if ($data["is_tree_only"])
        $tpl->Parse(  "comments.html:Visible", "Preparsed:COMMENTS" );
      else
      {
        if ($data["parent_id"] != 0)
         $tpl->Assign("Href:LevelUp", $tpl->GetValue("Href:Record")."/comments/".$data["parent_id"] );
        $tpl->Parse(  "comments.html:Partial", "Preparsed:COMMENTS" );
      }

      $this->Handler( "_show", $params, &$principal ); 
    }
 
  } 
  else return $this->NotFound("CommentNotFound");

  return GRANTED;

?>