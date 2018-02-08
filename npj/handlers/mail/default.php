<?php

 $command = $rh->base_full."node/mail";

 $rs = $db->Execute("SELECT state FROM ".$rh->db_prefix."npz where command=".$db->Quote($command));
 if ($rs->fields["state"]==2) 
 {
  $db->Execute("UPDATE ".$rh->db_prefix."npz SET state=0 where command=".$db->Quote($command));
 }
 if ($rs->fields["state"]==1)
 {
   //??? use tepmlates
   $_html = "<b>ALERT!</b><br> mail2npj problems!";
   $_text = "ALERT!\n mail2npj problems!";

   $recipients = array("admin <".$rh->urgent_mail.">");
   $from = "".$tpl->message_set["Mail:From"]." <".$rh->node_mail.">";
   $subject = "Alert ftom mail2npj";

   $this->prepMail($subject, $_html, $_text, $from);
   $this->sendMail($recipients);

   $db->Execute("UPDATE ".$rh->db_prefix."npz SET state=2 where command=".$db->Quote($command));
   return DENIED;
 }

 $rh->UseLib("Net_Socket", "PEAR");
 $rh->UseLib("Net_POP3", "PEAR");
 $rh->UseLib("mailparse", "MailReceive");


 $pop3 =& new Net_POP3();

 if (!$pop3->connect($this->rh->node_mail_pop, 110)) $debug->Error("Couldnt connect to ".$this->rh->node_mail_pop);
 if (!$pop3->login($this->rh->node_mail_login, $this->rh->node_mail_passw, false)) $debug->Error("Couldnt login");
 $db->Execute("UPDATE ".$rh->db_prefix."npz SET state=1 where command=".$db->Quote($command));



 $parser =& new MailParse();

 $this->parser =& $parser;

 $tpl->MergeMessageSet( $rh->message_set."_mail_integration");
 $dontdelete = $tpl->message_set["DontDeleteNpjCode"]; 

 if ($pop3->numMsg()>10) $num = 10;
 else $num = $pop3->numMsg();

 for ($i=1; $i<=$num; $i++)
 {
   $this->data["body"] = $pop3->getMsg($i);
   $message = str_replace("\r","",$this->data["body"]);
   $res = $parser->parse($message);
   if (!is_array($res) || (!$res["html"] && !$res["text"])) 
   {
     $this->data["error"] = "PARSER: ".$parser->error;
     $this->Save();
   }
   else 
   {
     if ($res["text"])
      $text = $res["text"];
     else
      $text = $res["html"];

     $this->data["error"] = "";
     $this->data["text"] = $res["text"];
     $this->data["html"] = $res["html"];
     $this->data["message"] = $message;
     $raw_headers = explode("\n\n", $message);
     $this->data["headers"] = $parser->explodeHeaders($raw_headers[0]);

     //$debug->Trace_R($this->data["headers"]);
     $debug->Trace($parser->um_decode($this->data["headers"]["subject"]));

     // $debug->Error( "mail received", 4 );

     if ($parser->um_decode($this->data["headers"]["subject"])=="REPLICATION")
     {
      $handler = "repreceive";
     }
     else if (preg_match("/NPJCODE:([0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-Fa-f]{32})/",$text,$matches) ||
         preg_match("/([0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-Fa-f]{32})/",$this->data["headers"]["to"],$matches) ||
         preg_match("/([0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-Fa-f]{32})/",$this->data["headers"]["in-reply-to"],$matches) ||
         preg_match("/([0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-Fa-f]{32})/",$this->data["headers"]["references"],$matches)
        )
     {
      $this->data["npjcode"] = $matches[1];

      $text1 = preg_replace("/^([^\n]*)NPJCODE:[0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-F]{32}\s*\n?(\\1)?(".preg_quote($dontdelete).")?/im","",$this->data["text"]);
      if ($text1==$this->data["text"])
       $text = preg_replace("/NPJCODE:[0-9a-fz\s\n\r]*/i","",$this->data["text"]);
      else
       $text = $text1;
      $this->data["text"] = str_replace($this->data["npjcode"],"",$text);

      $text1 = preg_replace("/^([^\n]*)NPJCODE:[0-9]{1,10}Z[0-9]{1,10}Z[0-9]{1,10}Z[0-9A-F]{32}\s*\n?(\\1)?(".preg_quote($dontdelete).")?/im","",$this->data["html"]);
      if ($text1==$this->data["html"])
       $text = preg_replace("/NPJCODE:[0-9a-fz\s\n\r]*/i","",$this->data["html"]);
      else
       $text = $text1;
      $this->data["html"] = str_replace($this->data["npjcode"],"",$text);

      $uns = trim(strtolower($text));
      if (strtolower($uns)=="unsubscribe" || strtolower($uns)=="unsubskribe")
       $handler = "unsubscribe";
      else
       $handler = "comments";
     }
     else
     {
      $sue = strpos($text, "\n\n");
      if ($sue === false) $handler = "postlite";
      else 
      {
        $head = substr($text,0,$sue);
        if (!preg_match("/^(user|login):/im",$head)) $handler = "postlite";
        else if (!preg_match("/^s?password:/im",$head)) $handler = "postlite";
        else $handler = "postfull";
      }
      if ($handler == "postlite" && !preg_match("/^\s*[a-z0-9\-]+:[a-z0-9]+/i",$text)) $handler = "error";
     }
     $debug->Trace($handler);
     if (!$this->Handler( $handler, "", &$principal ))
     {
       $this->data["error"] = "DEFAULT: ".$handler;
       $this->Save();
     }
   }
 $pop3->deleteMsg($i);
 }

 $pop3->disconnect();
 $db->Execute("UPDATE ".$rh->db_prefix."npz SET state=0 where command=".$db->Quote($command));

 //$debug->Error(1);

?>