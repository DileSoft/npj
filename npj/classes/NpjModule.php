<?php
/*

    ���������������� ��� "������" ��� ���.
    ������������ ��� ��� ����� ������� �������������, �� ����������

    NpjModule( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- ����� ������������ ����� � ����������� ��� ������?
      - $section_id -- ������������� ����������� ������� ����� (�� ������ ������ ������)
      - $handlers_dir, $messageset_dir -- � ������ ����������� �� $rh->..

  ---------

  * Init( $rel_url ) -- ������������� ��� ����������� ������� $this->Handler().
                        ����� ����������� ������ ����.
                        (������� ��-��������� "_passthru" ������� ���������� ������� � ���)

  * &GenerateTemplateEngine( $te_profile=NOT_EXIST ) -- ������� �����������, ��������� �� ��� ��������� ������
                                                        (����� �������� �� ����� ���������� ��������/�������)
      - $te_profile -- ��������� ������� ��� ������. ���� ���������� ������ -- ���������� ���, ��� ������ � ������������

  * &SpawnHelper( &$npj_object ) -- ������ ��������� � ���� "��������������-����������"
                                    ������������ �����, ��������� ������-������� HelperAbstract
                                    ��������� ��� �������:
                                    * http://npj.ru/node/razrabotka/helperarchitecture

  // ��������� ����������
  * &StaticFactory( &$npj_object, $module_class ) -- �������, �������� ������ ������� ������
  * PassToModule( $module_handler, $_params, &$principal ) -- �������� ���������� ������ �� ������� ����������
                                                              (���������� �� NpjObject::PassToModule)

  // ������� ���������� (��� override)
  * Npj_Load( $abs_npj_address, $cache_level, $cache_class, $no_cache=false ) -- ��� ��������� � ������ �������
  * Npj_LoadById( $id, $cache_level, $cache_class, $no_cache=false )          -- ����-������� �����������
  * Npj_OnComment( $comment_id, $record_id, &$principal ) -- � ������ ���������� �����������
  * Npj_Action( $module_action, $params, &$principal )    -- ����������� ������� � module`s actions
  * Npj_Handler( $method, $params, &$principal )          -- �������� �������� ���������� ������
  * Npj_IsGrantedTo( &$principal, $method, $object_class="", $object_id=0, $options="" ) -- access control override

========================================= v.2 (kuso@npj)
*/
define("NPJ_MODULE_PROCESSED", GRANTED);
define("NPJ_MODULE_PASSTHRU",  DENIED);

class NpjModule extends Module
{
  var $module_name = "Npj Generic Module"; // for use in debug

  function NpjModule( &$rh, $base_href, $module_config, &$object )
  {
    $this->config = $module_config;
    $this->object = &$object;
    $this->classes_dir = $rh->modules_dir.$this->config["module_dir"]."classes/";

    $result = Module::Module( &$rh, $base_href, $rh->message_set."_".$this->config["messageset_prefix"], 0, 
                           $rh->modules_dir.$this->config["module_dir"]."handlers/",
                           $rh->modules_dir.$this->config["module_dir"]."messagesets/"
                          );

    $no_skin = trim($this->config["module_dir"], "/");
    $this->config["template_engine"]  = array(  // template engine profile
                  "cache_prefix"  => $this->config["subspace"]."_skins@",
                  "themes_dir"    => $rh->modules_dir,
                  "skins"         => array($no_skin),
                  "skin"          => $no_skin, 
                                            );

    $rh->UseClass("ListObject", $rh->core_dir);
    $this->helper = &$rh->helper;

    return $result;
  }

  function Init( $rel_url )
  {
    $this->method = "_passthru";
    $this->params = array();
    return;
  }

  function &GenerateTemplateEngine( $te_profile=NOT_EXIST )
  {
    if ($te_profile == NOT_EXIST) $te_profile = $this->config["template_engine"];
    $TE = &Module::GenerateTemplateEngine( $te_profile );
    $TE->Assign( "Npj:Node", $this->rh->node_name );
    $TE->Assign( "/", $this->rh->tpl->GetValue("/") );
    return $TE;
  }

  function &SpawnHelper( &$npj_object )
  {
    return false;
  }

  // ================================================================================================
  // ��������� ���������� ---------------------------------------------------------------------------
  // �������, ������� ������ � �������������� ��������� ������
  function &StaticFactory( &$npj_object, $module_class )
  {
     if (!isset($npj_object->rh->modules[$module_class]["multi-instance"]))
       if (isset($npj_object->rh->modules[$module_class]["&instance"]))
         return $npj_object->rh->modules[$module_class]["&instance"];

     $module_config = &$npj_object->rh->modules[$module_class];

     // #1. LINKING TO ACCOUNT
     if (isset($module_config["root"]))
     {
       // root-rel-url
       $base_href= $npj_object->_NpjAddressToUrl( $npj_object->npj_account);
       $rel_url = substr( $npj_object->npj_address, strlen($module_config["root"])+1 );
     }
     // #2. SUBSPACE MODULE
     if (isset($module_config["subspace"]))
     {
       // subspacing
       $subspace = $npj_object->subspace;
       if ($subspace != "") $subspace.="/";
       $base_npj = $npj_object->npj_account.":".$subspace.$npj_object->subspace_name;
       if ($npj_object->subspace_name != "") $more_slash=1;
       else                                  $more_slash=0;

       if (isset($module_config["subspace_root_only"]))
         $base_href= $npj_object->subspace_name; // ! works only from ROOT
       else
         $base_href= $npj_object->_NpjAddressToUrl( $base_npj );

       $rel_url = substr( $npj_object->npj_address, strlen($base_npj)+$more_slash );
     }
     // #3. QUASI-NODE MODULE
     if (isset($module_config["as_foreign"]))
     {
       // root-rel-url
       $base_href = $npj_object->_NpjAddressToUrl( $npj_object->npj_account );
       $rel_url = substr( $npj_object->npj_address, strlen($npj_object->npj_account)+1 );
     }
     // #3. --

     // build_up module
     $module_config["module_class"] = $module_class;
     $npj_object->rh->UseClass( $module_config["classname"], $npj_object->rh->modules_dir.
                                $module_config["module_dir"]."classes/" );
     eval('$module = &new '.$module_config["classname"].'( &$npj_object->rh, $base_href, $module_config, &$npj_object );');
  
     if (!isset($npj_object->rh->modules[$module_class]["multi-instance"]))
       $npj_object->rh->modules[$module_class]["&instance"] = &$module;
     
     $npj_object->module_instance = &$module;

     // prepare to init module by relative url
     $module->rel_url = $rel_url;
     return $module;
    
  }

  // ��������� ���������� �� ��� ----------------------------------------------
  // ������ ��� ������ ������:
  // 1. ����� handler
  // 2. ����� action
  // 3. load, load_by_id
  // 4. on_comment
  // ������� � ������ ��������, �� ���� � � ����� ����� ����.
  function PassToModule( $module_handler, $_params, &$principal )
  {
    $npj_object = &$this->object;

    // overriding/forbidding on account-class basis
    $parent_account = &new NpjObject( &$npj_object->rh, $npj_object->npj_account );
    $parent_account->Load(2);
    if ($npj_object->rh->account_classes && $parent_account->data["account_class"] != "")
    {
      $target_class_data = $npj_object->rh->account_classes[$parent_account->data["account_class"]];
      if (isset($target_class_data["modules_override"]))
      {
        if (!is_array($target_class_data["modules_override"]))
          return $parent_account->Forbidden( "ModuleIsNotAllowedInClass" );
        else
          if (isset($target_class_data["modules_override"][ $this->config["module_class"] ]))
          {
            $this->config = array_merge( $this->config, $target_class_data["modules_override"][ $this->config["module_class"] ] );
          }
      }
    }
    // --
    switch( $module_handler )
    {
      case "is_granted_to":
                      return $this->Npj_IsGrantedTo( &$principal,
                                  $_params["method"],    $_params["object_class"], 
                                  $_params["object_id"], $_params["options"] );
      case "action":  return $this->Npj_Action ( $_params["module_action"], $_params["params"], &$principal );
      case "handler": return $this->Npj_Handler( $_params["method"], $_params["params"], &$principal );
      case "load":    return $this->Npj_Load( $_params["abs_npj_address"], 
                                              $_params["cache_level"], 
                                              $_params["cache_class"], 
                                              $_params["no_cache"], 
                                              &$principal );
      case "load_by_id":  return $this->Npj_LoadById( $_params["id"], 
                                              $_params["cache_level"], 
                                              $_params["cache_class"], 
                                              $_params["no_cache"], 
                                              &$principal );
      case "on_comment":  return $this->Npj_OnComment( $_params["comment_id"], $_params["record_id"], 
                                              &$principal );

      default: $npj_object->rh->debug->Error( "PassToModule *".$this->config["name"]."* method [$module_handler] is not implemented");   
    }
  }

  // �������� ���������� ��� ����������
  function Npj_Load( $abs_npj_address, $cache_level, $cache_class, $no_cache=false )
  {
     return $this->object->_Load( $abs_npj_address, $cache_level, "record", $no_cache );
  }
  function Npj_LoadById( $id, $cache_level, $cache_class, $no_cache=false )
  {
     return $this->object->_LoadById( $id, $cache_level, "record", $no_cache );
  }
  function Npj_OnComment( $comment_id, $record_id, &$principal )
  {
     return NPJ_MODULE_PASSTHRU; // passthru
  }
  function Npj_Action( $module_action, $params, &$principal )
  {
    $action = $this->Action( $module_action, &$params, &$principal );
    $this->rh->tpl->Assign( "Action:TITLE", $this->rh->tpl->GetValue("Preparsed:TITLE") );
    return $this->rh->tpl->GetValue("Preparsed:CONTENT");
  }
  function Npj_Handler( $method, $params, &$principal )
  {
    $this->npj_handler_method = $method;
    $this->npj_handler_params = $params;
    return $this->Handle( $this->rel_url );
  }
  function Npj_IsGrantedTo( &$principal, 
                            $method, $object_class="", $object_id=0, $options="" )
  {
    return DENIED;
  }


// EOC { NpjModule }
}


?>