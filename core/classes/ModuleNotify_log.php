<?php
/*
    ModuleNotify_*( &$notify_iteration ) -- ��������

  ---------

  * SendLetter( $event ) -- ��������� ������ �� ������ �������. 
                            ������ �������� ������ �����:
                             * "letter_subject"    \
                             * "letter_abstract"    } - ��� ������� �������� �������
                             * "letter_body"       / 
                             * "letter_event"      -> ������������� "event"
                             * "letter_prefix"     -> ������� ��� ��, ��� �� ��� �������
                             * "letter_subscriber" -> ��������� ����������
                             * "event"
                             * "created_datetime"

  // �������� � ���������

=============================================================== v.0 (KusoMendokusee)
*/
class ModuleNotify_log
{
  var $log_to = "files/notify.log";

  function ModuleNotify_log( &$notify_iteration )
  {
     $this->iteration = &$notify_iteration;
     $this->notify = &$this->iteration->notify;
     $this->rh     = &$this->notify->rh;
     $this->prefix = $this->rh->db_prefix.$this->notify.table_prefix;
  }

  function SendLetter( $event )
  {
    return $this->_SendLetter( $this->AbsoluteUrls($event) );
  }
  function _SendLetter( $event )
  {
    $subscriber = $event["letter_subscriber"];
    // 1. ��������� ������
    $content = "[ ".$event["created_datetime"]." / ".date("Y-m-d H:i:s")." ] -> ".
               $subscriber["notifier"].":".$subscriber["email"].
               " { ".$event["event"]." } ". 
               $this->rh->tpl->Format($event["verbose"], "html2text").
               "\n"; 

    // 2. ����� ����� � � ���� ���������
    if (file_exists($this->log_to) && !is_writable( $this->log_to ) )
     $this->rh->debug->Error( "No access to logfile: ". $this->log_to );

    if (!file_exists( $this->log_to ) && 
        !is_writable( preg_replace("/\/[^\/]*$/","",$this->log_to ) ))
     $this->config->debug->Error( "No access to entire dir for logfile: ". preg_replace("/\/[^\/]*$/","",$this->log_to ) );
    
    $fp = fopen( $this->log_to ,"a" );
    fputs($fp,$content);
    fclose($fp);

    return true;

  }

  function AbsoluteUrls( $event )
  {
    $event["letter_subject"]  = $this->rh->tpl->Format( $event["letter_subject"],  "absurl" );
    $event["letter_abstract"] = $this->rh->tpl->Format( $event["letter_abstract"], "absurl" );
    $event["letter_body"]     = $this->rh->tpl->Format( $event["letter_body"],     "absurl" );
    $event["letter_prefix"]   = $this->rh->tpl->Format( $event["letter_prefix"],   "absurl" );
    return $event;
  }


// EOC { ModuleNotify_log }
}




?>