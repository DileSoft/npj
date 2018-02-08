<?php

 // подчасть edit.php

 // есть $account -- тот, кому добавляем
 $this->Handler("_count_friends", array(), &$principal );

 $tpl->theme = $rh->theme;
   $tpl->Parse( "friends.edit.html:Done".$data["account_type"], "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "Операция завершена" ); // !!! to messageset
 $tpl->theme = $rh->skin;
 return GRANTED;

?>