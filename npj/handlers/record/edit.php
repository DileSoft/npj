<?php
//хэндлер EDIT. Великий и Ужасный. Впрочем, _save страшнее.
/*
  ??? кажется доделанная.
      первый раз вызываются в фазе ДЕФИНИЦИИ ФОРМЫ
      второй раз вызываются внутри сэйва.


  ! работает с парамсами -- определяя адд или пост по $params[0]
  ! что-то по версиям, определяя тоже по парамсам -- $params[0]
  * инклюдит !form_profile
  * портирует данные из формы в $this->data. в лоб. все поля.

*/

  // =================================================================================
  //  ФАЗА 1. снос магик-даты и загрузка данных из БД
  //  
  $tag = $this->tag;
  if (strstr($tag, "MyMagicDate".date("Ymdh"))) $tag = preg_replace("/MyMagicDate[0-9]*/","",$tag); //меджик-дейт сыграл свою роль и может уходить.
  //если мы зашли сюда поредактировать, то загрузим-ка запись
  if ($params[0]!="add" && $params[0]!="post") $this->Load( 4, "record"); 
  //алиас
  $data = &$this->data;
  //если записи ещё нет в природе, то is_new == TRUE
  if (!$data || !is_array($data) || !isset($data["record_id"])) 
  {
   $is_new=TRUE;
   if (!is_array($data)) $data = array();
   if (!isset($this->parent))
     $rh->Redirect( $this->Href( $this->npj_account.":add/".$tag, NPJ_ABSOLUTE, STATE_USE ) );
  }
  // тип текущей записи. Если мы его не знаем, то возьмём объект за уши и отпустим. 
  // Если побежал - то заяц, если побежала - зайчиха.
  if (!$data["type"]) $data["type"]=$this->GetType();
  $type = $data["type"];
  //можно ли редактировать адрес записи. однозначно определяется типом и is_new.
  $show_tag = $is_new && ($type==RECORD_DOCUMENT);  

  // =================================================================================
  //  ФАЗА 2. Если мы редактируем версию, то надо её подгрузить-с.
  //          !! кажется, сейчас не работает.
  if (is_numeric($params[0])) $version_id = $params[0];
  else
  if ($params[0] == "version") $version_id = $params[1];
  if ($version_id)
  {
    $version = &new NpjObject( &$rh, $this->npj_object_address."/versions/".$version_id );
    if (is_array($version->Load(3)))
    {
      $is_version = true;
      $debug->Trace_R( $version->data );
    }
  }

  // =================================================================================
  //  ФАЗА 3. проверка прав доступа
  //          * здесь
  // проверка на банлист

  if ($rh->admins_only_documents)
  if (!$this->HasAccess( &$principal, "acl_text", $rh->node_admins) && $type == RECORD_DOCUMENT)
    return $this->Forbidden("RecordForbiddenEdit");

  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  if ($is_new)
  {
   // проверка того, что можно создавать новые сообщения и документы
    if ($rh->account->data["account_type"] == ACCOUNT_COMMUNITY)
     return $this->Forbidden("AnythingInCommunityIsForbidden");

    if (!$this->parent->HasAccess( &$principal, "owner" ) && 
        !($this->parent->HasAccess( &$principal, "acl_text", $rh->node_admins ) && $this->npj_account==$rh->node_user ) &&
        !(($type == RECORD_DOCUMENT) && $this->parent->HasAccess( &$principal, "acl", "add" ))) 
     return $this->Forbidden("RecordForbiddenAdd");
  }
  else
  { // проверка, можно ли редактировать

    if (($rh->account->data["account_type"] != ACCOUNT_USER) 
        && $rh->account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS)) ;
    else
      if ((($type == RECORD_MESSAGE) && $this->HasAccess( &$principal, "owner" ))
          ||
          (($type == RECORD_DOCUMENT) && ($this->HasAccess( &$principal, "acl", "write" ) || 
            ($this->HasAccess( &$principal, "acl_text", $rh->node_admins) && ($this->npj_account==$rh->node_user))
           ) 
          )
         ); 
      else     return $this->Forbidden("RecordForbiddenEdit");
  }
  // -------------------------
  
  // =================================================================================
  //  ФАЗА 4. Инклюдим дефиницию формы
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( $dir."/!form_record.php" );    

  // =================================================================================
  //  ФАЗА 5. Если нет тела формы в POST-запросе, то устанавливаем начальные значения
  //
 if (!isset($_POST["__form_present"])) 
 { 
    $form->ResetSession(); // сбросили предыдущее состояние
    if (!$is_new)   $form->DoSelect( $data["record_id"] ); // прочитали из бд, если запись уже есть
    $debug->Trace("is_new: ".(int)$is_new);
    $debug->Trace("record_id: ".$data["record_id"]);

   // overwrite версией, если редактирование версии происходит 
   // (заметьте, вот здесь используются данные с фазы 2 (загрузка версии)
   if ($is_version)
   {
     $formatters = array( "simplebr" => "body_simpleedit",
                          "wacko"    => "body_wikiedit", // [!!!] Shoo, dirty kukutz! Не мог написать body_wacko =)
                          "rawhtml"  => "body_richedit",    // [!!!] Shoo, dirty kukutz! Не мог написать body_rawhtml =) 
                         );
     // поскольку у нас три поля и каждое называется по-разному, нам надо угадать, в какое из них запаковать исходник
     $version->data[ $formatters[$version->data["formatting"]] ] = $version->data["body"];

     foreach( $version->data as $k=>$v )
      if (!is_numeric($k))
      if ($k != "edited_datetime") // нам не нужно обновлять поле "edited_datetime", чтобы не возбуждать подозрений
       if (isset($form->hash[$k])) 
       {
         $form->hash[$k]->RestoreFromDb( $version->data );
         $form->hash[$k]->StoreToSession( $form->config["session_key"] );
       }
   }
 }

  // =================================================================================
  //  ФАЗА 6. Теперь рисуем форму. Или обрабатываем её, непонятно пока.
  //
 $tpl->Assign("Preview", "" );
 if (!$is_new) $state->Set( "id", $data["record_id"] );
 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);

 if ($data["supertag"][strlen($data["supertag"])-1] == ":")
   $tpl->Assign("Preparsed:TITLE", "Заглавная страница журнала"); // !!! в message_set
 else
   $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
 if (!$is_new) $state->Free( "id" );

 $tpl->Assign("Preparsed:TIGHT", 1);

  // =================================================================================
  //  ФАЗА 7. Если форма не просто обработалась, а обработалась успешно,
  //          начинаем её геморроить. Во всех направлениях.
  //          эта фаза офигеть какая сложная, разбиваем на подфазы
  //          к тому же, она последняя.
  if ($form->success)
  {
    // =================================================================================
    //  ФАЗА 7.1. Переливаем данные из форм-дата в тхиз-дата
      if ($form->hash["subject"])
      if ($form->hash["subject"]->data == "")
        $form->hash["subject"]->data = $this->AddSpaces($form->hash["tag"]->data, " ", "not obsolete me, please");
      // никакого автосохранения в БД формпроцессор не делает сейчас.
      // поэтому заполняем this->data ручками
      foreach( $form->hash as $k=>$v )
      if ($k != "default_show_parameter")
      {                                                                                     
        // создаём db_data на основе data (важно для сложных полей, хотя здесь таких вроде и нет) 
        $form->hash[$k]->_StoreToDb(); 
        $this->data[$k] = $form->hash[$k]->db_data;
        if (is_array($form->hash[$k]->db_data))
        {
          $debug->Trace_R( $form->hash[$k]->db_data );
          foreach ($form->hash[$k]->db_data as $field=>$value)
            $this->data[ $form->hash[$k]->config["fields"][$field] ] = $value;
        }
      }

    // =================================================================================
    //  ФАЗА 7.2. Делаем магические пассы с форматтерами.
    //            Наверное, здесь Кукуц больше разбирается.
      //определяем форматтинг
      if (!$this->data["formatting"]) $this->data["formatting"] = $principal->data["_formatting"];

      //согласно форматтингу выбираем боди
      if ($this->data["formatting"]=="wacko") $this->data["body"] = $this->data["body_wikiedit"];
      if ($this->data["formatting"]=="simplebr") $this->data["body"] = $this->data["body_simpleedit"];
      if ($this->data["formatting"]=="rawhtml") $this->data["body"] = $this->data["body_richedit"];

      //афтерредактор-формат
      $this->data["body"] = $this->Format($this->data["body"], $this->data["formatting"], "after");


    // frozen by kuso@npj, 04042005 due to instability
    /*
    // PREVIEW FEATURE -------------------------------------------------------------------------
    if ($tpl->message_set["ButtonTextCommentPreview"] == $_POST["__button"])
    {
      $_body_post = $this->data["body"];
      $_body_post = $this->Format($_body_post, $this->data["formatting"]);
      $_body_post = $this->Format($_body_post, "paragrafica");
      $_body_post = $this->Format($_body_post, $formatting, "post");
      $_body_post = preg_replace("!</form>!i", "</span>", $_body_post);
      $_body_post = preg_replace("!<form!i", "<span", $_body_post);
      $_body_post = preg_replace("!<input!i", "<input DISABLED='DISABLED'", $_body_post);
      $_body_post = preg_replace("!<textarea!i", "<textarea DISABLED='DISABLED'", $_body_post);
      $tpl->Assign("Preview", $_body_post );
      
      // отрабатываем preview
      $form->invalid = true;
      $tpl->Skin($rh->theme);
        $tpl->Parse( "preview.html", "AFTER_BUTTONS" );
        $result = $form->Parse();
        if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);
      $tpl->UnSkin();

    }
    else
    */
    {
      // =================================================================================
      //  ФАЗА 7.3. Шоу-параметры. Тоже волшебные пассы. Заполняем дополнительные поля
      //            из специального навороченно сложного поля
        if (!$form->hash["default_show_parameter"]->config["only_more"])
        {
          $this->data["default_show_parameter"] = $form->hash["default_show_parameter"] ->data[0];
          $this->data["default_show_parameter_param"] = $form->hash["default_show_parameter"] ->data[1];
          $this->data["default_show_parameter_add"] = $form->hash["default_show_parameter"] ->data[2];
        }
        $this->data["default_show_parameter_more"] = $form->hash["default_show_parameter"] ->data[3];
        $this->data["default_show_parameter_more_param"] = $form->hash["default_show_parameter"] ->data[4];

      // =================================================================================
      //  ФАЗА 7.4, цирковая. Опять кролик. Теперь, если кролик оказался девочкой, мы ему пришиваем хуй.
      //                      А если это крольчиха, но меченая как мальчик, придётся хуй-то отрезать.
        $this->data["type"] = $type;
        if (!$show_tag) $this->data["tag"] = $tag;

      // =================================================================================
      //  ФАЗА 7.5. Мы знаем, что акли, если они были, сохраняются в левые таблицы.
      //            А группы -- в правые. Т.е. прямо в рекордз.
      if (($is_new) && ($type==RECORD_MESSAGE))
       { // ==== проставление group1..4
        if ($form->hash["groups"]->data[0]==-1) // все
        {
         $data["group1"]=0; $data["group2"]=0; 
         $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==0) // никто (но в БД запишется "-1")
        {
         $data["group1"]=$rh->account->group_nobody;
         $data["group2"]=-1; $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==-2) // все конфиденты
        {
         $data["group1"]=$rh->account->group_friends;
         $data["group2"]=-2; $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==ACCESS_GROUP_COMMUNITIES) // всем сообществам
        {
         $data["group1"]=$rh->account->group_communities;
         $data["group2"]=ACCESS_GROUP_COMMUNITIES; 
         $data["group3"]=1*$form->hash["groups"]->radio_data; 
         $data["group4"]=0;
        }
        else
        { //[_items_in_groups] -- мы не работаем с постом бля
         $grps = $form->hash["groups"]->data;
         for ($gnum=0; $gnum<4; $gnum++)
          if (!isset($grps[$gnum])) break;
          else $data["group".($gnum+1)] = $grps[$gnum];
        }
       }

      // =====================================================================================
      //  ФАЗА 7.6, подозрительная. Какие-то волшебные параметры мы ещё дописываем зачем-то.
      //                            Поэтому мы их закомментируем и отпустим кролика.
      /*
        $this->data["disallow_syndicate"] = $form->hash["disallow_syndicate"]->data;
        $this->data["keywords"] = $form->hash["keywords"]->data;
        $this->data["communities"] = $form->hash["communities"]->data;
        foreach( $this->acls as $aclg )
         foreach( $aclg as $acl )
           $this->data[$acl] = $form->hash[$acl]->data;
      */

      // =================================================================================
      //  ФАЗА 7.7. Сохраняем!
        $this->Save();

      // =================================================================================
      //  ФАЗА 7.8. Редирект на сохранённую запись
      //  если мы работали с документом и пометили галочку "перейти к созданию анонса", так и следует делать =)
        $bonus = "";
        if ($data["announce_after"]) $bonus = "/post/announce";
        $rh->Redirect( $this->Href($this->data["supertag"].$bonus, NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

     }//кончился "если не preview"

  }//кончился if (form->success)

  return GRANTED;
?>