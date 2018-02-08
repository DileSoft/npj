<?php
/*
    UtilityRef( &$rh )  -- Вспомогательные процедуры для работы с рефами
    ---------
      - пилотная версия, нуждается в последующем рефакторинге

  * KeywordsToRecordIds( &$account, $keywords_string ) --  Преобразовать список ключслов из строки вида "ТоДо Планы"
                                                           в массив record_ids.
                                                           Не пропускает "сообщества"

  * IsPublishedIn( $record_id, $keyword_ids ) -- опубликовано ли там-то?

  // рефакторинг:
  * переделать код в хелперах, чтобы использовал "это"
  * возможно перенести так, чтобы её создавал не NPJRH, а NPJO

=============================================================== v.0 (Kuso)
*/

class UtilityRef
{
  function UtilityRef( &$rh )
  {
    $this->rh = &$rh;
  }

  function KeywordsToRecordIds( &$account, $keywords_string )
  {
    if (is_string($account)) $_account = &new NpjObject( &$this->rh, $account );
    else                     $_account = &$account;

    $keywords_string = preg_replace("/[,; \n\r\t]+/i", " ", $keywords_string );
    $keywords_string = preg_replace("/[\.@:]+/i",      "/", $keywords_string );
    $kwds = explode(" ", $keywords_string);
    $supertags_q = array();
    foreach( $kwds as $k=>$v )
      $supertags_q[] = $this->rh->db->Quote( $_account->NpjTranslit($_account->npj_account.":".$v) );

    if (sizeof($supertags_q) == 0) return array();

    $sql = "select record_id from ".$this->rh->db_prefix."records ".
           "where supertag in (".implode(",",$supertags_q).")";
    $rs  = $this->rh->db->Execute($sql);
    $a   = $rs->GetArray();
    $result = array();
    foreach($a as $k=>$v)
     $result[] = $v["record_id"];
    return $result;
  }

  function IsPublishedIn( $record_id, $keyword_ids )
  {
    $db = &$this->rh->db;
    if (!is_array($keyword_ids)) $keyword_ids = array( $keyword_ids );
    if (sizeof($keyword_ids) == 0) return false;

    $kids_q = array();
    foreach($keyword_ids as $k=>$v) $kids_q[] = $db->Quote($v);

    $sql = "select record_id from ".$this->rh->db_prefix."records_ref ".
           " where record_id=".$db->Quote($record_id).
           " and keyword_id in (".implode(",",$kids_q).")";
    $rs  = $db->SelectLimit($sql, 1);
    $a   = $rs->GetArray();

    if (sizeof($a) == 0) return false;
    else                 return true;
  }



} // EOC { UtilityRef }

?>