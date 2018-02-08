<?php

 if ($_REQUEST["a"]) $params[0] = $_REQUEST["a"];
 if ($_REQUEST["b"]) $params[1] = $_REQUEST["b"];

 if (is_numeric($_REQUEST["a"]) && is_numeric($_REQUEST["b"]))
   if ($params[0] < $params[1]) { $t=$params[0]; $params[0]=$params[1]; $params[1]=t; }

 $data = &$this->Load( 4 );
 /*
 $a = $this->_UnwrapNpjAddress($params[0]);
 $a = preg_replace("/\/versions(\/.*)?$/i", "", $a);
 if ($a != $data["supertag"]) 
  $rh->Redirect( $this->Href( $a."/diff", NPJ_ABSOLUTE, STATE_USE ) );
 */

 if ($params[0] === 0) return $this->Forbidden("DiffSame"); // дифф, когда последн€€ верси€ трактуетс€ как перва€, запрещЄн
 $version = &new NpjObject( &$rh, $this->npj_object_address."/versions" );

 return $version->Handler( "diff", array($params[0], $params[1]), &$principal );

?>