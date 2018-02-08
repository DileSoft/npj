<?php

  if ($rh->principal->IsGrantedTo( "noguests" ))
  {
    echo $tpl->Parse( "design/userpanel.html:User"  );
  }
  else
  {
    $tpl->Assign( "UserPanel.Guest", 1 );
    $tpl->Assign( "UserPanel.FORM", $state->FormStart( MSS_POST, "login", "id=\"loginFormHere\" name=\"loginFormHere\"" ) );
    $tpl->Assign( "UserPanel.FORM.Return", $rh->Href( $rh->url, STATE_IGNORE ));
    echo $tpl->Parse( "design/userpanel.html:Guest" );
  }

?>