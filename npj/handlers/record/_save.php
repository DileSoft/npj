<?php
/*

  * �������� Save Record ����� ���������� ����������� ##$this->data##
  * ������ �� ��������� � �������� �������
  * ��������� acls �� $this->data["read"], ["write"], etc. 
    ?- �������� ���� ����� �� ���-�� ��������������, ����� �� �����������
  + ����� ��������� ����� � ��������  
  * �������� Mail ������ ����� ����� 
  - ������ �������� �������� �������� ����������� ��� �� (���� ��� ���)

  ? ����� �������������� $this->data:
    + keywords -- ������ ������. ������ npj-�������
    * communities -- ������ ������ ��-������, � ����� ������ npj-������� 
      ?- ����� �� ���������� � ����������� �� ����� �����
    - announce_to -- ���� ������������, ������ ��-������ 
         (�� ���-������, ������ ��� ����� �� � � ���������� �����)

*/

/*
  function Save() 
  �������� � $this->data
  ��������� �� ��������� - ��� ��������� � edit ????
*/

  if ($rh->debug_file_name)
  {
     $fp = fopen( $rh->debug_file_name ,"a");
     fputs($fp,"[".date("Y-m-d H:i:s")."] (".
               sprintf("%0.4f",$debug->_getmicrotime()).
               ") -- started Save(): ". $this->data["tag"]."\n");
     fclose($fp);
  }

  // =================================================================================
  //  ���� 1. ����������� ��������������� ����������
  //  
  $data =& $this->data;
  $debug->Trace("npj_object_address: ".$this->npj_object_address);  

  $owner = &new NpjObject( &$rh, $this->npj_account);
  $owner_data = &$owner->Load( 3 );  // load user_id, node_id, user_name

  // THIS IS DIRTY KOSTILYI!          
  if ($owner_data["node_id"]!=$rh->node_name && strpos($owner_data["node_id"],"/")===false)
  {
   $_nid = explode("/", $owner_data["node_id"]);
   $owner_data["node_id"] = $_nid[0]."/".$rh->node_name;
  }

  //�����-����, is_new ��� �����, �� �� ������������ �������������� ���������. ������ ��� - � ��� �����������������
  // - ��� ��� �������� ���������, �� ��������� ���������.
  $is_new = (int)($data["supertag"]=="");

  // �� ������ ���������, ��� ����� �� �������� "�������" ������������ ������
  if ($is_new)
  {
    //??? ���� ������ � �� ������, ��. ������ ������ ����. �� ��� ���� �� �������� add � �����������
    $_data =& $this->_Load($owner_data["login"]."@".$owner_data["node_id"].":".$this->NpjTranslit($data["tag"]), 2, "record");
    if (is_array($_data)) return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset
  }

  // this->is_new ��������� ������� mail. 
  // ��� ���� ���� ��������� � PreSave
  // �� �� ��������� � � Save
  $this->is_new = $is_new;
  //�����
  $type = $data["type"];
  $debug->Trace("isnew:".$is_new.";type=".$type.";formatting=".$data["formatting"]);

  // =================================================================================
  //  �����������. ���������� �����, ������ ��� ����� ����� ���������� 
  //               � ������� �� ���������� � �� 
    $helper = &$this->SpawnHelper( HELPER_WEAK ); // ���� ��� ���� ������� -- �� ����������
    $data   = &$helper->PreSave( &$data, &$principal, $this->is_new );



  // =================================================================================
  //  ���� 2. ����� Save Record
  //  
  //>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<
  require($this->rh->handlers_dir.$this->class."/_save_record.php"); 
  if ($this->_record_save_forbidden) 
     return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset

  // =================================================================================
  //  ���� 3. ���������� BackLinks
  //  
  //��������� ��: from_user_id, from_id, to_user_id, to_id, to_supertag, to_tag

  //������� ������ �����
  $db->Execute("delete from ".$rh->db_prefix."links where from_id = ".$db->Quote($data["record_id"]));
  //������������ ������� � ������
  if (is_array($this->backlinks))
  {
   $query = "";
   $from_user_id = $db->Quote($owner_data["user_id"]);
   $from_record_id = $db->Quote($data["_record_id"]);
   foreach ($this->backlinks as $b_supertag) 
   {
    $to_data =& $this->_Load($b_supertag,1); 
    if ($to_data=="empty" || !$to_data) 
    {
      // �������� ������ �����.
      $stag = $this->RipMethods($b_supertag);

     $to_data = array();
     $to_data["record_id"] = "0";
     $to_data["supertag"] = $stag; // !!!! ���������
     $to_data["tag"] = $this->backlinks_tag[$b_supertag];
     $user_data =& $rh->account->_Load($this->npj_account, 2); 
     $to_data["user_id"] = $user_data["user_id"];
     // ??? ���� ��������� node_id, ��� ��� ������ �� ������ ����
     // kuso: ����� ������������ ���� forthlinks
    }

    if (!$written[$to_data["supertag"]])
    {
     $query .= "(".$from_user_id.", ".$from_record_id.", ".$db->Quote($to_data["user_id"]).", ".
     $db->Quote($to_data["record_id"]).", ".$db->Quote($to_data["supertag"]).", ".$db->Quote($to_data["tag"])."),";
     $written[$to_data["supertag"]] = 1;
    }
   }
   $debug->Trace("Query: ".$query);
   $db->Execute("insert into ".$rh->db_prefix."links (from_user_id, from_id, to_user_id, to_id, to_supertag, to_tag)".
   " values ".rtrim($query,","));
  }

  // ���������� backlinks �� ��� �������� ��� isnew
  if ($is_new)
  {
   $db->Execute("update ".$rh->db_prefix."links SET to_id=".$db->Quote($data["_record_id"]).", to_tag=".
                $db->Quote($data["tag"])." WHERE to_supertag=".$supertag);
   // $supertag ��� �������� � ������������ � _save_record.php
  }

  // =================================================================================
  //  ���� 4. �������� ACLs � ����� �������
  //
  if (($is_new) && ($type==RECORD_DOCUMENT))
    { 
      $account = &new NpjObject( &$rh, $this->npj_account );
      $account->Load(2);
      $account_type = $account->data["account_type"];
      $security_type = $account->data["security_type"];

      $acls = array(); foreach($this->acls as $ag) foreach($ag as $acl) $acls[]=$db->Quote($acl);
      $db->Execute( "delete from ".$rh->db_prefix."acls where object_type=".$db->Quote("record").
                          " and object_id=".$data["_record_id"]." and object_right in (".
                          implode(",",$acls).")");
      $sql = ""; $f=0;

      $default_acls= $rh->default_acls[$account_type];
      // ������������� �������� acls �� account_classes ---
      if (isset($rh->account_classes[$account->data["account_class"]]))
      {
        $target_class = $rh->account_classes[$account->data["account_class"]];
        if (isset($target_class["acls"])) $default_acls = $target_class["acls"];
      }
      // ---
      if ($security_type == COMMUNITY_SECRET) $default_acls["actions"] = "&";

      $_acls = $this->acls;
      foreach( $_acls as $aclg )
      foreach( $aclg as $acl )
      { if ($f) $sql.=","; else $f=1;
        if (!isset( $this->data[$acl] )) $this->data[$acl] = $default_acls[$acl];
        $sql.="(".$db->Quote("record").",".$data["_record_id"].",".$db->Quote($acl).",".
                  $db->Quote($this->data[$acl]).")";                                         
      }
      if ($sql != "")
       $db->Execute("insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".$sql);

    }

  // =================================================================================
  //  ���� 5. ��������� � �������������, ����������, ����������
  //          ����������� ����� ����� �������
  $helper->Save( &$data, &$principal, $this->is_new );

  // =================================================================================
  //  ���� 6. ��������� ��������� ���������, ��������������� � ����� ��� ����������� ������
  //  
    $this->tag = $this->data["tag"];
    $this->npj_object_address = 
              $this->data["owner_login"]."@".$this->data["owner_node"].":".$this->NpjTranslit($this->tag);

  // =================================================================================
  //  ���� 7. ����� ��������� �����������
  //          * �����
  //          - ����������� �� ������ ����
  //          - ����������� � ������ �������
  $this->Handler( "mail", array(), &$principal );

  if ($rh->debug_file_name)
  {
     $fp = fopen( $rh->debug_file_name ,"a");
     fputs($fp,"[".date("Y-m-d H:i:s")."] (".
               sprintf("%0.4f",$debug->_getmicrotime()).
               ") -- done Save(): ". $this->data["tag"]."\n");
     fclose($fp);
  }

?>