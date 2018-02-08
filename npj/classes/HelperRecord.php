<?php
/*
    HelperRecord( &$rh, &$obj ) -- ������ ��� ����� �������������� ������ (�����)
      * � $obj:
          $obj->helper
          $obj->owner

  ---------
  - &TweakForm( &$form_fields, &$group_state, $edit=false ) -- ������������ ��������� ����� ��� ����� 
      * � ������������� ����� �������� ���������� ����� ������ ����������
      * ���������� �����, ���������� ������� ������ form_fields, ������ group_state
  - PreSave( &$data, &$principal, $is_new=false ) -- ��������� ���������� �������� �� ������������� $data
                                                     ��������� $this->ref
  - Save( &$data, &$principal, $is_new=false ) -- ��������� ��������� �������� �� ���������� ������ �� $data, 
                      ��� ��������� - ���-������ ���� <����-��������>, ���������� 
                      ���������� ����� �� $form->hash[...]
      * � ������������� ����� �������� ���������� ����� ����� ��������
  - _UpdateRef( &$principal ) -- ���������� ���, ��� ������� �� ��������������� ������� $this->ref � ��
  - _UpdateRare( &$principal ) -- ���������� ���, ��� ������� �� $this->rare � ��
  - _Automate( &$data, &$principal ) -- ������������ ���� �� ������ $this->unwrapped_refs

  // ������ ��������
  - $this->ref -- ������ npj-������� ������� ����
                  $this->ref["kuso@npj:"] => array( "announce" => 0,  "syndicate" => 0,
                                                    "group*"=> XX, 
                                                    "server_datetime" => XX,    
                                                    "user_datetime"   => XX,
                                                    "need_moderation" => 0, // ���� �� �������
                                                    "keyword" => "�����/������",
                                                  ),
  - $this->rare -- ������ ���� ( "announced_id" => 783, "announced_supertag" => "kuso@npj:todo", )
  - $this->unwrapped_refs -- ������ � �������� ������������ ������

  // ��� ����� ������
  - ��������� ������ "�������������" / "ref"
  - ��������� � ��� ������ ���� "�������"

=============================================================== v.1 (Kuso)
*/

class HelperRecord extends HelperAbstract
{
  var $ref; // ������ npj-�������, � �������� ����� ������� ����� ����
            // ��� �������� �������� � ����� ����� ��������� � ������
  var $rare; // ������ �������� "������" �����

  // -----------------------------------------------------------------
  // - ������� �������� ������ ref
  // - ������� � ������ ���� keywords
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;
    $owner = $obj->owner; // ������������, � ������� ��������� -- ��������, ������������!
                          // ������ ���� ���������
    if (!isset($owner))
    {
      // !!! panic!
      $rh->debug->Error("HelperRecord: no obj->owner supplied" );
    }


    // 0. �� ������ ����������� group_state
    if ($rh->hide_ref_pane_in_new_record)
     $add=1;
    else
    if ($edit) $add = "1"; else $add = "0";

    // 1. ������� ������ ref ����� body
    $debug->Trace( "HelperForm before: ($edit) ".$group_state );
    $new_groups = array();
    $gs=""; $c=0;
    foreach( $form_fields as $k => $v)
    {
     $new_groups[$k] = &$form_fields[$k];
     $gs.= $group_state[$c++];
     if ($k == "body") { $new_groups["ref"] = array(); $gs.=$add; }
    }
    $group_state = $gs; // ??? ������� �������� �������� ����������, ���������� �� ������

    // 2a. ���������, ����� ��������� ��� �������� �������� ����� ����������, ���� �����������
    //     �������� ���� ��-���������
    if ($edit)
    {
      $kwds = $this->__GetKeywordsDefaultValue( $obj->data["record_id"] );
    } else $kwds = "";
    // --
    if ($obj->post_from) $kwds = $obj->post_from->data["tag"]." ".$kwds;

    // 3. ������� ����
    if (1*$owner->data["options"]["classification"] == 0)
      $classification_model = $this->rh->interface_classification[ 1*$rh->principal->data["options"]["classification"] ];
    else
      $classification_model = $this->rh->interface_classification[ 1*$owner->data["options"]["classification"] ];
    $classification_config = array(
                               "field" => "keywords",
                               "db_ignore" => 1,
                               "add" => 1,
                               "sql" => "select tag as id, tag as value from ".$rh->db_prefix."records where ".
                                       "is_keyword=1 and user_id = ".$db->Quote($owner->data["user_id"]).
                                       " order by tag",
                               // "data" => array(),
                               "default" => $kwds,
                               "preface_tpl" => 1,
                               "tpl_groups" => "field_radio.html:Links",
                               "tpl_row" => "form.html:Row_Span",
                             );
    switch($classification_model)
    {
      case "rubrika_tree":
                $classification_config["tpl_data"]   = "field_rubrika.html:Single";
                $classification_config["tpl_groups"] = "field_rubrika.html:All";
                $classification_config["tpl_row"]    = "form.html:Row";
                break;
      case "rubrika_facet":
                $classification_config["tpl_data"]   = "field_rubrika.html:Facets";
                $classification_config["tpl_groups"] = "field_rubrika.html:All";
                $classification_config["tpl_row"]    = "form.html:Row";
                break;
      default:  ;
    }

    $new_groups["ref"][] = &new FieldStringSelect( &$rh, $classification_config );
    
    $this->rh->debug->Trace("Form tweaked");
    return HelperAbstract::TweakForm( &$new_groups, &$group_state, $edit ) ;
  }

  // refactoring: ������� ��������� �������� ��� ���� �������������� ��������
  function __GetKeywordsDefaultValue( $record_id )
  {
    $rh = &$this->rh; $db = &$rh->db;

    $sql = "select r.record_id, r.subject, r.tag, r.supertag ".
           "from ".$rh->db_prefix."records_ref as ref, ".$rh->db_prefix."records as r ".
           "where ref.record_id=".$db->Quote($record_id).
           " and priority=0 and ref.keyword_user_id=ref.owner_id and ref.keyword_id=r.record_id ".
           " order by r.supertag";
    $rs = $db->Execute( $sql ); 
    $a = $rs->GetArray();
    $kwds=""; $f=0;
    foreach($a as $item)
    { if ($f) $kwds.=" "; else $f=1;
      $kwds.= $item["tag"];
    }
    return $kwds;
  }


  function __SyndicateMode( $data, $type )
  {
    $syndicate = $data["disallow_syndicate"]?0:1;
    if ($type == RECORD_DOCUMENT) $syndicate = -2;
    return $syndicate;
  }
  // -----------------------------------------------------------------
  function &PreSave( &$data, &$principal, $is_new=false ) 
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;

    if (!isset($data["server_datetime"])) $data["server_datetime"] = date("Y-m-d H:i:s");

    $syndicate = $this->__SyndicateMode( $data, $obj->GetType() );
 
    $owner = $obj->owner; // ������������, � ������� ��������� -- ��������, ������������!
                          // ������ ���� ���������

    // 0. transparent "keywords"
    if (!isset($data["keywords"])) // passthru
      $data["keywords"] = $this->__GetKeywordsDefaultValue( $data["record_id"] );

    // 1. ���������� �� $data["keywords"] � $this->ref � ��������������� ����������� ���-������
    $keywords = explode(" ", preg_replace("/[\s\n,\.;\t]+/", " ", 
                             preg_replace("/[:@]+/", "/", $data["keywords"])));
    // -------------
    foreach( $keywords as $keyword )
    { 
      $supertag1 = $owner->_UnwrapNpjAddress( $keyword );
      // ���� ��� �� ������ ���� �� ��� ���������, �� ��������
      if (!isset($this->ref[ $supertag1 ]))
        $this->ref[ $supertag1 ] = array( "announce" => 0, "syndicate" => $syndicate,
            "group1" => 1*$data["group1"], "group2" => 1*$data["group2"], 
            "group3" => 1*$data["group3"], "group4" => 1*$data["group4"], 
            "server_datetime" => $data["server_datetime"],
            "user_datetime"   => $data["user_datetime"],
            "keyword" => $keyword,
                                           "need_moderation" => 0 );
    }

    // ��������� ����
    HelperRecord::_UnwrapRef( &$principal );

    // ��������� ��������
    $data = $this->_Automate( &$data, &$principal );

    return HelperAbstract::PreSave( &$data, &$principal, $is_new);
  }


  // -----------------------------------------------------------------
  function _AutomateLoadRules()
  {
    if (isset($this->ref_rules)) return;
    // 1. �������� ���� �������� ��������� ������
    foreach( $this->unwrapped_refs as $supertag=>$d )
     $supertags[] = $this->rh->db->Quote($supertag);
    $rules = array(); 
    if (sizeof($supertags) > 0)
    {
      $rs = $this->rh->db->Execute( "select rrr.* from ".$this->rh->db_prefix."records_ref_rules as rrr, ".
                                                         $this->rh->db_prefix."records as r where ".
                          "rrr.keyword_id = r.record_id and r.supertag in (".implode(",",$supertags).")" );
      $a = $rs->GetArray();
      foreach( $a as $k=>$v ) $rules[ $v["field"] ][] = $v["value"];
    }
    $this->ref_rules = $rules;
  }
  // -----------------------------------------------------------------
  function &_Automate( &$data, &$principal )
  {
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;

    $this->_AutomateLoadRules();
    $rules = $this->ref_rules;

    // 2.1. ������������ � ��������� ������� �������
    foreach( $rules as $k=>$v )
     if ($k{0} != "_") $data[$k] = $v[0];
    // 2.1.1. �� ������ �������� �� disallow_syndicate, ��� ������������� � �����
    if ($rules["disallow_syndicate"])
      foreach( $this->unwrapped_refs as $supertag=>$__data )
        $this->unwrapped_ref[$supertag]["syndicate"] = 0;


    $debug->Trace("<b>automated</b>");
    //$debug->Trace_R($data);
       
    return $data;
  }
  // -----------------------------------------------------------------
  //  - ���������� ������ �����
  //  NB: ������������ �� ����� ��������� ����� � �� ��������
  //      ���� ���� ������ ����������, � ��. ���� �� ��������� ���������, ������� ��� ���
  function Save( &$data, &$principal, $is_new=false ) 
  { 
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $debug = &$rh->debug;

    $debug->Trace("We at Save Helper Record!");
    
    $this->rare = array_merge((array)$data["rare"], (array)$this->rare);

    // 2. ������� _UpdateRef, ����� �� ������� �� �������������� $this->ref � ��
    $this->_UpdateRef( &$principal );
    // 3. ������� _UpdateRare, ����� �� ������� �� �������������� $this->obj->data["rare"] � ��
    $this->_UpdateRare( &$principal );

    $kwds = &$this->obj->CompileCrossposted( $data["record_id"] );
  }

  function _UnwrapRef( &$principal )
  { $debug = &$this->rh->debug;
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $owner = $obj->owner; // ������������, � ������� ��������� -- ��������, ������������!
                          // ������ ���� ���������
    // ������ ��������� ������������� ����.
    // ���� �������, ��� ����� �� ��� ���� ���������, ��������� ��� �� ����, ������� ����������� ����
    // �.�. � $this->ref ���������� ������� ["kuso@npj:���/����/������"], � ��� ���������� �������� 
    // �� �����.
    // 0. ������ ��������, ���������� �) ��� ����������, ������� �� � ���������
    //    � �) ���������� ���� ��� �����: keyword_id, keyword_user_id
    $refs = array(); $c=0;

    //$debug->Error_R( $this->ref );

    foreach ($this->ref as $supertag=>$ref)
    {
      // 1. �������� ���� �� ���������� $ref
      $supertag2 = explode(":", $supertag); // { 0:account , 1:path }
      $path = explode("/",$supertag2[1]);  
      $kwd_path = explode("/",$ref["keyword"]);  
      array_unshift( $path, $supertag2[0].":" );
      if ($path[ sizeof($path)-1 ] == "") array_pop($path);
      $ffsize = sizeof($path);

      // 2. �������� � ����� �� ����� �����. ������� �� ������� $ref, �������� ������ � $refs
      $addr = ""; $ff=0; $kwd = "";
      foreach( $path as $k=>$part )
      {
        if ($ff>1) { $addr.="/"; }
        if ($ff>1) { $kwd .="/"; }
        $ff++;
        $addr.=$part;
        if ($ff>1) { $kwd.=ucfirst($kwd_path[$k-1]); } 
        $debug->Trace( "add part: $kwd // $addr ($ff of $ffsize)" );
        $rs = $db->Execute("select record_id,user_id,type,is_keyword from ".$rh->db_prefix."records where supertag=".$db->Quote($addr));

        $skip_it=1;
        if ($rs->RecordCount() > 0) $skip_it=0;
        else // �� ����� ������ ��������� � ��, ������� � �������
        if ($addr[ strlen($addr)-1 ] != ":")  // ��� �� �������� ������, �����
        {
          $debug->Trace(" new keyword: $addr " );
          $addr_account = substr( $addr, 0, strpos( $addr, ":") );

          $addr_owner = &new NpjObject( &$rh, $addr_account.":" );
          $addr_owner->Load(4);
          $addr_owner->Handler("add_keyword", array("by_script"=>1, "tag"=>$kwd, "desc"=>""),
                               &$principal);

          $rs = $db->Execute("select record_id,user_id,type,is_keyword from ".$rh->db_prefix."records where supertag=".$db->Quote($addr));
          if ($rs->RecordCount() > 0) $skip_it=0;
        }

        // � ������ ������ ����.
        if (!$skip_it)
        {
          // ����� ����� �������� ��� ���������, ���� ������ ��� ��� �������� �� ����.
          if (($rs->RecordCount() > 0) && (!$rs->fields["is_keyword"]) 
              && ($ff > 1))
             
            $db->Execute( "update ".$rh->db_prefix."records set is_keyword=1 where record_id = ".
                          $db->Quote( $rs->fields["record_id"] ) );
          // ----
          $c++;
          $priority = ($ffsize-$ff);
          if ($f) $sql.=", "; else $f=1;
          if (!isset($refs[ $addr ])) $refs[ $addr ] = $ref; // ������������ � ����, ������ ���� ������ ��� �� ������
          $refs[ $addr ]["keyword"] = $rs->fields["record_id"];
          $refs[ $addr ]["keyword_id"] = $rs->fields["record_id"];
          $refs[ $addr ]["keyword_user_id"] = $rs->fields["user_id"];
          $refs[ $addr ]["priority"] = $priority;
        }

        // ���� �� ���-�� ���������� � ����������� ���������, �����������
        if ($skip_it) break;
      }
    }
    $this->unwrapped_refs = $refs;
    $this->unwrapped_c = $c;
  }
  // --------------------------------------------
  function _UpdateRef( &$principal )
  { $debug = &$this->rh->debug;
    $rh = &$this->rh; $db = &$this->rh->db; $obj = &$this->obj; //  RH, DB, OBJ
    $owner = $obj->owner; // ������������, � ������� ��������� -- ��������, ������������!
                          // ������ ���� ���������

    // ������ ��������� ������������� ����.
    $refs = &$this->unwrapped_refs;
    $c = $this->unwrapped_c;

    // X. ����� ���� �� ������� ������ ������, ��
    $db->Execute( "delete from ".$rh->db_prefix."records_ref where record_id = ".$db->Quote($obj->data["record_id"]) );

//    $debug->Error("check");
    // 1. ���������� ������� ������-������ � �������� ��� �� ������� records_ref
    if ($c > 0)
    {
      $sql = "insert ignore into ".$rh->db_prefix."records_ref ".
             "(record_id, owner_id, keyword_id, keyword_user_id, group1,group2,group3,group4, ".
              "server_datetime, user_datetime, priority, syndicate, need_moderation, announce ) values ";
      $f=0;
      foreach ($refs as $r=>$row)
      { if ($f>0) $sql.=", "; else $f=1;
        $sql.= "(". $db->Quote($obj->data["record_id"]).", ".
                    $db->Quote($obj->data["user_id"]).", ".
                    $db->Quote($row["keyword_id"]).", ". 
                    $db->Quote($row["keyword_user_id"]).", ".
                    $db->Quote($row["group1"]).", ". 
                    $db->Quote($row["group2"]).", ". 
                    $db->Quote($row["group3"]).", ". 
                    $db->Quote($row["group4"]).", ".
                    $db->Quote($row["server_datetime"]).", ". 
                    $db->Quote($row["user_datetime"]).", ". 
                    $db->Quote($row["priority"]).", ". 
                    $db->Quote($row["syndicate"]).", ".
                    $db->Quote($row["need_moderation"]).", ". 
                    $db->Quote($row["announce"]).
               ")";
        $debug->Trace( $r. " = ". $row["announce"] );
      }
      $db->Execute( $sql );
    }
  }

  // ���������� records_rare
  function _UpdateRare( &$principal )
  {
    $rh   = &$this->rh;
    $db   = &$this->rh->db;

    if (sizeof($this->rare))
    {
      // 1. ������������ REPLACE
      $s="";
      foreach( $rh->records_rare as $field )
       if (isset($this->rare[$field]))
         $s.= ", ".$field." = ". $db->Quote( $this->rare[$field] );
      // 2. ���������, ���� ���� ���� ���� ����
      if ($s !== "")
       $db->Execute( "REPLACE INTO ".$rh->db_prefix."records_rare set record_id=".
                     $db->Quote($this->obj->data["record_id"]). $s );

    }
  }

// EOC { HelperRecord }
}


?>