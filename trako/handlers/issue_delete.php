<?php

  // "DELETE" handler
  $tpl->Assign("Preparsed:TITLE", "Удаление рапорта"); // !!! to msgset
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

  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if (!$this->HasAccess( &$principal, &$account, $issue, "delete")) 
     return $account->Forbidden("Trako.DeniedByRank");

  // =================================================================================
  //  ФАЗА 2. Инклюдим дефиницию формы
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( dirname(__FILE__)."/!form_issue_delete.php" );    

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

    // какой номер у бага? ---------------------------------------------
    $issue_no = $issue["issue_no"];

    // Пишем заметку о нашем мальчике ---------------------------------------------
    $event_data = array(
                          "record_id"  => $issue["RECORD"]["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue["RECORD"]["user_id"],
                          "event_name" => "issue_delete",
                          "issue"      => $issue,
                          "details"    => $reason,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  Удаляем доп-поля ---------------------------------------------
    $sql = "delete from ".$rh->db_prefix."trako_issues ".
           "where record_id = ".$db->Quote($issue["record_id"]);
    $db->Execute( $sql );

    //  Удаляем запись в records ---------------------------------------------
    // а также всех, кто замешан.
    $record = &new NpjObject( &$rh, $issue["RECORD"]["supertag"] );
    $record->class= "record";
    $params = array();
    $record->Handler("_delete", $params, &$principal );

    //  Редирект в корень ------------------------------
    $rh->Redirect( $account->Href($account->npj_object_address.":trako", NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//кончился if (form->success)

  
  return GRANTED;

?>