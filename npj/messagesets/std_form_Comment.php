<?php

  $this->message_set = array(
     "Form.formatting"             => "Формат текста",
     "Form.formatting.Data" => array( "wacko"=>"Вики/вака-разметка", 
                                       "simplebr"=>"Только переносы строк", 
                                       "rawhtml"=>"Голый HTML" ),

     "Form.user_name" => "Автор комментария",
     "Form.subject"   => "Заголовок",
     "Form.body" => "Текст комментария",

     "Form.pic_id"          => "Ваш аватар",
     "Form.pic_id.Desc"     => (($this->config->principal->data["node_id"] == $this->config->node_name )
                                  ?("Вы можете изменить ".$this->config->Link(
                                    $this->config->principal->data["login"]."/profile/pictures",
                                    "список ваших картинок")):""),

     "Form.subscription"   => "Подписаться на:",
     "Form.subscription_tree"      => "все последующие комментарии к&nbsp;этой записи",
     "Form.subscription_childs"    => "всю дискуссию, касающуюся вашего комментария",
     "Form.subscription.Desc"   => "Все ответы прямо на Ваш комментарий и так придут Вам по почте ".
                                   "(если Вы её указали в профиле, конечно)",

     "Form._Name"             => "Добавить комментарий",
     "Form._Group.0"          => "Информация о вас (подпись, юзерпик)", 
     "Form._Group.1"          => "Комментарий",
   );

?>
