// здесь могут быть какие-то скрипты, специфичные для данной шкуры (не темы)

// прелоадер картинок, вызывается из BODY onload=
function preloadSkinImages( imageRoot )
{
  if (document.images) 
  {
    /*
    preloadPics( imageRoot, "userpic_def", "userpic_set_def", "userpic_set_def_", "userpic_del", "userpic_del_");
    */
  }
  preloadFlag = true;
}

// кустомная инициализация шкуры на onload же
function skin_init( isGuest )
{
}

