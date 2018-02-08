<?php
/*
    Form( &$rh, $form_config, $fields, $buttons ) -- �������� ����� ������� ����
      - &$rh -- ������ �� RequestHandler, � ������� ���������� ������������ �������
      - $form_config -- ���-������ ������������ ����� ((FormConfig))
      - $fields -- ������ �� �����, ������ ������ -- ������ �� �����-����������� ((Field)).
                   ����=>��������, ���� Common ������ �� messageset["Form._Group.Common"];
                   ���� ��� ��� "Form._Name"  
      - $buttons -- ������ ���-�������� �������� ������ (��� [[ButtonList]])
      - ��������� �� ((/Manifesto/���������������������/ListSimple �������� ������))
  ---------

  * Handle()        -- �������� ����������. ���� �� �� ������������� -- ����������� ���! �������� Load, Parse � ����-����-����

  * Load( &$data, $no_action=false )  -- ��������� ������ � ����� �� ���-������� $data
  * IsValid()       -- true, ���� �� � ����� ���� ����� ��� ������

  * Parse( $tpl_root=NULL, $store_to=NULL, $append=0 ) -- ��������� ����������� � ����� �������, ����������� 
                                                          �������� ������� ��� �����
      - $tpl_root -- ���� � ����� �������� �����. ���� �� ���������, ������ �� ((FormConfig))
      - $store_to -- ���� ��������� ��������� (��� ���� � ������ TemplateEngine)
      - $append   -- �������� � ������������� �������� ��� ���������� � ����

  * ParsePreview( $tpl_root=NULL, $store_to=NULL, $append=0 ) -- ��������� ������� ��� preview ����������� �����
      - ������ ����� ����� ������������� ��������
      - ������ ������������ ��� ����. ����������, ������� ((FormHandlers)) ��� � ������
      - ��������� ��������� � Parse, ��� ��������

  * _ParseOne( $tpl_name, $pos, &$obj, $count ) -- ������� ����� ������ (overriden)
      - ���������� �����

  // ������ � ����� ������ 
  * CreateUPDATE( $no_update=0 ) -- ������� ���������� UPDATE-������
      - $no_update -- ���������� ��������, �������� �� ����� (������������ � CreateINSERT)
  * CreateINSERT()               -- ������� ���������� INSERT-������
  * CreateSELECT()               -- ������� ���������� SELECT-������
  * DoSELECT( $id )     -- ������� � ��������� SELECT-������, �������� ������ �� ��
      - $id -- �������� ����������������� ����
  * DoUPDATE( $id )     -- ������� � ��������� UPDATE-������, �������� ������������ � �� ������
      - $id -- �������� ����������������� ����
  * DoINSERT()          -- �������� ��������� INSERT-������, �������� ������
      - ���������� $id -- �������� ����������������� ���� ����������� ������

  // ������ � ������� (�� ����� ������������)
  * ResetSession()       -- ����� ������� ������
  * StoreToSession()     -- ��������� ������� ��������� � ������
  * RestoreFromSession() -- ������������ �� ������

  // ������ ��������
  * $this->buttons         -- ������ ����������� �������� ��� ((ButtonList))
  * $this->buttons_blocked -- ��� ���� ����� �� ������, ��� ������������� �����
  * $this->fields          -- ���-������ �������� ����� ����� -- ������ ((Field))
  * $this->hash            -- �� �� ����� ����, ������ ����� ������ �������������
  * $this->data_id         -- ���� ���������� � �����-�� ��������, �� ��������� �� ������ � ��
  * $this->strict_groupnames -- ������� �� �������� ����� ����� ����� �� $ms


  // ������-��!
  * $rh->need_form_css -- ������������� � ����, ����� ���������� ���� css
  * $rh->form_auto_editorship -- ���� ��� = True, �� ��� ���� ���� �� ��������� �������� �����
                                  "auto_user_id" & "auto_datetime"

=============================================================== v.8 (Kuso)
*/

class Form extends ListSimple
{
  var $form_config;
  var $fields;
  var $hash;
  var $buttons;
  var $_valid;
  var $data_id;
  var $strict_groupnames = false;

  function Form( &$rh, $form_config, $fields, $buttons )
  {
    $this->ListSimple( &$rh, &$fields );
    $this->form_config = $form_config;
    $this->fields      = $fields;
    $this->hash        = array();

    $this->principal = &$rh->principal;

    $rh->need_form_css = true;

    foreach ($this->fields as $gno=>$group)
     foreach ($group as $fno=>$field)
     {
       $this->fields[$gno][$fno]->form = &$this;
       $this->hash[ $field->config["field"] ] = &$this->fields[$gno][$fno];
     }

    $this->buttons     = $buttons;     
    $this->_valid      = true; // patched 25112003 by kuso
    $this->data_id     = -1;

    // assigning default values
    if (!isset($this->form_config["name"]))         $this->form_config["name"] = "Form._Name";
    if (!isset($this->form_config["tpl_prefix"]))   $this->form_config["tpl_prefix"] = "forms/";
    if (!isset($this->form_config["tpl_name"]))     $this->form_config["tpl_name"] = "form.html:Form";
    if (!isset($this->form_config["tpl_buttons"]))  $this->form_config["tpl_buttons"] = "form.html:Buttons";
    if (!isset($this->form_config["session_key"]))  $this->form_config["session_key"] = $this->form_config["db_table"];
    if (!isset($this->form_config["db_id"]))        $this->form_config["db_id"] = "id";
    // ��� ���� ��� ��� ���� � ����� -- ���������
    if (!isset($this->form_config["critical"]))     $this->form_config["critical"] = $rh->critical_forms; 
    // ��� ����, ��� � ����� ����� ���� ������ ���� ������� ������
    if (!isset($this->form_config["flip_one"]))     $this->form_config["flip_one"] = false; 

    if (isset($this->form_config["on_before_action"]) && 
        !is_array($this->form_config["on_before_action"]))
        $this->form_config["on_before_action"] = array( $this->form_config["on_before_action"] );
    if (isset($this->form_config["on_after_action"]) && 
        !is_array($this->form_config["on_after_action"]))
        $this->form_config["on_after_action"] = array( $this->form_config["on_after_action"] );

    // ����������, ��� � ����� ������/����� �������
    if ($rh->form_auto_editorship)
    {
      if (!isset($this->form_config["auto_user_id"]))  $this->form_config["auto_user_id"] = 1;
      if (!isset($this->form_config["auto_datetime"]))  $this->form_config["auto_datetime"] = 1;
    }

    // message_set support
    if (isset($form_config["message_set"])) 
    {
      $rh->tpl->MergeMessageSet($form_config["message_set"]);

      foreach ($this->fields as $gno=>$group)
       foreach ($group as $fno=>$field)
       {
         $this->fields[$gno][$fno]->config["_name"] = $this->fields[$gno][$fno]->config["name"];
         $this->fields[$gno][$fno]->config["_desc"] = $this->fields[$gno][$fno]->config["desc"];
         $this->fields[$gno][$fno]->config["name"] = $rh->tpl->message_set[$this->fields[$gno][$fno]->config["_name"]];
         $this->fields[$gno][$fno]->config["desc"] = $rh->tpl->message_set[$this->fields[$gno][$fno]->config["_desc"]];
       }
    }

  }

  // ������ � �������
  function ResetSession()   { unset($_SESSION[ $this->config["session_key"]]); }
  function StoreToSession() 
  { 
    $_SESSION[ $this->config["session_key"] ] = array(); 
    $_SESSION[ $this->config["session_key"] ]["__id"] = $this->data_id; 
    foreach ($this->fields as $group)
     foreach ($group as $field)
      $field->StoreToSession( $this->config["session_key"] );
  }
  function RestoreFromSession() 
  { 
    if (isset($_SESSION[ $this->config["session_key"] ]["__id"]))
     $this->data_id = $_SESSION[ $this->config["session_key"] ]["__id"];
    else 
     $this->data_id = -1;

    foreach ($this->fields as $k1=>$group)
     foreach ($this->fields[$k1] as $k2=>$field)
     {
      $this->fields[$k1][$k2]->RestoreFromSession( $this->config["session_key"] );
     }
  }

  // ������� workflow
  function IsValid() { return $this->_valid; }
  function Handle()
  {
    if ($this->Load($_POST))
     return true;
    else
     return $this->Parse();

   return false;
  }

  // ������ � ������
  function Load( &$data, $no_action=false )
  {
    $this->invalid = false;
    if (isset($data["__form_present"]))
    {  
      // commented by kuso @ 2003-11-08 03:31; -- �� ����, ��������� ��� ���.
      // ��������������� ������, ��� �������� readonly-����� �������� ���������� readonly ���� ��� ����������
      //   $this->ResetSession();
      // --
      // restore data from form
      $valid = true;
      foreach ($this->fields as $k1=>$group)
       foreach ($group as $k2=>$field)
       {
        if (!$this->fields[$k1][$k2]->config["readonly"])
         $valid = $this->fields[$k1][$k2]->Load( &$data ) && $valid;
        else $this->fields[$k1][$k2]->SetDefault();

        if (isset($this->fields[$k1][$k2]->config["block_form"]))
         if ($this->fields[$k1][$k2]->data == $this->fields[$k1][$k2]->config["block_form"])
         {
           $this->blocked=true;
           $this->RestoreFromSession();
           return false;
         }
       }
      $this->_valid = $valid;
      // store data into session, check validity
      $this->StoreToSession();

      // �������� ������
      if ($this->form_config["minor_edit"] && isset($data["__minor_edit"]))
      {
        $this->form_config["auto_datetime"] = false;
        $this->form_config["auto_user_id"]  = false;
      }

      // ���� ���������� �����, �� �� �������� � ������ "���������� ������ ���� ������", � ���������� ��� ������
      if (!$this->IsValid) $this->form_config["flip_one"] = false; 

      if ($no_action) return false;

      // find appropriate handler
      $hname = $data["__button"];
      $handler = "";
      foreach ($this->buttons as $button) if ($hname == $button["name"]) { $handler=$button["handler"]; break; }
      if (!$handler)
        foreach ($this->buttons as $button) if ($button["default"]) { $handler=$button["handler"]; break; }

      // if data is valid, exec handler
      if (!$this->IsValid() && ($handler!="_cancel")) return false;

      // on_before_actions
      if (is_array($this->form_config["on_before_action"]))
      foreach($this->form_config["on_before_action"] as $on_before_action)
      {
        $__fullfilename = $this->rh->handlers_dir.$on_before_action.".php";
        $this->rh->debug->Trace("Launching on_before_action handler: ".$__fullfilename);
        if (!file_exists($__fullfilename)) $this->rh->debug->Error("on_before_action handler not exist {".$__fullfilename."}!", 3);
        $output = include($__fullfilename);
        if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
      }

      $__dir          = $this->rh->handlers_dir."forms/";
      $__fullfilename = $__dir.$handler.".php";
      $this->rh->debug->Trace("Launching button handler: ".$__fullfilename);
      if (!file_exists($__fullfilename)) $this->rh->debug->Error("Unknown button handler {".$__fullfilename."}!", 3);
      $output = include($__fullfilename);
      if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());

      if (($handler=="_cancel") || $this->form_config["ping_pong"]) 
        $this->rh->Redirect( $this->rh->Href($this->rh->url) );
      else if ($handler!="_cancel") $this->success = true;

      // on_after_actions on each field, if any
      // launch per-field-after-handler
      $valid = true;
      foreach ($this->fields as $k1=>$group)
       foreach ($group as $k2=>$field)
        $this->fields[$k1][$k2]->AfterHandler();

      // on_after_actions
      if (is_array($this->form_config["on_after_action"]))
      foreach($this->form_config["on_after_action"] as $on_after_action)
      {
        $__fullfilename = $this->rh->handlers_dir.$on_after_action.".php";
        $this->rh->debug->Trace("Launching on_after_action handler: ".$__fullfilename);
        if (!file_exists($__fullfilename)) $this->rh->debug->Error("on_after_action handler not exist {".$__fullfilename."}!", 3);
        $output = include($__fullfilename);
        if ($output===false) $debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
      }

      return $this->success;
    }
    else { $this->RestoreFromSession(); return false; }
  }

  // �������
  function Parse( $tpl_root=NULL, $store_to=NULL, $append=0 )
  {
    $this->rh->tpl->LoadDomain( array( 
        "_minor_edit" => ($this->form_config["minor_edit"] && ($this->data_id != -1))?
                         $this->rh->tpl->Parse($this->form_config["tpl_prefix"]."minor_edit.html"):"",
        "Form"      => $this->rh->state->FormStart( MSS_POST,$this->rh->url, "id=\"form_".$this->form_config["session_key"]."\" name=\"form_".$this->form_config["session_key"]."\"".
                                                    ($this->form_config["upload"]?" enctype='multipart/form-data' ":"").
                                                    ($this->form_config["critical"]?" cf='true' ":"").
                                                    ($this->form_config["params"]?$this->form_config["params"]:"")
                                                     ).
                       ($this->form_config["flip_one"]?" <script language=\"javascript\" type='text/javascript'>flipOnlyOne=true;</script>":""),
        "/Form"     => $this->rh->state->FormEnd().
                       ($this->form_config["focus_to"]?"<script language=\"javascript\" type='text/javascript'>document.forms[\"form_".
                                                       $this->form_config["session_key"].
                                                       "\"].elements[\"_".
                                                       $this->form_config["focus_to"].
                                                       "\"].focus();</script>":""),
        "FormName"  => "form_".$this->form_config["session_key"],
                          )      );

    if (!isset($this->rh->tpl->domain["CUSTOM"]))
      $this->rh->tpl->Assign("CUSTOM", "");

    if ($this->blocked) $this->buttons = $this->buttons_blocked;
    $buttons = &new ButtonList( &$this->rh, $this->buttons );
      $this->rh->tpl->Assign("ButtonPostfix", "");
    $buttons->Parse(  $this->form_config["tpl_prefix"].$this->form_config["tpl_buttons"], "BUTTONS" );
    if ($this->form_config["buttons_small"])
    {
      $buttons->postfix = "_Small";
      $buttons->Parse(  $this->form_config["tpl_prefix"].$this->form_config["tpl_buttons"], "BUTTONS_SMALL" );
    }


    if (!$tpl_root) $tpl_root = $this->form_config["tpl_name"];
    $tpl_root = $this->form_config["tpl_prefix"].$tpl_root;

    if ($this->form_config["tpl_manual"]) $this->rh->debug->Error("manual form templates not implemented yet [to be supplied.]");
    else return ListSimple::Parse( $tpl_root, $store_to, $append );
  }
  function _ParseOne( $tpl_name, $pos, &$obj, $count )
  {
    // ������ ����-�����
    if (!$this->IsValid() && isset($this->invalid))
    {
      $this->form_config["group_state"][$count] = "1";
      foreach($obj as $k=>$field)
       if ($field->invalid)
        $this->form_config["group_state"][$count] = "0";
    }

    // ����� ����� �����
    $this->rh->tpl->Reset("FIELDS");
    if (is_numeric($pos) || !$this->strict_groupnames)
    {
      $this->rh->tpl->Assign("GroupName", $this->rh->tpl->message_set["Form._Group.".$pos] );
      $this->rh->tpl->Assign("GroupID", "fieldgroup".$pos );
    }
    else
    {
      $p = explode("|", $pos);
      if (isset($this->form_config["message_set"]))  
        $this->rh->tpl->Assign("GroupName", $this->rh->tpl->message_set[$p[sizeof($p)-1]] );
      else
        $this->rh->tpl->Assign("GroupName", $p[sizeof($p)-1] );
      $this->rh->tpl->Assign("GroupID", $p[0] );
    }
    $this->rh->tpl->Assign("GroupState", $this->form_config["group_state"][$count]?0:1 );
    $this->rh->tpl->Assign("GroupHidden", $this->form_config["group_state"][$count]?"_hidden":"" ); // !!!! NB: underscore

    foreach ($obj as $k=>$field)
    {
     $_field= &$this->fields[$pos][$k];
     if ($this->blocked) $_field->config["readonly"] = 1;
     if (isset($_field->config["tpl_row"]))
     {
      $_field->ParseTo( $this->form_config["tpl_prefix"], "FIELDS");
     }
    }

    return $this->rh->tpl->Parse( $tpl_name );
  }

  // ������� ��� ����
  function ParsePreview( $tpl_root=NULL, $store_to=NULL, $append=0 )
  {
    $items= array();
    foreach( $this->fields as $k1=>$group )
     foreach( $group as $k2=>$field )
     {
       $items[] = $field->config["readonly"];
       $this->fields[$k1][$k2]->config["readonly"] = 1;
     }

    $result = $this->Parse( $tpl_root, $store_to, $append );

    $c=0;
    foreach( $this->fields as $k1=>$group )
     foreach( $group as $k2=>$field )
       $this->fields[$k1][$k2]->config["readonly"] = $items[$c++];

    return $result;
  }

  // ������ � ����� ������ 
  function CreateUPDATE( $no_update=0 )
  {
    if ($no_update) $res=""; else
    $res = "UPDATE ".$this->form_config["db_table"]." SET ";

    $app = "";
    if ($this->form_config["auto_user_id"])
    {
      $app.=" edited_user_id = ". $this->principal->data["user_id"].", ";
    }
    if ($this->form_config["auto_datetime"])
    {
      $d = $this->rh->db->Quote(date("Y-m-d H:i:s"));
      $app.=" edited_datetime = ".$d.", ";
    }
    $res.= $app;
    
    $f=0;
    foreach ($this->fields as $k1=>$group)
     foreach ($group as $k2=>$field)
     if (!isset($this->fields[$k1][$k2]->config["db_ignore"]))
     {
      $field = $this->fields[$k1][$k2]->CreateUPDATE( );
      if ($field === "") if ($f) $f=0; else $f=-1;
      if ($f>0) $res.=", "; else $f++;
      $res .= $field;
     }

   if ($no_update) return $res;

   if ($this->data_id == -1)
    $this->rh->debug->Error("Form->CreateUPDATE: no data_id supplied");

   $res .= " WHERE ".$this->form_config["db_id"]." = ".$this->rh->db->Quote($this->data_id);
   return $res;
  }
  function CreateINSERT()
  {
    $app = "";
    if ($this->form_config["auto_user_id"])
    {
      $app.=" created_user_id = ". $this->principal->data["user_id"].", ";
    }
    if ($this->form_config["auto_datetime"])
    {
      $d = $this->rh->db->Quote(date("Y-m-d H:i:s"));
      $app.=" created_datetime = ".$d.", ";
    }

    if ($this->form_config["auto_locking"])
     $this->rh->debug->Error("Form: no AUTO_LOCKING implemented yet." );

    $res = "INSERT INTO ".$this->form_config["db_table"]. " SET ";
    $res.= $app;
    $res.= $this->CreateUPDATE( 1 );
    return $res;
  }
  function CreateSELECT()
  {
    $res = "SELECT ";
    foreach ($this->fields as $k1=>$group)
     foreach ($group as $k2=>$field)
     if (!isset($this->fields[$k1][$k2]->config["db_ignore"]))
     {
      if ($f) $res.=", "; else $f=1;
      $field = $this->fields[$k1][$k2]->CreateSELECT( );
      if ($field) $res .= $field;
      else $f=0;
     }
    if ($this->form_config["auto_user_id"])
     $res.=", created_user_id, edited_user_id";
    if ($this->form_config["auto_datetime"])
     $res.=", created_datetime, edited_datetime";

   $res.=" FROM ".$this->form_config["db_table"];

   if ($this->data_id == -1)
    $this->rh->debug->Error("Form->CreateSELECT: no data_id supplied");

   $res .= " WHERE ".$this->form_config["db_id"]." = ".$this->rh->db->Quote($this->data_id);
   return $res;
  }
  function DoSELECT( $id )
  {
    $this->data_id = $id;
    $sql = $this->CreateSELECT();
    $rs = $this->rh->db->SelectLimit( $sql, 1 );
    if ($rs === false) 
    { $this->rh->debug->Error("Form->DoSELECT: no entry to select { $sql }", 1); return false; }
    $a = $rs->GetArray();
    $fields = &$a[0];

    $this->ResetSession();
    foreach ($this->fields as $k1=>$group)
     foreach ($group as $k2=>$field)
     if (!isset($this->fields[$k1][$k2]->config["db_ignore"]))
     {
      $this->fields[$k1][$k2]->RestoreFromDb( &$fields );
      if ($this->fields[$k1][$k2]->config["readonly"])
       $this->fields[$k1][$k2]->config["default"] = $this->fields[$k1][$k2]->data;

      if (isset($this->fields[$k1][$k2]->config["block_form"]))
        if (is_array($this->fields[$k1][$k2]->config["block_form"]))
          if (in_array($this->fields[$k1][$k2]->data, $this->fields[$k1][$k2]->config["block_form"]))
           $this->blocked=true;
        else
       if ($this->fields[$k1][$k2]->data == $this->fields[$k1][$k2]->config["block_form"])
         $this->blocked=true;
     }
    $this->StoreToSession();

    return true;
  }
  function DoUPDATE( $id )
  {
    $this->data_id = $id;
    $sql = $this->CreateUPDATE();
    $this->rh->db->Execute( $sql );
    return $id;
  }
  function DoINSERT() // returns $id
  {
    $sql = $this->CreateINSERT();

    $this->rh->db->Execute( $sql );
    $id = $this->rh->db->Insert_ID();

    if ($this->form_config["auto_locking"])
     $this->rh->debug->Error("Form: no AUTO_LOCKING implemented yet." );

    return $id;
  }

// EOC { Form }
}


?>
