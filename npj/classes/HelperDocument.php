<?php
/*
    HelperDocument( &$rh, &$obj ) -- Хелпер для формы редактирования документов
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
   * пустой

=============================================================== v.1 (Kuso)
*/

class HelperDocument extends HelperRecord
{

  // -----------------------------------------------------------------
  // - добавим в группу body поле announce_after
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $new_groups = &HelperRecord::TweakForm( &$form_fields, &$group_state, $edit ) ;

    // 3. добавим поле
    $f = &new FieldCheckboxes( &$rh, array(
                           "field" => "announce_after_",
                           "fields" => array("announce_after"),
                           "db_ignore" => 1,
                           "tpl_row" => "form.html:Row_Span",
                            ) );
    array_push($new_groups["body"], &$f );
    $this->rh->debug->Trace("Form tweaked");
    return $new_groups;
  }

  // -------------------------------------------------------------------------
  // - надо бы обновить disallow comments у всех анонсов
  function Save( &$data, &$principal, $is_new=false ) 
  { 
    $this->rh->db->Execute( "update ".$this->rh->db_prefix."records_rare set announced_disallow_comments=".
                            $this->rh->db->Quote( 1*$data["disallow_comments"] )." where announced_id = ".
                            $this->rh->db->Quote( 1*$data["record_id"] ) );
    HelperRecord::Save( &$data, &$principal, $is_new );
  }

// EOC { HelperDocument }
}


?>