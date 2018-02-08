<?php

  $rh->UseClass("ListObject", $rh->core_dir);

  $mode = preg_replace( "/[^0-9a-z_\-\\\\>]/i", "", strtolower($object->_category_mode));
  $modes = $tpl->message_set["RoubricSettings"];
  if (isset($modes[$mode])) unset( $modes["_"] );

  // get from QS
  if ($state->Get("roubric"))
    $mode = $state->Get("roubric");

  $output = array();
  foreach( $modes as $k=>$v )
   $output[] = array(
                  "is_selected" => $k==$mode,
                  "name"        => $v,
                  "Href:name"   => $k=="_"?$state->Minus( "roubric" )
                                          :$state->Plus( "roubric", $k ),
                  );

  if (!isset($modes[$mode])) 
    array_shift($output);

  $list = &new ListObject( &$rh, $output );
  $list->implode = true;
  echo $list->Parse( "roubric.html:SettingsPanel" );

  // RoubricSettings.current_name !!!!
?>