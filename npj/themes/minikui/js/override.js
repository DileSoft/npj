
// здесь могут быть какие-то скрипты, специфичные для данной шкуры (не темы)

// прелоадер картинок, вызывается из BODY onload=
function preloadSkinImages( imageRoot )
{
  if (document.images) 
  {
    preloadPics( imageRoot, "tab_1", "tab_1_", "tab_2", "tab_2_", "userpic_def", "userpic_set_def", "userpic_set_def_", "userpic_del", "userpic_del_");
  }
  preloadFlag = true;
}

var theme_init = false;
var skin_init  = false;