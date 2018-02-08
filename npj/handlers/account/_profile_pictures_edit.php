<?php

 // сохранение изменённых имён у картинок

 // получаем данные
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");
 if (!$this->HasAccess( &$principal, "owner" )
     && 
     !($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && 
       (($this->npj_account == $rh->node_user) || ($this->npj_account == $rh->guest_user)))
    ) 
    return $this->Forbidden("UsepicsTune");

 // получаем список
 $sql = "select pic_id, user_id, have_big, have_small, description from ".$rh->db_prefix.
        "userpics where user_id=".$db->Quote($data["user_id"]);
 $rs = $db->Execute( $sql );
 $a = $rs->GetArray();
 $b = array();

 foreach ($a as $k=>$v)
  if ($_POST["description_".$v["pic_id"]])
  {
    $sql = "update ".$rh->db_prefix.
           "userpics set description=".$db->Quote($_POST["description_".$v["pic_id"]]).
           " where pic_id=".$db->Quote( $v["pic_id"] );
    $db->Execute( $sql );
  }

 // редирект на profile/pictures
 $rh->Redirect( $rh->Href($object->_NpjAddressToUrl( "profile/pictures", 1 ),1) );


?>