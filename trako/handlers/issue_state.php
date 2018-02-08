<?php

  // "STATE" handler
  $tpl->Assign("Preparsed:TITLE", "Смена статуса и состояния рапорта"); // !!! to msgset
  $trako = &$this;
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $issue = &$this->LoadIssue( &$account, $this->params["issue_no"], 4 ); // 4 -- for edit
  if ($issue == NOT_EXIST) return $account->NotFound("Trako.IssueNotFound");
  $this->current_issue = $issue;

  // в $params[0] - нам пришло "новое состояние". Смотрим, куда мы идём?
  $state_params = array("state" =>$params[0], "status"=>$issue["state_status"] );
  $state_params = $trako->ChangeState( &$principal, &$account, $issue, $state_params, "dont`t log" );
  if ($state_params["state"] == $issue["state"])
    $changing_state=false;
  else
    $changing_state=true;

  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if ($changing_state)
    if (!$this->HasAccess( &$principal, &$account, $issue, "view")) 
       return $account->Forbidden("Trako.DeniedByRank");
    else;
  else // если только статус меняем, то смотрим, можем ли мы это себе позволить
    if (!$this->HasAccess( &$principal, &$account, $issue, "status")) 
       return $account->Forbidden("Trako.DeniedByRank");
    else;
  
  // =================================================================================
  //  ФАЗА 2. Инклюдим дефиницию формы
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue_state.php" );    

  // =================================================================================
  //  ФАЗА 3. Если нет тела формы в POST-запросе, то устанавливаем начальные значения
  //
  if (!isset($_POST["__form_present"])) 
  { 
     $form->ResetSession(); // сбросили предыдущее состояние
  }

  // =================================================================================
  //  ФАЗА 4. Теперь рисуем форму. Или обрабатываем её, непонятно пока.
  //
  $tpl->theme = $rh->theme;
  $result= $form->Handle();
  $tpl->theme = $rh->skin;
  if ($result !== false) 
  {
    $result = $TE->Parse("interface.html:2").$result;
    $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);
  }

  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  // =================================================================================
  //  ФАЗА 5. Если форма не просто обработалась, а обработалась успешно,
  //          начинаем её геморроить. Во всех направлениях.
  //          эта фаза офигеть какая сложная, разбиваем на подфазы
  //          к тому же, она последняя.
  if ($form->success)
  {
    // забираем из формы status
    if ($form->hash["issue_state_status"])
    {
      $form->hash["issue_state_status"]->_StoreToDb();
      $state_params["status"] = $form->hash["issue_state_status"]->db_data;
    }

    // находим поле с "комментарием"
    //  Делаем магические пассы с форматтерами. ------------------------
    //  Наверное, здесь Кукуц больше разбирается.
    //определяем форматтинг
    $formatting = $principal->data["_formatting"];

    //согласно форматтингу выбираем боди
    if ($formatting=="wacko")    $reason     = $form->hash["body_wikiedit"]->data;
    if ($formatting=="simplebr") $reason     = $form->hash["body_simpleedit"]->data;
    if ($formatting=="rawhtml")  $reason     = $form->hash["body_richedit"]->data;

    //афтерредактор-формат
    $reason = $this->object->Format($reason, $formatting, "after");
    $issue["_logger_reason"] = $reason;

    // какой номер у бага? ---------------------------------------------
    $issue_no = $issue["issue_no"];

    //  сохраняем изменения в БД ---------------------------------------------
    $this->rh->UseClass("HelperAbstract");
    $this->rh->UseClass("HelperRecord");
    $this->rh->UseClass("HelperTrakoIssue", $this->classes_dir);
    //  Создаём болванку под запись и сохраняем её ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->Load( 4 ); // load for edit;
    unset($issue_record->data["keywords"]);
    $issue_record->data["issue_no"]             = $issue_no;
    $issue_record->data["issue_state"]          = $state_params["state"];
    $issue_record->data["issue_state_status"]   = $state_params["status"];
    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["&issue"]    = &$issue; // prev. state
    $issue_record->owner = &$account;
    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // всё ок! Логгер на состояние позван в процессе сохранения записи вроде

    //  Редирект в тот же баг ------------------------------
    $rh->Redirect( $this->object->Href($issue["RECORD"]["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//кончился if (form->success)

  return GRANTED;

?>