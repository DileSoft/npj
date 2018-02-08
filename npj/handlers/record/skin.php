<?php

  // !!!!! перенести идею кода в NpjRH

  if (!in_array($params[0], $rh->skins)) $params[0] = $rh->skin;

  $tpl->Skin($params[0]);
  $tpl->LoadDomain( array ( 
       "skin"        => "/".$rh->base_url.$rh->themes_www_dir.$params[0],
       "images"       => "/".$rh->base_url.$rh->themes_www_dir.$params[0]."/images/",
     ) );


  if ((sizeof($params) < 2) || (trim($params[1]) == ""))
  { 
    $_handler = "default"; $_params = array();
  }
  else
  {
    $_handler = $params[1];
    $_params  = array_slice( $params, 2 );
  }
  
  $res = $object->Handler( $_handler, $_params, &$principal );
  $tpl->UnSkin();

  $tpl->Skin($params[0]);
  $tpl->LoadDomain( array ( 
       "skin"        => "/".$rh->base_url.$rh->themes_www_dir.$params[0],
       "images"       => "/".$rh->base_url.$rh->themes_www_dir.$params[0]."/images/",
     ) );

  return $res;

?>