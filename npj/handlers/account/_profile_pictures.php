<?php

 // получаем данные
 $data = $this->Load(2);
 if (!is_array($data)) return $this->NotFound("AccountNotFound");

 // выводим список
 $sql = "select pic_id, user_id, have_big, have_small, description from ".$rh->db_prefix.
        "userpics where user_id=".$db->Quote($data["user_id"]);
 $rs = $db->Execute( $sql );
 $a = $rs->GetArray();
 $b = array();

 foreach ($a as $k=>$v)
  $b[$k] = array(
              "pic_id"      => $v["pic_id"],
              "have_small"  => ($v["have_small"]!=""),
              "have_big"    => ($v["have_big"]!=""),
              "description" => htmlspecialchars($v["description"]),
              "Link:pic_big"=> $rh->user_pictures_dir.$this->data["user_id"]."_big_".
                               $v["pic_id"].$v["have_big"],
              "Link:pic_small"=> $rh->user_pictures_dir.$this->data["user_id"]."_small_".
                               $v["pic_id"].$v["have_small"],
              "Link:default"   => $this->Href( $this->name.":profile/pictures/default/".$v["pic_id"] ),
              "Link:remove"    => $this->Href( $this->name.":profile/pictures/remove/".$v["pic_id"] ),
              "is_default"   => ($v["pic_id"] == $data["_pic_id"]),
                );

  $tpl->LoadDomain( array(              
              "Form:edit"      => $state->FormStart( MSS_POST, $this->_NpjAddressToUrl( $this->name.":profile/pictures/edit" )),
              "/Form"          => $state->FormEnd(),
              )    );

 $rh->UseClass( "ListObject", $rh->core_dir );
 $list = &new ListObject( &$rh, $b );

 // !!!! Preparsed:Title

 if (!$this->HasAccess( &$principal, "owner" )
     && 
     !($this->HasAccess( &$principal, "acl_text", $rh->node_admins ) && 
       (($this->npj_account == $rh->node_user) || ($this->npj_account == $rh->guest_user)))
    ) 
   $list->Parse( "user_pictures.html:List", "Preparsed:CONTENT" );
 else
 { 
   $t = $tpl->theme; $tpl->theme = $rh->theme;
   $list->Parse( "user_pictures.owner.html:List", "Preparsed:CONTENT" );
   $tpl->theme = $t;
 }

?>