<?php

  // "EDIT" handler
  $tpl->Assign("Preparsed:TITLE", "Правка рапорта"); // !!! to msgset
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


  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if (!$this->HasAccess( &$principal, &$account, $issue, "edit")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  //  ФАЗА 2. Инклюдим дефиницию формы
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue.php" );    

  // =================================================================================
  //  ФАЗА 3. Если нет тела формы в POST-запросе, то устанавливаем начальные значения
  //
  if (!isset($_POST["__form_present"])) 
  { 
     $form->ResetSession(); // сбросили предыдущее состояние
     $form->DoSelect( $issue["record_id"] ); // прочитали из бд, если запись уже есть
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
    // переносим данные из хэша формы  -------------------------------
    foreach( $form->hash as $k=>$v )
    {                                                                                     
      // создаём db_data на основе data (важно для сложных полей, хотя здесь таких вроде и нет) 
      $form->hash[$k]->_StoreToDb(); 
      $issue_data[$k] = $form->hash[$k]->db_data;
      if (isset($form->hash[$k]->db_groups_data))
        $issue_data[ $form->hash[$k]->config["groups_field"] ] = $form->hash[$k]->db_groups_data;
      if (is_array($form->hash[$k]->db_data))
      {
        //$debug->Trace_R( $form->hash[$k]->db_data );
        foreach ($form->hash[$k]->db_data as $field=>$value)
          $issue_data[ $form->hash[$k]->config["fields"][$field] ] = $value;
      }
    }
    // переносим данные из хэша формы  -------------------------------
    $issue_data = array();
    include( dirname(__FILE__)."/__include_issue_save.php" );

    //  Делаем магические пассы с форматтерами. ------------------------
    //  Наверное, здесь Кукуц больше разбирается.
    //определяем форматтинг
    if ($issue["RECORD"]["formatting"]) $issue_data["formatting"] = $issue["RECORD"]["formatting"];
    else                                $issue_data["formatting"] = $principal->data["_formatting"];


    //согласно форматтингу выбираем боди
    if ($issue_data["formatting"]=="wacko")    $issue_data["body"] = $issue_data["body_wikiedit"];
    if ($issue_data["formatting"]=="simplebr") $issue_data["body"] = $issue_data["body_simpleedit"];
    if ($issue_data["formatting"]=="rawhtml")  $issue_data["body"] = $issue_data["body_richedit"];

    //афтерредактор-формат
    $issue_data["body"] = $this->object->Format($issue_data["body"], $issue_data["formatting"], "after");

    // какой номер у бага? ---------------------------------------------
    $issue_no = $issue["issue_no"];
    $issue_data["issue_no"] = $issue_no;
    //$debug->Error_R( $issue_data );

    //  Создаём болванку под запись и сохраняем её ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->Load( 4 ); // load for edit;
    foreach( $issue_data as $k=>$v )
      $issue_record->data[$k] = $v;
    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["&issue"]    = &$issue; // prev. state
    $issue_record->owner = &$account;

    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // всё ок!
    $event_data = array(
                          "record_id"  => $issue_record->data["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue_record->data["user_id"],
                          "event_name" => "issue_edit",
                          "issue"      => $issue,
                          "new_issue"  => $issue_data,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  Редирект на сохранённую запись ------------------------------
    $rh->Redirect( $this->object->Href($issue_record->data["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//кончился if (form->success)

  
  return GRANTED;

?>