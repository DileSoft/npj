<?php
  // helpful block
  $trako = &$rh->modules["trako"]["&instance"];
  $account = &new NpjObject( &$rh, $object->npj_account );
  $account->Load(2);
  $TE = &$trako->GenerateTemplateEngine( $trako->config["template_engine"] );

  if (!isset($trako->current_issue)) return "";

  // Панель интерфейса справа
  $actions = array( "issue_view"   => "view", 
                    "issue_edit"   => "edit", 
                    "issue_state"  => "status",
                    "issue_log"    => "log",
                    "issue_delete" => "delete",
                    );
  $actions_ = array( "issue_view"   => "view", 
                     "issue_log"    => "view",
                     "issue_delete" => "delete",
                    );

  // what is granted
  $granted = array();
  if ($trako->config["states"][$trako->current_issue["state"]]["block"] != "*")
  {
    foreach ($actions as $k=>$v)
      if ($trako->HasAccess( &$rh->principal, &$account, $trako->current_issue, $v ))
        $granted[] = $k;
  }
  else
  {
    foreach ($actions_ as $k=>$v)
      if ($trako->HasAccess( &$rh->principal, &$account, $trako->current_issue, $v ))
        $granted[] = $k;
  }
  // get names from msgset
  $names = array();
  $base = $account->npj_object_address.":".$trako->config["subspace"]."/".
          $trako->current_issue["issue_no"];
  foreach($granted as $v)
  {
    $to = substr($v,6);
    if ($to == "log") $to.="#log";
    if ($to == "view") $to = "";
    else $to="/".$to;

    $names[] = array(
             "href"  => $object->Href($base.$to, NPJ_ABSOLUTE),
             "title" => $v,
             "text"  => $tpl->message_set["Trako.actions"][$v],
                    );
  }

  // parse
  $list = &new ListCurrent( &$rh, $names, "title", $trako->method );
  $list->tpl = &$TE;
  echo $list->Parse("interface_panel.html:List");



?>