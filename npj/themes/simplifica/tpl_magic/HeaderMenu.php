<?php

  $menu = array( 

        $rh->Href( "" )               => "Заглавная страница узла",
        $rh->Href( "freshdirectory" ) => "Пользователи узла" ,

        "http://npj.ru/node/chastyevoprosy/zapisizhurnala/wikisintaksis" 
              => "Помощь",



       );

  $list = &new ListSimple( &$rh, $menu );
  $list->implode = true;
  echo $list->Parse( "plugins/header_menu.html:List" );

?>