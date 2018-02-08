<?php

  // ����� ��� ���������� ���������
  //
  // ����� �������� ���� �������� "by_script", ������ ��� ���� ���������� �� ������� � ������ ������ ���� ������� ������ � �����.
  //

// 1. �������� ����, ��� ����� ��������� ����� ���������

   $this->Load(1);

//   [!!!] ���������� �� ���� ������������ ������������ �������

    /*
    if ($rh->account->data["account_type"] == ACCOUNT_COMMUNITY)
    if ($params["by_script"]) return; else
     return $this->Forbidden("AnythingInCommunityIsForbidden");
    */

    if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) 
     if ($params["by_script"]) return; else                    
      return $this->Forbidden("YouAreInBanlist");

    if (!$this->HasAccess( &$principal, "acl", "add") )
     if ($params["by_script"]) return; else                    
      return $this->Forbidden("RecordForbiddenAdd");

// 2. �������� ������, ����������� ��� �������� �����
   if (!$params["by_script"])
   {
      include( $dir."/!form_keyword.php" );
      if (!isset($_POST["__form_present"])) 
      { 
        $form->ResetSession();
      }
      $debug->Milestone( "Starting form handler" );
      $tpl->theme = $rh->theme;
      $result= $form->Handle();
      $tpl->theme = $rh->skin;
      if ($result !== false) $tpl->Assign("Preparsed:CONTENT", $result);
      $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);

      if ($form->success)
      {
        // ��������� $tag, $desc, $acl ������ �� �����
        $tag  = $form->hash["tag"]->data;
        $desc = $form->hash["desc"]->data;
        $kacls = array();
        foreach( $this->acls as $g=>$aclg )
        foreach( $aclg as $acl )
         $kacls[$g][$acl] = $form->hash[$acl]->data;
      }
   }
   else
   {
     // ��������� $tag, $desc, $acl �� $params
     $tag  = $params["tag"];
     $desc = $params["desc"];
     $kacls = $params["acls"];

     $_tag = $this->NpjTranslit( $tag );
     if ($this->validate_reserved_words( $_tag, &$rh ) !== 0)
      return DENIED;

   }

   if ($form->success || $params["by_script"])
   { $subject = $tag;
     //$tag = strtolower($tag); 
     $taga = explode (" ", $tag);
     foreach($taga as $i=>$v) $taga[$i] = ucfirst($v);
     $tag = implode("", $taga);
     $tag = preg_replace("/[^a-z0-9\/\-\xc0-\xff\xa8\xb8]/i", "", $tag);
     $tag = trim( $tag, "/" );

// -  ���������, ��� �� � ��� ��� �����?

// 3. ��������� ������ user@node:keyword
     $record = &new NpjObject( &$rh, $this->npj_account.":" );
     $_present = $record->_Load( $this->_UnwrapNpjAddress($this->npj_account.":".$tag) );
     if ($_present !== "empty") return DENIED;
     $record -> Load(4);
// 4. �������� ������ ����
     // record_id, tag, supertag, is_keyword, body, body_r, author_id, 
     unset($record->data["record_id"]);
     $record->data["subject"] = $object->AddSpaces($subject, " ");
     $record->data["tag"] = $tag;
     $record->data["supertag"] = ""; 
     $record->data["body"] = $desc; // deprecated: ."\n".$record->data["body"];
     $record->data["author_id"] = $principal->data["user_id"];
     $record->data["is_keyword"] = "1";
// 5. ��������� �
     $record->Save();
     $r_id = $record->data["record_id"];
// 6. ��������� ACL // seems to be obsolete
/*
      $acls = array(); foreach($this->acls as $ag) foreach($ag as $acl) $acls[]=$db->Quote($acl);
      $db->Execute( "delete from ".$rh->db_prefix."acls where object_type=".$db->Quote("record").
                          " and object_id=".$r_id." and object_right in (".
                          implode(",",$acls).")");
      $sql = ""; $f=0;
      foreach( $this->acls as $g=>$aclg )
      foreach( $aclg as $acl )
      { if ($f) $sql.=","; else $f=1;
        $sql.="(".$db->Quote("record").",".$r_id.",".$db->Quote($acl).",".
                  $db->Quote($kacls[$g][$acl]).")";
      }
      if ($sql != "")
       $db->Execute("insert into ".$rh->db_prefix."acls (object_type, object_id, object_right, acl) VALUES ".$sql);
*/
// 7. ���� �� � ������-����, �� �������� �� ��������� ��������, ���
      if (!$params["by_script"])
       $rh->Redirect( $object->Href( $object->npj_account.":".$object->NpjTranslit($tag), 
                      NPJ_ABSOLUTE, IGNORE_STATE ), IGNORE_STATE ); 
      else $tpl->Assign(":AddKeyword", $r_id);
   }
?>