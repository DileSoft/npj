<?php
/*
    HelperTrakoIssue( &$rh, &$obj ) -- Хелпер для формы редактирования бага
      * у $obj:
          $obj->helper
          $obj->owner

  ---------
  - &TweakForm( &$form_fields, &$group_state, $edit=false ) -- видоизменить коллекцию полей для формы 
      * в наследованных метод родителя вызывается ПЕРЕД своими действиями
      * возвращает новый, правильный вариант списка form_fields, меняет group_state
  - PreSave( &$data, &$principal, $is_new=false ) -- выполнить шампанские действия по видоизменению $data
                                                     заполняет $this->ref
  - Save( &$data, &$principal, $is_new=false ) -- выполнить шаманские действия по сохранению данных из $data, 
                      где последний - хэш-массив вида <поле-значение>, получаемый 
                      перегонным кубом из $form->hash[...]
      * в наследованных метод родителя вызывается ПОСЛЕ своих действий
  - _UpdateRef( &$principal ) -- занимается тем, что сливает из подготовленного массива $this->ref в БД
  - _UpdateRare( &$principal ) -- занимается тем, что сливает из $this->rare в БД
  - _Automate( &$data, &$principal ) -- модифицирует дату на основе $this->unwrapped_refs

  // Важные свойства
  - $this->ref -- массив npj-адресов ЗАПИСЕЙ вида
                  $this->ref["kuso@npj:"] => array( "announce" => 0,  "syndicate" => 0,
                                                    "group*"=> XX, 
                                                    "server_datetime" => XX,    
                                                    "user_datetime"   => XX,
                                                    "need_moderation" => 0, // хотя не уверено
                                                    "keyword" => "ЧтоТо/СЧемТо",
                                                  ),
  - $this->rare -- массив вида ( "announced_id" => 783, "announced_supertag" => "kuso@npj:todo", )
  - $this->unwrapped_refs -- массив с готовыми анврапнутыми рефами

  // Что умеет делать
  - Переставляет группу "Классификация" вверх
  - [Добавляет дополнительные поля в эту группу]

=============================================================== v.1 (ariman@gmail.com)
*/

class HelperTrakoIssue extends HelperRecord
{
  var $ref; // массив npj-адресов, с которыми нужно связать через рефы
            // это касается ключслов и потом будет расширено в постах
  var $rare; // массив значений "редких" полей

  // -----------------------------------------------------------------
  // - переставим группу "ref" "вверх"
  // - допишем нужные поля
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $trako = &$this->rh->modules["trako"]["&instance"];
    $result =& HelperRecord::TweakForm( &$form_fields, &$group_state, $edit );

    $debug = &$this->rh->debug;

    // 0. не забыть исковеркать group_state
    if ($edit) $add = "1"; else $add = "0";

    // 1. вычёркиваем группу "реф"
    $new_groups = array();
    $gs=""; $c=0;
    foreach( $result as $k => $v)
     if ($k == "ref") $group_ref = &$result[$k];
     else
     {
       $new_groups[$k] = &$result[$k];
       $gs.= $group_state[$c++];
     }

    // 2. добавляем её вверху всегда открытой
    $new_groups_2 = array( "ref" => &$group_ref );
    $gs_2="0"; $c=0;
    foreach( $new_groups as $k => $v)
    {
      $new_groups_2[$k] = &$new_groups[$k];
      $gs_2.= $gs[$c++];
    }

    // - фиксируем преобразования:
    $groups_state=$gs;

    // 3- делаем одно поле обязательным
    $new_groups_2["ref"][0]->config["nessesary"] = 1;
    // 3. Добавляем ещё поля
    $new_groups_2["ref"][] = &new FieldRadio( &$this->rh, 
                        array(
                           "issue_only" => 1,
                           "field" => "issue_consistency",
                           "default" => 1*$this->obj->data["consistency"],
                           "db_ignore" => 1,
                           "tpl_data"  => "field_radio.html:Select",
                            ) ); 
    // 
    $this->rh->UseClass("FieldRadioGrouped", $this->rh->core_dir);
    $severities = array();
    foreach( $trako->config["severity_classes"] as $sev_class )
      $severities[$sev_class] = $this->rh->tpl->message_set["Trako.severity_values"][$sev_class];
    $new_groups_2["ref"][] = &new FieldRadioGrouped( &$this->rh, 
                        array(
                           "field"        => "issue_severity_value",
                           "groups_field" => "issue_severity_class",
                           "default"      => $this->obj->data["severity_value"],
                           "groups" => $severities,
                           "nessesary" => 1,
                           "db_ignore" => 1,
                            ) ); 
    //
    if (!$edit) // при добавлении вставляем только приоритет (если вообще что-то вставляем)
    {
      if ($trako->HasAccess( &$this->rh->principal, &$this->obj->owner, array(), "priority")) 
      {
        $new_groups_2["ref"][] = &new FieldRadio( &$this->rh, 
                            array(
                               "issue_only" => 1,
                               "field" => "issue_priority",
                               "default"   => $this->rh->tpl->message_set["Trako.priority_default"],
                               "db_ignore" => 1,
                               "tpl_data"  => "field_radio.html:Select",
                                ) ); 
      }
    }
    if ($edit) 
    {
      $ctrls = array( "priority"  => "priority", 
                      "status"    => "state_status", 
                      "assign_to" => "developer_id", );
      $accessible = array();
      foreach( $ctrls as $ctrl=>$field )
      if ($trako->HasAccess(  &$this->rh->principal, &$this->obj->owner, $this->obj->data, $ctrl)) 
       $accessible[$ctrl] = $field;
      if (sizeof($accessible) > 0)
      {
        $ctrl_fields = array();
        $msg=&$this->rh->tpl->message_set;
        $this->rh->tpl->MergeMessageset( $this->rh->message_set."_form_Issue", $trako->messagesets_dir );
        $f=0;
        foreach( $accessible as $ctrl=>$field )
        {
          if ($f==0) $field_first = $field;
          switch( $ctrl )
          {
            //-------------------------
            case "priority":   $ctrl_fields[] =  &new FieldRadio( &$this->rh, array(
                                 "issue_only" => 1,
                                 "field" => "issue_".$field,
                                 "name"  => $f?$msg["Form.issue_".$field]:"",
                                 "default"   => $this->obj->data[$field],
                                 "db_ignore" => 1,
                                 "tpl_data"  => "field_radio.html:Select",
                                ) );
                                $f++;
                                break; 
            //-------------------------
            case "status":      
                                // get available statuses
                                $statuses = $trako->config["states"][$this->obj->data["state"]]
                                                          ["statuses"];
                                $status_data = array();                            
                                foreach( $statuses as $status )
                                  $status_data[ $status ] = $trako->config["statuses"][$status];
                                // append by states
                                $state_ranks = $trako->config["states"][$this->obj->data["state"]]
                                                             ["to"];
                                foreach( $state_ranks as $to_state=>$ranks )
                                  foreach( $ranks as $rank )
                                    if ($trako->_HasAccess( &$this->rh->principal, &$this->obj->owner, 
                                                             $this->obj->data, $rank ))
                                      $status_data[ "state_".$to_state ] = " -> ".$trako->config["states"][$to_state]["name"];

//                                $debug->Trace($status_data[$this->obj->data[$field]]);
//                                $debug->Error_R($status_data);

                                $ctrl_fields[] =  &new FieldRadio( &$this->rh, array(
                                 "issue_only" => 1,
                                 "field" => "issue_".$field,
                                 "name"  => $f?$msg["Form.issue_".$field]:"",
                                 "default"   => $this->obj->data[$field],
                                 "data"      => $status_data,
                                 "db_ignore" => 1,
                                 "tpl_data"  => "field_radio.html:Select",
                                ) );
                                $f++;
                                break; 
            //-------------------------
            case "assign_to":   
                                 // get developers
                                 $developer_data = array( 0 => $msg["Form.issue_".$field.".None"] );
                                 $sql = "select u.user_id, u.user_name, u.login, u.node_id from ".
                                              $this->rh->db_prefix."user_groups as ug, ".
                                              $this->rh->db_prefix."groups as g, ".
                                              $this->rh->db_prefix."users as u ".
                                        " where ug.group_id = g.group_id and u.user_id = ug.user_id".
                                        " and g.group_rank >= ".$this->rh->db->Quote($trako->config["security"]["developer"]).
                                        " and g.user_id = ".$this->rh->db->Quote( $this->obj->owner->data["user_id"] ).
                                        " and u.account_type = ".$this->rh->db->Quote( ACCOUNT_USER ).
                                        " order by u.login asc";
                                 $rs  = $this->rh->db->Execute( $sql );
                                 $a   = $rs->GetArray();
                                 foreach($a as $k=>$v)
                                   $developer_data[ $v["user_id"] ] = $v["login"]."@".$v["node_id"];
                                 //
                                 $ctrl_fields[] =  &new FieldRadio( &$this->rh, array(
                                 "issue_only" => 1,
                                 "field" => "issue_".$field,
                                 "name"  => $f?$msg["Form.issue_".$field]:"",
                                 "default"   => $this->obj->data[$field],
                                 "data"      => $developer_data,
                                 "db_ignore" => 1,
                                 "tpl_data"  => "field_radio.html:Select",
                                ) );
                                $f++;
                                break; 
          }
        }
        $this->rh->UseClass("FieldWrapper", $this->rh->core_dir);
        $new_groups_2["ref"][] = &new FieldWrapper( &$this->rh, 
                            array(
                               "issue_only" => 1,
                               "fields"     => &$ctrl_fields,
                               "field"      => "issue_".$field_first,
                               "tpl_fields_row"   => "field_wrapper.html:Simple_Cols",
                               "tpl_data"         => "field_wrapper.html:Row_w100",
                               "db_ignore"  => 1,
                                ) ); 
      }
    }

      
    /*
                                  "priority"    => 10, // 20=GROUPS_MODERATORS   manager
                                  "assign_self" => 5, 
                                  "assign_to"   => 10,
                                  "status"      => 10,
    */


    $this->rh->debug->Trace("Form tweaked");
    return $new_groups_2;
  }

  // -----------------------------------------------------------------
  //  - правки в данных ДО записи в records
  function &PreSave( &$data, &$principal, $is_new=false ) 
  { 
    // 1. всегда обновляем user_datetime
    $data["user_datetime"] = date("Y:m:d H:i:s");
    // 2. говорим, что это принадлежит модулю
    $data["by_module"] = "trako";
    // E. вызовем родительский Save( d,p )
    return HelperRecord::PreSave( &$data, &$principal, $is_new );
  }

  // -------------------------------------------------------------------
  // маленький патч, позволяющий запретить синдицирование багов в ленты.
  function __SyndicateMode( $data, $type )
  {
    return -10; // never!!!!
  }

  // -------------------------------------------------------------------------
  // - надо сохранить все поля в issues
  function Save( &$data, &$principal, $is_new=false ) 
  { 
    $trako = &$this->rh->modules["trako"]["&instance"];
    
    $trans = array(
            "record_id"      => "record_id",
            "project_id"     => "user_id",
            "issue_no"       => "issue_no",
            "consistency"    => "issue_consistency",
            "severity_class" => "issue_severity_class",
            "severity_value" => "issue_severity_value",
            "developer_id"   => "issue_developer_id",
                  );

    $issue_fields = array();

    // raw fields
    foreach($trans as $to=>$from)
      if (isset($data[$from])) $issue_fields[$to] = $data[$from];

    // if adding
    if (!isset($data["&issue"])) // adding!
      $issue_fields["reporter_id"] = $principal->data["user_id"];

    // priority
    if (isset($data["issue_priority"]) && 
        $trako->HasAccess( &$principal, &$this->obj->owner, array(), "priority")) 
          $issue_fields["priority"]   = $data["issue_priority"];
    else  if (isset($data["&issue"]))
            $issue_fields["priority"] = $data["&issue"]["priority"];
          else
            $issue_fields["priority"] = $this->rh->tpl->message_set["Trako.priority_default"];

    // change status
    if (isset($data["&issue"])) // editing
    {
      $old_state_params = array( "state"  => $data["&issue"]["state"],
                                 "status" => $data["&issue"]["state_status"],
                               );
      $new_state_params = $old_state_params;
      //
      if (isset($data["issue_state_status"])) // there is data about state/status
         if (strpos($data["issue_state_status"],"state_") === 0) // changing state
         {
           $target_state = substr($data["issue_state_status"],6);
           $new_state_params["state"] = $target_state;
         }
         else // changing status
         {
           $target_status = $data["issue_state_status"];
           $new_state_params["status"] = $target_status;
         }
      //
      if (isset($data["issue_state"])) // there is personal about state
      {
         $target_state = $data["issue_state"];
         $new_state_params["state"] = $target_state;
      }
    }
    else // set defaults if insert
    {
      $new_state_params = NULL;
    }
    // go state workflow
    $state_params = $trako->ChangeState( &$principal, &$this->obj->owner, $data["&issue"], $new_state_params,
                                         false,                                 // do not log ???????
                                         $data["issue_assign_self"]?true:false  // do not check security
                                       );
    $issue_fields["state"]        = $state_params["state"];
    $issue_fields["state_status"] = $state_params["status"];
    $issue_fields["state_sort"]   = 1*$trako->config["states"][$state_params["state"]]["sort_weight"] +
                                    1*$trako->config["statuses_sort_weight"][$state_params["status"]];

    if (($trako->config["states"][$state_params["state"]]["auto_assign"] == TRAKO_AUTOASSIGN_STRONG)
        ||
        (($trako->config["states"][$state_params["state"]]["auto_assign"] == TRAKO_AUTOASSIGN_WEAK)
         &&
         ($data["&issue"]["developer_id"] == 0))
       )
    {
      $issue_fields["developer_id"] = $principal->data["user_id"];
    }

    foreach($issue_fields as $k=>$v)
     $issue_fields[$k] = $this->rh->db->Quote($v);

    if (!isset($data["&issue"])) // adding!
    {
      $this->rh->db->Execute( "insert into ".$this->rh->db_prefix."trako_issues (record_id) values (".
                              $issue_fields["record_id"].")");
    }

    $glued = ""; $f=0;
    foreach($issue_fields as $k=>$v)
    {
      if ($f) $glued.=", "; else $f=1;
      $glued.= $k."=".$v;
    }
    $this->rh->db->Execute( "update ".$this->rh->db_prefix."trako_issues set ".
                            $glued.
                            " where record_id = ".$issue_fields["record_id"]);

    // call parent
    HelperRecord::Save( &$data, &$principal, $is_new );
  }

// EOC { HelperTrakoIssue }
}


?>