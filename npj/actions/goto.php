<?php
/*    Переход по НПЖ-адресу (консольная команда)

   {{Goto
      [to="kuso@npj:todo"] or [param0="kuso@npj:todo"]
      [immediate=1]
      [absolute=1]
   }}

*/

  // если уже есть куда идти, просто идёт
  if ($_REQUEST["goto"] != "") 
    $rh->Redirect( $object->Href( $_REQUEST["goto"], NPJ_RELATIVE, STATE_IGNORE ) );
  if ($params["immediate"] && $params[0]) 
    $rh->Redirect( $object->Href( $params[0], 
                                  $params["absolute"]?NPJ_ABSOLUTE:NPJ_RELATIVE, STATE_IGNORE ) );

  // иначе выдаём форму
  $tpl->Assign("Goto.Address", $params[0]);
  $tpl->Assign("Goto.Form",    $state->FormStart( MSS_GET, 
                                  $object->_NpjAddressToUrl( "node@npj:goto", NPJ_ABSOLUTE) ) );
  return $tpl->Parse("actions/goto.html");

?>