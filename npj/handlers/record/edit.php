<?php
//������� EDIT. ������� � �������. �������, _save ��������.
/*
  ??? ������� ����������.
      ������ ��� ���������� � ���� ��������� �����
      ������ ��� ���������� ������ �����.


  ! �������� � ��������� -- ��������� ��� ��� ���� �� $params[0]
  ! ���-�� �� �������, ��������� ���� �� �������� -- $params[0]
  * �������� !form_profile
  * ��������� ������ �� ����� � $this->data. � ���. ��� ����.

*/

  // =================================================================================
  //  ���� 1. ���� �����-���� � �������� ������ �� ��
  //  
  $tag = $this->tag;
  if (strstr($tag, "MyMagicDate".date("Ymdh"))) $tag = preg_replace("/MyMagicDate[0-9]*/","",$tag); //������-���� ������ ���� ���� � ����� �������.
  //���� �� ����� ���� ���������������, �� ��������-�� ������
  if ($params[0]!="add" && $params[0]!="post") $this->Load( 4, "record"); 
  //�����
  $data = &$this->data;
  //���� ������ ��� ��� � �������, �� is_new == TRUE
  if (!$data || !is_array($data) || !isset($data["record_id"])) 
  {
   $is_new=TRUE;
   if (!is_array($data)) $data = array();
   if (!isset($this->parent))
     $rh->Redirect( $this->Href( $this->npj_account.":add/".$tag, NPJ_ABSOLUTE, STATE_USE ) );
  }
  // ��� ������� ������. ���� �� ��� �� �����, �� ������ ������ �� ��� � ��������. 
  // ���� ������� - �� ����, ���� �������� - �������.
  if (!$data["type"]) $data["type"]=$this->GetType();
  $type = $data["type"];
  //����� �� ������������� ����� ������. ���������� ������������ ����� � is_new.
  $show_tag = $is_new && ($type==RECORD_DOCUMENT);  

  // =================================================================================
  //  ���� 2. ���� �� ����������� ������, �� ���� � ����������-�.
  //          !! �������, ������ �� ��������.
  if (is_numeric($params[0])) $version_id = $params[0];
  else
  if ($params[0] == "version") $version_id = $params[1];
  if ($version_id)
  {
    $version = &new NpjObject( &$rh, $this->npj_object_address."/versions/".$version_id );
    if (is_array($version->Load(3)))
    {
      $is_version = true;
      $debug->Trace_R( $version->data );
    }
  }

  // =================================================================================
  //  ���� 3. �������� ���� �������
  //          * �����
  // �������� �� �������

  if ($rh->admins_only_documents)
  if (!$this->HasAccess( &$principal, "acl_text", $rh->node_admins) && $type == RECORD_DOCUMENT)
    return $this->Forbidden("RecordForbiddenEdit");

  if (!$rh->account->HasAccess( &$principal, "not_acl", "banlist" )) return $this->Forbidden("YouAreInBanlist");

  if ($is_new)
  {
   // �������� ����, ��� ����� ��������� ����� ��������� � ���������
    if ($rh->account->data["account_type"] == ACCOUNT_COMMUNITY)
     return $this->Forbidden("AnythingInCommunityIsForbidden");

    if (!$this->parent->HasAccess( &$principal, "owner" ) && 
        !($this->parent->HasAccess( &$principal, "acl_text", $rh->node_admins ) && $this->npj_account==$rh->node_user ) &&
        !(($type == RECORD_DOCUMENT) && $this->parent->HasAccess( &$principal, "acl", "add" ))) 
     return $this->Forbidden("RecordForbiddenAdd");
  }
  else
  { // ��������, ����� �� �������������

    if (($rh->account->data["account_type"] != ACCOUNT_USER) 
        && $rh->account->HasAccess( &$principal, "rank_greater", GROUPS_MODERATORS)) ;
    else
      if ((($type == RECORD_MESSAGE) && $this->HasAccess( &$principal, "owner" ))
          ||
          (($type == RECORD_DOCUMENT) && ($this->HasAccess( &$principal, "acl", "write" ) || 
            ($this->HasAccess( &$principal, "acl_text", $rh->node_admins) && ($this->npj_account==$rh->node_user))
           ) 
          )
         ); 
      else     return $this->Forbidden("RecordForbiddenEdit");
  }
  // -------------------------
  
  // =================================================================================
  //  ���� 4. �������� ��������� �����
  //
  // >>>>>>>>>>>>>>>>> include <<<<<<<<<<<<<<<<<<
  include( $dir."/!form_record.php" );    

  // =================================================================================
  //  ���� 5. ���� ��� ���� ����� � POST-�������, �� ������������� ��������� ��������
  //
 if (!isset($_POST["__form_present"])) 
 { 
    $form->ResetSession(); // �������� ���������� ���������
    if (!$is_new)   $form->DoSelect( $data["record_id"] ); // ��������� �� ��, ���� ������ ��� ����
    $debug->Trace("is_new: ".(int)$is_new);
    $debug->Trace("record_id: ".$data["record_id"]);

   // overwrite �������, ���� �������������� ������ ���������� 
   // (��������, ��� ����� ������������ ������ � ���� 2 (�������� ������)
   if ($is_version)
   {
     $formatters = array( "simplebr" => "body_simpleedit",
                          "wacko"    => "body_wikiedit", // [!!!] Shoo, dirty kukutz! �� ��� �������� body_wacko =)
                          "rawhtml"  => "body_richedit",    // [!!!] Shoo, dirty kukutz! �� ��� �������� body_rawhtml =) 
                         );
     // ��������� � ��� ��� ���� � ������ ���������� ��-�������, ��� ���� �������, � ����� �� ��� ���������� ��������
     $version->data[ $formatters[$version->data["formatting"]] ] = $version->data["body"];

     foreach( $version->data as $k=>$v )
      if (!is_numeric($k))
      if ($k != "edited_datetime") // ��� �� ����� ��������� ���� "edited_datetime", ����� �� ���������� ����������
       if (isset($form->hash[$k])) 
       {
         $form->hash[$k]->RestoreFromDb( $version->data );
         $form->hash[$k]->StoreToSession( $form->config["session_key"] );
       }
   }
 }

  // =================================================================================
  //  ���� 6. ������ ������ �����. ��� ������������ �, ��������� ����.
  //
 $tpl->Assign("Preview", "" );
 if (!$is_new) $state->Set( "id", $data["record_id"] );
 $tpl->theme = $rh->theme;
 $result= $form->Handle();
 $tpl->theme = $rh->skin;
 if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);

 if ($data["supertag"][strlen($data["supertag"])-1] == ":")
   $tpl->Assign("Preparsed:TITLE", "��������� �������� �������"); // !!! � message_set
 else
   $tpl->Assign("Preparsed:TITLE", $tpl->message_set["Form._Name"]);
 if (!$is_new) $state->Free( "id" );

 $tpl->Assign("Preparsed:TIGHT", 1);

  // =================================================================================
  //  ���� 7. ���� ����� �� ������ ������������, � ������������ �������,
  //          �������� � ����������. �� ���� ������������.
  //          ��� ���� ������� ����� �������, ��������� �� �������
  //          � ���� ��, ��� ���������.
  if ($form->success)
  {
    // =================================================================================
    //  ���� 7.1. ���������� ������ �� ����-���� � ����-����
      if ($form->hash["subject"])
      if ($form->hash["subject"]->data == "")
        $form->hash["subject"]->data = $this->AddSpaces($form->hash["tag"]->data, " ", "not obsolete me, please");
      // �������� �������������� � �� ������������� �� ������ ������.
      // ������� ��������� this->data �������
      foreach( $form->hash as $k=>$v )
      if ($k != "default_show_parameter")
      {                                                                                     
        // ������ db_data �� ������ data (����� ��� ������� �����, ���� ����� ����� ����� � ���) 
        $form->hash[$k]->_StoreToDb(); 
        $this->data[$k] = $form->hash[$k]->db_data;
        if (is_array($form->hash[$k]->db_data))
        {
          $debug->Trace_R( $form->hash[$k]->db_data );
          foreach ($form->hash[$k]->db_data as $field=>$value)
            $this->data[ $form->hash[$k]->config["fields"][$field] ] = $value;
        }
      }

    // =================================================================================
    //  ���� 7.2. ������ ���������� ����� � ������������.
    //            ��������, ����� ����� ������ �����������.
      //���������� ����������
      if (!$this->data["formatting"]) $this->data["formatting"] = $principal->data["_formatting"];

      //�������� ����������� �������� ����
      if ($this->data["formatting"]=="wacko") $this->data["body"] = $this->data["body_wikiedit"];
      if ($this->data["formatting"]=="simplebr") $this->data["body"] = $this->data["body_simpleedit"];
      if ($this->data["formatting"]=="rawhtml") $this->data["body"] = $this->data["body_richedit"];

      //�������������-������
      $this->data["body"] = $this->Format($this->data["body"], $this->data["formatting"], "after");


    // frozen by kuso@npj, 04042005 due to instability
    /*
    // PREVIEW FEATURE -------------------------------------------------------------------------
    if ($tpl->message_set["ButtonTextCommentPreview"] == $_POST["__button"])
    {
      $_body_post = $this->data["body"];
      $_body_post = $this->Format($_body_post, $this->data["formatting"]);
      $_body_post = $this->Format($_body_post, "paragrafica");
      $_body_post = $this->Format($_body_post, $formatting, "post");
      $_body_post = preg_replace("!</form>!i", "</span>", $_body_post);
      $_body_post = preg_replace("!<form!i", "<span", $_body_post);
      $_body_post = preg_replace("!<input!i", "<input DISABLED='DISABLED'", $_body_post);
      $_body_post = preg_replace("!<textarea!i", "<textarea DISABLED='DISABLED'", $_body_post);
      $tpl->Assign("Preview", $_body_post );
      
      // ������������ preview
      $form->invalid = true;
      $tpl->Skin($rh->theme);
        $tpl->Parse( "preview.html", "AFTER_BUTTONS" );
        $result = $form->Parse();
        if ($result !== false) $tpl->Assign("Preparsed:CONTENT", "<a name=\"form\"></a>".$result);
      $tpl->UnSkin();

    }
    else
    */
    {
      // =================================================================================
      //  ���� 7.3. ���-���������. ���� ��������� �����. ��������� �������������� ����
      //            �� ������������ ����������� �������� ����
        if (!$form->hash["default_show_parameter"]->config["only_more"])
        {
          $this->data["default_show_parameter"] = $form->hash["default_show_parameter"] ->data[0];
          $this->data["default_show_parameter_param"] = $form->hash["default_show_parameter"] ->data[1];
          $this->data["default_show_parameter_add"] = $form->hash["default_show_parameter"] ->data[2];
        }
        $this->data["default_show_parameter_more"] = $form->hash["default_show_parameter"] ->data[3];
        $this->data["default_show_parameter_more_param"] = $form->hash["default_show_parameter"] ->data[4];

      // =================================================================================
      //  ���� 7.4, ��������. ����� ������. ������, ���� ������ �������� ��������, �� ��� ��������� ���.
      //                      � ���� ��� ���������, �� ������� ��� �������, ������� ���-�� ��������.
        $this->data["type"] = $type;
        if (!$show_tag) $this->data["tag"] = $tag;

      // =================================================================================
      //  ���� 7.5. �� �����, ��� ����, ���� ��� ����, ����������� � ����� �������.
      //            � ������ -- � ������. �.�. ����� � �������.
      if (($is_new) && ($type==RECORD_MESSAGE))
       { // ==== ������������ group1..4
        if ($form->hash["groups"]->data[0]==-1) // ���
        {
         $data["group1"]=0; $data["group2"]=0; 
         $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==0) // ����� (�� � �� ��������� "-1")
        {
         $data["group1"]=$rh->account->group_nobody;
         $data["group2"]=-1; $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==-2) // ��� ����������
        {
         $data["group1"]=$rh->account->group_friends;
         $data["group2"]=-2; $data["group3"]=0; $data["group4"]=0;
        }
        else if ($form->hash["groups"]->data[0]==ACCESS_GROUP_COMMUNITIES) // ���� �����������
        {
         $data["group1"]=$rh->account->group_communities;
         $data["group2"]=ACCESS_GROUP_COMMUNITIES; 
         $data["group3"]=1*$form->hash["groups"]->radio_data; 
         $data["group4"]=0;
        }
        else
        { //[_items_in_groups] -- �� �� �������� � ������ ���
         $grps = $form->hash["groups"]->data;
         for ($gnum=0; $gnum<4; $gnum++)
          if (!isset($grps[$gnum])) break;
          else $data["group".($gnum+1)] = $grps[$gnum];
        }
       }

      // =====================================================================================
      //  ���� 7.6, ��������������. �����-�� ��������� ��������� �� ��� ���������� �����-��.
      //                            ������� �� �� �������������� � �������� �������.
      /*
        $this->data["disallow_syndicate"] = $form->hash["disallow_syndicate"]->data;
        $this->data["keywords"] = $form->hash["keywords"]->data;
        $this->data["communities"] = $form->hash["communities"]->data;
        foreach( $this->acls as $aclg )
         foreach( $aclg as $acl )
           $this->data[$acl] = $form->hash[$acl]->data;
      */

      // =================================================================================
      //  ���� 7.7. ���������!
        $this->Save();

      // =================================================================================
      //  ���� 7.8. �������� �� ���������� ������
      //  ���� �� �������� � ���������� � �������� ������� "������� � �������� ������", ��� � ������� ������ =)
        $bonus = "";
        if ($data["announce_after"]) $bonus = "/post/announce";
        $rh->Redirect( $this->Href($this->data["supertag"].$bonus, NPJ_ABSOLUTE, STATE_IGNORE), STATE_IGNORE);

     }//�������� "���� �� preview"

  }//�������� if (form->success)

  return GRANTED;
?>