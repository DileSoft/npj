<?php
/*
    UtilitySkin( &$rh )  -- Вспомогательные процедуры для скинов и организации НПЖ-вывода в целом
    ---------
      - пилотная версия, нуждается в последующем рефакторинге

  * InitContextMenu( &$record ) -- инициализирует текущее контекстное меню для некоторой записи
  * InitPanel( &$object )       -- подготавливает управляющую панель "просмотр/правка/версии"
  * ParsePanel( $granted, $panel, $base, $links, $current="show",
                $Name="Record0", $tplt="panel.html:Panel_Item", 
                $prefix="<div class=\"control_panel\">", 
                $postfix="</div><br clear=\"all\"/>", 
                $separator="&nbsp;| " ) -- вывод подготовленной панели -- возвращает строкой
        - $granted -- { PANEL_OWNER=>1, PANEL_READ=>0, ... }
        - $panel   -- { "edit" => PANEL_EDIT, "rights" => PANEL_OWNER ... } -- ключ массива -- имя картинки метода в том числе. 
                                                                       Используется для всех остальных
        - $base    -- начало ссылки, к нему дописываются $links
        - $links   -- { "edit"=>0, "rights"=>0, "add_friend"=>"manage/add_friend" } -- если нуль, дописываем ключ массива
        - $current -- "текущий" ключ -- название хандлера
        - $Name    -- для поиска текстовых значений в мессаджсете -- $tpl->message_set[$Name."Methods"] == Record0Methods, etc.
        - $tplt    -- название шаблона, по которому будет выводится панель
        - $prefix, $postfix, $separator -- шапка, подножие и разделитель панели

  * AssignRecordStats( &$object ) -- записывает в TemplateEngine все, что нужно для блока рекорд-статов
        - если возвращает false, то выводить ничего не надо
  * ParseRecordRef( &$record, $show_kwds=false, $show_comm=false, 
                    $show_comm_mod=true, $show_announces_for=true ) -- парсит и возвращает шаблон с рефами, верхними или нижними
        - $show_kwds          -- показывать или нет ключслова/рубрики
        - $show_comm          -- показывать ли перечень сообществ, где опубликовано
        - $show_comm_mod      -- показывать ли подробное описание сообществ с блоком модерирования
        - $show_announces_for -- показывать ли блок "Анонсировано в журналах"

  * ParseCommunityFilterFlip( &$record ) -- парсит блок переключателя между фильтрами


  // рефакторинг:
  * написать InitPanel для всех классов объектов отдельные 
  * разбить парс-рефов на их получение, отдельно по записям и разным типам, и на сам парсинг

=============================================================== v.0 (Kuso)
*/
define( "PANEL_OWNER",     0 );
define( "PANEL_ALL",       1 );
define( "PANEL_READ",      2 );
define( "PANEL_WRITE",     3 );
define( "PANEL_COMMENTS",  4 );
define( "PANEL_ACL_READ",  5 );
define( "PANEL_DELETE",    6 );
define( "PANEL_COMMENT_DELETE" ,  7 );
define( "PANEL_JOIN_COMMUNITY" ,  8 );
define( "PANEL_JOIN_WORKGROUP" ,  9 );
define( "PANEL_MODERATORS", 10 );
define( "PANEL_MEMBERS",    11 );
define( "PANEL_COMMENT_ADD",       12 );
define( "PANEL_COMMENT_PARENT",    13 );
define( "PANEL_VERSIONS",    14 );
define( "PANEL_REGISTERED",    15 );
define( "PANEL_FOREIGN",        16 );
define( "PANEL_REPLICATION",    17 );
define( "PANEL_ANNOUNCE",       18 );
define( "PANEL_SUBSCRIBE",      19 );
define( "PANEL_WORKGROUP" ,  20 );
define( "PANEL_OWNER_ADMIN", 21);

class UtilitySkin 
{
  // контекстное меню, ага
  var $context_panelcount = 0;
  var $context_panel1;
  var $context_panel1_param;
  var $context_panel2;
  var $context_panel2_param;
  var $panel = array( "base" => "", "Name" => "", "granted" => array(), 
                      "method" => "", "panel" => array(), "links" => array() );
  var $stats_type;  // "comment", RECORD_POST, RECORD_DOCUMENT, further???
  var $stats_hide = array();
  var $stats_object;


  function UtilitySkin( &$rh )
  {
    $this->rh = &$rh;
  }


  // ------------------------------------------------------------------------------------------------------
  // Парсинг рефов по стандартным шаблонам в стандартные поля
  //  * используемые шаблоны:
  //     - record.ref.html -- :Moderate, :Keywords, :Communities
  //     - record.announce.html:BackRef
  //  * куда записываем результат:
  //     - Record.Stats.Ref
  //     - Record.Announce.Top
  //     - Record.Stats.Ref.RecordId (кажется вспомогательный)
  function ParseRecordRef( &$record, $show_kwds=false, $show_comm=false, $show_comm_mod=true, $show_announces_for=true  )
  {
     $tpl   = &$this->rh->tpl;
     $rh    = &$this->rh;
     $debug = &$this->rh->debug;
     $db    = &$this->rh->db;
     $cache = &$this->rh->cache;
     $state = &$this->rh->state;


     if (!isset($record->data)) 
     return ; //$debug->Error("RecordRef got no record");

     // 1. попробовать взять рефы из кэша
     $refs = $cache->Restore( "recordref", $record->data["record_id"], 1 );
     if ($refs === false)
     {
       // 2. взять рефы из БД
       $sql = "select ref.announce, ref.need_moderation, r.record_id, u.user_id, u.login, u.node_id, u.user_name, r.subject, r.tag, r.supertag ".
              "from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."records as r, ".$rh->db_prefix."users as u ".
              "where ref.record_id=".$db->Quote($record->data["record_id"]).
              " and (priority=0 or ref.announce > 0) and ref.keyword_id=r.record_id and ref.keyword_user_id=u.user_id".
              " order by r.supertag";
       $rs = $db->Execute( $sql ); 
       $a = $rs->GetArray();
       $refs = array( array(), array(), array() ); // 0 - keywords, 1 - communities moderated, 2 - comms notmoderated
       foreach($a as $item)
       {
        $objecto = array(
            "account"      => $item["login"]."@".$item["node_id"],
            "Href:account" => $record->Href( $item["login"]."@".$item["node_id"], NPJ_ABSOLUTE, IGNORE_STATE ),
            "Link:account" => $record->Link( $item["login"]."@".$item["node_id"] ),
            "Form:mod"     => $state->FormStart( MSS_POST, $record->Href( $item["login"]."@".$item["node_id"], NPJ_ABSOLUTE, IGNORE_STATE )
                                                 ."/manage/".$record->data["record_id"]),
            "subject"      => $item["subject"],
            "tag"          => $item["tag"],
            "user_name"    => $item["user_name"],
            "user_id"      => $item["user_id"],
            "is_mod"       => $rh->principal->IsGrantedTo("rank_greater", "account", 
                                                          $item["user_id"], GROUPS_MODERATORS),
            "need_mod"   => 0,
            "done_mod"   => 0,
            "href"         => $record->Href( $item["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ),
            "announce"     => $item["announce"],
                 );
        if ($objecto["is_mod"])
        {
          if ($item["need_moderation"]) $objecto["need_mod"] = 1;
          else                          $objecto["done_mod"] = 1;
        }
         

        if (!$item["need_moderation"])
          {
         if (($item["user_id"] != $record->data["user_id"]) || ($item["tag"] != ""))
         $refs[1*($item["user_id"] != $record->data["user_id"])][] = $objecto;
       
           // помечаем как анонс в собственном журнале
           if (($item["user_id"] == $record->data["user_id"]) && ($item["announce"] > 0)) 
            array_unshift($refs[1], $objecto);
          }
        if ($item["user_id"] != $record->data["user_id"])
   //        if ($objecto["is_mod"])
          $refs[2][] = $objecto;
       }
   //    $debug->Trace_R( $refs );
   //    $debug->Error( $record->data["user_id"] );
       // 3. положить рефы в кэш
       $cache->Store( "recordref", $record->data["record_id"], 1, &$refs );
     }

     $rh->UseClass( "ListObject", $rh->core_dir );
     // 4. сформировать рубрикацию в пределах журнала
     $list = &new ListObject( &$rh, $refs[0] );
     $list->implode = true;
     if ($show_kwds)
      $kwds = $list->Parse( "record.ref.html:Keywords" );
     // 5. сформировать рубрикацию по сообществам
     $list->data = &$refs[1];
     if ($show_comm)
      $comm = $list->Parse( "record.ref.html:Communities" );
     else
     if ($show_comm_mod)
     { $list->implode = false;
       $list->data = &$refs[2];
       $tpl->Assign("Record.Stats.Ref.RecordId", $record->data["record_id"] );
       $comm = $list->Parse( "record.ref.html:Moderate" );
     }
     // 6. сложить всё в общий шаблон
     $tpl->Assign( "Record.Stats.Ref", $kwds.$comm );


     // ---------------------------------------------------------------------- АНОНСИРОВАНИЕ ДОКУМЕНТА
     if (($record->GetType() == RECORD_POST) &&
         $record->data["rare"]["announced_supertag"] && (!$record->_announced))
     {
       $announced = &new NpjObject( &$rh, $record->data["rare"]["announced_supertag"] );
       $record->_announced = &$announced;
       $a_data = &$announced->Load(2);
       if (!is_array($a_data)) $a_data = array( "tag" => $record->data["rare"]["announced_supertag"] );
       $a_data["Href:tag"]     = $announced->Href( $record->data["rare"]["announced_supertag"], NPJ_ABSOLUTE, STATE_IGNORE );
       $a_data["Link:subject"] = $announced->Link( $record->data["rare"]["announced_supertag"], "", 
                                                   $a_data["subject"] );
       $a_data["fulltag"]      = $announced->npj_account.":".$a_data["tag"];

       $tpl->LoadDomain( $a_data );
       $tpl->Parse( "record.announce.html:Top", "Record.Announce.Top" );
     }

     // ---------------------------------------------------------------------- ПЕРЕЧЕНЬ АНОНСОВ ДЛЯ ДОКУМЕНТА
     if ($show_announces_for)
     {
       // 2. взять рефы из БД
       $sql = "select ref.announce, ref.need_moderation, r.record_id, u.user_id, u.login, u.node_id, u.user_name, r.subject, r.tag, r.supertag ".
              "from ".$rh->db_prefix."records_ref as ref, ".
                      $rh->db_prefix."records as r, ".
                      $rh->db_prefix."records_rare as rr, ".
                      $rh->db_prefix."users as u ".
              "where ref.record_id=rr.record_id and rr.announced_id=".$db->Quote($record->data["record_id"]).
              " and (priority=0) and ref.keyword_id=r.record_id and ref.keyword_user_id=u.user_id".
              " order by r.supertag";
       $rs = $db->Execute( $sql ); 
       $a = $rs->GetArray();
       $backrefs = array( ); // 0 - keywords, 1 - communities moderated, 2 - comms notmoderated
       foreach($a as $item)
       {
        $objecto = array(
            "account"      => $item["login"]."@".$item["node_id"],
            "Href:account" => $record->Href( $item["login"]."@".$item["node_id"], NPJ_ABSOLUTE, IGNORE_STATE ),
            "Link:account" => $record->Link( $item["login"]."@".$item["node_id"] ),
            "Form:mod"     => $state->FormStart( MSS_POST, $record->Href( $item["login"]."@".$item["node_id"], NPJ_ABSOLUTE, IGNORE_STATE )
                                                 ."/manage/".$record->data["record_id"]),
            "subject"      => $item["subject"],
            "tag"          => $item["tag"],
            "user_name"    => $item["user_name"],
            "user_id"      => $item["user_id"],
            "is_mod"       => $rh->principal->IsGrantedTo("rank_greater", "account", 
                                                          $item["user_id"], GROUPS_MODERATORS),
            "need_mod"   => 0,
            "done_mod"   => 0,
            "href"         => $record->Href( $item["supertag"], NPJ_ABSOLUTE, IGNORE_STATE ),
            "announce"     => $item["announce"],
                 );
          if ($objecto["is_mod"])
          {
            if ($item["need_moderation"]) $objecto["need_mod"] = 1;
            else                          $objecto["done_mod"] = 1;
          }
         
         $backrefs[$objecto["account"]] = $objecto;
       }

       $list = &new ListObject( &$rh, &$backrefs ) ;
       $list->Parse( "record.announce.html:BackRef", "Record.Stats.Ref", TPL_APPEND );
     }

  }

  // ------------------------------------------------------------------------------------------------------
  // Подготовка домента TE для парсинга этих, как его, рекорд-статов
  // если возвращает false, то выводить ничего не надо
  // записываем
  //  * Record.Stats.ChangedByName   -- from TPL
  //  * Link:Record.Stats.ChangedBy  -- 
  //  * Record.Stats.ChangedDT       -- 
  //  * Record.Stats.Type            -- comment/1/2
  //  * Record.Stats.TypeName        -- название типа "комментарий"/"документ" что ли?
  //  * Record.Stats.Address         -- нпж-адрес, превращённый в путь
  //  * Record.Stats.Security        -- текстом написанный "код доступа" (из tpl) 
  //  * Record.Stats.Security.IsPublic -- 1, if is public 
  //  * Record.Stats.Security.Icon     -- public, friends, custom, etc.
  function AssignRecordStats( &$object )
  {
    $this->stats_type = "unknown";
    $rh  = &$this->rh;
    $tpl = &$this->rh->tpl;
    $cache = &$this->rh->cache;
       
    $tpl->Assign( "Record.Stats.Security", "" );
    $tpl->Assign( "Record.Stats.Security.IsPublic", 1 );
    // === инфа о журнале
    $tpl->Assign( "Record.Stats.Journal0", $rh->account->data["account_type"] == 0 );
    $tpl->Assign( "Record.Stats.Journal1", $rh->account->data["account_type"] == 1 );
    $tpl->Assign( "Record.Stats.Journal2", $rh->account->data["account_type"] == 2 );

    // ============================================================================== КОММЕНТАРИЙ
      if ($object->class == "comments")
        return $this->AssignRecordStats( $object->record );
      else
      if ($object->class == "versions")
        return $this->AssignRecordStats( $object->record );
      else
      if ($object->class == "account")
        return $this->AssignRecordStats( $object->record );
      else
    // ============================================================================== ЗАПИСЬ
      if ($object->class == "record") 
      {
        $o=$object;
        if (!$o) { return false; }
        if ($o->data == "empty") 
        { 
          $o = $o->record;
          if (!$o || ($o->data == "empty")) return false;
        }
        $this->stats_object = &$o;

        if (isset($o->data["edited_user_login"]) && !($object->method == "post"))
        {
          if ($o->data["edited_datetime"] == $o->data["created_datetime"] || $o->data["type"]==1)
           $tpl->Assign( "Record.Stats.ChangedByName", $tpl->message_set["CreatedBy"] );
          else
           $tpl->Assign( "Record.Stats.ChangedByName", $tpl->message_set["ChangedBy"] );
          if ($o->GetType()==RECORD_POST)
          {
           $tpl->Assign( "Link:Record.Stats.ChangedBy", $o->Link(
             $o->data["edited_user_login"]."@".$o->data["edited_user_node_id"],
             "", $o->data["edited_user_name"] ));
           $tpl->Assign( "Record.Stats.Type", $o->data["type"] );

             $d = explode(" ",$o->data["user_datetime"]);
             $d[0] = explode("-",$d[0]);
             $h=array(); $h[0] = $o->Href( $o->npj_account.":".$d[0][0], NPJ_ABSOLUTE, STATE_IGNORE );
                         $h[1] = $h[0]."/".$d[0][1];
                         $h[2] = $h[1]."/".$d[0][2];
             $d[0][0] = "<a href=\"".$h[0]."\">".$d[0][0]."</a>";
             $d[0][1] = "<a href=\"".$h[1]."\">".$d[0][1]."</a>";
             $d[0][2] = "<a href=\"".$h[2]."\">".$d[0][2]."</a>";
             $d[0] = implode("&#150;", $d[0] );
             $tpl->Assign( "Record.Stats.ChangedDT", implode(" ",$d) );

          
          }
          else
          {
           $tpl->Assign( "Link:Record.Stats.ChangedBy", $o->Link(
             $o->data["edited_user_login"]."@".$o->data["edited_user_node_id"],
             "", $o->data["edited_user_name"] ));

           $tpl->Assign( "Record.Stats.ChangedDT", $o->data["edited_datetime"] );

           $tpl->Assign( "Record.Stats.Type", $o->data["type"] );
          }

         // security -----------
         $security ="public";
         if ($o->GetType() == RECORD_DOCUMENT) // ключики/глазки для документов
         { 
           $acl = $cache->Restore( "record_acl_read", $o->data["record_id"], 2 );
           $acl = $acl["acl"];
           $security = "custom";
           if ($acl == "") $security = "private";
           if ($acl == "*") $security = "public";
           if ($acl == "&") $security = "friends";
         }
         else // ------------------ ключики/глазки для сообщений
          if ($o->data["group1"])
           switch($o->data["group2"])
           { 
             case -1: $security= "private";  break; 
             case -2: $security= "friends";  break; 
             case -3: $security= "comm";  break; 
             default: $security= "custom";   break;
           }
         $tpl->Assign( "Record.Stats.Security.IsPublic", $security == "public" );
         $tpl->Assign( "Record.Stats.Security.Icon", $security );
         $tpl->Assign( "Record.Stats.Security", $tpl->message_set["Record.Stats.Sec"][$security] );

        }
        else
        {     
          $tpl->Assign( "Record.Stats.ChangedByName", $tpl->message_set["CreatingBy"] );
          $tpl->Assign( "Link:Record.Stats.ChangedBy", $o->Link(
            $rh->principal->data["login"]."@".$rh->principal->data["user_node_id"],
            "", $rh->principal->data["user_name"] ));
          $tpl->Assign( "Record.Stats.ChangedDT", date("Y-m-d h:i:s") );
          if ($o->class == "comments") $tpl->Assign( "Record.Stats.Type", "comment" );
          else
          if ($object->method == "post")
            $tpl->Assign( "Record.Stats.Type", 1 );
          else
            $tpl->Assign( "Record.Stats.Type", $o->data["type"] );
        }
        
        if ($tpl->GetValue("Record.Stats.Type") == 2) $tpl->Assign( "Record.Stats.ShowVersions", 1 );
        $tpl->Assign( "Record.Stats.TypeName", $tpl->message_set["ItIs"][$tpl->GetValue("Record.Stats.Type")] );

        $address = "";
        $address .= $object->Link($object->npj_account).":";
        if ($o->data["tag"] == "") $address.=$tpl->message_set["JournalHomePage"];
        else
        { $path = explode("/", $o->data["tag"] );
          $links = explode("/", $o->name );
          $tag  = ""; $f=0; $endkey = sizeof($path)-1;
          foreach ($path as $key=>$link)
          { $tag.="/".$links[$key];
            if ($f) $address.="<span class=\"slash-\">/</span>"; else $f=1;
            if ($key == $endkey) break;
            $address.= "<a href=\"".$tpl->GetValue("Href:Account").$tag."\">".$link."</a>";
          }
          $address.= "<a href=\"".$o->Href($o->npj_object_address, NPJ_ABSOLUTE, STATE_IGNORE)."\">".$link."</a>";

        }
        $tpl->Assign( "Record.Stats.Address", $address );

         // что не показывать в панели 
         $this->stats_hide = array();
         $this->stats_type = $object->GetType();
         return true;
      }
      else return false;

  }
 
  // ------------------------------------------------------------------------------------------------------
  // Парсинг панели по подготовленному хозяйству
  // ??? возможно сюда ещё передавать и наименование шкуры, для которой рендерить и делать Skin/Unskin
  function ParsePanel( $granted, $panel, $base, $links, $current="show",
                       $Name="Record0", $tplt="panel.html:Panel_Item", 
                       $prefix="<div class=\"control-panel\">", $postfix="</div><br clear=\"all\"/>", $separator="&nbsp;| " )
    { $tpl   = &$this->rh->tpl;
      $debug = &$this->rh->debug;
     // 1. shorten $links
     $_links = array();
     foreach ($panel as $handle=>$grant)
      if ($granted[$grant])
      {
       $_links[] = array(
            "href"=> ($links[$handle]!==0?($base.$links[$handle]):($base.$handle)),
            "text"=> $tpl->message_set[$Name."Methods.Short"][$handle],
            "title"=> $tpl->message_set[$Name."Methods"][$handle],
            "icon"=> "<img src=\"".$tpl->GetValue("images")."methods/".$handle.(($current == $handle)?"_current":"").".gif\" class=\"panel_icon\" border=\"0\"".
                     " title=\"".$tpl->message_set[$Name."Methods"][$handle]."\" />",
            "icon_src" => $tpl->GetValue("images")."methods/".$handle.(($current == $handle)?"_current":"").".gif",
            "current" => ($current == $handle)
                        );
      }
     // 2. if empty -- return
     if (sizeof($_links) == 0) return;
     // 3. output
     $result = $prefix;
     $f=0;
     foreach( $_links as $item )
     { if ($f) $result.= $separator; else $f=1;
       $tpl->Assign("_Href", $item["href"] );
       $tpl->Assign("_Text", $item["text"] );
       $tpl->Assign("_Title", $item["title"] );
       $tpl->Assign("_Icon", $item["icon"] );
       $tpl->Assign("_IconSrc", $item["icon_src"] );
       if ($item["current"]) $result.= $tpl->Parse( $tplt."_Current" );
       else                  $result.= $tpl->Parse( $tplt );
     }
     $result.=$postfix;
     return $result;
   }

  // ------------------------------------------------------------------------------------------------------
  // Инициализация панелей с функциями
  // !!! нуждается в пересмотре и рефакторинге
  function InitPanel( &$object )
  {
    $rh = &$this->rh;
    $tpl = &$this->rh->tpl;
    $debug = &$this->rh->debug;
    $principal = &$rh->principal;

    if ($tpl->GetValue("404")) return;
    if (!$object) return;
    if ($object->data == "empty") return;

    $is_node_admin = $rh->principal->IsGrantedTo("node_admins");

    $panel = array(); $links = array();

    $method = $object->method;
    if ($object->class=="versions") if ($object->name == "") $method = "versions"; else $method="versions/some";
      $debug->Trace( "<b>InitPanel</b> ".$object->record );
    if ($method == "_acl") $method = "rights";
    if ($method == "_groups") $method = "rights";
    if (($object->class=="friends") && ($rh->account->data["account_type"]>0))
    { $_object = &$rh->account; $method= "friends";
    }

    $special="";    // owner, all, read, write, comment, acl_read, delete, comment_delete, 
                    // join_community, join_workgroup, moderators, members, versions, registered
    $granted = array( 0, 1, );
    $granted[ PANEL_REGISTERED ] = $object->HasAccess(&$principal, "noguests");
    $granted[ PANEL_FOREIGN ] = $granted[ PANEL_REGISTERED ] && ($principal->data["node_id"]!=$rh->node_name);
  
     // ***** КОММЕНТАРИЙ =================================================================
     if ($object->class == "comments")
     {
       $o = &$object->record;
       return $this->InitPanel( &$o );
     }
     // ***** ВЕРСИИ =================================================================
     if ($object->class == "versions")
     {
       $o = &$object->record;
       return $this->InitPanel( &$o );
     }
     // ***** ЗАПИСЬ =================================================================
     if ($object->class == "record")
     {
       $object->Load(2);

       if ($object->data == "empty") return;


       $sec = $object->security_handlers[ $object->GetType() ];
       $granted[ PANEL_OWNER ]    = $object->HasAccess(&$principal, "owner");
       $granted[ PANEL_READ ]     = $object->HasAccess(&$principal, $sec, "read" );
       $granted[ PANEL_VERSIONS ] = $granted[ PANEL_READ ] && ($object->data["type"] == 2);
       $granted[ PANEL_REPLICATION ] = $granted[ PANEL_FOREIGN ] && $granted[ PANEL_READ ];
       $granted[ PANEL_ANNOUNCE ] = ($object->GetType() == RECORD_DOCUMENT) && $principal->IsGrantedTo("noguests")
                                && ($object->name != "");
       $granted[ PANEL_SUBSCRIBE ] = $granted[ PANEL_READ ] && $granted[ PANEL_REGISTERED ];
       if ($object->GetType() == RECORD_DOCUMENT)
       {
        $granted[ PANEL_WRITE ]    = $granted[ PANEL_READ ] && 
            (
             $object->HasAccess(&$principal, $sec, "write") || 
             $object->data["user_id"]==2 && 
             $object->HasAccess(&$principal, "acl_text", $rh->node_admins)
            );
        if ($rh->admins_only_documents) $granted[ PANEL_WRITE ] = $granted[ PANEL_WRITE ] && $object->HasAccess(&$principal, "acl_text", $rh->node_admins);
        $granted[ PANEL_ACL_READ ] = $granted[ PANEL_READ ] && $object->HasAccess(&$principal, "acl", "acl_read" );
        $granted[ PANEL_DELETE ]   = $granted[ PANEL_READ ] && $object->HasAccess(&$principal, "acl", "remove" ) &&
                                     ($object->name != "");

        if ($rh->admins_delete_records)
        $granted[ PANEL_DELETE ]   = $granted[ PANEL_DELETE ] || $is_node_admin;

        $granted[ PANEL_COMMENT_ADD ]  = $granted[ PANEL_READ ] && $object->HasAccess(&$principal, "acl", "comment" );
       }
       else
       {
         $granted[ PANEL_WRITE ]       = $granted[ PANEL_OWNER ];
         $granted[ PANEL_ACL_READ ]    = $granted[ PANEL_OWNER ];
         $granted[ PANEL_DELETE ]    = $granted[ PANEL_OWNER ];
         $granted[ PANEL_COMMENT_ADD ]  = $granted[ PANEL_READ ];

         if ($rh->admins_delete_records)
         $granted[ PANEL_DELETE ]   = $granted[ PANEL_DELETE ] || $is_node_admin;
       }
       $granted[ PANEL_COMMENT_ADD ]  = $granted[ PANEL_COMMENT_ADD ] && $object->data["disallow_comments"];
       $granted[ PANEL_COMMENTS ]     = $granted[ PANEL_COMMENT_ADD ] && ($object->data["number_comments"]>0);
       $panel = array_merge((array)$panel, array( "show"=>PANEL_READ, "edit"=>PANEL_WRITE, "versions"=>PANEL_VERSIONS, 
                                           "rights"=>PANEL_ACL_READ, "delete"=>PANEL_DELETE, "subscribe"=>PANEL_SUBSCRIBE,
                                           "replication"=>PANEL_REPLICATION, 
                                           "announce" => PANEL_ANNOUNCE,
                                           "automate" => ($object->data["type"] == 2)?PANEL_OWNER:-1 ));
       $links = array_merge((array)$links,array( "show"=>0,"edit"=>"edit#form","versions"=>0,"rights"=>0,"delete"=>0, 
                                          "subscribe"=>0, "replication"=>"add/replication",
                                          "announce" => "post/announce", "automate"=>0 ));
       $base = $object->Href( $object->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE )."/";
       $Name = "Record".$object->data["type"];
     }
     // ***** АККАУНТЫ =================================================================
     if (($rh->object->class == "account") || isset( $_object ))
     { if (!isset($_object)) $_object = &$rh->object;
       $granted[ PANEL_JOIN_COMMUNITY ]    = ($_object->data["account_type"] == 1) && ($_object->data["security"]<2);
       $granted[ PANEL_JOIN_WORKGROUP ]    = ($_object->data["account_type"] == 2) && ($_object->data["security"]<2);
       $granted[ PANEL_WORKGROUP ]         = ($_object->data["account_type"] == 2);
       $granted[ PANEL_DELETE ]            = 0;
       $granted[ PANEL_VERSIONS ]          = $granted[ PANEL_OWNER ];
       $granted[ PANEL_OWNER_ADMIN ]       = $granted[ PANEL_OWNER ] && $object->HasAccess(&$principal, "acl_text", $rh->node_admins );
       if ($_object->data["account_type"] == ACCOUNT_USER)
         $panel = array_merge((array)$panel, array( "keywords"=>PANEL_READ, 
                                             "add"=>($rh->admins_only_documents?PANEL_OWNER_ADMIN:PANEL_OWNER), 
                                             "post"=>PANEL_OWNER, //"freeze"=>PANEL_OWNER,
                                             "journaldigest"=>($rh->admins_only_documents?PANEL_OWNER_ADMIN:PANEL_OWNER),
                                             "manage"=>PANEL_OWNER,
                                             "add_user"=>PANEL_REGISTERED,
                                             "automate" => PANEL_OWNER,
                                            ));
       else
       { // для СООБЩЕСТВ немного иначе
         if (!isset($_object)) $_object = &$rh->object;
         $granted[ PANEL_OWNER ]    = $_object->HasAccess(&$principal, "owner");
         $granted[ PANEL_MODERATORS ]        = $granted[ PANEL_OWNER ] || $_object->HasAccess(&$principal, "rank_greater", GROUPS_MODERATORS);
         $granted[ PANEL_MEMBERS    ]        = $_object->HasAccess(&$principal, "rank_greater", GROUPS_LIGHTMEMBERS);
         $granted[ PANEL_JOIN_COMMUNITY ]    = $granted[ PANEL_JOIN_COMMUNITY ] &&  !$granted[PANEL_MEMBERS];
         $granted[ PANEL_JOIN_WORKGROUP ]    = $granted[ PANEL_JOIN_WORKGROUP ] &&  !$granted[PANEL_MEMBERS];
         $granted[ PANEL_READ ]              = ($_object->data["security"]<3);
         $granted[ PANEL_SUBSCRIBE ] = $granted[ PANEL_READ ] && $granted[ PANEL_REGISTERED ];
         $panel = array( "show"    => PANEL_READ, 
                         "join"    => (($_object->data["account_type"] == 1)?PANEL_JOIN_COMMUNITY:PANEL_JOIN_WORKGROUP), 
                         "unjoin"  => PANEL_MEMBERS, 
                         "friends" => PANEL_READ, 
                         "modfeed" => PANEL_MODERATORS,
                         "post"=>PANEL_MEMBERS, 
                         "add"=>PANEL_WORKGROUP,
                         "journaldigest"=>PANEL_MODERATORS,
                         "manage"=>PANEL_MODERATORS,
                         "add_community"=>PANEL_REGISTERED,
                         "subscribe"=>PANEL_SUBSCRIBE,
                         "replication"=>PANEL_REPLICATION,
                         "automate" => PANEL_MODERATORS,
                                            );
       }

       $base = $object->Href( $_object->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE )."/";
       $links = array_merge((array)$links,array( "unjoin"=>"join", "join"=>0, "keywords"=>"keywordstree", 
                                          "add"=>0, "post"=>0, "journaldigest"=>0,
                                          "freeze"=>"manage/freeze", "manage"=>0,
                                          "add_user"     =>"manage/add_friend/",
                                          "add_community"=>"manage/add_friend/",
                                          "friends"=>0, "modfeed"=>0, "automate"=>0 ));
       $Name = "Account".$_object->data["account_type"];
     }
     // ***** ФРЕНДЫ =================================================================
     if (($object->class == "friends") && !isset($_object)) // !!! при добавлении рабочих групп пересмотреть
     {
       $base = $object->Href( $rh->account->npj_object_address, NPJ_ABSOLUTE, IGNORE_STATE )."/friends/";
       $granted[ PANEL_JOIN_COMMUNITY ]    = ($rh->object->data["account_type"] == 2) && ($rh->object->data["security"]<2);
       $granted[ PANEL_JOIN_WORKGROUP ]    = ($rh->object->data["account_type"] == 3) && ($rh->object->data["security"]<2);
       $granted[ PANEL_MODERATORS ]        = $rh->account->HasAccess(&$principal, "rank_greater", GROUPS_MODERATORS);
       $panel = array(   "feed"        => PANEL_ALL,
                         "groups"      => PANEL_ALL,
                         "edit"        => PANEL_MODERATORS,
                         "edit_groups" => PANEL_MODERATORS,
                         "join_c"        => PANEL_JOIN_COMMUNITY,
                         "join_wg"       => PANEL_JOIN_WORKGROUP,
                         "add"         => PANEL_MODERATORS,
                          );
       $links = array(   "feed"        => "",
                         "groups"      => 0,
                         "edit"        => 0,
                         "edit_groups" => "groups/edit",
                         "join_c"        => "join",        "join_wg"        => "join",
                         "add"         => 0,
                          );
       $Name = "Friends";
     }
    
     // записать сюда
     $this->panel = array( "base" => $base,     "Name" => $Name,   "granted" => $granted, 
                           "method" => $method, "panel" => $panel, "links" => $links );

  }

  // ------------------------------------------------------------------------------------------------------
  // Инициализация контекстного меню
  function InitContextMenu( &$record )
  {
     // #0. если не запись, то до свидания
     if ($record->class != "record")
      if (!isset($record->record)) 
     {
        $this->context_panelcount = 0;
        return;
     }

     if (isset($record->record)) $o = &$record->record;
     else $o = &$record;


     $tpl = &$this->rh->tpl;
    
     // #1. Умолчания
     // * сообщения: календарь
     if ($o->data["type"] == RECORD_MESSAGE)  $default = "calendar";
     // * документы: recent-changes
     if ($o->data["type"] == RECORD_DOCUMENT) 
      if ($o->data["tag"] == "")
       $default = "keywordsindex";
      else
       $default = "journalchanges";
     // * ключслова: дерево ключслов
     if ($o->data["is_keyword"])              $default = "keywordsindex";
     if ($o->data["user_id"] < 3) 
     {
        $this->context_panelcount = 0;
        return;
     }

     // #2. Панель №1
     $panel1 = $o->data["default_show_parameter"];
     $panel1_p = $o->data["default_show_parameter_param"];
     if (!$panel1) $panel1=0;

     // #3. Панель №2
     $panel2 = $o->data["default_show_parameter_more"];
     $panel2_p = $o->data["default_show_parameter_param"];
     if (!$panel2) $panel2=0;

     // #4. Замещение панели №1 или запрет панели №2
     if (($o->data["default_show_parameter_add"] == 1) && $panel2)
     { $panel1 = $panel2; $panel1_p = $panel2_p; }
     if ($o->data["default_show_parameter_add"] >  0) $panel2 = 0;

     // #5. Если после всего этого панель №1 -- пуста, то заменяем её на значение по умолчанию
     if (!$panel1) $panel1 = $default;

     // Сколько панелек отображать
     $panelcount=0;
     if ($panel1 !== 0) $panelcount++;
     if ($panel2 !== 0) $panelcount++;

     if ($tpl->GetValue("404")) $panelcount = 0;

     $this->context_panelcount = $panelcount;
     $this->context_panel1 = $panel1;
     $this->context_panel1_param = $panel1_p;
     $this->context_panel2 = $panel2;
     $this->context_panel2_param = $panel2_p;
  }


  // парсит блок переключателя между фильтрами
  function ParseCommunityFilterFlip( &$record )
  {
    if (!$this->rh->community_filter) return ""; // no filter option on node

    $rh = &$this->rh;
    $filter_object = &new NpjObject( &$rh, $rh->object->npj_filter."@".$rh->node_name );
    $filter_data   = $filter_object->Load(2);

    $record_data   = $record->Load(2);
    $filters       = explode(",", $record_data["filter"]);
    
    if ($record_data["filter"] == "") return ""; // no filter avail.

    $filter_list = array();
    foreach($filters as $k=>$v)
    {
      $filter_list[] = ($v == $rh->object->npj_filter)
          ?$v."@".$rh->node_name
          :$filter_object->Link( "in/".$v."/by/".$rh->object->npj_address , 
                                 array("subject" =>1),
                                 $v."@".$rh->node_name);
    }
    $filter_list[] = ("" == $rh->object->npj_filter)
        ?$rh->tpl->message_set["CommunityFilter.None"]
        :$filter_object->Link( "in/".$rh->object->npj_address , 
                               array("subject" =>1),
                               $rh->tpl->message_set["CommunityFilter.None"],
                               NPJ_ABSOLUTE, STATE_USE );

    if ("" == $rh->object->npj_filter)
      $rh->tpl->Assign("filter_link", $rh->tpl->message_set["CommunityFilter.TitleNone"] );
    else
      $rh->tpl->Assign("filter_link", $rh->tpl->message_set["CommunityFilter.Title".$filter_data["account_type"]].
                            $filter_object->Link( $filter_object->npj_object_address, "",
                                                  $filter_data["user_name"],
                                                  NPJ_ABSOLUTE, STATE_USE ));

    // ссылка на "показать все"
    if ($rh->principal->IsGrantedTo( "rank_greater", "account", $filter_data["user_id"], GROUPS_MODERATORS ))
    {
      if (!$rh->state->Get("cfilter"))
      {
        $rh->tpl->Assign("Href:more", $rh->state->Plus( "cfilter", "show" ) );
        $rh->tpl->Assign("more", $rh->tpl->message_set["CommunityFilter.Show"] );
      }
      else
      {
        $rh->tpl->Assign("Href:more", $rh->state->Minus( "cfilter" ) );
        $rh->tpl->Assign("more", $rh->tpl->message_set["CommunityFilter.Hide"] );
      }
    }
    else
      $rh->tpl->Assign("more", "" );

    $list = &new ListSimple( &$rh, $filter_list);
    $list->implode = true;
    return $list->Parse( "community_filter.html:List" );
  }

} // EOC { SkinUtility }

?>