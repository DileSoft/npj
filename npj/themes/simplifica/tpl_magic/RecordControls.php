<?php

  if ($tpl->GetValue("404"))  return;
  if ($tpl->GetValue("Preparsed:PRINT")) return;
  if (!$rh->principal->IsGrantedTo("noguests")) return;


  $record_object = &$rh->object;
  if ($record_object->class == "account")
  {
    $record_object = &new NpjObject( $rh, $rh->object->npj_account.":" );
    $record_object->Load(3);
  }
  if ($record_object->class == "comments")
  {
    $record_object = &new NpjObject( $rh, $rh->object->RipMethods( $rh->object->npj_object_address ));
    $record_object->Load(3);
  }

  $menu = array();
  if ($record_object->class == "record")
  {
    switch ($record_object->GetType())
    {
      case RECORD_POST:
                             $menu = array(
                                 "edit"   => "Правка",
                                 // !!!! versions
                                 "rights" => "Доступ",
                                 "delete" => "Удалить",
                                 "subscribe" => "Подписка",
                                          );
                             break;


      case RECORD_DOCUMENT:
                             if ($record_object->tag == "") 
                             {
                               $menu = array(
                                   "post"      => "Написать в журнал...",
                                   "subscribe" => "Подписка на журнал",
                                            );
                             }
                             else
                             if ($record_object->data["is_keyword"])
                             {
                               $menu = array(
                                   "post"      => "Написать в рубрику...",
                                   "subscribe" => "Подписка на рубрику",
                                            );
                             }
                             break;
                        
    }


    $access = array();
    // in-future: !!!!!!!!! allow edit posts by some free rule
    $access["edit"]   = $record_object->HasAccess( $rh->principal, "owner" ); 
    $access["rights"] = $record_object->HasAccess( $rh->principal, "owner" );
    $access["delete"] = $record_object->HasAccess( $rh->principal, "owner" );
    $access["subscribe"] = true;

    switch ($rh->account->data["account_type"])
    {
      case ACCOUNT_USER:
                            $access["post"] = $rh->account->HasAccess( $rh->principal, "owner" );
                            break;

      default:
                            $access["post"] = $rh->account->HasAccess( $rh->principal, "rank_greater", GROUPS_LIGHTMEMBERS );
                            break;
    }
    
    $_menu = array();

    if (($record_object->method != "show") && ($record_object->method != "default"))
    {
      $_menu[ $record_object->Href("!/") ] = array(-1, "Просмотр");
    }

    foreach( $menu as $k=>$v )
     if ($access[ $k ])
     {
        $_menu[ $record_object->Href("!/".$k) ] = array( $k, $v );
     }

    $menu = array();

    foreach( $_menu as $k=>$v )
    {
      $menu[$k]["href"] = $k;
      $menu[$k]["text"] = $v[1];
      $menu[$k]["title"] = $record_object->method == $v[0]; 
    }


  }
  //$debug->Error_R( $_menu );

  $list = &new ListSimple( $rh, $menu );
  $list->implode=true;
  echo $list->Parse( "design/controls.html:List" );



?>
