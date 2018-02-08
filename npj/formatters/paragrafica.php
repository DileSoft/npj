<?php
//     <a name="p1249-1"></a><p...>
//     <a name="h1249-1"></a><hX..>

// занимаемся параграфированием.

 $rh->UseClass("paragrafica", $rh->formatters_classes_dir);

 // we got pure HTML on input.
 $para = &new paragrafica( &$object );
 $result = $para->correct($text);

 $debug->Trace( "paratext<hr />".$text );
 $debug->Trace( "paratocsize<hr />".sizeof($para->toc) );

 foreach($para->toc as $k=>$v)
  $para->toc[$k] = implode("<poloskuns,col>", $v);
 $object->data["body_toc"] = implode("<poloskuns,row>", $para->toc);

 $debug->Trace( "TOC<hr />".$object->data["body_toc"] );

 echo $result;


?>