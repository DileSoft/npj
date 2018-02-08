<?php
/*

    Баг-трекер на базе НПЖ

    ModuleTrako( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- какой присоединить набор с сообщениями для вывода?
      - $section_id -- идентификатор гигантского раздела сайта (не группы внутри модуля)
      - $handlers_dir, $messageset_dir -- в замену стандартным из $rh->..

  ---------
  - Handle( $rel_url ) -- весь рабочий цикл в одном вызове
  - Error( $msg )      -- отметиться, что ошибка
  - isError()          -- true, если где-то был вызван Error

  - Init( $rel_url )   -- понять, в каком состоянии мы пришли сюда, найти обработчик
  - Handler( $handler, $params, &$principal ) -- осуществить обработку одного из handlers, распихав результат по:
      - Preparsed:TITLE
      - Preparsed:CONTENT
      - Preparsed:ERROR -- заполняется в случае ошибки
  - Action( $handler, $params, &$principal )  -- специальные обработчики-сервисы, возвращающие результат строкой

  // Свойства модуля
  * message_set
  * section_id
  * handlers_dir -- заимствуется из $rh->handlers_dir, если что -- можно перезадать

  // Настройки модуля
  * method -- на какой метод настроен модуль при ините
  * params[...]

========================================= v.2 (ariman@gmail.com + kuso@npj)
*/
define("ACCESS_GROUP_TRAKO", -10);
define("RSS_TRAKO_ISSUES",   -10);
define("TRAKO_AUTOASSIGN_NONE",   0);
define("TRAKO_AUTOASSIGN_WEAK",   1);
define("TRAKO_AUTOASSIGN_STRONG", 2);
define("TRAKO_OWNER_RANK", -1);

define("TRAKO_LOGGER_COMMENT", 2);

class ModuleTrako extends NpjModule
{
  var $methods = array( 
                  "issue"=> array( "show", "view", "edit", "delete", 
                                   "subscribe", "log",
                                   "state", "assignself" ),
                      );

  var $module_name = "Trako";

  function ModuleTrako( &$rh, $base_href, $module_config, &$object )
  {
    $result = NpjModule::NpjModule( &$rh, $base_href, $module_config, &$object );

    // custom template settings (overridable)
    $this->config["template_engine"]  = array(  // template engine profile
                  "cache_prefix"  => "trako_skins@",
     /* cribaskin
                  "themes_dir"    => $rh->modules_dir.$this->config["module_dir"]."themes/",
                  "skins"         => array("criba"),
                  "skin"          => "criba", // if not found in "skins"
     */
     /* noskin */
                  "themes_dir"    => $rh->modules_dir,
                  "skins"         => array("trako"),
                  "skin"          => "trako", 

                                            );
    // сколько нулей надо в имени бага: #00000018 = 8
    if (!isset($this->config["zero_padding"]))  $this->config["zero_padding"] = 8;

    return $result;
  }

  function &GenerateTemplateEngine( $te_profile )
  {
    $TE =& NpjModule::GenerateTemplateEngine( $te_profile );
    $TE -> Assign( "Trako:Version", $this->rh->tpl->message_set["Trako.version"] );
    return $TE;
  }

  function Init( $rel_url )
  {
    $this->method = "default";
    $this->params = array();

    $parts = explode( "/", $rel_url );
    
    // add an issue:
    if ($parts[0] == "add")
    {
      $this->method = "issue_add";
      $this->params = array_slice( $parts, 1 );
      return;
    }

    // filter:
    if ($parts[0] == "filter")
    {
      $this->method = "filter";
      $this->params = array_slice( $parts, 1 );
      return;
    }

    // mass
    if ($parts[0] == "mass")
    {
      $this->method = "mass";
      return;
    }

    // look@issue#
    $issue_no = 1*ltrim($parts[0],0);
    if ($issue_no > 0)
    {
      $this->_method = "view";
      $this->_class   = "issue";
      $this->params = array_slice( $parts, 1 );
      $this->params["issue_no"] = $issue_no;
      // alter method
      if (in_array($this->params[0], $this->methods[$this->_class]))
      {
        $this->_method = $this->params[0];
        $this->params = array_slice( $this->params, 1 );
      }
      // compose
      $this->method = $this->_class."_".$this->_method;
      return;
    } 

    // другие обработчики дописывать сюда
  }

  // Вспомогательные ништяки для логгера --------- 
  function LoadLoggerRootId( $record_id )
  {
    $db = &$this->rh->db;
    $sql = "select comment_id from ".$this->rh->db_prefix."comments ".
           " where record_id=".$db->Quote( $record_id ).
           " and frozen = ".$db->Quote(TRAKO_LOGGER_COMMENT)." order by comment_id asc";
    $rs  = $db->SelectLimit( $sql, 1 );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) 
    {
      $this->SpawnLoggerRoot( $record_id );
      return $this->LoadLoggerRootId( $record_id );
    }
    return $a[0]["comment_id"];
  }
  function SpawnLoggerRoot( $record_id )
  {
    $rh = &$this->rh;
    $rh->tpl->MergeMessageset( $rh->message_set."_Trako_logger", $this->messagesets_dir );
    $ms = &$this->rh->tpl->message_set;
    $principal = &$this->rh->principal;
    $account = &new NpjObject( &$rh, $this->object->npj_account );
    $account->Load(2);
    $record_data = $account->_LoadById( $record_id, 3, "record" );
    $rh->debug->Trace( "record-id = $record_id" );
    $comment = &new NpjObject(&$rh, $record_data["supertag"]."/comments");

    $principal->MaskById(1);
    $comment->data["active"]    = 0;
    $comment->data["frozen"]    = TRAKO_LOGGER_COMMENT;
    $comment->data["subject"]   = $ms["Trako.logger_root.subject"];
    $comment->data["body_post"] = $this->rh->tpl->Format($ms["Trako.logger_root.body"], "paragrafica");
    $comment->data["user_id"]      = $principal->data["user_id"];
    $comment->data["user_login"]   = $principal->data["login"];
    $comment->data["user_name"]    = $principal->data["user_name"];
    $comment->data["user_node_id"] = $principal->data["node_id"];
    $comment->data["created_datetime"] = date("Y-m-d H:i:s");
    $comment->data["record_id"] = $record_data["record_id"];
    $comment->data["parent_id"] = 0; 
    $comment->data["lft_id"] = 0;
    $comment->data["rgt_id"] = 0;
    $comment->Save();
    $principal->UnMask();
  }

  // Работа с Issues ----------------------------------------------------------------------------------------
  function &LoadIssueByRecordId( $record_id, $record_level=3 ) // 3 is for view, not edit
  {
    $db = &$this->rh->db;
    $sql = "select * from ".$this->rh->db_prefix."trako_issues ".
           " where record_id=".$db->Quote( $record_id );
    $rs  = $db->SelectLimit( $sql, 1 );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) return NOT_EXIST;

    $issue = $a[0];
    $record = $this->object->_LoadById( $issue["record_id"], $record_level, "record" );
    $account_data = $this->object->_LoadById( $record["user_id"], 1, "account" );
    $project_account = &new NpjObject( &$this->rh, $account_data["login"]."@".$account_data["node_id"] );
    $project_account->Load(1);
    
    $issue["&account"] = &$project_account;
    $issue["RECORD" ]  = &$record;

    return $this->PreparseIssue( &$issue );
  }

  function &LoadIssue( &$project_account, $issue_no, $record_level=3 ) // 3 is for view, not edit
  {
    $db = &$this->rh->db;
    $sql = "select * from ".$this->rh->db_prefix."trako_issues ".
           " where project_id=".$db->Quote( $project_account->data["user_id"] ).
           " and issue_no=". $db->Quote( $issue_no );
    $rs  = $db->SelectLimit( $sql, 1 );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) return NOT_EXIST;

    $issue = $a[0];
    $record = $project_account->_LoadById( $issue["record_id"], $record_level, "record" );
    $issue["&account"] = &$project_account;
    $issue["RECORD" ]  = &$record;

    return $this->PreparseIssue( &$issue );
  }
  function &PreparseIssue( &$issue )
  {
    $rh= &$this->rh; $db = &$rh->db;
    $account = &$issue["&account"];

    $issue["RECORD"] = $account->_PreparseArray($issue["RECORD"]);
    // (+) data
    $issue["subject"]            = $issue["RECORD"]["subject_post"];
    $issue["body"]               = $issue["RECORD"]["body_post"];
    $issue["created_dt"]         = $issue["RECORD"]["created_dt"];
    $issue["user_dt"]            = $issue["RECORD"]["user_dt"];
    $issue["keywords"]           = preg_replace("/<a href=('|\")([^\"']+)\\1/i",
                                                "<a href=$1$2/trako$1", 
                                                $issue["RECORD"]["keywords"]);

    $issue["number_comments"]    = $issue["RECORD"]["number_comments"];

    $issue["issue_no_aligned"]    = str_pad($issue["issue_no"], $this->config["zero_padding"], "0", STR_PAD_LEFT);
    // (+) priority
    $issue["priority_text"]    = $this->rh->tpl->message_set["Trako.priorities"      ][$issue["priority"]];
    $issue["priority_symbol"]  = $this->rh->tpl->message_set["Trako.priority_symbols"][$issue["priority"]];
    // (+) consistency
    $issue["consistency"]    = $this->rh->tpl->message_set["Trako.consistency"][$issue["consistency"]];
    // (+) severity
    $issue["severity_class_text"]    = $this->rh->tpl->message_set["Trako.severity_classes"][$issue["severity_class"]];
    $issue["severity_value_text"]    = $this->rh->tpl->message_set["Trako.severity_values" ][$issue["severity_class"]][$issue["severity_value"]];
    // (+) status
    $issue["status"]         = $issue["state_status"];
    $issue["status_text"]    = $this->config["statuses"][$issue["status"]];
    // (+) users:
    $sql = "select user_id, user_name, login, node_id from ".$rh->db_prefix."users ".
           " where user_id in (". $db->Quote($issue["reporter_id"]).",".$db->Quote($issue["developer_id"]).")";
    $rs  = $db->Execute($sql);
    $a   = $rs->GetArray();
    $users = array();
    $users[0] = array(
      "Link:user"      => "Посетитель сайта", // !!!! to msgst
      "Link:user_name" => "Посетитель сайта", // !!!! to msgst
      "Href:user"      => "",
      "Npj:user"       => "",
      "is_none"        => 1,
      "user_name"      => "Посетитель сайта", // !!!! to msgst
                                 );
    foreach ($a as $k=>$v)
    {
      $users[$v["user_id"]] = array(
        "is_none"        => 0,
        "Link:user"      => $account->Link( $v["login"]."@".$v["node_id"] ),
        "Link:user_name" => $account->Link( $v["login"]."@".$v["node_id"], "", $v["user_name"] ),
        "Href:user"      => $account->Href( $v["login"]."@".$v["node_id"], NPJ_ABSOLUTE, STATE_IGNORE ),
        "Npj:user"       => $v["login"]."@".$v["node_id"],
        "user_name"      => $v["user_name"],
                                   );
    }
    $u = array("reporter", "developer" );
    foreach ($u as $v)
    {
      if (isset($users[ $issue[$v."_id"] ])) $_v = $issue[$v."_id"];
      else                                   $_v = 0;
      foreach ($users[$_v] as $kk=>$vv)
       $issue[$v.">".$kk] = $vv;
    }
    // (+) hrefs
    $issue["Href:tag"] = $issue["RECORD"]["Href:tag"];
    $issue["Href:comments_target"] = $issue["RECORD"]["Href:comments_target"];
    
    return $issue;
  }

  // WORKFLOW (subject to refactor) --------------------------------------------------------------------------
  // workflow.
  // state_params == array( "state", "status" )
  function ChangeState( &$principal, &$project_account, $issue_data, $new_state_params=NULL, $dont_log=false,
                        $dont_check_security = false )
  {
    $result = array();
    if ($new_state_params === NULL) // spawn state from NULL
    {
      $result["state"]  = $this->config["default_state"];
      $result["status"] = $this->config["states"][ $result["state"] ]["default_status"];
      return $result;
    }

    $result["state"]  = $issue_data["state"];
    $result["status"] = $issue_data["state_status"];


    if ($issue_data["state"] == $new_state_params["state"])
    {
      // changing status
      if ($dont_check_security || $this->HasAccess( &$principal, &$project_account, $issue_data, "status" ))
        if (in_array( $new_state_params["status"], $this->config["states"][$result["state"]]["statuses"] ))
        {
          $result["status"] = $new_state_params["status"];
          // call for "logger"!
          $event_data = array(
                                "record_id"    => $issue_data["record_id"],
                                "issue_no"     => $issue_data["issue_no"],
                                "project_id"   => $issue_data["project_id"],
                                "event_name"   => "issue_status",
                                "issue"        => $issue_data,
                                "state_params" => $result,
                             );
          if (!$dont_log) $this->Handler( "_logger", $event_data, &$principal );
        }
    }
    else 
    {
      // changing state
      if (isset($this->config["states"][$issue_data["state"]]["to"][$new_state_params["state"]]))
      {
        $state_ranks = $this->config["states"][$issue_data["state"]]["to"][$new_state_params["state"]];
        $forbidden=true;
        foreach($state_ranks as $rank)
          if ($this->_HasAccess( &$principal, &$project_account, $issue_data, $rank ))
            $forbidden=false;
        if ($dont_check_security) $forbidden = false;
        if (!$forbidden)
        {
          $result["state"]  = $new_state_params["state"];
          $result["status"] = $this->config["states"][ $result["state"] ]["default_status"];
          // сразу пробуем поменять и статус:
          if ($dont_check_security || $this->HasAccess( &$principal, &$project_account, $issue_data, "status" ))
            if (in_array( $new_state_params["status"], $this->config["states"][$result["state"]]["statuses"] ))
              $result["status"] = $new_state_params["status"];
          // call for "logger"!
          $event_data = array(
                                "record_id"    => $issue_data["record_id"],
                                "issue_no"     => $issue_data["issue_no"],
                                "project_id"   => $issue_data["project_id"],
                                "event_name"   => "issue_state",
                                "issue"        => $issue_data,
                                "state_params" => $result,
                             );
          if (!$dont_log) $this->Handler( "_logger", $event_data, &$principal );
        }
      }
    }

    return $result;
  }

  // -- управление доступом к рапортам --
  function HasAccess( &$principal, &$project_account, $issue, $action="view" )
  {
    $target_rank = $this->config["security"][$action];
    $this->rh->debug->Trace(" action { $action } against rank = ".$target_rank);

    if ($principal->data["user_id"] == 1) $is_anonymous=1; // is GUEST@NPJ?

    // blocking by state
    if ($this->config["states"][$issue["state"]]["block"] == "*")
      if (($action != "view") && ($action != "comments") && ($action != "delete")) return false;

    if ($this->config["security_for_reporter"][$action])
      if (!$is_anonymous && $issue["reporter_id"] == $principal->data["user_id"])
        $target_rank = TRAKO_OWNER_RANK; //

    return $this->_HasAccess( &$principal, &$project_account, $issue, $target_rank );
  }
  function _HasAccess( &$principal, &$project_account, $issue, $rank )
  {
    // VIEW is a must
    $view_rank    = $this->config["security"]["view"];
    $private_rank = $this->config["security"]["private"];
    $issue_rank   = $issue["access_rank"];
    if ($issue_rank != 0) $view_rank = $issue_rank;

    if ($principal->data["user_id"] == 1) $is_anonymous=1; // is GUEST@NPJ?

    $this->rh->debug->Trace(" against rank = ".$rank." and view=$view_rank and issue=$issue_rank" );

    $forbidden = true;
    if ($rank < 0) // rank is private
      if ($project_account->HasAccess( &$principal, "rank_greater", $private_rank)) $forbidden = false;
      else if (!$is_anonymous && $issue["reporter_id"] == $principal->data["user_id"]) $forbidden = false;
           else;
    else
      if ($rank <= $view_rank)  // requested rank is lesser than (view) -> check (view) instead
       if ($view_rank == 0) $forbidden = false; 
       else if ($project_account->HasAccess( &$principal, "rank_greater", $view_rank)) $forbidden = false;
            else;
      else                      // requested rank is stricter than (view) -> check it!
        if ($project_account->HasAccess( &$principal, "rank_greater", $rank)) $forbidden = false;

    return !$forbidden;
  }

  // ======== МАШИНЕРИЯ ИНТЕГРАЦИИ =======
  function Npj_Load( $abs_npj_address, $cache_level, $cache_class, $no_cache=false )
  {
    $data = NpjModule::Npj_Load( $abs_npj_address, $cache_level, "record", $no_cache );
    $data["_post_supertag_cancel"] = true;
    $data["_post_date_cancel"]     = true;
    return $data;
  }
  function Npj_LoadById( $id, $cache_level, $cache_class, $no_cache=false )
  {
    $data = NpjModule::Npj_LoadById( $id, $cache_level, "record", $no_cache );
    $data["_post_supertag_cancel"] = true;
    $data["_post_date_cancel"]     = true;
    return $data;
  }
  function Npj_OnComment( $comment_id, $record_id, &$principal )
  {
    $rh= &$this->rh;
    $db= &$rh->db;
    $sqls[] = "update ".$rh->db_prefix."records set user_datetime = commented_datetime ".
              "where record_id=".$db->Quote($record_id);
    $sqls[] = "update ".$rh->db_prefix."records_ref set user_datetime = commented_datetime ".
              "where record_id=".$db->Quote($record_id);
    foreach($sqls as $sql)
      $db->Execute( $sql );
    return NPJ_MODULE_PROCESSED; // done
  }
  function Npj_IsGrantedTo( &$principal, 
                            $method, $object_class="", $object_id=0, $options="" )
  {
    if ($method == "groups")
    {
       $issue = &$this->LoadIssueByRecordId( $object_id, 1 );
       //$this->rh->debug->Trace( $issue["access_rank"] );
       $result = $this->HasAccess( &$principal, &$issue["&account"], $issue );
       //$this->rh->debug->Error( "HasAccess=".$result );
       return $result;
    }
    return DENIED;
  }

// EOC { ModuleTrako }
}


?>