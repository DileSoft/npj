<?php
/*
    ModuleNotify( &$rh, $base_href, $message_set="notify", $section_id=0, 
                                     $handlers_dir="", $messageset_dir="" ) -- �������� � ��������
       - ��������� �� Module

  ---------
  ~ Error( $msg )      -- ����������, ��� ������
  ~ Init( $rel_url )   -- ������, � ����� ��������� �� ������ ����, ����� ����������

  * PreformatEvent( $event ) -- �������������� ��������� Event (���������� "name", "verbose",
                                ���� ����� ��� �������� �� ����)

  * AddEvent( $event, $principal = NULL) -- �������� ������� �� ����� ������������
                                            ������� -- ��������� ����, ���������� � ���������
                                            ���� name & verbose -- �������������
                                            letter_count ������ ������

  * AddReport() -- �������� ������� "����� ������". ����� ��������� ������ ������/�������
  * Iteration() -- ���� �������� ������ �� ��������
                                            
  * NotifySubscription( $subscriber_id, $notify_type=MODULE_NOTIFY_SUBSCRIBE ) -- ������� �����������
              - subscribe -- � ��������
              - settings  -- �� ��������� ��������
              - remove    -- �� �������

  // �������� � ���������
  * $ttl           -- ����� � ��������, ������� ����� ������������ ������� � ���� � ��������������� ������
  * $event_fields  -- �������� �����, ��������� � ������� �������
  * $module_tree   -- ���� �� ������, �� �������, � ����� ������� ������ ������ � ������� �����
  * $table_prefix  -- ������ ��� ������������ ������, � ��������� � �����
  * $tpl_prefix    -- ��� ����� �������, �� ������ �� �����
  * $event_states  -- �������� ���������
  * $event_state_success -- ����������
  * $log_letters   -- if true -- ������������� ����� ��� ������ � ���

  // ��������� ������

=============================================================== v.0 (KusoMendokusee)
*/
define( "MODULE_NOTIFY_SUBSCRIBE", "subscribe" );
define( "MODULE_NOTIFY_SETTINGS",  "settings"  );
define( "MODULE_NOTIFY_REMOVE",    "remove"    );


class ModuleNotify extends Module
{
  var $table_prefix = "notify_";
  var $module_tree  = "module_tree";
  var $tpl_prefix   = "notify/";

  var $log_letters = false;

  var $event_fields = array( "created_user_id",  "created_datetime", 
                             "event", "details", "name",
                             "module_id", "item_hash", "item_id", "verbose",
                             "letter_count",
                           );

  var $ttl = array( 
                    "events"      => 604800,  // 1 week
                    "subscribers" => 2678400, // 1 month
                  );
  var $event_states = array( "���������� � �������",  
                             "���������� � �������� ��������",
                             "�������� ������",
                             "���������� ����������" );
  var $event_state_success = 3;
  var $event_state_error   = 2;

  function ModuleNotify( &$rh, $base_href, $message_set="notify", $section_id=0, 
                                           $handlers_dir="", $messageset_dir="" )
  {
     if ($handlers_dir == "") $handlers_dir = $rh->handlers_dir."notify/";
     Module::Module( &$rh, $base_href, $message_set, $section_id, $handlers_dir, $messageset_dir );
  }

  function Init( $rel_url )
  {
    $url = trim($rel_url, "/");
    // !!!! todo: ������ ���� �� ������������ ��� ����, ����� �������� � ������� �����
    if ($url == "iteration") { $this->Iteration(); $this->rh->debug->Error("iteration done"); }
    if ($url == "report")    { $this->AddReport(); $this->rh->debug->Error("report done"); }

    if ($url == "")         { $this->method = "subscribe";   return; }
    if ($url == "remove")   { $this->method = "remove";      return; }
    if ($url == "settings") { $this->method = "settings";    return; }

    $this->method="404";
    $this->tpl->Assign("Preparsed:404", 1);
  }

  function Iteration()
  {
    $this->rh->UseClass("ModuleNotifyIteration", $this->rh->core_dir);
    $iteration =  &new ModuleNotifyIteration( &$this );
    $iteration->Handle();
  }
  function AddReport()
  {
     // add event 
     $this->AddEvent( array(
                      "event" => "notify:report",
                      "verbose" => "������ �� �������� ������",
                      "module_id" => -1,
                    ) );
     // clean 
     foreach( $this->ttl as $table=>$ttl)
     {
       $sql = "delete from ".$this->rh->db_prefix.$this->table_prefix.$table." where ".
              "created_datetime < NOW()-".$ttl;
       if ($table == "subscribers") $sql.=" and confirmed=0";
       $this->rh->debug->Trace($sql);
       $this->rh->db->Execute($sql);
     }
  }

  function AddEvent( $event, $principal=NULL )
  {
    if ($principal == NULL) $principal = &$this->rh->principal;
    $event = $this->PreformatEvent( $event );
    // created_datetime, _user_id
    if (!$event["created_datetime"]) $event["created_datetime"] = date("Y-m-d H:i:s");
    if (!$event["created_user_id"]) $event["created_user_id"] = $principal->data["user_id"];
    // �������� �������� �����������
    // -- �� �������, ����������� � ����������� �������
       $sql = "select * from ".$this->rh->db_prefix.$this->table_prefix."subscribers where ".
              " confirmed = 1 and event=".$this->rh->db->Quote($event["event"]).
              " and module_id=".$this->rh->db->Quote($event["module_id"]).
              " and item_hash=".$this->rh->db->Quote($event["item_hash"]).
              " and item_id=".$this->rh->db->Quote($event["item_id"]).
              " ";
       $rs =  $this->rh->db->Execute( $sql );
       $a  =  $rs->GetArray();
    // -- �� �������, ����������� � ������
       $sql = "select * from ".$this->rh->db_prefix.$this->table_prefix."subscribers where ".
              " confirmed = 1 and event=".$this->rh->db->Quote($event["event"]).
              " and module_id=".$this->rh->db->Quote($event["module_id"]).
              " and item_hash=".$this->rh->db->Quote("").
              " ";
       $rs =  $this->rh->db->Execute( $sql );
       $a2  =  $rs->GetArray();
    // -- �� ������������ ������� (*)
       $sql = "select * from ".$this->rh->db_prefix.$this->table_prefix."subscribers where ".
              " confirmed = 1 and event=".$this->rh->db->Quote("*").
              " ";
       $rs =  $this->rh->db->Execute( $sql );
       $b  =  $rs->GetArray();
    // --
    $c = array_merge( (array)$a, (array)$a2, (array)$b );
    // ���� �����, �� ������� �������������� ���� �� ����
    if (sizeof($c) == 0) return;
    $already = array();
    foreach( $c as $k=>$v )
    if (!$already[$v["id"]]) { $already[$v["id"]] = $v; $letter_count++; }
    $event["letter_count"] = $letter_count;
    // store into DB ���� �������
    foreach($this->event_fields as $field) $f[] = $this->rh->db->Quote($event[$field]);
    $sql = "insert into ".$this->rh->db_prefix.$this->table_prefix."events (".
           implode(",", $this->event_fields).") values (".implode(",",$f).")";
    $this->rh->db->Execute( $sql );
    $id = $this->rh->db->Insert_ID();

    // ��������� � ���� ������� ��� �����������
    $sql = "insert into ".$this->rh->db_prefix.$this->table_prefix."queue ".
           "(event_id,user_hash,user_id,notifier,email) values ";
    $f = 0;
    foreach( $already as $k=>$v )
    {
      if ($f) $sql.=",";
      $f=1;
      $already[$v["id"]] = 1;
      $sql.="(".$this->rh->db->Quote($id).",".
                $this->rh->db->Quote($v["user_hash"]).",".
                $this->rh->db->Quote($v["user_id"]).",".
                $this->rh->db->Quote($v["notifier"]).",".
                $this->rh->db->Quote($v["email"]).")";
    }
    $this->rh->db->Execute($sql);
  }
  function PreformatEvent( $event )
  {
    // ��������� ������������ ��� �������
    if (!$event["name"])
     if (isset($this->rh->tpl->message_set["Notify/Event:".$event["event"]]))
      $event["name"] = $this->rh->tpl->message_set["Notify/Event:".$event["event"]];
     else
      $event["name"] = $event["event"];
    // ��������� verbose �������� �������, � ������� ��������� �������
    $verbose = 0;
    if (!$event["verbose"])
    {
     $verbose = 1;
     if ($event["item_hash"])
     {
       $sql = "select * from ".$this->rh->db_prefix.$event["item_hash"]." where id=".
              $this->rh->db->Quote( $event["item_id"] );
       $rs  = $this->rh->db->Execute($sql);
       $a   = $rs->GetArray();
       if (sizeof($a) > 0)
       {
         if ($a[0]["name"]) $event["verbose"] = $a[0]["name"];
         if ($a[0]["subject"]) $event["verbose"] = $a[0]["subject"];
         $verbose = isset($event["verbose"])?2:1;
       }
     }
    }
    // ������� � verbose
    if ($verbose && $this->module_tree)
    {
       $sql = "select * from ".$this->rh->db_prefix.$this->module_tree." where id=".
              $this->rh->db->Quote( $event["module_id"] );
       $rs  = $this->rh->db->Execute($sql);
       $a   = $rs->GetArray();
       if (sizeof($a) > 0)
       {
         if ($verbose>1)
          $event["verbose"] .= " (".$a[0]["name"].")"; 
         else
          $event["verbose"] = $a[0]["name"]; 
       }
      
    }
    return $event; 
  }

  function NotifySubscription( $subscriber_id, $notify_type=MODULE_NOTIFY_SUBSCRIBE )
  {
    $event = array();
    // 0. Get Subscriber data
    $sql = "select * from ".$this->rh->db_prefix.$this->table_prefix."subscribers where id=".
           $this->rh->db->Quote( $subscriber_id );
    $rs  = $this->rh->db->Execute( $sql );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) return false;
    $event["letter_subscriber"] = $a[0];
    // 1. Build a letter
    $this->rh->tpl->LoadDomain( $a[0] );
    $tplt = $this->tpl_prefix."notification_".$notify_type.".html";
    $event["letter_subject"]  = $this->rh->tpl->Parse( $tplt.":Subject" );
    $event["letter_prefix"]   = $event["letter_subject"];
    $event["letter_raw"]      = $this->rh->tpl->Parse( $tplt.":Raw" );
    // 3. Build notifier & send
    $notifier = "ModuleNotify_".$event["letter_subscriber"]["notifier"];
    $file = str_replace( "\\", "/", __FILE__);
    $path = preg_replace("/\/[^\/]*$/","/",$file );
    $this->rh->UseClass( "ModuleNotify_log", $path );
    $this->rh->UseClass( $notifier, $path );
    $str = '$notifier = &new '.$notifier.'( &$this->rh );';
    eval($str);
    // 3.2. �������� ���������� ����� 
    if ($notifier->SendLetter($event)) return "ok";
    else return false;
  }

// EOC { ModuleNotify }
}




?>