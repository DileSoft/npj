<?php

function usermenu_cmp ($a, $b) { if ($a["pos"] == $b["pos"]) return 0;  return ($a["pos"] < $b["pos"]) ? -1 : 1; } 

  $object->Load(3);

 // здесь нет стандартного обработчика, есть две формы
  $tpl->LoadDomain( array(
         "Form:List"    => $state->FormStart(MSS_POST, 
                              $object->_NpjAddressToUrl($object->npj_object_address.":manage/usermenu",0),
                              " name='listform' id='listform' "),
         "/Form"        => $state->FormEnd(),
          )   );

 // Бва-ха-ха! Я обманул вас. Здесь есть и стандартный обработчик
 include( $dir."/!form_usermenu.php" );
 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("AddForm", $result );


 // загружаем меню из базы данных, складываем в массив
 $sql = "select item_id, user_id, pos, title, npj_address from ".$this->rh->db_prefix.
        "user_menu where user_id=".$this->rh->db->Quote( $object->data["user_id"] ).
        " order by pos";
 $rs = $this->rh->db->Execute( $sql );
 $a = $rs->GetArray(); $b = array();
 foreach($a as $k=>$v)
 {
   $b[$k]["user_id"] = $v["user_id"];
   $b[$k]["item_id"] = $v["item_id"];
   $b[$k]["pos"] = $v["pos"];
   $b[$k]["title"] = $v["title"];
   $b[$k]["npj_address"] = $v["npj_address"];
 }
 $object->data["user_menu"] = &$b;

 /// Обработка нашей специальной формы
 if ($_POST["_listform_present"])
 {
  if ($_POST["dont_delete"])
  {
    // repos
    $data = array();
    foreach( $object->data["user_menu"] as $k=>$item )
     $data[] = array( "item_id" => $item["item_id"], "pos"=> 1*$_POST["pos_".$item["item_id"]] );
    usort ($data, "usermenu_cmp"); 
    foreach( $data as $k=>$item )
     $data[$k]["pos"] = $k+1;
    // save
    foreach( $data as $item )
    {
      $sql = "update ".$rh->db_prefix."user_menu set ".
             " pos = ".$db->Quote($item["pos"]).", title=".$db->Quote(substr($_POST["title_".$item["item_id"]],0,250)).
             " where item_id = ".$db->Quote($item["item_id"]);
      $db->Execute( $sql );
    }
  }
  else
  {
    $deletion = "";
    foreach( $object->data["user_menu"] as $item )
     if ($_POST["delete_".$item["item_id"]]) 
     { if ($deletion != "") $deletion.=", ";
       $deletion.= $db->Quote($item["item_id"]);
     }
    if ($deletion != "")
    {
      $sql = "DELETE FROM ".$rh->db_prefix."user_menu where item_id in (".$deletion.")";
      $db->Execute($sql);
    }
  }
  $form->success=true;
 }

 // список нашей супер-особенной формы
 $tpl->theme = $rh->theme;
 $rh->UseClass( "ListObject", $rh->core_dir );
 $list = &new ListObject( &$rh, $object->data["user_menu"] );
 $list->Parse( "edit.usermenu.html:List", "EditDeleteList" );
 $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

 // итоговая сборка странички из двух кусочков, блах.
 $tpl->Parse( "edit.usermenu.html:Page", "Preparsed:CONTENT" );

 $tpl->theme = $rh->skin;
 // если что-то зацепило, надо перегрузить страницу и обновить меню в сессии
 if ($form->success)
 {
   $principal->LoadMenu();
   $rh->Redirect( $rh->Href($object->_NpjAddressToUrl($object->npj_object_address.":manage/usermenu", IGNORE_STATE), IGNORE_STATE ),IGNORE_STATE) ;
 }

?>