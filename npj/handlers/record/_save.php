<?php
/*

  * инклюдит Save Record после подготовки корректного ##$this->data##
  * ничего не сохраняет в основную таблицу
  * сохраняет acls из $this->data["read"], ["write"], etc. 
    ?- наверное есть смысл их как-то запрефиксовать, чтобы не столкнуться
  + будет сохранять также и баклинки  
  * вызывает Mail вообще после всего 
  - должна вызывать институт выходных коннекторов тут же (пока его нет)

  ? нужна подготовленная $this->data:
    + keywords -- сейчас строка. массив npj-адресов
    * communities -- сейчас массив ид-шников, а будет массив npj-адресов 
      ?- может их объединить с ключсловами до этого этапа
    - announce_to -- куда анонсировать, массив ид-шников 
         (не нпж-адреса, потому что анонс он и в подрубрике анонс)

*/

/*
  function Save() 
  работаем с $this->data
  секьюрити не проверяем - оно проверено в edit ????
*/

  if ($rh->debug_file_name)
  {
     $fp = fopen( $rh->debug_file_name ,"a");
     fputs($fp,"[".date("Y-m-d H:i:s")."] (".
               sprintf("%0.4f",$debug->_getmicrotime()).
               ") -- started Save(): ". $this->data["tag"]."\n");
     fclose($fp);
  }

  // =================================================================================
  //  ФАЗА 1. определение вспомогательных переменных
  //  
  $data =& $this->data;
  $debug->Trace("npj_object_address: ".$this->npj_object_address);  

  $owner = &new NpjObject( &$rh, $this->npj_account);
  $owner_data = &$owner->Load( 3 );  // load user_id, node_id, user_name

  // THIS IS DIRTY KOSTILYI!          
  if ($owner_data["node_id"]!=$rh->node_name && strpos($owner_data["node_id"],"/")===false)
  {
   $_nid = explode("/", $owner_data["node_id"]);
   $owner_data["node_id"] = $_nid[0]."/".$rh->node_name;
  }

  //опять-таки, is_new нам нужен, но он определяется существованием супертага. Потому что - и это недокументировано
  // - тот кто пытается сохранить, не заполняет супертага.
  $is_new = (int)($data["supertag"]=="");

  // мы должны проверить, что никто не пытается "создать" существующую запись
  if ($is_new)
  {
    //??? этот запрос в БД лишний, см. двойку уровня кеша. Но без него не работает add с параметрами
    $_data =& $this->_Load($owner_data["login"]."@".$owner_data["node_id"].":".$this->NpjTranslit($data["tag"]), 2, "record");
    if (is_array($_data)) return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset
  }

  // this->is_new проверяет хендлер mail. 
  // ещё чуть ниже передаётся в PreSave
  // он же передаётся и в Save
  $this->is_new = $is_new;
  //алиас
  $type = $data["type"];
  $debug->Trace("isnew:".$is_new.";type=".$type.";formatting=".$data["formatting"]);

  // =================================================================================
  //  ПРОТИВОФАЗА. Перенесено снизу, потому что нужно уметь обратиться 
  //               к хелперу ДО сохранения в БД 
    $helper = &$this->SpawnHelper( HELPER_WEAK ); // если уже есть готовый -- не пересоздаём
    $data   = &$helper->PreSave( &$data, &$principal, $this->is_new );



  // =================================================================================
  //  ФАЗА 2. вызов Save Record
  //  
  //>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<
  require($this->rh->handlers_dir.$this->class."/_save_record.php"); 
  if ($this->_record_save_forbidden) 
     return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset

  // =================================================================================
  //  ФАЗА 3. сохранение BackLinks
  //  
  //Структура БД: from_user_id, from_id, to_user_id, to_id, to_supertag, to_tag

  //Очищаем старые линки
  $db->Execute("delete from ".$rh->db_prefix."links where from_id = ".$db->Quote($data["record_id"]));
  //Конструируем запросы с новыми
  if (is_array($this->backlinks))
  {
   $query = "";
   $from_user_id = $db->Quote($owner_data["user_id"]);
   $from_record_id = $db->Quote($data["_record_id"]);
   foreach ($this->backlinks as $b_supertag) 
   {
    $to_data =& $this->_Load($b_supertag,1); 
    if ($to_data=="empty" || !$to_data) 
    {
      // отрезаем методы здесь.
      $stag = $this->RipMethods($b_supertag);

     $to_data = array();
     $to_data["record_id"] = "0";
     $to_data["supertag"] = $stag; // !!!! проверить
     $to_data["tag"] = $this->backlinks_tag[$b_supertag];
     $user_data =& $rh->account->_Load($this->npj_account, 2); 
     $to_data["user_id"] = $user_data["user_id"];
     // ??? надо проверить node_id, нах нам ссылки на другой ноде
     // kuso: чтобы использовать мощь forthlinks
    }

    if (!$written[$to_data["supertag"]])
    {
     $query .= "(".$from_user_id.", ".$from_record_id.", ".$db->Quote($to_data["user_id"]).", ".
     $db->Quote($to_data["record_id"]).", ".$db->Quote($to_data["supertag"]).", ".$db->Quote($to_data["tag"])."),";
     $written[$to_data["supertag"]] = 1;
    }
   }
   $debug->Trace("Query: ".$query);
   $db->Execute("insert into ".$rh->db_prefix."links (from_user_id, from_id, to_user_id, to_id, to_supertag, to_tag)".
   " values ".rtrim($query,","));
  }

  // Обновление backlinks на эту страницу при isnew
  if ($is_new)
  {
   $db->Execute("update ".$rh->db_prefix."links SET to_id=".$db->Quote($data["_record_id"]).", to_tag=".
                $db->Quote($data["tag"])." WHERE to_supertag=".$supertag);
   // $supertag уже заквочен и определяется в _save_record.php
  }

  // =================================================================================
  //  ФАЗА 4. создание ACLs у новых записей
  //
  if (($is_new) && ($type==RECORD_DOCUMENT))
    { 
      $account = &new NpjObject( &$rh, $this->npj_account );
      $account->Load(2);
      $account_type = $account->data["account_type"];
      $security_type = $account->data["security_type"];

      $acls = array(); foreach($this->acls as $ag) foreach($ag as $acl) $acls[]=$db->Quote($acl);
      $db->Execute( "delete from ".$rh->db_prefix."acls where object_type=".$db->Quote("record").
                          " and object_id=".$data["_record_id"]." and object_right in (".
                          implode(",",$acls).")");
      $sql = ""; $f=0;

      $default_acls= $rh->default_acls[$account_type];
      // заимствование настроек acls из account_classes ---
      if (isset($rh->account_classes[$account->data["account_class"]]))
      {
        $target_class = $rh->account_classes[$account->data["account_class"]];
        if (isset($target_class["acls"])) $default_acls = $target_class["acls"];
      }
      // ---
      if ($security_type == COMMUNITY_SECRET) $default_acls["actions"] = "&";

      $_acls = $this->acls;
      foreach( $_acls as $aclg )
      foreach( $aclg as $acl )
      { if ($f) $sql.=","; else $f=1;
        if (!isset( $this->data[$acl] )) $this->data[$acl] = $default_acls[$acl];
        $sql.="(".$db->Quote("record").",".$data["_record_id"].",".$db->Quote($acl).",".
                  $db->Quote($this->data[$acl]).")";                                         
      }
      if ($sql != "")
       $db->Execute("insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".$sql);

    }

  // =================================================================================
  //  ФАЗА 5. Изменения в классификации, рубрикации, публикации
  //          Реализуется через вызов хелпера
  $helper->Save( &$data, &$principal, $this->is_new );

  // =================================================================================
  //  ФАЗА 6. Фиксируем последние параметры, предназначенные в целом для фцнирования движка
  //  
    $this->tag = $this->data["tag"];
    $this->npj_object_address = 
              $this->data["owner_login"]."@".$this->data["owner_node"].":".$this->NpjTranslit($this->tag);

  // =================================================================================
  //  ФАЗА 7. вызов исходящих коннекторов
  //          * почта
  //          - публикаторы на другие узлы
  //          - публикаторы в другие системы
  $this->Handler( "mail", array(), &$principal );

  if ($rh->debug_file_name)
  {
     $fp = fopen( $rh->debug_file_name ,"a");
     fputs($fp,"[".date("Y-m-d H:i:s")."] (".
               sprintf("%0.4f",$debug->_getmicrotime()).
               ") -- done Save(): ". $this->data["tag"]."\n");
     fclose($fp);
  }

?>