<?php

  // "ADD" handler

  $section = &new NpjObject( &$rh, $this->object->npj_account.":".$this->object->subspace );
  if ($section->Load(2) == NOT_EXIST)
  {
    $section = &new NpjObject( &$rh, $this->object->npj_account.":" );
    $section->Load(2);
  }
  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  $issue = array(); // это для формы.
  
  // =================================================================================
  //  ФАЗА 1. Проверка прав доступа
  if (!$section->HasAccess( $principal, "acl", "actions" ))
    return $section->Forbidden( "Trako.DeniedByActionsAcl" );

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
  }

  // =================================================================================
  //  ФАЗА 4. Теперь рисуем форму. Или обрабатываем её, непонятно пока.
  //
  $tpl->theme = $rh->theme;
  $result= $form->Handle();
  $tpl->theme = $rh->skin;
  if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);

  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TITLE", "Добавление рапорта"); // !!! to msgset

  // =================================================================================
  //  ФАЗА 5. Если форма не просто обработалась, а обработалась успешно,
  //          начинаем её геморроить. Во всех направлениях.
  //          эта фаза офигеть какая сложная, разбиваем на подфазы
  //          к тому же, она последняя.
  if ($form->success)
  {
    // переносим данные из хэша формы  -------------------------------
    $issue_data = array();
    include( dirname(__FILE__)."/__include_issue_save.php" );

    // получаем номер для этой записи.
    $sql = "select max(issue_no) as last_issue from ".$rh->db_prefix."trako_issues ".
           " where project_id = ".$db->Quote($account->data["user_id"]);
    $rs  = $db->Execute( $sql );
    $a   = $rs->GetArray();
    if (sizeof($a) == 0) $issue_no = 1;
    else                 $issue_no = 1* $a[0]["last_issue"] + 1;

    //  Создаём болванку под запись и сохраняем её ---------------------------------------------
    $tag   =  $this->config["subspace"]."/".$issue_no;
    $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
    $issue_record->class = "record";
    $issue_record->data = $issue_data;
    $issue_record->data["issue_no"]      = $issue_no;
    $issue_record->data["tag__leave_as_is"] = true;
    $issue_record->data["tag"]           = $tag;
    $issue_record->data["user_id"]       = $section->data["user_id"];
    $issue_record->data["type"]          = RECORD_POST;
    $issue_record->data["disallow_syndicate"]     = 1;
    $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
    $issue_record->data["group1"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group2"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group3"]        = ACCESS_GROUP_TRAKO;
    $issue_record->data["group4"]        = ACCESS_GROUP_TRAKO;

    $issue_record->owner = &$account;
    $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
    $issue_record->helper = &$issue_helper;
    $issue_record->Save();

    // всё ок!
    $event_data = array(
                          "record_id"  => $issue_record->data["record_id"],
                          "issue_no"   => $issue_no,
                          "project_id" => $issue_record->data["user_id"],
                          "event_name" => "issue_add",
                          "new_issue"  => $issue_data,
                       );
    $this->Handler( "_logger", $event_data, &$principal );
  
    //  Редирект на сохранённую запись ------------------------------
    $rh->Redirect( $this->object->Href($issue_record->data["supertag"], NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

  }//кончился if (form->success)

  
  return GRANTED;

?>