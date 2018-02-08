<?php
//      {{Unknown}}

  if (!$tpl->GetValue("IncludeBuffered:404"))
  {

    if ($params["forbidden"])
    {
      return $tpl->Parse( "actions/404.html:Forbidden" );
    } else
    if ($params["error"])
    {
      $tpl->MergeMessageSet( $rh->message_set."_forbidden_common" );
      $tpl->Assign( "404:Error", $tpl->message_set[ $params["error"] ]);
      return $tpl->Parse( "actions/404.html:CustomError" );
    } else
    if ($params[0])
    {
      $tpl->Assign( "Link:Create", $this->Link( $params[0] ) );
      return $tpl->Parse( "actions/404.html:404" );
    }
  }

  return $tpl->Parse("actions/404.html:Unknown");

?>