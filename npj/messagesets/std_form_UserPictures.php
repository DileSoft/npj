<?php
  $rh = &$this->config;

  $this->message_set = array(
    "Form.description"        => "Имя картинки",
    "Form.have_big"           => "Для записей и журнала (".$rh->user_pictures_big_x."x".$rh->user_pictures_big_y.")",
    "Form.have_small"         => "Для комментариев (".$rh->user_pictures_small_x."x".$rh->user_pictures_small_y.")",

    "Form.is_default"         => "Основная картинка?",
    "Form.is_default.Data"    => array("нет, самая обычная", "да, указать в качестве основной"),

    "Form._Name"      => "Добавить картинку",
    "Form._Group.0"   => "Информация о картинке",
    "Form._Group.1"   => "Два варианта картинки",
   );

?>