<?php

$data = $this->Load( 2 ); // все поля без блобов
$parent_npj = "/".$this->name;
if (is_array($data))  $record = &$this;
else
{
  while (!is_array($data)) 
  {
    $ps = strrpos($parent_npj, "/");
    if ($ps !== false) $parent_npj = substr($parent_npj, 0, $ps);
    else return $this->Forbidden("BrokenAccount"); 
    $data = $this->_Load( $this->npj_account.":".$parent_npj, 2 );
  }
  $record = &new NpjObject( &$rh, $this->npj_account.":".$parent_npj );
  $data = $record->Load( 2 );
}
$this->record = &$record;
$this->tag = $data["tag"]; 

$tag="1";
$newrecord = &new NpjObject( &$rh, $this->npj_account.":".$tag );
$newrecord->parent = &$this->record;

if ($params[0] == "announce") // это ж анонс документа! ========================================================
{
  // если мы находимся, скажем, по адресу kuso@npj:BlaBla/post/announce,
  // то надо перейти на анонсирование документа kuso@npj:BlaBla
  if ($data["tag"] != "")
   $rh->Redirect( $this->Href($principal->data["login"]."@".$principal->data["node_id"].
                              ":post/announce/".$data["supertag"], NPJ_ABSOLUTE) );

  // здесь надо проверить, а есть ли такой документ, прежде чем записывать в поля рары
  array_shift( $params );
  if ((sizeof($params) > 0) && ($params[0]!=""))
  {
    $supertag = $this->_UnwrapNpjAddress(rtrim(implode("/",$params),"/"));
    $newrecord->data["rare"]["announced_supertag"] = $supertag;

    // !!!! refactor this into HelperAnnounce::ParseRequest / TweakBody

    $a_obj = &new NpjObject( &$rh,  $newrecord->data["rare"]["announced_supertag"] );
    $a_data = &$a_obj->Load( 4 );
    if (!is_array($a_data)) 
     $newrecord->data["rare"]["announced_supertag"] = "";
    else
    {
      $a = explode(":",$a_data["supertag"]);
      $newrecord->data["rare"]["announced_supertag"] = $a[0].":".$a_data["tag"];
      $newrecord->data["rare"]["announced_title"]    = $a_data["subject"];
      $newrecord->data["rare"]["announced_supertag_readonly"] = 1;
      $newrecord->data["subject"]    = $tpl->message_set["Announcing"].$a_data["subject"];

      // [!!!] refactor HasAccess from "write" to "source"
      if ($a_obj->HasAccess( &$principal, $this->security_handlers[$data["type"]], "write"))
      {
        $newrecord->data["body"]             = $a_data["body"];
        $newrecord->data["formatting"]       = $a_data["formatting"];
      }                        
      else
        $newrecord->data["body"]       = $tpl->message_set["AccessDenied"];
    }
  }
  $newrecord->data["is_announce"] = 2;
  $newrecord->data["communities"] = array();
  $params = array();
}
else
if ($params[0] == "event") // это ж анонс события!
{
  array_shift( $params );
  $newrecord->data["is_announce"] = 1;
  $newrecord->data["announce_in"] = $params;
  $newrecord->data["communities"] = array();
}
else
if (sizeof($params) > 0)
{
  // указали сообщества, куда постить
  $newrecord->data["communities"] = $params;
}

array_unshift( $params, "post" ); // для того, чтобы постить в сообщества.


// если мы не в журнале, блин
if ($object->data["tag"] != "")
{
  $object_account = &new NpjObject( &$rh, $object->npj_account );
  $object_account->Load(2);
  if ($object_account->data["account_type"] == ACCOUNT_USER) // теперь для того, чтобы постить в ключслово
   $newrecord->post_from = &$object;
  else
   $rh->Redirect( $object->Href( $principal->data["login"]."@".$principal->data["node_id"].":post/".$object->npj_account,
                                 NPJ_ABSOLUTE, STATE_IGNORE ) );

}


return $newrecord->Handler( "edit", $params, &$principal );
?>
