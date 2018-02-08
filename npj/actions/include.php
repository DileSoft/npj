<?php
//  {{include 
//            page="Kuso@NPJ:PageAddress" 
//            [wrapper="*" -- * все стандартные врапперы: none, div, fieldset, etc. "include" is default] 
//            [wrap_always="0|1"]
//            [no_error="0|1"] - by kukutz
//            [subject="0|1|subject"]}}
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );

  // 0. unwrap
  $supertag = $this->_UnwrapNpjAddress( $params[0] );
  if (strpos($supertag,":") === false)
   $supertag.=":";

  // 1. block POSTs
  if ($this->class != "record") 
   if (!isset($this->record)) return DENIED;
   else $data = $this->record->Load(2);
  else $data = $this->Load(2);
  if ($data["type"] == RECORD_POST)
  {
   $inside = &new NpjObject( &$rh, $supertag );
   $idata = $inside->Load(1);
   $tpl->Assign("IncludeTag"       , rtrim($inside->GetFullTag(), ":"));      
   $tpl->Assign("IncludeSubject"      , $idata["subject"]); 
   $tpl->Assign("Link:Include" , $this->Href( $idata["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ) );
   $tpl->Assign( "IncludeTitle", $params["subject"]?$tpl->GetValue("IncludeSubject"):$tpl->GetValue("IncludeTag") );
   return $tpl->Parse( "actions/include.html:InPost" );
  }

  // 2. cycles
  if (!is_array( $rh->include_hash )) $rh->include_hash = array();
  if ($this->class != "record") $rh->include_hash[ $this->npj_object_address.":" ] = 1;
                           else $rh->include_hash[ $this->npj_object_address ] = 1;
  if (isset($rh->include_hash[ $supertag ]))
  {
    $inside = &new NpjObject( &$rh, $supertag );
    $idata = $inside->Load(2);
    $tpl->Assign("IncludeTag"       , rtrim($inside->GetFullTag(), ":"));      
    $tpl->Assign("IncludeSubject"      , $idata["subject"]); 
    $tpl->Assign("Link:Include" , $this->Href( $idata["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ) );
    $tpl->Assign( "IncludeTitle", $params["subject"]?$tpl->GetValue("IncludeSubject"):$tpl->GetValue("IncludeTag") );
    return $tpl->Parse( "actions/include.html:Cycle" );
  }
  $rh->include_hash[ $supertag ] = 1;

  // 3. load "inside"
  $inside = &new NpjObject( &$rh, $supertag );
  $idata = $inside->Load(3);
  if (!is_array($idata)) return $this->Action( "_404", &$params, &$principal );
  // 3+. проверяем, есть ли доступ
  if (!$inside->HasAccess( &$principal, $this->security_handlers[$idata["type"]] )) 
  {
    if (!$params["no_error"])
    {
     $params["forbidden"] = 1;
     return $this->Action( "_404", &$params, &$principal );
    }
    else
      return "";
  }


  // 4. if smart & readonly -- turn wrapper off!
  if (!$params["wrap_always"]  &&
      ($params["wrapper"] != "menu") &&
      !$inside->HasAccess( &$principal, "acl", "write" ))
      $params["wrapper"] = "none";
  // 4a. if wrapper not set, set it to "include"
  if (!isset($params["wrapper"])) $params["wrapper"] = "include";

  // 5. return standalone body
  // 5a. store action params
    $nowrap = $tpl->GetValue("Action:NoWrap");
    $none = $tpl->GetValue("Action:NONE");
  // 5b. get
  // 5b-. if was like "kuso@npj:changes" -- call action
    if (preg_match( "/^".$rh->NPJ_ACTIONS."$/", $inside->method))
    {
      $inside->params["wrapper"] = "none";
      $result = $inside->Action($inside->method, $inside->params, &$principal);
      $idata["subject"] .= " (".$tpl->message_set["Actions"][$inside->method].")";
      $idata["supertag"] .= "/".$inside->method;
      $noedit=1;
      $tag = rtrim($inside->GetFullTag(), ":") . " (".$tpl->message_set["Actions"][$inside->method].")";
    }
    else
    {
      $noedit=0;
      $result = $inside->Format($idata["body_r"], $idata["formatting"], "post");
      $tag = rtrim($inside->GetFullTag(), ":");
    }
  // 5c. restore
    $tpl->Assign("Action:NoWrap", $nowrap);
    $tpl->Assign("Action:NONE",   $none);

  // 6. Prepare Action:TITLE -- wrapper header
  if ($params["wrapper"] != "none")
  {
    $tpl->LoadDomain( array(
       "NoEdit" => $noedit,
       "IncludeTag"       => $tag,      
       "IncludeSubject"      => $idata["subject"] , 
       "Link:Include" => $this->Href( $idata["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ),
                   )      );
    $tpl->Assign( "IncludeTitle", $params["subject"]?$tpl->GetValue("IncludeSubject"):$tpl->GetValue("IncludeTag") );

    $tpl->Parse( "actions/include.html:Title", "Action:TITLE" );
  }

  return $result;

?>