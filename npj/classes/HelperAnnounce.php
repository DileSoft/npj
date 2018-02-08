<?php
/*
    HelperAnnounce( &$rh, &$obj ) -- ’елпер дл€ формы редактировани€ анонсов документов
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   - ƒобавл€ет в группу "јнонс" поле "јнонсируемый документ" и сопутствующие
   - ”бираем галочку "блокировать комментирование"

=============================================================== v.1 (Kuso)
*/

class HelperAnnounce extends HelperEvent
{

  // -----------------------------------------------------------------
  // - добавим в группу announes поле announced_supertag
  // - убираем галочку "блокировать комментирование"
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $new_groups = &HelperEvent::TweakForm( &$form_fields, &$group_state, $edit ) ;

    // 1. «аполн€ем заголовок из $account
    $account = &new NpjObject( &$rh, $obj->npj_account );
    $account_data = $account->Load(3);
    $subject = $account_data["template_announce"];
    $subject = str_replace( "{subject}", $obj->data["rare"]["announced_title"], 
                            $subject );
    $subject = str_replace( "{tag}",     $obj->data["rare"]["announced_supertag"], $subject );
    foreach( $new_groups as $k=>$v )
     foreach( $new_groups[$k] as $kk=>$vv )
      if ($new_groups[$k][$kk]->config["field"] == "subject")
       $new_groups[$k][$kk]->config["default"] = $subject;

    // 3. добавим поле
    $f = &new FieldString( &$rh, array(
                           "field" => "announced_supertag",
                           "maxsize" => 250,
                           "default" => $obj->data["rare"]["announced_supertag"],
                           "readonly" => $obj->data["rare"]["announced_supertag_readonly"],
                           "db_ignore" => 1,
                            ) );
    $f2 = &new FieldString( &$rh, array(
                           "field" => "announced_title",
                           "maxsize" => 250,
                           "default" => $obj->data["rare"]["announced_supertag"],
                           "db_ignore" => 1,
                            ) );
    array_unshift($new_groups["announces"], &$f2 );
    array_unshift($new_groups["announces"], &$f  );
    // 4. надо убрать поле-галочку "блокировать комментирование"
    foreach($new_groups["options"] as $k=>$field)
     if ($field->config["field"] == "disallow") // отрезаем две первые галочки
     {
       array_shift($new_groups["options"][$k]->config["fields"]);
       array_shift($new_groups["options"][$k]->config["fields"]);
     }
    $this->rh->debug->Trace("Form tweaked");
    return $new_groups;
  }

  // -----------------------------------------------------------------
  //  - прописывание в "редкие пол€" ссылки на анонсированный документ
  //    пол€ сохран€ютс€ в HelperRecord
  // -----------------------------------------------------------------
  function &PreSave( &$data, &$principal, $is_new=false ) 
  { 
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $debug->Trace("ANNOUNCE HELPER NOT WASTED");

    $owner = $obj->owner; // пространство, в котором находимс€ -- возможно, неправильное!
                          // должно быть загружено
    // a. ѕроверка, существует ли документ с указанным супертагом
    $supertag = $obj->_UnwrapNpjAddress($data["announced_supertag"]);
    $announced = &new NpjObject( &$rh, $supertag );
    $announced_data = $announced->Load(2);
    if (is_array($announced_data) && $announced_data["type"] == RECORD_DOCUMENT)
    {
    // б. ≈сли да, то надо записать в рары супертаг этого документа
      $this->rare["announced_id"]                 = $announced_data["record_id"];
      $this->rare["announced_supertag"]           = $supertag;
      $this->rare["announced_title"]              = $data["announced_title"];
      $this->rare["announced_comments"]           = $announced_data["number_comments"];
      $this->rare["announced_disallow_comments"]  = $announced_data["disallow_comments"];
    } else
      $data["is_announce"] = 1; // это перестаЄт быть анонсом документа
    // 2. вызовем родительский Save( d,p )
    return HelperEvent::PreSave( &$data, &$principal, $is_new );
  }

// EOC { HelperAnnounce }
}


?>