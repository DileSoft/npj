<?php
/*
  UtilityAction( &$rh )  -- Вспомогательные процедуры для работы с actions
  ---------
   status: under construction
   based on: npj/classes/UtilityRef.php
   created on: 2004-11-18

   пилотная версия, результат рефакторинга:
   http:/in.jetstyle.ru/max/npjdoc/progress/refaktorim/actionsatnpjobject

//вынесенные из NpjObject методы:

   function TypografeToc( $body_toc )

   function BuildToc( $tag, $from, $to, $num, $link=-1 )

   function NumerateToc( $what ) // numerating toc using prepared "$this->post_wacko_toc"

   function NumerateTocCallbackToc( $matches )

   function NumerateTocCallbackP( $matches )

// ------------------------------------------------------------------------------------
// предобработка итемов для типичных акшнов -- http:/npj.ru/kuso/npj/refaktoringactions
   function &_PreparseArray( &$item, $npjobj = NULL )

// ------------------------------------------------------------------------------------
// предобработка аккаунтов для типичных акшнов -- http:/npj.ru/kuso/npj/refaktoringactions
   function &_PreparseAccount( &$acc )

// ------------------------------------------------------------------------------------
// * подключение и вызов нужного обработчика actions
// * инклуд и вызов алгоритма вывода действия -- http:/npj.ru/kuso/npj/refaktoringactions
   function _ActionOutput( &$data, &$params, $default="list", $npjobj = NULL )

// * выполнение запроса на получение тел для "records"
   function GetRecordBodies ( &$record_ids, $db_fields_mode = "", $is_digest = false, $order )



   function BuildTree( &$obj, $root, &$defaults, &$hash, &$raw_subtree )

   
  * &NpjToRecords( &$obj, &$principal, $kwds ) -- преобразование строки вида "kuso@npj; test@npj hoohoo@npj:for" 
                                                в массив из записей
         * $obj       -- контекст для преобразования
         * $principal -- проверяем, есть ли у него доступ на акшны
         * $kwds      -- список нпж-адресов, абсолютных или относительно контекста

  * ComposeRefQueryPart( &$account, &$keywords, $record_field = "r.record_id", 
                         $method="or", $facet_keywords_all = NULL )
                         -- сборка трёх частей SQL-запроса для того, чтобы ограничить выборку 
                            согласно рубрикации по ключсловам
         * $account      -- аккаунт для работы метода "facet" и для завязки с НПЖ
         * $keywords     -- полученный предыдущим методом список рубрик
         * $record_field -- как называется поле, по которому проверяем принадлежность строки запроса рубрикатору
         * $method       -- and, or, facet
         * $facet_keywords_all -- если не дан, то метод получает ключслова для аккаунта и формирует "фасеты" по этой выборке
                                  иначе за основу берётся этот список -- перечень ТАГОВ
                                  актуально только для выборки типа "facet"
                                  

=============================================================== v.1 (Kuso)
*/

class UtilityAction
{
  var $tocs = array(); // cache for TOC
  var $tocs_numerated = array(); // cache for TOC`s numeration flag
  var $toc_context = array(); // build-toc anti-recursion
  // for use in "feeds", "forums", etc.
  // use:
  // $sql = "select ".$__db_record_fields." from ". $__db_record_tables." where ..."
  var $__db_record_bodies = array( 0 =>"", 1 => " r.body_post, r.crossposted, r.keywords, r.filter, " );
  var $__db_record_fields;
  var $__db_record_tables;

  var $paragrafica_styles = array(
     "before"  =>
      array( "_before"=>"", "_after"=>"", "before"=> "<span class='pmark'>[##]</span><br />", "after"=>"" ),
     "after"  =>
      array( "_before"=>"", "_after"=>"", "before"=> "", "after"=>" <span class='pmark'>[##]</span>" ),
     "right" =>
      array( "_before"=>"<div class='pright'><div class='p-'>&nbsp;<span class='pmark'>[##]</span></div><div class='pbody-'>", "_after"=>"</div></div>", "before"=> "", "after"=>"" ),
     "left" =>
      array( "_before"=>"<div class='pleft'><div class='p-'><span class='pmark'>[##]</span>&nbsp;</div><div class='pbody-'>", "_after"=>"</div></div>", "before"=> "", "after"=>"" ),
                                );
  var $paragrafica_patches = array(
     "before" => array("before"),
     "after"  => array("after"),
     "right"  => array("_before"),
     "left"  => array("_before"),
                                 );

  function UtilityAction ( &$rh )
  {
    $this->rh = &$rh;
    $this->__db_record_fields =
           " r.record_id, r.record_id as id, r.subject, subject_post, tag, supertag, ".
           " r.user_id, edited_user_name, edited_user_login, edited_user_node_id, ".
           " r.commented_datetime, r.created_datetime, r.created_datetime as server_datetime, ".
           " r.edited_datetime, r.user_datetime, r.by_module, ".
//         " r.body_post, r.crossposted, r.keywords, ".
//         " r.filter, ".
//           ($is_digest?"body, ":"").
           " r.number_comments, r.disallow_comments, ".
           " r.group1, r.group2, r.group3, r.group4, ".
           " r.type, r.is_digest, r.formatting, ".
           " r.is_announce, ".
           " version_tag, is_parent, depth, r.disallow_replicate, ".
           " r.pic_id, ".
           " r.last_comment_id, ".

           // comments
           " c.user_id       as comment_user_id, ".
           " c.user_login    as comment_user_login, ".
           " c.user_name     as comment_user_name, ".
           " c.user_node_id  as comment_user_node_id, ".
           " c.created_datetime as comment_datetime, ".

           // rares
           " rr.announced_id, rr.announced_comments, rr.announced_disallow_comments, ".
           " rr.announced_supertag, rr.announced_title,".
           " rr.replicator_user_id ";


    $this->__db_record_tables =
           $this->rh->db_prefix."records as r ".
           " left join ".
           $this->rh->db_prefix."records_rare as rr on rr.record_id=r.record_id ".
           " left join ".
           $this->rh->db_prefix."comments as c on c.comment_id=r.last_comment_id ";


  }

//=================== QQ proudly presents: ToC =====================

  function TypografeToc( $body_toc )
  {
    $toc = explode( "<poloskuns,row>", $body_toc );
    foreach($toc as $k=>$v)
     $toc[$k] = explode("<poloskuns,col>", $v);
    foreach( $toc as $k=>$v )
     if ($toc[$k][2] < 66666) // this is Hx
       $toc[$k][1] = $this->rh->tpl->Format( $toc[$k][1], "typografica" );
    foreach($toc as $k=>$v)
     $toc[$k] = implode("<poloskuns,col>", $v);
    $toc = implode( "<poloskuns,row>", $toc );
    return $toc;
  }


  function BuildToc( $tag, $from, $to, $num, $link=-1, $npjobj = NULL )
  {
    if ($npjobj) $this->npjobj = $npjobj;

    if (isset($this->tocs[ $tag."-".$from."-".$to ])) return $this->tocs[ $tag ];

    $page = &$this->npjobj->_Load( $tag, 3 );

    if ($link === -1)
     $_link = ($this->npjobj->data["tag"] != $page["tag"])?$this->npjobj->Href($page["tag"], NPJ_ABSOLUTE, STATE_IGNORE):"";
     else $_link = $link;

    $toc = explode( "<poloskuns,row>", $page["body_toc"] );
    foreach($toc as $k=>$v)
     $toc[$k] = explode("<poloskuns,col>", $v);
    $_toc = array();

    foreach($toc as $k=>$v)
     if ($v[2] == 99999) // this is (include)
     {
       if (!in_array($v[0],$this->toc_context))
        if (!($v[0] == $this->npjobj->npj_object_address)) // ???
        {
          array_push($this->toc_context, $v[0]);
          $__toc = $this->BuildToc( $v[0], $from, $to, $num, $link, &$npjobj );
          $_toc = array_merge( (array)$_toc, (array)$__toc );
          array_pop($this->toc_context);
        }
     }
     else
     if ($v[2] == 77777) // this is (p)
     {
       $toc[$k][3] = $_link;
       $_toc[] = &$toc[$k];
     }
     else
     if (($v[2] >= $from) && ($v[2] <= $to))
     {
       $toc[$k][3] = $_link;
       $_toc[] = &$toc[$k];
       if ($page["formatting"] == "wacko")
         $toc[$k][1] = $this->npjobj->Format($toc[$k][1], "post_wacko");
     }

    $this->tocs[ $tag."-".$from."-".$to ] = $_toc;  // сохраняем с рамками требуемой глубины
    $this->tocs[ $tag ] = $_toc; // для других целей
    return $_toc;
  }


  function NumerateToc( $what, $npjobj = NULL ) // numerating toc using prepared "$this->post_wacko_toc"
  // { if (!is_array($this->post_wacko_action)) return $what;
  {
    if ($npjobj) $this->npjobj = $npjobj;

    if (!is_array($this->npjobj->post_wacko_action)) return $what; // << max@ 2004-11-19 >>

    // strip <!--notoc-->...<!--/notoc-->
    $ignored = array();
    {
      $total = preg_match_all("/(<!--notoc-->.*?<!--\/notoc-->)/si", $what, $matches);
      $what = preg_replace("/(<!--notoc-->.*?<!--\/notoc-->)/si", "\201", $what);
      for ($i=0;$i<$total;$i++)
      {
        $ignored[] = $matches[0][$i];
      }
    }

    // #1. hash toc
    $hash = array();
    foreach( $this->npjobj->post_wacko_toc as $v )
     $hash[ $v[0] ] = $v;
    $this->npjobj->post_wacko_toc_hash = &$hash;
    if ($this->npjobj->post_wacko_action["toc"])
    {
      // #2. find all <a></a><hX> & guide them in subroutine
      //     notice that complex regexp is copied & duplicated in formatters/paragrafica (subject to refactor)
      $what = preg_replace_callback( "!(<a name=\"(h[0-9]+-[0-9]+)\"></a><h([0-9])>(.*?)</h\\3>)!i",
                                     array( &$this, "NumerateTocCallbackToc"), $what );
    }
    if ($this->npjobj->post_wacko_action["p"])
    {
      // #2. find all <a></a><p...> & guide them in subroutine
      //     notice that complex regexp is copied & duplicated in formatters/paragrafica (subject to refactor)
      $what = preg_replace_callback( "!(<a name=\"(p[0-9]+-[0-9]+)\"></a><p([^>]+)>(.+?)</p>)!is",
                                     array( &$this, "NumerateTocCallbackP"), $what );
    }

    // return stripped
    $a = explode( "\201", $what );
    if ($a)
    {
      $what = $a[0];
      $size = count($a);
      for ($i=1; $i<$size; $i++)
      {
       $what= $what.$ignored[$i-1].$a[$i];
      }
    }

    return $what;
  }


  function NumerateTocCallbackToc( $matches )
  {
    return '<a name="'.$matches[2].'"></a><h'.$matches[3].'>'.
           ($this->npjobj->post_wacko_toc_hash[$matches[2]][1]?$this->npjobj->post_wacko_toc_hash[$matches[2]][1]:$matches[4]).
           '</h'.$matches[3].'>';
  }


  function NumerateTocCallbackP( $matches )
  {
    $before=""; $after="";
    if (!($style = $this->paragrafica_styles[ $this->npjobj->post_wacko_action["p"] ]))
    { $this->npjobj->post_wacko_action["p"] = "right";
      $style = $this->paragrafica_styles[ "right" ];
    }
    $len = strlen("".$this->npjobj->post_wacko_maxp);
    $link = '<a href="#'.$matches[2].'">'.
            str_pad($this->npjobj->post_wacko_toc_p[ $matches[2] ],$len,"0",STR_PAD_LEFT).
            '</a>';
    foreach ( $this->paragrafica_patches[ $this->npjobj->post_wacko_action["p"] ] as $v )
     $style[$v] = str_replace( "##", $link, $style[$v] );

    return $style["_before"].'<a name="'.$matches[2].'"></a><p'.$matches[3].'>'.
           $style["before"].$matches[4].$style["after"].
           '</p>'.$style["_after"];
  }


// ------------------------------------------------------------------------------------
// предобработка итемов для типичных акшнов -- http:/npj.ru/kuso/npj/refaktoringactions
   function &_PreparseArray( &$item, $npjobj = NULL  )
   {
     if ($npjobj) $this->npjobj = $npjobj;

     if (!is_array($item)) return $item;

     $item["_account"] = substr($item["supertag"], 0, strpos($item["supertag"], ":")+1);

     if (!isset($item["content_count"])) $item["content_count"] = "";

     // subject
     if ($item["subject"] != "") $item["non_empty_subject"] = $item["subject"];
     else if ($item["body_post"])
     {
       $item["non_empty_subject"] = $this->rh->tpl->Format($item["body_post"], "non_empty_subject");
     }
     else
     {
       $item["non_empty_subject"] = $item["_account"].$item["tag"];
     }

     // comment
     if ($item["comment_href"])
     {
       $item["non_empty_subject"] = "Комментарий к записи ".$item["non_empty_subject"];
     }

     // announce
     if (!isset($item["announce"])) $item["announce"] = $item["is_announce"];
     // Link:user, Href:user
// << max@ >>
//     $item["Link:user"] = $this->Link( $item["edited_user_login"]."@".$item["edited_user_node_id"] );
     $item["Link:user"] = $this->npjobj->Link( $item["edited_user_login"]."@".$item["edited_user_node_id"] );
     $item["Link:user_name"] = $this->npjobj->Link( $item["edited_user_login"]."@".$item["edited_user_node_id"], "",
                                                    $item["edited_user_name"] );
     $item["Href:user"] = $this->npjobj->Href( $item["edited_user_login"]."@".$item["edited_user_node_id"], NPJ_ABSOLUTE, STATE_IGNORE );
     $item["Npj:user"] = $item["edited_user_login"]."@".$item["edited_user_node_id"];
     // only for comments!
     if ($item["last_comment_id"])
       $item["Link:comment_user"] = $this->npjobj->Link( $item["comment_user_login"]."@".$item["comment_user_node_id"] );
       $item["Link:comment_user_name"] = $this->npjobj->Link( $item["comment_user_login"]."@".$item["comment_user_node_id"], "",
                                                              $item["comment_user_name"] );
       $item["Href:comment_user"] = $this->npjobj->Href( $item["comment_user_login"]."@".$item["comment_user_node_id"], NPJ_ABSOLUTE, STATE_IGNORE );
       $item["Npj:comment_user"] = $item["comment_user_login"]."@".$item["comment_user_node_id"];

     // dt:
     $item["commented_dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($item["commented_datetime"]));
     $item["created_dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($item["created_datetime"]));
     $item["edited_dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($item["edited_datetime"]));
     $item["user_dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($item["user_datetime"]));
     $item["dt"] = strftime("%H:%M <b>%d.%m.%Y</b>", strtotime($item["datetime"]));
     // userpics
     $item["userpic"] = "<img border=\"0\" src=\"".$this->rh->user_pictures_dir.$item["user_id"].
                        "_big_".$item["pic_id"].".gif\" />";
     $item["userpic_small"] = "<img border=\"0\" src=\"".$this->rh->user_pictures_dir.$item["user_id"].
                        "_small_".$item["pic_id"].".gif\" />";
     // security
     if ($item["group1"])
     {
      switch($item["group2"])
      { case  0: $item["security"]= "custom";   break;
        case -1: $item["security"]= "private";  break;
        case -2: $item["security"]= "friends";  break;
        case -3: $item["security"]= "comm";     break;
        default: $item["security"]= "custom";
      }
      $item["is_public"] = 1;
     }
     else { $item["security"] = "public"; $item["is_public"] = 1; }
     $item["security_title"] = $this->rh->tpl->message_set["Record.Stats.Sec"][ $item["security"] ];

     // Href:tag
     if ($item["_empty_tag"])                                                                              //shitty code by kukutz 09.01.2005 !!!
      $item["Href:tag"]             = $this->npjobj->Href( $item["supertag"], NPJ_RELATIVE, STATE_IGNORE ).$item["comment_href"];
     else
      $item["Href:tag"]             = $this->npjobj->Href( $item["_account"].$item["tag"], NPJ_RELATIVE, STATE_IGNORE ).$item["comment_href"];

     // comments, announced*
     $item["Href:comments_target"] = $item["Href:tag"];
     if ($item["type"] == RECORD_DOCUMENT)
          $item["Href:versions_target"] = $item["Href:tag"];
     else $item["Href:versions_target"] = "";
     if ($item["announce"] == 2)
     {
       $item["disallow_comments"] = $item["announced_disallow_comments"];
       $item["number_comments"]   = $item["announced_comments"];
       $item["Href:announced"]    = $this->npjobj->Href( $item["announced_supertag"], NPJ_ABSOLUTE, STATE_IGNORE );
       $item["Link:announced"]    = $this->npjobj->Link( $item["announced_supertag"], "", $item["announced_title"] );
       $item["Href:comments_target"]     = $item["Href:announced"];
       $item["Href:versions_target"]     = $item["Href:announced"];
     }
     else
     {
       $item["Href:announced"]    = "";
       $item["Link:announced"]    = "";
     }
     if ($item["replicator_user_id"])
     {
       $item["announce"] = "-rep";
       $item["replica"] = "Реплицированное сообщение";  //!!!!
     }
     // зануляем комменты, где заблокано
     if ($item["disallow_comments"]) $item["number_comments"] = 0;

     // кто куда закросспостил
     if ($item["crossposted"] == "!")  $item["crossposted"] = "";
     if ($item["crossposted"] != "")
       $item["crossposted"] = $this->rh->tpl->message_set["Crossposted"].$item["crossposted"];
     if ($item["keywords"] != "")
     {
       $item["keywords"] = $this->rh->tpl->message_set["Keyworded"].$item["keywords"];
       if ($item["crossposted"] != "") $item["keywords"] .= "<br />";
//     if ($item["crossposted"] != "") $item["keywords"] = $item["keywords"]."<br />";
       $item["crossposted"]=$item["keywords"].$item["crossposted"];
     }

     if (!isset($item["replica"])) $item["replica"] = "";

     // misc (для кустомной перегрузки)
     return $item;
   }


// ------------------------------------------------------------------------------------
// предобработка аккаунтов для типичных акшнов -- http:/npj.ru/kuso/npj/refaktoringactions
   function &_PreparseAccount( &$acc, $npjobj = NULL  )
   {
     if ($npjobj) $this->npjobj = $npjobj;

     // hrefs
     $npj = $acc["login"]."@".$acc["node_id"];
     if ($acc["node_id"] != $this->rh->node_name) $npj.="/".$this->rh->node_name;
     $acc["Href:account"] = $this->npjobj->Href( $npj );
     $acc["Link:account"] = $this->npjobj->Link( $npj );
     // shortnames
     if ($acc["parent"])
       $acc["short_login"] = preg_replace("/[^\-]+-/i", "", $acc["login"]);
     else
       $acc["short_login"] = $acc["login"];

     $acc["non_empty_bio"] = $this->rh->tpl->Format( $acc["bio"], "non_empty_abstract" );

     return $acc;
   }


// ------------------------------------------------------------------------------------
// инклуд и вызов алгоритма вывода действия -- http:/npj.ru/kuso/npj/refaktoringactions
   function _ActionOutput( &$data, &$params, $default="list", $npjobj = NULL )
   {
     if ($npjobj) $this->npjobj = $npjobj;

     $modes = array( "feed"=>1, "list"=>1, "periodical"=>1 );
     if (!isset($modes[$params["mode"]])) $params["mode"] = $default;

     include_once( $this->rh->npj_actions_dir."_".$params["mode"].".php" );
     switch ($params["mode"])
     {
       case "accounts":   return npj_object_action_accounts( &$this->npjobj, &$data, &$params );
       case "forum":      return npj_object_action_forum( &$this->npjobj, &$data, &$params );
       case "feed":       return npj_object_action_feed( &$this->npjobj, &$data, &$params );
       case "list":       return npj_object_action_list( &$this->npjobj, &$data, &$params );
       case "periodical": return npj_object_action_periodical( &$this->npjobj, &$data, &$params );
     }
   }
// ------------------------------------------------------------------------------------
/*
   выполнение запроса на получение тел для "records"     << max@jetstyle 2004-11-24 >>
   tip: вызывается акшнами forum, feed, tree, changes
     $db_fields_mode
       0 - без тел
       1 - вместе с телами
*/


   function GetRecordBodies ( &$record_ids, $params = NULL, $db_fields_mode = 1, $is_digest = false, $order, $pagesize = NULL )
   {

   // $params -- массив $params вызывающего акшна
   // при его передаче сохраняется пользовательская настройка,
   // и мешает форсированию параметра $db_fields_mode
   // done -- !!!!! НЕ ЗАБЫТЬ отредактировать ВСЕ вызовы этого метода
   // 5. get bodies & stuff. ( code extract from /npj/actions/forum.php )
   // done-- 1. include_once >>> теперь в $this->__db_record_fields
   // done-- 2. в инклюдах сделать переменные в $this->
   // done-- 3. в инклюдах написать, откуда инклюдится и зачем, на пару строчек
   // done-- 4. убрать дублирующийся код в инклюдах
   //   <<max@jetstyle  2004-11-25>>
// << 2004-12-01 max@ >>
     if (is_array($params))
       switch ($params["mode"])
       {
         case "forum":        $db_fields_mode = 1; break;
         case "feed":         $db_fields_mode = 1; break;
         case "list":         $db_fields_mode = 0; break;
         case "periodical":   $db_fields_mode = 0; break;
       }
// << 2004-12-01 max@ />>

      $record_ids_q = array();
      foreach( $record_ids as $k=>$v ) $record_ids_q[] = $this->rh->db->Quote( $v );

      $data = array();
      if (sizeof($record_ids_q))
      {
        $sql =
           "select".
             $this->__db_record_bodies[$db_fields_mode]. // загрузить с телами: [1], или без: [0]
             ($is_digest?"body, ":""). // кажется, оставить так - пока нагляднее, чем переменную создавать
             $this->__db_record_fields.
           " from ".
             $this->__db_record_tables.
             " where r.record_id in (".implode(",",$record_ids_q).") ".
             " order by ".$order; // ." desc" снес дабы совместимость с "фидом" настала
        $this->rh->debug->Trace( "Utility Action::GetRecordBodies(); BODIES: <br />".$sql );
        if ($pagesize)
          $rs = $this->rh->db->SelectLimit( $sql, $pagesize );
        else
         $rs = $this->rh->db->Execute( $sql );
        $data = $rs->GetArray();
      }

     return $data;
   }


  // построение дерева для actions/tree.php ------------------------------------------------------------------
  /*
       - $root        -- корень дерева
  ??   - $defaults    -- массив значений, необходимых для того, чтобы заполнять несуществующие поля
       - $hash        -- хэш дерева по супертагу (содержит существующие страницы)
       - $raw_subtree -- втупую список поддерева (содержит только детей)
   ?? упорядоченность входных данных.
  */
  function BuildTree( &$obj, $root, &$defaults, &$hash, &$raw_subtree )
  {
    // обеспечивает то, что все дети выглядят так "ROOT-ADDRESS"."child_address", дописывая к ROOT недостающую "склейку"
    $c = $root[strlen($root)-1];
    if (($c != ":") && ($c != "/")) $root.="/";
    $tree = array();
    $obj->rh->debug->Trace( '<em>building tree foreach( $raw_subtree as $k=>$v)</em><br />');
 
    foreach( $raw_subtree as $k=>$v)
    {
      // отрежем первый кусочек
      $obj->rh->debug->Trace( "[$k] => ".$v["_tag"] . "<br />");
      $seppos = strpos( $v["_tag"], "/" );
      $part1  = substr( $v["_tag"], 0, $seppos );
      $part2  = substr( $v["_tag"], $seppos+1  );
      $obj->rh->debug->Trace( $root."+".$part1 );

      // если в кэше нет готовой страницы, то нам нужно будет создать болванку
      $rootpart1 = $obj->_UnwrapNpjAddress($root.$part1); // <- это формат "супертаг"
      //$obj->rh->debug->Trace("rootpart1 = $rootpart1");
      if (!isset($hash[$rootpart1]))
      {
        //$obj->rh->debug->Error( $rootpart1 );
        $href = $obj->Href( $rootpart1, NPJ_ABSOLUTE, STATE_IGNORE );
        $hash[ $rootpart1 ] = array_merge(
          (array)$defaults, array(
            "subject"              => $part1,
            "non_empty_subject"    => $part1,
            "tag"                  => $part1,
            "tag1"                 => $part1,
            "tag2"                 => $part1,
            "supertag"             => $rootpart1,
            "Href:tag"             => $href,
            "Href:comments_target" => $href,
            "Href:versions_target" => $href,
            "Href:add"             => $obj->Href( $root, NPJ_ABSOLUTE, STATE_IGNORE )."/add/".$part1, 
            "_childs" => array(),
          ) );
      }
      // породим страницу данного уровня, взяв из хэша
      if (!$hash[$rootpart1]["_intree"])
      {
        $tree[] = &$hash[$rootpart1];
        $hash[$rootpart1]["_intree"] = 1; // пометка ставится, чтобы показать, что сюда мы уже дописали
        $hash[$rootpart1]["_root"] = $rootpart1;
        $hash[$rootpart1]["tag1"] = $part1;
      }
      // впишем детей, если они есть
      if ($part2 != "")
      {
        $raw_subtree[$k]["_tag"] = $part2; // обкорачиваем таг
        $hash[$rootpart1]["_childs"][] = &$raw_subtree[$k];
      }
    }

    // теперь для каждого из детей уйдём в рекурсию =(
    foreach ($tree as $k=>$v)
     $tree[$k]["__childs"] = $this->BuildTree( &$obj, $v["_root"], &$defaults, &$hash, $tree[$k]["_childs"] );

    return $tree;
  }


  // преобразование строки вида "kuso@npj; test@npj hoohoo@npj:for" в массив из записей
  function &NpjToRecords( &$obj, &$principal, $kwds )
  {
    $kwds = str_replace(";", " ", $kwds);
    $kwds = explode( " ", $kwds );
    $keywords = array();
    foreach( $kwds as $k=>$v )
    {
      if ($v)
      {
        $supertag = $obj->_UnwrapNpjAddress( $v );
        $supertag = $obj->RipMethods( $supertag, RIP_STRONG );     // свести до ближайшей записи
        $category = &new NpjObject( &$this->rh, $supertag );
        $category->Load(2);
        if ($category->npj_account != $obj->npj_account)
        {
          $account = &new NpjObject( &$this->rh, $category->npj_account );
          $account->Load(2);
        }
        //  Проверка прав доступа
        if ($category->HasAccess( $principal, "acl", "actions" ))
        {
          $keywords[ $supertag ] = &$category;
          $this->rh->debug->Trace("Category -> ".$supertag );
        }
      }
    }
    return $keywords;
  }

  // сборка трёх частей SQL-запроса для того, чтобы ограничить выборку согласно рубрикации по ключсловам
  function ComposeRefQueryPart( &$account, &$keywords, $record_field = "r.record_id", $method="or", $facet_keywords_all = NULL )
  {
    $debug = &$this->rh->debug;
    $method = strtolower($method);
    // OR and AND
    if (($method == "or") || ($method == "and"))
    {
      $keyword_field = "ref.keyword_id";
      $op = " ".$method." ";
      $result = array(); $f=0;
      foreach( $keywords as $k=>$v )
      {
        $this->rh->debug->Trace("KEYWORD: ".$k );
        $result[] = $keyword_field." = ".$this->rh->db->Quote($v->data["record_id"]); 
      }
      $result = implode( $op, $result );
      return array( "filter_table" => ", ".$this->rh->db_prefix."records_ref AS ref",
                    "filter_where" => " and ref".$fno.".record_id = ".$record_field,
                    "filter"       => " and (".$result.")",
                  );
    }
    else // FACET
    {
      // a. получить "keywords_all"
      if (!isset($facet_keywords_all))
      {
         $sql = "select supertag, tag from ".$this->rh->db_prefix."records where ".
                 "is_keyword=1 and user_id = ".$this->rh->dbQuote($account->data["user_id"]).
                 " order by tag";
         $rs  = $this->rh->dbExecute( $sql );
         $a   = $rs->GetArray();
         $facet_keywords_all = array();
         foreach($a as $k=>$v)
           $facet_keywords_all[] = $v["tag"];
      }
      foreach( $facet_keywords_all as $k=>$v )
       $facet_keywords_all[$k] = $account->NpjTranslit($v);

      // find "facet" part
      foreach( $facet_keywords_all as $k=>$v )
      {
        $facet_keywords_all[$k] = array(
            "facet"     => preg_replace("/\/.*$/","",$v),
            "supertag"  => $v,
                                        );
      }
      // legal facets
      $groups     = array();
      $grouplings = array();
      foreach ($facet_keywords_all as $k=>$v)
      {
        if ($grouplings[$v["facet"]])
          $groups[ $v["facet"] ][] = $v;
        else
          $grouplings[$v["supertag"]] = $v;
      }
      // "etc." facet
      foreach( $grouplings as $supertag=>$v )
       if (!$groups[$supertag])
         $groups["_"][] = $v;

      // побить $keywords по фасетам
      $faceted_keywords = array();
      foreach($keywords as $k=>$v)
      {
        $facet = preg_replace("/^[^:]+:(.*?)\/.*$/","$1",$v->data["supertag"]);
        $this->rh->debug->Trace("KEYWORD ".$v->data["supertag"]." FACET: ".$facet );
        if (!$groups[$facet]) $facet = "_";
        $faceted_keywords[$facet][] = &$keywords[$k];
      }
      $fno="";
      // составить OR часть запроса
      foreach($faceted_keywords as $name => $facet)
      {
        $keyword_field = "ref".$fno.".keyword_id";
        if ($fno=="") $fno=1; else 
        {
          $filter_facet_table.= ", ".$this->rh->db_prefix."records_ref AS ref".$fno;
          $filter_facet_where.= " and ref".$fno.".record_id = ".$record_id;
          $fno++;
        }
        $op = " or ";
        $result = ""; $f=0;
        foreach( $facet as $k=>$v )
        {
          $this->rh->debug->Trace("KEYWORD: ".$v->data["supertag"]. " { ".$v->data["record_id"]." }" );
          if ($f) $result.=$op; else $f=1;
          $result.=$keyword_field." = ".$this->rh->dbQuote($v->data["record_id"]); 
        }
        $faceted_keywords[$name] = "(".$result.")";
        //  -
      }
      // составить AND часть запроса
      $result = ""; $f=0;
      foreach( $faceted_keywords as $k=>$v )
      {
        $op = " and ";
        if ($f) $result.=$op; else $f=1;
        $result.=$v; 
      }
      $filter .= " and (".$result.")";
      return array( "filter_table" => $filter_facet_table,
                    "filter_where" => $filter_facet_where,
                    "filter"       => $filter,
                  );
    }
  }



} // EOC { UtilityAction }

?>