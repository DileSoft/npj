<?php
/*
    HelperDigestForm( &$rh, &$obj ) -- Хелпер для продвинутого черновика
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   - работает на форм-процессоре и какой-то мути, которую я ещё даже не придумал.

=============================================================== v.0 (Kuso)
*/
class HelperDigestForm extends HelperDigest
{

  // -----------------------------------------------------------------
  //  - специфичное для дайджеста дело -- заполнение "тела черновика"
  function &TweakBody( &$form_fields, &$group_state, $edit=false )
  {
    $this->rh->UseClass("FieldString", $rh->core_dir);
    $this->rh->UseClass("FieldDT",     $rh->core_dir);

    // 1. меняем форматтер на raw_html
    foreach( $form_fields as $k=>$v )
     foreach( $form_fields[$k] as $kk=>$vv )
      if ($form_fields[$k][$kk]->config["field"] == "formatting")
        $form_fields[$k][$kk]->config["default"] = "rawhtml";

    // 2. скрываем основное текстовое поле
    foreach( $form_fields as $k=>$v )
     foreach( $form_fields[$k] as $kk=>$vv )
      if (strpos($form_fields[$k][$kk]->config["field"],"body") === 0)
        $form_fields[$k][$kk]->config["tpl_row"] = "form.html:Row_Hidden";

    // 3. Сборка тела
    $groups = array();
    $rh = &$this->rh;
    foreach( $this->digest_bodies as $k=>$v)
    {
      $row = "field_string.html:";
      if ($v["formatting"] == "simplebr") $row .= "Textarea_SimpleEdit";
      if ($v["formatting"] == "wacko"   ) $row .= "Textarea_WikiEdit";
      if ($v["formatting"] == "rawhtml" ) $row .= "Textarea_RichEdit";

      $subject = &new FieldString( &$rh, array( "field"   => "subject_".$v["record_id"], 
                                   "name"    => "Form.Digest.Subject", "default" => $v["subject"] ));
      $dt      = &new FieldDT( &$rh, array( "field"        => "dt_".$v["record_id"], "name"=> "Form.Digest.DT",
                                   "default"  => $v["created_datetime"], "readonly" => 1));
      $author  = &new FieldString( &$rh, array( "field"   => "author_".$v["record_id"],  "readonly" => 1,
                                   "name"    => "Form.Digest.Author", "default" => $v["Npj:user"] ));
      if ($v["formatting"] != "rawhtml" ) 
       $body    = &new FieldString( &$rh, array( "field"   => "body_".$v["record_id"], "name"=> "Form.Digest.Body",
                                    "default" => $v["body"], "maxlen" => "4000",
                                    "tpl_data" => $row ));
      else
       $body    = &new FieldString( &$rh, array( "field"   => "body_".$v["record_id"], "name"=> "Form.Digest.Body",
                                    "default" => $v["body"], "maxlen" => "4000", 
                                    "tpl_data" => $row ));

      $groups["digest".$k] = array( $subject, $dt, $author, $body ); 
      $rh->tpl->message_set["Form._Group.digest".$k] = "@".$v["dt"]." &#151; ".$v["Npj:user"].":".$v["tag"]." (".$v["subject"].")";
    }

    // 4. Вставка коллекции групп
    $new_groups = array(); $gs = $group_state; $group_state=""; $c=0;
    foreach( $form_fields as $k=>$v )
    { $new_groups[$k] = &$form_fields[$k]; $group_state.=$gs{$c++};
      if ($k == "ref")
       foreach($groups as $kk=>$vv)
       {
         $new_groups[$kk] = &$groups[$kk]; $group_state.="1";
       }
    }
      $new_groups["ref"][] = &new FieldString( &$rh, array( "field"   => "_digest_hint",  "readonly"=>1, "db_ignore" =>1,
                                                            "name" => "", "default" => $rh->tpl->message_set["DigestHint"],
                                                            "tpl_row" => "form.html:Row_Span" ));

    return $new_groups;
  }

  // -----------------------------------------------------------------
  //  - установка is_digest = 2
  //  - прописывание в "редкие поля" параметром дайджеста
  //    поля сохраняются в HelperRecord
  function Save( &$data, &$principal, $is_new=false ) 
  { 
    if (!$data["is_digest"]) $data["is_digest"] = 2; //plain digest

    // 2. вызовем родительский Save( d,p )
    HelperDigest::Save( &$data, &$principal, $is_new );
  }

  // -----------------------------------------------------------------
  //  - компилирование готового html-тела дайджеста
  //  - патч $data["body"]
  //  - имеет смысл только при адде, но не при едите
  function &PreSave( &$data, &$principal, $is_new=false )
  {
    if (!$is_new) return $data;
    $debug = &$this->rh->debug;

    $this->rh->tpl->Skin("_digest");

      // 1. Снова загрузить дайджест
      $this->digest_bodies = &$this->LoadDigest();

      // 2. Пропатчить его из формы
      foreach( $this->digest_bodies as $k=>$v )
      {
        $body = $this->rh->tpl->FormatConvert($data["body_".$v["record_id"] ], $v["formatting"], "rawhtml");
        $this->digest_bodies[$k]["Form.body"]    = $body;
        $this->digest_bodies[$k]["Form.subject"] = $data["subject_".$v["record_id"] ];
      }
  
      // 3. Собрать список
      $list = &new ListObject( &$this->rh, &$this->digest_bodies );
      $body = $list->Parse( "form_". $this->request_params["html"] .".html:List");
      
    $this->rh->tpl->Unskin();

    // 4. Вклеить боди куда надо
    $data = &HelperDigest::PreSave( &$data, &$principal );
    $data["body"] = $body;
    return $data;
  }

// EOC { HelperDigestForm }
}


?>