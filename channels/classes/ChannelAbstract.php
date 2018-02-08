<?php
/*

    “рансл€ци€, один канал

    ChannelAbstract( &$module )
      - $module -- модуль ModuleChannels

  ---------

  Methods:

  * &Load( $id, $is_npj_address=CHANNELS_NPJ_ID ) -- загружает и кладЄт в кэш и $this->data данные о канале
                                       и трансл€ционном аккаунте, на который канал настроен
                                       возвращает NOT_EXIST, если хоть чего-нибудь нет.

  * &ComposeFormGroup() -- подготавливает специфичную группу в форме создани€/редактировани€ канала

  * SaveFromForm( &$form ) -- обновл€ет информацию о существующем канале согласно форме
                               NB: не создаЄт аккаунт/канал!

  * GetAccountClass() -- класс аккаунта дл€ данной трансл€ции

  * Aggregate() -- цикл агрегации канала

  - etc.

  Static:

  * &ChannelAbstract::Factory( &$module, $type = "file", $id=-1, $is_npj_address=0 )
        -- инклюдит файл с нужным классом, создаЄт класс,
           загружает аккаунт, загружает информацию о канале
        -- returns false if any error.
        -- $id=782 or $id="myfeed@rss"

========================================= v.1 (kuso@npj)
*/
define("CHANNEL_INSERT", 0);
define("CHANNEL_UPDATE", 1);

class ChannelAbstract
{
  var $data = array();
  var $type = ""; 
  var $custom_errors = array( CHANNELS_STATE_ERROR => "Unknown error" );

  // overridable
  var $templates = array(
          "subject"   => "{subject}",
          "body"      => "{body}",
          "body_post" => "",
                        );

  function ChannelAbstract( &$module )
  {
    $this->rh= &$module->rh;
    $this->module= &$module;
  }

  function &Factory( &$module, $type = "file", $id=-1, $is_npj_address = CHANNELS_NPJ_ID )
  {
    if (!isset($module->channel_classes[$type])) return $result=false;

    // build class
    $rh = &$module->rh;
    $rh->UseClass($module->channel_classes[$type], $module->classes_dir);
    eval( '$channel = &new '.$module->channel_classes[$type].'( &$module );');

    $channel->type = $type;

    if ($id === -1) return $channel;

    // load channel
    if (NOT_EXIST == $channel->Load( $id, $is_npj_address )) return $result=false;

    return $channel;
  }

  function &Load( $id, $is_npj_address = CHANNELS_NPJ_ID )
  {
    $this->data = &ChannelAbstract::_LoadStatic( &$this->rh, $id, $is_npj_address );
    return $this->data;
  }

  function &_LoadStatic( &$rh, $id, $is_npj_address = CHANNELS_NPJ_ID )
  {
    $cached = &$rh->cache->Restore( "module_channels_".$is_npj_address, $id, 1);
    if (!is_array($cache))
    {
      // 0. load by channel-id
      if ($is_npj_address == CHANNELS_ID)
      {
        $sql = "select * from ".$rh->db_prefix."channels where channel_id = ".
               $rh->db->Quote( $id );
        $rs  = $rh->db->Execute( $sql );
        $a   = $rs->GetArray();
        if (!sizeof($a)) $data = NOT_EXIST;
        else 
        {
          $_data = $a[0];
          $data = $rh->object->_LoadById( $_data["user_id"], 3, "account" );
        }
      }
      else
      {
        // 1. load npj account.
        if ($is_npj_address == CHANNELS_NPJ_ID)
          $data = $rh->object->_LoadById( $id, 3, "account" );
        if ($is_npj_address == CHANNELS_NPJ_ADDRESS)
          $data = $rh->object->_Load( $id, 3, "account" );
      }

      // 2. load channel-specific
      if ($data != NOT_EXIST) 
      {
        if (!is_array($_data))
        {
          $sql = "select * from ".$rh->db_prefix."channels where user_id = ".
                 $rh->db->Quote( $data["user_id"] );
          $rs  = $rh->db->Execute( $sql );
          $a   = $rs->GetArray();
          if (!sizeof($a)) $_data = NOT_EXIST;
          else             $_data = $a[0];
        }
        if ($_data == NOT_EXIST) $data = NOT_EXIST;
        else
          foreach( $_data as $k=>$v )
            if (!is_numeric($k))
              $data["channel:".$k] = $v;
      }
      
      // 3. store to cache
      $cached = &$data;
      $rh->cache->Store( "module_channels_".$is_npj_address, $id, 1, &$cached);
    }
    return $cached;
  }

  // build form group -- override in childs!
  function &ComposeFormGroup()
  { return false; }

  function SaveFromForm( &$form, $user_id = NULL )
  {
    $rh =& $this->rh;
    $db =& $rh->db;

    // convert form->hash into acceptable data
    foreach( $form->hash as $name=>$field ) $data[$name] = $field->data;

    if (!isset($user_id)) $data["user_id"] = $this->data["user_id"];
    else $data["user_id"] = $user_id;

    // prepare data for Save()
    $channel_data = array();
    $channel_data["user_id"] = $data["user_id"];
    $channel_data["channel_type"] = $this->type;
    $channel_data["state"] = 0;
    $channel_data["state_verbose"] = "Not checked yet";
    $channel_data["managing_user_id"] = $rh->principal->data["user_id"];
    $channel_data["checked_datetime"] = "0000-00-00 00:00:00";

    $channel_data["template_subject"]   = $data["template_subject"];
    $channel_data["template_body"]      = $data["template_body"];
    $channel_data["template_body_post"] = $data["template_body_post"];

    $this->MapFormToChannel(&$form, &$channel_data);

    $this->Save($channel_data, $data, $user_id ? CHANNEL_INSERT : CHANNEL_UPDATE);
     
  }

  function Save( &$channel_data, $account_data, $mode = CHANNEL_UPDATE)
  {
    $rh =& $this->rh;
    $db =& $rh->db;

    // update account data
    if (isset($account_data["user_name"]))
    {
     $sql = "UPDATE ".$rh->db_prefix."users SET ".
       "user_name=".$db->Quote($account_data["user_name"]).
       " WHERE user_id=".$db->Quote($account_data["user_id"]);
     $db->Execute($sql);
    }

    // managing -> owner
    if (isset($channel_data["managing_user_id"]))
    {
     $sql = "UPDATE ".$rh->db_prefix."users SET ".
       "owner_user_id=".$db->Quote($channel_data["managing_user_id"]).
       " WHERE user_id=".$db->Quote($account_data["user_id"]);
     $db->Execute($sql);
    }

    if (isset($account_data["bio"]))
    {
     $sql = "UPDATE ".$rh->db_prefix."profiles SET ".
       "bio=".$db->Quote($account_data["bio"]).
       " WHERE user_id=".$db->Quote($account_data["user_id"]);
     $db->Execute($sql);
    }

    // update specific data
    foreach($channel_data as $k=>$v)
    {
      $v = $db->Quote($v);
      $fields[] = $k;
      $values[] = $v;
      $update[] = $k."=".$v;
    }

    if ($mode==CHANNEL_INSERT)
      $sql = "INSERT INTO ".$rh->db_prefix."channels (".implode(",", $fields).") VALUES ".
             "(".implode(",", $values).")";
    else
      $sql = "UPDATE ".$rh->db_prefix."channels SET ".implode(",", $update).
             " WHERE user_id=".$db->Quote($account_data["user_id"]);
    $db->Execute($sql);
  }

  function MapFormToChannel (&$form, &$channel_data)
  {
   /* 
   For override.
   Maps form to this fields:
     * source  
     * access_login  
     * access_pwd  
     * access_more
     * formatting
   */   
  }

  function CreateAccount ($login)
  {
    $rh =& $this->rh;
    $db =& $rh->db;

    $node = $this->type."/".$rh->node_name;

    $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($login)." AND node_id=".$db->Quote($node));
    if ($rs->RecordCount()==0)
    {
     $sql = "INSERT INTO ".$rh->db_prefix."users (login,node_id,alive,account_class,password) VALUES (".
       $db->Quote($login).", ".
       $db->Quote($this->type).", ".
       $db->Quote(1).", ".
       $db->Quote($this->GetAccountClass()).", ".
       $db->Quote("none").
       ")";
     $db->Execute($sql);

    //попул€ци€
     $account = &new NpjObject( &$rh, $login."@".$node );
     $node_principal = &new NpjPrincipal( &$rh );
     $rh->principal->MaskById(2);
     $account->Handler( "populate", array("foreign"=>1,), &$node_principal );
     $rh->principal->UnMask();

     $account->Load(1);

     $sql = "INSERT INTO ".$rh->db_prefix."profiles (user_id) VALUES (".$db->Quote($account->data["user_id"]).")";
     $db->Execute($sql);

     //настройки
     // ??? not tested (15.11.2004 04:52)
     if ($this->module->config["account_preset_users"])
     {
       $sql = "update ".$rh->db_prefix."users set ";
       $f=0;
       foreach( $this->module->config["account_preset_users"] as $field=>$value )
       { if ($f) $sql.=","; else $f=1;
         $sql.= $field."=".$db->Quote($value);
       }
       $sql.=" where user_id=".$db->Quote($account->data["user_id"]);
       $db->Execute($sql);
     }

     return $account->data["user_id"];
    }

    return false;
  }

  function GetAccountClass()
  {
    return "channels--".$this->type;
  }

  // ======================================================================================
  // ј√–≈√ј÷»я -----
  function Aggregate()
  {
    // 1. get channel contents
    $records = $this->_GetChannelContents();

    $this->_UpdateState( is_array($records)?CHANNELS_STATE_OK:$records );

    // 2. post `em
    if (is_array($records))
      foreach( $records as $k=>$v )
        $this->_Post( $v );

  }

  // запросить содержимое канала
  // если что, ошибка канала
  // заполнить массив дл€ постера
  // здесь -- абстрактный, всегда возвращает пустоту.
  function _GetChannelContents()
  {
    return array();
  }

  // сохранение одного поста.
  /*
    subject, body, user_datetime
    guid_hash, author 
    & stuff.
  */
  function _Post( $record_data )
  {
    $rh = &$this->rh;
    $db = &$rh->db;
    $principal = &$rh->principal;

    $principal->MaskById( $this->data["user_id"] );
    
    $record =& new NpjObject(&$rh, $this->data["login"]."@".$this->data["node_id"]."/".$rh->node_name.
                                   ":1"); // for a "post" tag generation.

    $record->data["by_module"] = $this->module->config["subspace"];
    $record->data["type"] = RECORD_POST;
    $record->data["user_id"] = $this->data["user_id"];
    $record->data["tag"] = "1"; // "post" tag generation

    $formatting = $this->data["channel:formatting"];
    $record->data["formatting"] = $formatting;

    //
    if ($record_data["user_datetime"])
      $record->data["user_datetime"] = $record_data["user_datetime"];
    $pass2 = array( "subject", "body", "body_post" );
    foreach($pass2 as $v)
      $record->data[$v] = $this->ParseTemplate( $v, $record_data );

    // after-body-post
    if ($this->module->config["body_post_formatter"])
      $record->data["body_post"] = $rh->tpl->Format($record->data["body_post"],
                                                    $this->module->config["body_post_formatter"]);

    // ready-to-save
    $record->data["strict:body_post"] = $record->data["body_post"];

    // this post is always public!
    $record->data["group1"]=0; $record->data["group2"]=0; 
    $record->data["group3"]=0; $record->data["group4"]=0;

    $record->Save();
    $principal->UnMask();

    // GET RECORD ID!
    $record_id = $record->data["record_id"];

    // insert record_handle into r1_channels_items
    $sql = "insert into ".$rh->db_prefix."channels_items (record_id, author, guid_hash, channel_id)".
           " values (".
              $db->Quote($record_id).",".
              $db->Quote($record_data["author"]).",".
              $db->Quote($record_data["guid_hash"]).",".
              $db->Quote($this->data["channel:channel_id"]).
           ")"; 
    $db->Execute($sql);

    // return record_id;
    return $record_id;
  }

  // обновить в Ѕƒ состо€ние канала
  function _UpdateState( $new_state = CHANNELS_STATE_OK )
  {
    if ($new_state >= CHANNELS_STATE_ERROR)
      $verbose = $this->custom_errors[$new_state];

    $sql = "update ". $this->rh->db_prefix."channels set ".
           " state = ".        $this->rh->db->Quote($new_state).  ", ".
           " state_verbose    = ".$this->rh->db->Quote($verbose).    ", ".
           " checked_datetime = ".$this->rh->db->Quote(date("Y-m-d H:i:s")).
           " where channel_id=". $this->data["channel:channel_id"];
    $this->rh->db->Execute( $sql );
  }


  // -------------------- ЎјЅЋќЌџ ¬џ¬ќƒј -----------
  function ParseTemplate( $tplname, $record_data )
  {
    $tpl = $this->data["channel:template_".$tplname];

    $dt = strtotime($record_data["user_datetime"]);
    $record_data["dt"] = $record_data["user_datetime"];
    $record_data["d"]  = date("d", $dt);
    $record_data["m"]  = date("m", $dt);
    $record_data["y"]  = date("Y", $dt);
    $record_data["h"]  = date("H", $dt);
    $record_data["i"]  = date("i", $dt);
    $record_data["s"]  = date("s", $dt);

    $stuff = array();
    foreach($record_data as $k=>$v) $stuff["{".$k."}"] = $v;

    $result = strtr($tpl, $stuff);

    return $result;
  }

// EOC { ChannelAbstract }
}


?>