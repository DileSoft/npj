<?php
/*
    HelperDigest( &$rh, &$obj ) -- Хелпер для черновика дайджеста
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   - умеет расшифровывать параметры из урла into $this->request_params[]
   - умеет проверять валидность параметров и подгонять их (используется также в npj/actions/digest.php)
   - заполняет body
   - записывает в рары информацию о дайджесте

=============================================================== v.1 (Kuso)
*/

class HelperDigest extends HelperDocument
{
  var $digest_bodies;

  // -----------------------------------------------------------------
  //  - заполняем таг и заголовок дайджеста
  //  - вызываем метод для заполнения body
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $new_groups = &HelperDocument::TweakForm( &$form_fields, &$group_state, $edit ) ;

    if ($edit) return $new_groups;

    // 1. заполняем таг дайджеста по маске
    $today = date( "dmY" );
    $tag = $this->request_params["targetmask"].$today;
    foreach( $new_groups as $k=>$v )
     foreach( $new_groups[$k] as $kk=>$vv )
      if ($new_groups[$k][$kk]->config["field"] == "tag")
       $new_groups[$k][$kk]->config["default"] = $tag;

    // 2. получить тела записей, входящих в дайджест
    $this->digest_bodies = &$this->LoadDigest();

    // 3. заполняем заголовок дайджеста
    $feed_obj = &new NpjObject( &$this->rh, $this->request_params["feed"] );
    $feed_data = $feed_obj->Load(2);
    $account = &new NpjObject( &$rh, $obj->npj_account );
    $account_data = $account->Load(3);
    $subject = $account_data["template_digest"];
    $subject = str_replace( "{subject}", $feed_data["subject"],                    $subject );
    $subject = str_replace( "{tag}",     $feed_data["tag"],                        $subject );
    $subject = str_replace( "{npj}",     rtrim($feed_obj->npj_object_address,":"), $subject );
    $subject = str_replace( "{from}",    $this->request_params["dtfrom"],          $subject );
    $subject = str_replace( "{to}",      $this->request_params["dtto"],            $subject );
    foreach( $new_groups as $k=>$v )
     foreach( $new_groups[$k] as $kk=>$vv )
      if ($new_groups[$k][$kk]->config["field"] == "subject")
       $new_groups[$k][$kk]->config["default"] = $subject;

    // 4. заполняем тело дайджеста
    $_new_groups = &$this->TweakBody( &$new_groups, &$group_state, $edit ) ;

    // 5. добавить скрытый параметр, хранящий свойства дайджеста
    // 5.1. собрать параметр
    $params = $this->request_params;
    $digest_data = array();
    $digest_data[0] = $params["feed"];
    $digest_data[1] = $this->_ParseDatetime($params["dtfrom"]); 
    $last_dt = strtotime($this->digest_bodies[ sizeof($this->digest_bodies)-1 ]["server_datetime"]);
    $digest_data[2] = date("Y-m-d H:i:s", strtotime("+1 sec", $last_dt)); 
    $filters = array( 0, "announce", "events", "documents" ); 
    $digest_data[3] = array_search($params["filter"], $filters);
    // 5.2. добавить поле
    $_new_groups["body"][] = &new FieldString( &$rh, array(
                            "field" => "digest_data",
                            "db_ignore" => 1,
                            "readonly"  => 1,
                            "default"   => implode(",",$digest_data),
                            "tpl_row" => "form.html:Row_Hidden", 
                           ) );

    $this->rh->debug->Trace("Form tweaked");
    return $_new_groups;
  }

  // -----------------------------------------------------------------
  //  - внутренняя.
  //  - переводим из формата ДД.ММ.ГГГГ в ГГГГ-ММ-ДД
  function _ParseDatetime( $dt )
  {
    $_dt   = explode(" ", $dt);
    $_date = explode(".", $_dt[0]);
    if (sizeof($_dt) > 1)
    {
      $_time = explode(":", $_dt[1]);
      return date("Y-m-d H:i:s" , mktime( $_time[0], $_time[1], 0, $_date[1], $_date[0], $_date[2] ) );
    }
    return date("Y-m-d" , mktime( 0, 0, 0, $_date[1], $_date[0], $_date[2] ) );
  }
  // -----------------------------------------------------------------
  //  - загрузить subject, body и другие важные поля из БД 
  //    для каждого из сообщений, попадающих в дайджест
  //  - вызываем {{Feed mode="digest"}}
  function &LoadDigest()
  { 
    // 1. если строим дайджест "со времен предыдущего", то выясняем точно это время
    if ($this->request_params["dtlast"])
    {
      $db = &$this->rh->db; $rh = &$this->rh;
      $rs = $db->SelectLimit( "select digest_dtto from ".$rh->db_prefix."records as r, ".
                              $rh->db_prefix."records_rare as rr where r.record_id = rr.record_id and ".
                              " r.type = ".RECORD_DOCUMENT." and r.is_digest > 0 order by digest_dtto desc", 1 );
      if ($rs->RecordCount() > 0)
      {
       $this->request_params["_dtfrom"] = $rs->fields["digest_dtto"];
       $this->request_params["dtfrom"] = date("d.m.Y", $rs->fields["digest_dtto"]);
      }
      else
       $this->request_params["_dtfrom"] = $this->_ParseDatetime($this->request_params["dtfrom"]);

      $this->request_params["_dtto"]   = $this->_ParseDatetime($this->request_params["dtto"])." 23:59:59";
    } 
    else
    {
      $this->request_params["_dtfrom"] = $this->_ParseDatetime($this->request_params["dtfrom"]);
      $this->request_params["_dtto"]   = $this->_ParseDatetime($this->request_params["dtto"])." 23:59:59";
    }

//    $this->rh->debug->Error( $this->request_params["_dtfrom"] );

    // 2. вызываем {{feed mode="digest"}}
    $pass_thru = array( "feed" => "for", 
                        "_dtfrom"=>"dtfrom", "_dtto"=>"dtto", 
                        "filter" => 1, );
    $params = array( "mode" => "digest" );
    foreach( $pass_thru as $k=>$v )
     $params[  is_numeric($v)?$k:$v ] = $this->request_params[$k];

    $this->obj->Action( "feed", $params, &$this->rh->principal );
    $result = &$this->rh->tpl->GetValue( "Preparsed:DIGEST" );

    // 3. видоизменяем некоторые параметры объектов ленты
    foreach( $result as $k=>$v )
    {
     $result[$k]["created_dt"] = strip_tags( $result[$k]["created_dt"] );
     $result[$k]["edited_dt"]  = strip_tags( $result[$k]["edited_dt"] );
     $result[$k]["user_dt"]    = strip_tags( $result[$k]["user_dt"] );
     $result[$k]["dt"]         = strip_tags( $result[$k]["dt"] );
    }
    return $result;
  }

  // -----------------------------------------------------------------
  //  - специфичное для дайджеста дело -- заполнение "тела черновика"
  function &TweakBody( &$form_fields, &$group_state, $edit=false )
  {
    // 1. меняем форматтер в соответствие с.
    foreach( $form_fields as $k=>$v )
     foreach( $form_fields[$k] as $kk=>$vv )
      if ($form_fields[$k][$kk]->config["field"] == "formatting")
       if ($this->request_params["formatting"] != "default")
        $form_fields[$k][$kk]->config["default"] = $this->request_params["formatting"];
       else
        $this->request_params["formatting"] = $form_fields[$k][$kk]->config["default"];

    // 2. Сборка тела
    $tpl = &$this->rh->tpl; 
    $tpl->Skin( "_digest" ); // >>>>> switch theme to "_digest"

    // 2.1. Сборка кусочков каждой записи по шаблону
    foreach( $this->digest_bodies as $k=>$v )
    {
      $this->digest_bodies[$k]["Digest:body"]     = $tpl->FormatConvert( $v["body"], $v["formatting"], 
                                                                         $this->request_params["formatting"] );
      $this->digest_bodies[$k]["Digest:compiled"] = $this->ComposeDigestPart( &$this->digest_bodies[$k], 
                                                                               $this->request_params["formatting"],
                                                                               $this->request_params["template"] );
    }
    // 2.2. Сборка длинной колбасы
    $list = &new ListObject( &$this->rh, &$this->digest_bodies );
    $body = $list->Parse( $this->request_params["formatting"]."_".$this->request_params["template"].".html:List" );

    $tpl->Unskin();         // <<<<< unswitch theme back
    
    // 3. Записать тело
    $formatters = array( "simplebr" => "body_simpleedit",
                         "wacko"    => "body_wikiedit", // [!!!] Shoo, dirty kukutz! Не мог написать body_wacko =)
                         "rawhtml"  => "body_richedit",    // [!!!] Shoo, dirty kukutz! Не мог написать body_rawhtml =) 
                        );
    // поскольку у нас три поля и каждое называется по-разному, нам надо угадать, в какое из них запаковать исходник
    foreach( $form_fields as $k=>$v )
     foreach( $form_fields[$k] as $kk=>$vv )
      if ($form_fields[$k][$kk]->config["field"] == $formatters[$this->request_params["formatting"]])
       $form_fields[$k][$kk]->config["default"] = $body;

    return $form_fields;
  }

  // -----------------------------------------------------------------
  //  - сборка кусочка дайджеста -- одного сообщения
  //  - $data -- preparsed array() этого сообщения
  //  - TemplateEngine уже skinned на дайджесты к этому моменту. Структура темы "digest"
  //      * npj/themes/_digest/wacko_default.html:Item -- одно сообщение (используется здесь)
  //      * npj/themes/_digest/wacko_default.html:List            -- список (используется выше)
  function ComposeDigestPart( &$data, $formatting, $template )
  {
    $this->rh->tpl->LoadDomain( &$data );
    return $this->rh->tpl->Parse( $formatting."_".$template.".html:Item" );
  }


  function ValidityCheck( &$params, &$object )
  {
  // проверяем валидство параметров ------------------------------------------------------------------------------
  // feed
     if (isset($params["for"])) $params["feed"] = $params["for"];
     if (strpos($params["feed"], ":") === false) $params["feed"].=":";
     $target = $object->_Load( $params["feed"], 1 );
     if (!is_array($target)) $params["feed"] = "";
  // targetmask
     if (!isset($params["targetmask"])) $params["targetmask"] = "Дайджесты/От"; // !!! перенести в мессаджсет/конфиг
  // DTLAST
     if ($params["dtlast"] != 0)
      $params["dtlast"]=1;
  //  - dtfrom/dtto
     {
       if (isset($params["dtfrom"])) $dtfrom = strtotime( $this->_ParseDatetime( $params["dtfrom"] ));
       else                          $dtfrom = strtotime( "-1 month" );
       if (isset($params["dtto"])  ) $dtto = strtotime( $this->_ParseDatetime( $params["dtto"] ));
       else                          $dtto = time();
       if ($dtfrom > $dtto)
       { $_t = $dtfrom; $dtfrom = $dtto; $dtto = $dtfrom; }
       $params["dtfrom"] = date("d.m.Y", $dtfrom );
       $params["dtto"]   = date("d.m.Y", $dtto   );
     }
  // filter
     $filters = array( 0, "announce", "events", "documents" ); 
     $params["filter"] = strtolower( $params["filter"] );
     if (!in_array($params["filter"], $filters))
      $params["filter"] = 0;
  // mode
     $modes = array( "simple", "form" ); 
     $params["mode"] = strtolower( $params["mode"] );
     if (!in_array($params["mode"], $modes))
      $params["mode"] = "simple";

  // MODE:simple
     if ($params["mode"] == "simple")
     {
  //  - template
       $templates = array( "default", "default_users", "full", "full_users", ); 
       $params["template"] = strtolower( $params["template"] );
       if (!in_array($params["template"], $templates))
        $params["template"] = "default";
  // - formatting
       $formatters = array( "default", "wacko", "rawhtml", "simplebr" ); 
       $params["formatting"] = strtolower( $params["formatting"] );
       if (!in_array($params["formatting"], $formatters))
        $params["formatting"] = "default";
     }
  // MODE:form
     if ($params["mode"] == "form")
     {
       $params["template"]   = "default";
       $params["formatting"] = "rawhtml";
  //  - html skins
       $skins = array( "default", "users", ); 
       $params["html"] = strtolower( $params["html"] );
       if (!in_array($params["html"], $skins))
        $params["html"] = "default";
     }

    return $params;
  }

  // -----------------------------------------------------------------
  //  - проверяем валидность полей из $params (копия кода из actions/digest.php)
  //  - проверяем наличие адреса, указанного в "feed"
  function ParseRequest( $params ) 
  { 
    // проверяем валидство параметров (copy from {{Digest}} action) ------------------------------------------------------------------------------
    $params = $this->ValidityCheck( $params, &$this->obj );

    // extended validity check -------------------------------------------------------------------------
    $feed_obj = &new NpjObject( &$this->rh, $params["feed"] );
    $feed_data = $feed_obj->Load(2);
    if (!is_array($feed_data)) $params["feed"] = $this->obj->npj_account.":";
    else                       $params["feed"] = $feed_obj->npj_object_address;

    // copy params into object
    $pass_thru = array( "feed", "targetmask", "dtlast", "dtfrom", "dtto", "filter", 
                        "mode", "template", "formatting", "html" );
    foreach( $pass_thru as $v )
     $this->request_params[$v] = $params[$v];

    $this->rh->debug->Trace("request params parsed");

    HelperDocument::ParseRequest( $request );
  }

  // -----------------------------------------------------------------
  //  - установка is_digest = 1
  //  - прописывание в "редкие поля" параметром дайджеста
  //    поля сохраняются в HelperRecord
  function Save( &$data, &$principal, $is_new=false ) 
  { 
    if (!$data["is_digest"]) $data["is_digest"] = 1; //plain digest

    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $debug->Trace("DIGEST HELPER NOT WASTED");

    $owner = $obj->owner; // пространство, в котором находимся -- возможно, неправильное!
                          // должно быть загружено
    if ($data["digest_data"])
    {
      // формат: feed,dtfrom,dtto,filter
      $digest_data = explode(",", $data["digest_data"]);
      // a. Проверка, существует ли документ с указанным супертагом -- рубрика, для которой собирался дайджест
      $supertag = $obj->_UnwrapNpjAddress($digest_data[0]);
      $announced = &new NpjObject( &$rh, $supertag );
      $announced_data = $announced->Load(2);
      if (is_array($announced_data) && $announced_data["type"] == RECORD_DOCUMENT)
      {
      // б. Если да, то надо записать в рары супертаг этого документа
        $this->rare["announced_id"]                 = $announced_data["record_id"];
        $this->rare["announced_supertag"]           = $supertag;
        $this->rare["announced_title"]              = $announced_data["subject"];
        $this->rare["announced_comments"]           = 0;
        $this->rare["announced_disallow_comments"]  = 0;
      } else
        $data["is_digest"] = 0; // это становится всего лишь ординарным документом
      // в. надо записать и периодику дайджеста тоже
      $this->rare["digest_dtfrom"] = $digest_data[1];
      $this->rare["digest_dtto"  ] = $digest_data[2];
      $this->rare["digest_filter"] = $digest_data[3];
    }

    // 2. вызовем родительский Save( d,p )
    HelperDocument::Save( &$data, &$principal, $is_new );
  }

// EOC { HelperDigest }
}


?>