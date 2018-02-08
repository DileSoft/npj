<?php

  // инклюдится из new.php & edit.php

  // =================================================================================
  //  ФАЗА 2. Собираем и инклюдим дефиницию формы
  include( dirname(__FILE__)."/!form_channel.php" );

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

  
  // =================================================================================
  //  ФАЗА 5. Если форма не просто обработалась, а обработалась успешно,
  //          начинаем её геморроить. Во всех направлениях.
  //          эта фаза офигеть какая сложная, разбиваем на подфазы
  //          к тому же, она последняя.
  if ($form->success)
  {
    $login = $form->hash["login"]->data;
    $channel = &$params["&channel"];
    if ($channel->data["login"]) $login = $channel->data["login"];

    // 5.1. Если у нас "add", то популируем внешний аккаунт
    if ($params["mode"]=="add")
      $user_id = $channel->CreateAccount( $login );
    else
      $user_id = NULL;
    
    // 5.2. Сохраняем в БД обновлённые данные канала/аккаунта
    $channel->SaveFromForm( &$form, $user_id );

    // 5.3. Редирект на аккаунт.
    $rh->Redirect( $this->object->Href( $login."@".$channel->type."/".$rh->node_name,
                                        NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);
  }



?>