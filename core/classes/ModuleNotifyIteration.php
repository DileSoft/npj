<?php
/*
    ModuleNotifyIteration( &$notify ) -- Итерация

  ---------

  // Свойства и константы

=============================================================== v.0 (KusoMendokusee)
*/
class ModuleNotifyIteration
{
  function ModuleNotifyIteration( &$notify )
  {
     $this->notify = &$notify;
     $this->rh     = &$notify->rh;
     $this->prefix = $this->rh->db_prefix.$this->notify->table_prefix;
  }

  function Handle()
  {
    $timeout = $this->rh->notify_timeout;
    $start = time();
    do $have_more = $this->Step(); while ($have_more && (time()-$start < $timeout));
  }

  function Step()
  {
    // 1. Получить из БД следующее <письмо> 
    $sql = "select * from ".$this->prefix."queue where state<2 order by id asc";
    $rs  = $this->rh->db->SelectLimit($sql,1);
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) { $this->rh->debug->Trace("queue is empty!"); return false; }
    $letter = $a[0];
    // 1.1. Установить в ошибочный статус
    $sql = "update ".$this->prefix."queue set state=".($letter["state"]+1)." where id=".$this->rh->db->Quote($letter["id"]);
    $this->rh->db->Execute($sql);

    // 2. Сформировать по <письму> пакет 
    $event = $this->_PreparePack( $letter );
    // 3. Отправить пакет 
    if (!$event) $letter = false;
    else
    {
      $result = $this->_SendPack( $event );
      if ($result == "error") $letter=false;
    }
    // 4. Обновить состояние события 
    $this->_UpdateEvent( $event );
    // 5. Обновить состояние очереди <писем>
    $this->_RemoveLetter( $letter );

    return true;
  }

  function _PreparePack( $letter )
  {
    if (!$letter) return;
    // 2.1. Получить информацию о событии 
    $sql = "select * from ".$this->prefix."events where id=".$this->rh->db->Quote($letter["event_id"]);
    $rs  = $this->rh->db->Execute($sql);
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) { $this->rh->debug->Trace("no such event!"); return false; }
    $event = $a[0];
    // 2.2. Получить доп.информацию о подписчике 
    $subscriber = $this->_GetSubscriber( $letter );
    if (!$subscriber) return false;
    $event["letter_subscriber"] = &$subscriber;
    // 2.pre3. Обработать внутренние события
    if ($event["module_id"] < 0) // special events
    {
      if ($event["event"] == "notify:report")
       $event = $this->BuildReport( &$this, $event );
    }
    else
    {
      // 2.3. Найти формирователя пакета
      $module = $this->rh->cache->Restore( "modules", $event["module_id"]);
      // 2.4. Формировать пакет
      if ($module)  $event = $module->NotifyBuild( &$this, $event ); 
      else          $event = $this->BuildFreetext( &$this, $event );
    }

    return $event;
  }
  function _SendPack( $event )
  {
    if (!$event) return false;

    $file = str_replace( "\\", "/", __FILE__);
    $path = preg_replace("/\/[^\/]*$/","/",$file );
    // 3.0. принудительное логирование
    $this->rh->UseClass( "ModuleNotify_log", $path );
    if ($this->notify->log_letters && $event["letter_subscriber"]["notifier"] != "log")
    {
      $logger = &new ModuleNotify_log( &$this );
      $logger->SendLetter($event);
    }
    // 3.1. Создать <нотифайер> 
    $notifier = "ModuleNotify_".$event["letter_subscriber"]["notifier"];
    $this->rh->UseClass( $notifier, $path );
    $str = '$notifier = &new '.$notifier.'( &$this );';
    eval($str);
    // 3.2. Передать нотифайеру пакет 
    if ($notifier->SendLetter($event)) return "ok";
    else return "error";
  }

  function _GetSubscriber( $letter )
  {
    if (!is_array($letter))
    {
      $sql = "select * from ".$this->prefix."subscribers where id=".$this->rh->db->Quote($letter);
      $rs  = $this->rh->db->Execute($sql);
      $a   = $rs->GetArray();
      if (sizeof($a) == 0) { $this->rh->debug->Trace("no such subscriber"); return false; }
      $letter = $a[0];
    }
    if (($letter["user_hash"] != "") && ($letter["user_id"] != 0))
    {
      $sql = "select * from ".$this->rh->db_prefix.$letter["user_hash"].
             " where user_id=".$this->rh->db->Quote($letter["user_id"]);
      $rs  = $this->rh->db->Execute($sql);
      $a   = $rs->GetArray();
      if (sizeof($a) > 0) $letter["user"] = $a[0];
    }
    return $letter;
  }

  function _UpdateEvent( $event )
  {
    if (!$event) return;
    $sql = "select count(id) as remains from ".$this->prefix."queue where state=0 and event_id=".
           $this->rh->db->Quote($event["id"]);
    $rs  = $this->rh->db->SelectLimit($sql,1);
    $a   = $rs->GetArray();
    if ($a[0]["remains"] == 0)
    {
      $sql = "update ".$this->prefix."events set state=".
             $this->rh->db->Quote($this->notify->event_state_success).
             " where id=".$this->rh->db->Quote($event["id"]);
      $this->rh->db->Execute($sql);
    }
  }
  function _RemoveLetter( $letter )
  {
    if (!$letter) return;
    $sql = "delete from ".$this->prefix."queue where id=".$this->rh->db->Quote($letter["id"]);
    $this->rh->db->Execute($sql);
  }

  // стандартный формирователь
  function BuildFreetext( &$iteration, $event )
  {
    if (!$event) return;
    // 1. получить элемент из БД
    $sql = "select * from ".$this->rh->db_prefix.$event["item_hash"]." where active=1 and id=".
           $this->rh->db->Quote($event["item_id"]);
    if ($this->helper) $sql = $this->helper->Sql($sql);

    //$this->rh->debug->Error_R($sql);
    $rs  = $this->rh->db->Execute( $sql );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) return false;
    $item = $a[0];

    // 2. отражение полей
    $item = $this->MirrorNotifyItemFields( $item );

    // 3. используем шаблонный движок
    $this->rh->tpl->LoadDomain( $item );
    $event["letter_subject"]  = $this->rh->tpl->Parse( $this->notify->tpl_prefix."builder_freetext.html:Subject" );
    $event["letter_abstract"] = $this->rh->tpl->Parse( $this->notify->tpl_prefix."builder_freetext.html:Abstract" );
    $event["letter_body"]     = $this->rh->tpl->Parse( $this->notify->tpl_prefix."builder_freetext.html:Body" );
    $event["letter_prefix"]   = $this->rh->tpl->Parse( $this->notify->tpl_prefix."builder_freetext.html:Prefix" );

    return $event;
  }

  // формирователь рапорта
  function BuildReport( &$iteration, $event )
  {
    return $event;
  }

  function MirrorNotifyItemFields( $item ) 
  {
    if (!$item["name"] && $item["subject"])   $item["name"] = $item["subject"];
    if (!$item["body"] && $item["body_r"])    $item["body"] = $item["body_r"];
    if (!$item["body"] && $item["abstract"])  $item["body"] = $item["abstract"]; else
    if (!$item["abstract"] && $item["body"]) 
    {
      $abstract = strip_tags( $item["body"] );
      $abstract = str_replace( "\n", " ", $abstract );
      $words    = explode( " ", $abstract);
      $words    = array_slice( $words, 0, 100 );
      $abstract = implode( " ", $words );
      if (strlen($abstract > 500))
        $abstract = preg_replace("/^(.{0,50}[\.,!\?])(\s.*)$/i", "$1", $abstract);
      $item["abstract"] = $abstract;
    }

    $item["_subject"]  = $this->rh->tpl->Format( $item["name"], "typografica" );
    $item["_abstract"] = $this->rh->tpl->Format( $this->rh->tpl->Format( $item["abstract"], "wysiwyg" ), "typografica" );
    $item["_body"]     = $this->rh->tpl->Format( $this->rh->tpl->Format( $item["body"], "wysiwyg" ), "typografica" );

    return $item;
  }

// EOC { ModuleNotifyIteration }
}




?>