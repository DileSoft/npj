<?php

 $tpl->Assign( "id", $params[1] );
 $tpl->Assign( "404", 1 );
 $tpl->Assign( "Preparsed:TITLE", "Комментарий добавлен" ); // to message_set
 $tpl->Parse( "comments.html:Done", "Preparsed:CONTENT");


// unset($rh->object->record);
?>