<?php
  // constants & tunings
  setlocale(LC_CTYPE, array("ru_RU.CP1251","ru_SU.CP1251","ru_RU.KOI8-r","ru_RU","russian","ru_SU","ru"));  
  error_reporting (E_ALL ^ E_NOTICE );
  header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
  header("Content-Type: text/html; charset=windows-1251");


  ob_start("ob_gzhandler");
//  ob_start();

  require("core/classes/RSS.php");
  require("npj/classes/NpjRSS.php");
  $rss = &new NpjRSS( "config.php" );
  $tmp = &$rss->CheckHttpRequest();

  require("core/classes/Debug.php");
  require("core/classes/RequestHandler.php");
  require("npj/classes/NpjRequestHandler.php");

  // вот оно! 
  $dbg = &new Debug( 1 );

  $rh = &new NpjRequestHandler( "config.php" );
  $debug_hook = &$rh->debug;
  $rh->rss = &$tmp;
  if ($rh->rss) $rh->rss->rh = &$rh;


  $dbg->Milestone( "constructor done." );

  $rh->Output( $rh->HandleRequest( ) );

  $dbg->Milestone( "output done ." );
  
  $rh->End();

  $dbg->Flush();

  echo("<hr />");

  $rh->debug->Trace("Total TPL->Parse: ".$rh->tpl->_total_time );
  $rh->debug->Trace("From which TPL->LoadTpl: ".$rh->tpl->_total_time_load );
  $rh->debug->Trace("Total TPL->Format: ".$rh->tpl->_formatter_time );
  $rh->debug->Trace("Total Principal->IsGranted: ".$rh->principal->_security_time );
  $rh->debug->Flush();


  ob_end_flush();

?>
