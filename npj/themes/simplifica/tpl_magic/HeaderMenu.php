<?php

  $menu = array( 

        $rh->Href( "" )               => "��������� �������� ����",
        $rh->Href( "freshdirectory" ) => "������������ ����" ,

        "http://npj.ru/node/chastyevoprosy/zapisizhurnala/wikisintaksis" 
              => "������",



       );

  $list = &new ListSimple( &$rh, $menu );
  $list->implode = true;
  echo $list->Parse( "plugins/header_menu.html:List" );

?>