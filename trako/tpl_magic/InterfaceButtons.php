<?php
  // helpful block
  $trako = &$rh->modules["trako"]["&instance"];
  $account = &new NpjObject( &$rh, $object->npj_account );
  $account->Load(2);
  $TE = &$trako->GenerateTemplateEngine( $trako->config["template_engine"] );

  if (!isset($trako->current_issue)) return "";

  // Панель интерфейса справа
  $actions = array( 
                    "issue_edit_"        => "edit",
                    "issue_assign_self"  => "assign_self",
                    "issue_subscribe"    => "subscribe",
                    );

  // what is granted
  $granted = array();
  if ($trako->config["states"][$trako->current_issue["state"]]["block"] != "*")
  {
    foreach ($actions as $k=>$v)
    {
      $forbidden = 1;
      if ($trako->HasAccess( &$rh->principal, &$account, $trako->current_issue, $v ))
        $forbidden = 0;
      if ($v == "assign_self")
        if ($trako->current_issue["developer_id"] == $rh->principal->data["user_id"])
         $forbidden = 1;
      if ($v == "subscribe")
        if (!$rh->principal->IsGrantedTo("noguests"))
         $forbidden = 1;

      if (!$forbidden) $granted[$k] = $k;
    }
  }

  // дополнить состояниями
   $state_ranks = $trako->config["states"][$trako->current_issue["state"]]
                                ["to"];
   foreach( $state_ranks as $to_state=>$ranks )
     foreach( $ranks as $rank )
       if ($trako->_HasAccess( &$rh->principal, &$account, $trako->current_issue, $rank ))
         $granted["issue_state/".$to_state] = "issue_state_to_".$to_state;

  // get names from msgset
  $base = $account->npj_object_address.":".$trako->config["subspace"]."/".
          $trako->current_issue["issue_no"];
  $names = array();
  foreach($granted as $k=>$v)
  {
    if ($k == "issue_subscribe")
    {
       // загрузить текущее состояние подписки
       $sql = "select object_id from ".$rh->db_prefix."subscription ".
              " where object_class=".$db->Quote("record").
              " and   object_method=".$db->Quote("comments").
              " and   method_option=".$db->Quote("").
              " and   user_id=".$db->Quote($rh->principal->data["user_id"]).
              " and   object_id=".$db->Quote( $trako->current_issue["record_id"] );
       $rs  = $db->Execute( $sql );
       $a   = $rs->GetArray();
       if (sizeof($a)) $v = "issue_unsubscribe";
    }

    $to = substr($k,6);
    if ($to == "view") $to = "";
    else $to="/".$to;

    $names[] = array(
             "href"  => $object->Href($base.$to),
             "title" => $k,
             "text"  => $tpl->message_set["Trako.actions"][$v],
                    );
  }

  // parse
  $list = &new ListSimple( &$rh, $names);
  $list->tpl = &$TE;
  echo $list->Parse("interface_buttons.html:List");



?>