<?php

  // получаем запись
  $record = $this->record;
  $data = $record->Load(2);
  if (!is_array($data)) return $this->NotFound("RecordNotFound");
  if ($data["type"] != 2) return $this->Forbidden("VersionsOfMessage");
  // проверяем, есть ли доступ
  if (!$record->HasAccess( &$principal, $this->security_handlers[$data["type"]] )) 
    return $this->Forbidden("VersionsForbidden");


  if ($this->name == "all")
  {
   $p = array( "_all" => 1, );
    return $this->Handler("default", $p, &$principal );
  }


  // получаем версию
  $version = &$this->Load( 2 );
  if ($version === "empty") return $this->NotFound("VersionNotFound");

  $tpl->LoadDomain( array(
         "version_id"   => $version["version_id"],
         "datetime"     => $version["edited_datetime"],
         "author"       => $item["edited_user_name"],
         "Link:author"  => $this->Href($item["edited_user_login"]."@".$item["edited_user_node_id"]),
         "version_tag"  => ($item["version_tag"]===""?"":" &#151; ".$item["version_tag"]),
         "Link:Edit"  => $this->Href( preg_replace( "/(\/|:)versions\/([^\/]*)(\/.*)?/i", "$1edit/version/$2", 
                                                    $this->npj_object_address) ),
         "Preparsed:TITLE" => $record->data["subject"],
          )   );

  $tpl->Assign( "body_post", $version["body_post"] );

  $tpl->Parse( "versions_one.html", "Preparsed:CONTENT" );

  return GRANTED;

?>