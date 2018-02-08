<?php

  $TE = &$this->GenerateTemplateEngine();

  // "AGGREGATE" handler

  // =================================================================================

  if ($params[0] == "error")
  // �������� "����� ��������� ���������"
    $sql = "select user_id, channel_type from ".$rh->db_prefix."channels where state>=".CHANNELS_STATE_ERROR.
           " order by checked_datetime";
  else
  // ��� �� �������� �� ���� ������� N=1 �����, � �������� state=0 (�������� �� checked_datetime asc) 
    $sql = "select user_id, channel_type from ".$rh->db_prefix."channels where state=".CHANNELS_STATE_OK.
           " order by state, checked_datetime";

  $rs  = $db->SelectLimit( $sql, $this->config["channels_per_aggregate"] );
  $a   = $rs->GetArray();

  $this->aggregation_start = time();
  foreach($a as $k=>$v)
  {
    $current = time();
    if ($current- $this->aggregation_start > $this->config["aggregate_timeout"]) break;

    // ������������ Channel �������� 
    $channel = &$this->SpawnChannel( $v["channel_type"], $v["user_id"], CHANNELS_NPJ_ID );
     
    // ����� ��� ���������� 
    $result = $channel->Aggregate();
  }
//  $debug->Error(1);

  // =================================================================================
  // �������
  $tpl->Assign("Html:TITLE",        $this->module_name );
  $tpl->Assign("Preparsed:TITLE",   "" );
  $tpl->Assign("Preparsed:CONTENT", $TE->Parse("default.html") );
  $tpl->Assign("Preparsed:TIGHT", 1);
  $tpl->Assign("Preparsed:TIGHT_COMMENTS", 1);

  $debug->Error( "Aggregator done." );
  return GRANTED;

?>