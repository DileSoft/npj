<?php

  // helpful block
  $trako = &$rh->modules["trako"]["&instance"];
  $account = &new NpjObject( &$rh, $object->npj_account );
  $account->Load(2);
  $TE = &$trako->GenerateTemplateEngine( $trako->config["template_engine"] );

  $TE->Assign( "Href:Add",     $account->Href( $account->npj_object_address ).
                               "/".$trako->config["subspace"]."/add" );
  $TE->Assign( "Href:AddSame", $account->Href( $account->npj_object_address ).
                               "/".$trako->config["subspace"]."/add" );
  $TE->Assign( "Href:Trako",   $account->Href( $account->npj_object_address ).
                               "/".$trako->config["subspace"] );

  echo $TE->Parse("interface_menu.html");
?>