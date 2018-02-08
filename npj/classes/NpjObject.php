<?php
/*
    NpjObject( &$rh, $npj_address=NULL )  -- ������-�������������� ������. �� ���!
      - $rh          -- ��� ������, RequestHandler
      - $npj_address -- ������ ������� ��������������� ������������� ���� ������
                        ���� ���������� �� � NULL, �� ���������� ����� _Init(..)

  ---------

  // Brand new stuff
  * CacheGroups() -- �������� ������ �� ����������� ��������, �������� ��� ������ � �������,
                     ��������� ��� ��������� �������� �������
  * RipMethods( $npj_address ) -- �������� ������ � ���������������, ������ � "�������", ���� �����
  * &SpawnHelper( $weak = 0 ) -- ������ �������, ��������� �� $this->GetType() � $this->data
      - $weak = HELPER_WEAK -- ������ ���� ��� ��� �������� �������
      - ������ ������������� � $this->helper � ������������ �������
  * CompileCrossposted( $record_id ) -- ����������� �����-������-���� ��� ���� (� ��� ����� ������)
  * _UsageState( &$principal, $event="handler", $method="show", $params="" ) -- ������ �� �����. ����������


  * _Init( $npj_address ) -- ������������� ������� �������������� ��� � �����-�� ��������� ���
      - $npj_address -- ���������� �������� ���� kuso@npj:baka
  * GetType() -- ���������� 1 ��� 2, � ����������� �� ����, �������� ��� ��� ������
      - ������� ������ ��� ������ (!!! ������������� � GetRecordType ��� ������� �������� ��� ���� � ������� ���������)

  // ������ ��������, ������ ��������������� �� �������������
  * GetResourceValue( $message )  -- ���������� $this->rh->tpl->message_set[$message]
  * GetConfigValue( $message)     -- ���������� $this->rh->{$message}
  * GetInterWikiUrl($name, $tag)  -- ���������� ����� ��������� (!!! -- ����� ����������)
  * AddDatetime($supertag)        -- ���������� � ��������� ������� ������ (!!! -- ���� ������, ���������, ��������� �����)
  * AddSpaces($text, $space="&nbsp;")              -- ��������� ������� � ��������� ����-������ (!!! -- �����)

  // ������ ��� ������ �� ��������
  * Href( $npj_address, $is_rel=1, $ignore_state=1 ) -- ���������� URL, ��������������� ������� ���������
      - $npj_address  -- �������� ���� kuso@npj:baka ��� !/����������
      - $is_rel       -- ���� ����� ���������� (kuso@npj:baka), ����� ���������� � ����. ����, �� ��������� ������, ��� ������ ������������� ������� ����������.
      - $ignore_state -- ���� ����������� � ����, �� � ����� URL ������������� StateSet::Pack(MSS_GET)
      - URL ������������ ��� ������� � ������ ��� � <a href=...>

  * Link( $npj_address, $method="deprecated", $text="", $is_rel=1, $ignore_state=1) -- ��������� ������ ��� ������� � ���� ��������
      - $npj_address  -- �������� ���� kuso@npj:baka ��� !/����������
      - $method       -- ��� ������������� � �����
      - $text         -- �����, ������� ����� ������� �� ������
      - $is_rel       -- ���� ����� ���������� (kuso@npj:baka), ����� ���������� � ����. ����, �� ��������� ������, ��� ������ ������������� ������� ����������.
      - $ignore_state -- ���� ����������� � ����, �� � ����� URL ������������� StateSet::Pack(MSS_GET)
      - URL ������������ ��� ������� � ������ ��� � <a href=...>
      - ����� ������ ����������� =((( (!!!!)

  * PreLink($tag, $text = "") -- ���������� ������ ��� ���������� � � body_r 
      - $tag          -- ���� ���������� ������
      - $text         -- �����, ������� ����� ������� �� ������

  // ������ ������ ���� ���������
  * HasAccess( &$principal, $method="none", $options="" )  -- ���� �� � ����� ������� ������ � ����������?
      - $principal -- ������ ������ ((/Manifesto/����������/Principal Principal))
      - $method    -- ����� ����� �������� ������� �������� (��������, "acl" ��� "groups")
      - $options   -- �������������� ����� ��� ������ (� ������� ����. ��������, "acl" �������, 
                      ��� ��� ������-�������� ���� acl)

  * Format( $what, $formatter="wiki", $options="" ) -- ����� ���������� �� ((/Manifesto/����������/TemplateEngine TemplateEngine))
      - $what      -- ��� �������������
      - $formatter -- ����� ����������� (��������, "wiki", "wacko", "post_wacko")
      - $options   -- �����-�� �������������� ����� ��� ����������. --�� ����������� �� =)--


  // Handlers, actions & such stuff
  * IncludeBuffered( &$principal, $dir, $script_name, $params="" ) -- ����� ������ ����������� �������
      - $principal   -- ������ ������ ((/Manifesto/����������/Principal Principal))
      - $dir         -- ������� � handlers/, �� ���� -- ����� �������
      - $script_name -- �������� �������, �� ���� -- �������� ������ �������
      - $params      -- �����-�� ��������� ����� �����������
      - ��. ��� ���� ���� � ((/Manifesto/IncludeBuffered IncludeBuffered))

  * Handler( $method, $params, &$principal ) -- ����� Handler ��� �������� �������
  * Action( $method, $params, &$principal )  -- ����� Action
  * _PreparseArray( &$item ) -- ������������� ����� ��� "��������" ������ (http://npj.ru/kuso/npj/refaktoringactions)
  * _PreparseAccount( &$acc ) -- ������������� �������� ��� "��������" ������ (http://npj.ru/kuso/npj/refaktoringactions)
  * _ActionOutput( &$data, &$params, $default="list" ) -- ����������� � ����� ������� ����������� actions
  * Forbidden( $message_code="Common", $message_set = "forbidden_common" ) -- ����� ����������� ����������� �������� ��� �������� ������ ��������
      - $message_code -- ��� ������ � message_set "Forbidden.".$message_code
      - $message_set  -- � ����� message_set ������ ��� ������ 
      - ���� ����� "_forbidden"
  * NotFound( $message_code="Common", $message_set = "404_common" ) -- ����� ����������� ������ 404 ��� �������� ������ ��������
      - $message_code -- ��� ������ � message_set "Forbidden.".$message_code
      - $message_set  -- � ����� message_set ������ ��� ������ 
      - ���� ����� "_404"
  * Save()                   -- ����� ����������� ���������� �������� �������
      - ���� ����� "_save"

  // �������� ������ �� ��
  * &Load( $cache_level=2 ) -- �������� � ������� ������, � ��������������� ��������� �� ����
      - $cache_level -- ������� ����������� (������� ����� ���������)
      - ���� ����� "_load"
      - ��������� ��������� � $this->data

  * &_Load( $abs_npj_address, $cache_level=2, $cache_class=NULL, $no_cache=false ) -- �������� ��� ������������� ���������� �������
      - $abs_npj_address -- ������, ���������� �������� (���� kuso@npj:baka)
      - $cache_level -- ������� ����������� (������� ����� ���������)
      - $cache_class -- � ����� ����� ���������� ������ (���� NULL, �� ���� ����� �������� �������)
      - $no_cache    -- �� ���������� cache ��� ��������/�������������� ������, ������ ���� �����
      - ���� ����� "_load"
  * &_LoadById( $id, $cache_level=2, $cache_class=NULL, $no_cache=false ) -- �������� �� id ��� ������������� ���������� �������
      - $id          -- ��������� ������������� ������ � �������
      - $cache_level -- ������� ����������� (������� ����� ���������)
      - $cache_class -- � ����� ����� ���������� ������ (���� NULL, �� ���� ����� �������� �������)
      - $no_cache    -- �� ���������� cache ��� ��������/�������������� ������, ������ ���� �����
      - ���� ����� "_load_by_id"
  
  // ����������� � ���������
  * utf_decode($string) -- ��������� ������� �� UTF � win1251 (MSIE ����� ������� ����� � UTF) (!!! refactor)
  * NpjTranslit($tag) -- ������� $tag � �����������-����������� ������ (�� �������� ��������������)
  * Translit($tag, $direction=0) -- ������� $tag � ����������-����������� ������ (�������� ��������������)
  * Detranslit( $tag ) -- alias ��� Translit($tag, 1) -- �������������� �� �����������
  * GetFullTag( $tag = NULL, $supertag = NULL) -- �������� ������ ��� ���� kuso@npj:�����������
      - $tag      -- ���� �� ������, �� ���� �� $this->data["tag"], ��������, �����������
      - $supertag -- ���� �� ������, �� ���� �� $this->data["supertag"], ��������, kuso@npj:something
      - ���� �������� �� ������ � � $this->data ��� ���, ����������� $this->Load(2);
  * _UnwrapNpjAddress( $rel_npj_address, $unrecoverable=1 ) -- ����������� �������������� ������ � ����������
      - $rel_npj_adress -- ������������� �������� (���� baka ��� !/����������)
      - �������� NpjTranslit
  * _NpjAddressToUrl( $npj_address, $is_rel=0) -- ����������� ���������� ��������� � URL
      - $npj_adress -- ���������� ��� ������������� �������� (���� kuso@npj:baka ��� !/����������)
      - $is_rel     -- ���� ����� ���������� (kuso@npj:baka), ����� ���������� � ����. ����, �� ��������� ������, ��� ������ ������������� ������� ����������.
      - ����� ������ ���� ���������� �� ������ ���� (!!! ��� �����������, ������� ���� ���������)
  * _UrlToNpjAddress( $url, $node=NULL )                      
      - $url  -- ������������� URL ����� (��������, ��� http://node.ru/npjsite/kuso/baka ��� ����� "kuso/baka")
      - $node -- ����, � ������ �� ����� ���� URL. NULL -- ������ ������� (??? ��������� �� ������ �����)

  * _Trace( $what ) -- ���������� ����� �������, ������� � Trace-����� �������� � ������� ������ �������

  // ������ �������� ��������� ������� ������������
  * $this->data            -- ���������� ������, ��� ������
  * $this->cache_level     -- ������� ����������� ������

  * $this->class           -- ������-����� �������, ��������, "comments", "account", "record"
  * $this->method          -- �����/�������, �������� "show"
  * $this->params          -- array(..) � ����������� ������
  * $this->subspace        -- ���������������, � ������� ��������� ��������� �������� ����� ������, 
                              ��������, "" ��� "2002/12/31/26_new_year"
  * $this->name            -- ��� ����� �������, ��������, "2002/12/31/26_new_year" or "00026"
  * $this->tag             -- ��� ������� -- ��� ���� ������, �� ������������ NpjTranslit

  * $this->npj_address        -- ���-����� ������� (� �������)
  * $this->npj_object_address -- ���-����� ������� (��� ������)
  * $this->npj_context     -- �������� ��������� 
  * $this->npj_account     -- ����������� �������� 
  * $this->npj_node        -- ����������� ���� 

  * $this->path            -- ������ �� { "class", "name" }, ������������ ���� �� �������

  // ��������� � ������������ ���������
  * $this->NpjMacros, NpjLettersFrom, NpjLettersTo, NpjBiLetters -- ��������� ��� NpjTranslit
  * $this->Tran, DeTran -- ��������� ��� Translit/Detranslit
  * $this->NPJ_FUNCTIONS       -- �������� ������������ � ������ ������ ��� �������
  * $this->REGEX_NPJ_FUNCTIONS -- ���� �����, ������ ������� regex
  * $this->NPJ_SPACES    -- guess who? !!!
  * $this->REGEX_NPJ_SPACES    -- ������� regex ��������������, ������������ � ������ ������ ��� 

  * $this->security_handlers -- ������ ���� ������� ($this->GetType()) ������������� ����� ����������� ���������
  * $this->acls              -- ����� ������ acl � ������ ������ ���, ��� ������ �������� �������� �� �������

  * $this->rh->absolute_urls     -- � ���� ������ ��� �������� �� ���-������ � ��� �� ���������� � Host (http://ttlair fex.)

=============================================================== v.4 (Kuso)
*/
// <? <- ���� ������� �� �����, ����.
define("NO_NBSP", "1" );
define("NOT_EXIST", "empty" );
define("UPPER","[A-Z\xc0-\xdf\xa8]");
define("UPPERNUM","[0-9A-Z\xc0-\xdf\xa8]");              //?
define("LOWER","[a-z\xe0-\xff\xb8\/\-]");
define("ALPHA","[A-Za-z\xc0-\xff\xa8\xb8\_\-\/]");
define("ALPHA_L","[A-Za-z\xc0-\xff\xa8\xb8]");           //?
define("ALPHANUM","[0-9A-Za-z\xc0-\xff\xa8\xb8\_\-\/]");
define("ALPHANUM_L","[0-9A-Za-z\xc0-\xff\xa8\xb8\-]");
define("ALPHANUM_P","0-9A-Za-z\xc0-\xff\xa8\xb8\_\-\/");
define("NPJ_RELATIVE",1);
define("NPJ_ABSOLUTE",0);
define("NPJ_RECOVERABLE", 0);
define("NPJ_UNRECOVERABLE", 1);
define("NPJ_DECORATIVE", 1);
define("GROUPS_MODERATORS", 20);
define("GROUPS_REQUESTS", 0);
define("GROUPS_LIGHTMEMBERS", 5);
define("GROUPS_POWERMEMBERS", 10);
define("GROUPS_FRIENDS",  10);
define("GROUPS_COMMUNITIES",  9);
define("GROUPS_REPORTERS", 0);
define("GROUPS_SELF", 100);
define("ACCOUNT_USER", 0);
define("ACCOUNT_COMMUNITY", 1);
define("ACCOUNT_WORKGROUP", 2);
define("RECORD_MESSAGE",  1);
define("RECORD_POST",  1);
define("RECORD_DOCUMENT", 2);
define("COMMUNITY_PUBLIC", -1);
define("COMMUNITY_OPEN", 0);
define("COMMUNITY_LIMITED", 1);
define("COMMUNITY_CLOSED", 2);
define("COMMUNITY_SECRET", 3);
define("REP_RECORDS", 0);
define("REP_RECORD_COMMENTS", 1);
define("REP_COMMENTS", 2);
define("HELPER_ALWAYS", 0);
define("HELPER_WEAK", 1);
define("COMMENTS_TREE" , 1);
define("COMMENTS_FULL" , 0);
define("COMMENTS_PLAIN", 2);
define("WORKGROUPS_UNMANAGED", 0);
define("WORKGROUPS_MANAGED",   20); // �������, ��� ������� ���������� ����������� ���������� ��
define("ACTIONS4FEED", "a, anchor, toc"); //����������� � ���� �����
define("RIP_WEAK", 0); // ripmethods could be record or account
define("RIP_STRONG", 1); // ripmethods direct to record
/*
���� �� ���������? - ������ ���
����� ������� ����������? - ������ ���
�������� �� ������ ������� ��� �������������? - ������ ���
DOMAIN_NONE        - 000 = 0
DOMAIN_DIR_ONLY    - 001 = 1
DOMAIN_DOMAIN_ONLY - 011 = 3
DOMAIN_DIR         - 101 = 5
DOMAIN_DOMAIN      - 111 = 7
*/
define("DOMAIN_NONE",        0);
define("DOMAIN_DIR_ONLY",    1);
define("DOMAIN_DOMAIN_ONLY", 3);
define("DOMAIN_DIR",         5);
define("DOMAIN_DOMAIN",      7);

// �������� group2
define("ACCESS_GROUP_PUBLIC",      -1); // �� ������. �������, ��� group1=0
define("ACCESS_GROUP_PRIVATE",      0);
define("ACCESS_GROUP_CONFIDENTS",  -2); 
define("ACCESS_GROUP_COMMUNITIES", -3);


class NpjObject
{
  var $security_handlers = array( "unknown", "groups", "acl" );

  var $data;
  var $class;
  var $npj_address; // cache identifier
  var $npj_context; // address context
  var $path;
  var $rh;
  var $configuration;

  function NpjObject( &$rh, $npj_address=NULL )
  {
    $this->rh = &$rh;
    $this->NPJ_FUNCTIONS =  &$rh->NPJ_FUNCTIONS;
    $this->REGEX_NPJ_FUNCTIONS =  &$rh->REGEX_NPJ_FUNCTIONS;
    $this->NPJ_SPACES =  &$rh->NPJ_SPACES;
    $this->NPJ_ROOT_SPACES =  &$rh->NPJ_ROOT_SPACES;
    $this->REGEX_NPJ_SPACES =  &$rh->REGEX_NPJ_SPACES;

    $this->acls = $rh->RECORD_ACLS;
    $this->acls_actions_params = $rh->ACLS_ACTIONS_PARAMS;

    $this->configuration = get_object_vars( &$rh );
    if ($npj_address !== NULL) $this->_Init( $npj_address );
  }

  // ����������� ���� ��� ������
  function GetType() {
    if (is_numeric($this->name{0})) return 1; //blog
    return 2; //doc
  }

  // ������������� ���������� ������� 
  function _Init( $npj_address )
  {          
    $this->rh->debug->Milestone( "NpjObject->Init started ($npj_address)" );

    $a1 = explode( ":", $npj_address );
    $a1[0] = $this->NpjTranslit($a1[0]);
    $a2 = explode( "@", $a1[0] );
/*    
    $a3 = explode( "/", $a2[1] );
    $a2[1] = $a3[0];
    $a1[0] = $a2[0]."@".$a2[1];
*/
    // 1. strip node & account
    $this->npj_node    = $a2[1];
    $this->npj_account = $a1[0];
    $this->npj_filter  = "";
    $this->method   = "";
    $this->params   = array();
    $this->class    = "record";
    $this->subspace = "";
    $this->subspace_name = "";
    $this->module   = false;
    $this->module_instance = false;

    // 1.25 -- community-filter
    if ($this->rh->community_filter)
    {
      $in_by = explode("/", $this->npj_account);
      if ($in_by[0] == "in") array_shift($in_by);
      if ($in_by[1] == "by")
      {
        $npj_address = substr( $npj_address, strlen( $this->npj_account ));
        $this->npj_account = implode("/", array_slice( $in_by, 2 ));
        $this->npj_filter  = $in_by[0];
        $npj_address = $this->npj_account.$npj_address;
      }
    }
                           
    // 1.5 -- localizing node, �������� ������ ��� "������-����"
    $node_parts = explode("/", $this->npj_node);
    if ($this->rh->NPJ_QUASI_NODES[$node_parts[0]])
    {
      if (sizeof($node_parts) == 1)
      {
         $node_parts[1] = $this->rh->node_name;
         $this->npj_node    .= "/".$this->rh->node_name;
         $this->npj_account .= "/".$this->rh->node_name;
      }
      if ($node_parts[1] == $this->rh->node_name)
      {
        $this->rh->debug->Trace( "quasi-node model found: ".$node_parts[0] );
        $this->module = $this->rh->NPJ_QUASI_NODES[$node_parts[0]];
      }
    }

    // 2. �� ������� �� �� �������� ������ ����
   if (($a2[0] == "") || (preg_match("/^".$this->NPJ_FUNCTIONS."$/i", $a2[0], $match)))
   {
     $this->method = $a2[0];
     $this->class  = "node";
     array_shift($a1);
     $origdata = implode(":",$a1);
     $_origdata = $origdata;
   }
    else
   {
     
    // 3. ���������� ������ ��, ��� ����� : �� �����/�������/������
    $_npj_account = $a1[0];
    array_shift($a1);
    $origdata = implode(":",$a1);
    $_origdata = $origdata;
    $data = $this->NpjTranslit($origdata);

    // ������, ����� ���������� ����� ����������� �������
    if (isset($this->NPJ_ROOT_SPACES[$_npj_account]))
    {
      $this->rh->debug->Trace( "rigid module subspace found: ".$_npj_account );
      $this->module = $this->NPJ_ROOT_SPACES[$_npj_account];
    }
    
    // ���� ���-�� "���" ���������?
    if (sizeof($a1) > 0)
    {
      $_data = "/".$data."/";
      if (preg_match( $this->REGEX_NPJ_FUNCTIONS, $_data, $match ))
      {
        $this->rh->debug->Trace( "function found: ".$match[2] );
        $this->method = $match[2];
        //$this->params = explode("/", $match[3]); //�������� ������, �������� �������
        $data = $match[1];
        $origdata = "/".$origdata."/";
        $co = substr_count($_data, "/") - substr_count($data, "/");
        //$this->rh->debug->Trace($_data."++".$data);
        for ($i=0; $i<$co; $i++) $origdata = substr($origdata,0,strrpos($origdata,"/"));
        if ($this->method)
        {
         $opar = "/".$_origdata."/";
         for ($i=0; $i<substr_count($data, "/")+2; $i++) $opar = substr($opar,strpos($opar,"/")+1);
         $this->params = explode("/", $opar); //�������� ������� �������
         //$this->rh->debug->Trace($opar."++/".$a1[1]."/++".($co+2));
        }
      }

      // 4. ������ ���� �������� ��������������� ��������
      if ($data != "") 
      {
        $_data = "/".$data."/";
        if (preg_match( $this->REGEX_NPJ_SPACES, $_data, $match ))
        {
          $this->rh->debug->Trace( "subspace found: ".$match[2] );
          $this->class = $match[2];
          $this->subspace = ltrim($match[1], "/");
          $this->subspace_name = $match[2];
          $data = $match[3];
          $origdata = "/".$origdata."/";
          $co = substr_count($_data, "/") - substr_count($data, "/");
          for ($i=0; $i<$co; $i++) $origdata = substr($origdata,0,strrpos($origdata,"/"));
        } 
      } else 
      if ($this->method == "") $this->class="record"; 
                          else $this->class="account"; 

   }   else $this->class="account";

   }
    $data = trim($data, "/");
    $origdata = trim($origdata, "/");
    if ($data != "")  $this->name = $data;
    else if ($this->class == "node")
         {    $this->name = $this->NpjTranslit($_origdata);
              $this->params = explode("/", $this->name);
              $origdata = $_origdata;
         }
    else if ($this->class == "account") $this->name = $_npj_account;
    else $this->name="";

    $this->tag  = $origdata;

    if ($this->method == "") $this->method = "default";

    if (($this->class != "account") && ($this->class != "node"))
      $this->npj_object_address  = $this->npj_account.":".($this->subspace?$this->subspace."/":"").($this->subspace_name?$this->subspace_name.($this->name?"/":""):"").$this->name;
    else
      $this->npj_object_address = $this->npj_account;
/*    $this->rh->debug->trace("this->npj_account:".$this->npj_account);
    $this->rh->debug->trace("this->subspace:".$this->subspace);
    $this->rh->debug->trace("this->subspace_name:".$this->subspace_name);
    $this->rh->debug->trace("this->npj_object_address:".$this->npj_object_address);
    $this->rh->debug->trace("this->method:".$this->method);
    $this->rh->debug->trace("this->npj_address:".$this->npj_address);
*/    
    $this->npj_context = $this->subspace. ( ($this->class!="record")? "/".$this->class : "" ) .$this->name;
    $slash = strrpos($this->npj_context,"/");
    if ($slash !== false) $this->npj_context = substr($this->npj_context, 0, $slash);
    else $this->npj_context = "";

    if ($this->class=="account") $this->npj_context = "";

    // kuso patches
    $method_separator = "/";
    if ($this->class == "account") $method_separator = ":";
    if ($this->class == "node")    $method_separator = ":";
    if ((sizeof($this->params) == 1) && ($this->params[0] == ""))
     $this->params = array();
    // ---

    $this->npj_address = $this->npj_object_address.($this->method && $this->method!="default"?$method_separator.$this->method:"").(sizeof($this->params)?"/".trim(implode("/",$this->params),"/"):"");
    
    // !!!! refactor
    if (($this->class == "record") && (preg_match("/^2[0-9]{3}(\/([0-9]{1,2}|week)(\/[0-9]{1,2})?)?$/i", $this->name, $match)))
    {
      $this->method = "calendar";
      $this->params = explode("/",$this->name);
      $this->class  = "account";
      $this->name   = $this->npj_account;
    } else
    if (($this->class == "record") && (preg_match("/^2[0-9]{3}\/[0-9]{2}\/[0-9]{2}\/([0-9]+)([^\/]*)(\/.*)?$/", $this->name, $match)))
    {
      $this->name   = $match[1];
      $this->tag    = $this->name;
      $this->npj_address            = $this->npj_account.":".$match[1].$match[3];
      $this->npj_object_address     = $this->npj_account.":".$match[1];
      $this->npj_context            = "";
      //$this->_Trace( "NpjObject->Init done" );
      //$this->rh->debug->Error( $match[1] );
    }

    $this->_Trace( "NpjObject->Init done ($npj_address)" );
//    $this->rh->debug->Error( "NpjObject->Init done" );
    $this->_params = implode("|", $this->params );
    $this->rh->debug->Milestone( "NpjObject->Init done" );
  }

  // <? <- ���� ������� �� �����, ����.
  // ----------------------------------------- ������ ����� ��� ������ =====================================
  function Href( $npj_address, $is_rel=NPJ_RELATIVE, $ignore_state=STATE_IGNORE ) 
  {
    return $this->rh->Href( $this->_NpjAddressToUrl($npj_address, $is_rel), $ignore_state );
  }

  // !!! ����: ���������, ��� ��������.
  function GetResourceValue( $message ) 
  { return $this->rh->tpl->message_set[$message]; }
  function GetConfigValue( $message) 
  { return $this->configuration[ $message ]; }

  // !!!! stub
  function GetInterWikiUrl($name, $tag)
  {
    if ($url = $this->rh->interWiki[$name])
    {
      return $url.$tag;
    }
  }
  // ??? ����: �����������, ����� �� ����� ���������������� �����. ��� ��� overburden engine
  // 31.10.2004 kuso@npj claims it obsolete.
  function AddDatetime($supertag)
  { return ""; }

  function AddSpaces($text, $space="&nbsp;", $dont_obsolete=false)
  {
     if (!$dont_obsolete) return $text; // 31.10.2004 -- kuso@npj claims it obsolete yet.
     // �� �������� http:// ������
     if (strpos($text, "http://") === 0) return $text;

//   if ($user = $this->GetUser()) $show = $user["show_spaces"];
//   if ($show!="N") {
          $text = preg_replace("/(".ALPHANUM.")(".UPPERNUM.")/","\\1".$space."\\2",$text);
          $text = preg_replace("/(".UPPERNUM.")(".UPPERNUM.")/","\\1".$space."\\2",$text);
          $text = preg_replace("/(".ALPHANUM.")\//","\\1".$space."/",$text);
          $text = preg_replace("/(".UPPER.")".$space."(?=".UPPER.$space.UPPERNUM.")/","\\1",$text);
          $text = preg_replace("/(".UPPER.")".$space."(?=".UPPER.$space."\/)/","\\1",$text);
          $text = preg_replace("/\/(".ALPHANUM.")/","/".$space."\\1",$text);
          $text = preg_replace("/(".UPPERNUM.")".$space."(".UPPERNUM.")($|\b)/","\\1\\2",$text);
          $text = preg_replace("/([0-9])(".ALPHA.")/","\\1".$space."\\2",$text);
          $text = preg_replace("/(".ALPHA.")([0-9])/","\\1".$space."\\2",$text);
          $text = preg_replace("/([0-9])".$space."(?=[0-9])/","\\1",$text);
     // removing spaces before/after "/" is an obsolete action
     if (!$dont_obsolete)
     {
       $text = str_replace($space."/", "/", $text);
       $text = str_replace("/".$space, "/", $text);
       $text = str_replace("/".$space, "/", $text);
     }
//   }
   return $text;
  }
    // �����������������������������
    var $NpjMacros = array( "����" => "wiki", "����" => "wacko", "�����" => "shwacko",
                            "���" => "web", "����" => "lance", "�����" => "kukutz", "���������" => "mendokusee",
                            "������" => "iaremko", "�������" => "nikolai", "�������" => "aleksey", 
                            "��������" => "anatoly", "���" => "npj", 
                          );
    var $NpjLettersFrom = "���������������������";
    var $NpjLettersTo   = "abvgdeziklmnoprstufcy";
    var $NpjConsonant = "���������������������";
    var $NpjVowel = "���������";
    var $NpjBiLetters = array( 
      "�" => "jj", "�" => "jo", "�" => "zh", "�" => "kh", "�" => "ch", 
      "�" => "sh", "�" => "shh", "�" => "je", "�" => "ju", "�" => "ja"
                              );
    function NpjTranslit($tag, $unrecoverable=1, $decorative=0)
    // ���� ������������, �� ��� �� ������� ��������, ��
    {
      // ��� ������� ����������� �������� given $tag, ����� ��� �������� �����.
      if (!$unrecoverable) return $tag;

      $tag = str_replace( "//", "/", $tag );
      //$tag = str_replace( "-", "\xB6", $tag );
      $tag = str_replace( " ", "\xB6", $tag );
      //insert \xB6 between words
      $tag = preg_replace("/(".ALPHANUM.")(".UPPERNUM.")/","\\1\xB6\\2",$tag);
      $tag = preg_replace("/(".UPPERNUM.")(".UPPERNUM.")/","\\1\xB6\\2",$tag);

      $tag = strtolower( $tag );
      //here we replace �/� 
      $tag = preg_replace("/(�|�)([".$this->NpjVowel."])/","j\\2",$tag);
      $tag = preg_replace("/(�|�)/","",$tag);
      //drop \xB6
      $tag = str_replace( "\xB6", "", $tag );
      $tag = strtr( $tag, $this->NpjMacros );
      $tag = strtr( $tag, $this->NpjLettersFrom, $this->NpjLettersTo );
      $tag = strtr( $tag, $this->NpjBiLetters );
      if ($decorative == NPJ_DECORATIVE)
      {
        $tag = preg_replace( "/_+/", "_", $tag );
        $tag = preg_replace( "/_\//", "/", $tag );
        $tag = preg_replace( "/\/_/", "/", $tag );
        $tag = preg_replace( "/_-/", "-", $tag );
        $tag = preg_replace( "/-_/", "-", $tag );
        $tag = preg_replace( "/([0-9])_([0-9])/", "$1$2", $tag );
        $tag = preg_replace( "/([0-9])_([0-9])/", "$1$2", $tag );
      }
      else
      {
        if (is_numeric($tag[0])) $tag = preg_replace("/_.*?(\/|$)/", "\\1", $tag); 
        if ($tag[0] == "0") $tag = "0".ltrim($tag,"0");
        else $tag = str_replace( "_", "", $tag );
      }

      return rtrim($tag, "/");
    }

  function _icon( $icon_name, $is_skin = 0)
  {
    $title = $this->GetResourceValue( "IconTitle.".$icon_name );
    return "<img src=\"".($this->rh->absolute_urls?trim($this->rh->tpl->GetValue("BaseHost"),"/"):"").
     ($is_skin?$this->rh->tpl->GetValue("images"):$this->rh->tpl->GetValue("theme_images")).
     "i_".$icon_name.".gif\" align=\"absmiddle\" class=\"".$icon_name."\" border=\"0\" title=\"".$title."\" alt=\"\" />";
  }
  function Link( $npj_address, $options="", $text="", $is_rel=1, $ignore_state=1, $recursion=0) 
  {
    // super autotitle thing
    if ($npj_address{0} == "^")
    {
      $_npj_address = $this->_UnwrapNpjAddress( substr($npj_address, 1) );
      $record = &new NpjObject( $this->rh, $_npj_address );
      $record->Load(2);
      if (is_array($record->data))
      { 
        return $this->Link( $record->data["supertag"], $options, $record->data["subject"], $is_rel, $ignore_state, $recursion );
      }
    }

    if (!$recursion) 
    {
     if (!$text) $text = $this->AddSpaces(htmlspecialchars($npj_address, ENT_NOQUOTES));
     else        $text = htmlspecialchars($text, ENT_NOQUOTES);
    }

    $imlink = false;
    if (preg_match("/^[\.\-".ALPHANUM_P."\_]+\.(gif|jpg|jpe|jpeg|png)$/i", $text))
    {
      $account = &new NpjObject( &$this->rh, $this->npj_account);
      $data = & $account->Load(2);
      $dir = $data["file_url_prefix"];
      if (!$dir) $dir = $this->rh->npj_images_dir;
      $dir = rtrim($dir, "/");
      $imlink = $dir."/".$text;
    }
    else if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>]+)\.(gif|jpg|jpe|jpeg|png)$/i", $text))
      $imlink = $text;

    $tag = $npj_address;
    if (!$recursion) $text = $this->Format($text, "typografica"); //!!! refactor it, make option, move to prelink etc.
    $url = '';

    if (preg_match("/^(mailto[:])?[^\\s\"<>&\:]+\@[^\\s\"<>&\:]+\.[^\\s\"<>&\:]+$/", $tag, $matches))
    {// this is a valid Email
      $url = ($matches[1]=="mailto:" ? $tag : "mailto:".$tag); 
      $title = $this->GetResourceValue("IconTitle.mail"); 
      $icon = $this->_icon("mail");
    }
    else if (preg_match("/^#/", $tag))
    {// html-anchor
      return '<a href="'.$tag.'">'.$text.'</a>';
    }
    else if (preg_match("/^\\\\\\\\[[:alnum:]\\\!\.\_\-]+\\[".ALPHANUM_P."\\\!\.]*$/", $tag))
    {// LAN-path
      return '<a href="'.$tag.'">'.$text.'</a>';
    }
    else if (preg_match("/^([A-Z][a-zA-Z]+)[:](".ALPHANUM."*)$/", $tag, $matches))
    {//interwiki
      $parts = explode("/",$matches[2]);
      for ($i=0;$i<count($parts);$i++) $parts[$i]=urlencode($parts[$i]);
      $url = $this->GetInterWikiUrl($matches[1], implode("/",$parts));
      return $this->rh->Link( $url, $text, NULL, NULL, 1, $ignore_state );
    }
    else if (preg_match("/^[\.".ALPHANUM_P."]+\.(gif|jpg|jpe|jpeg|png)$/i", $tag))
    {// local image 
      $account = &new NpjObject( &$this->rh, $this->npj_account);
      $data = & $account->Load(2);
      $dir = $data["file_url_prefix"];
      if (!$dir) $dir = $this->rh->npj_images_dir;
      $dir = rtrim($dir, "/");
      $_text = strip_tags($text);
      return "<img src=\"".$dir."/".$tag."\" ".($_text?"alt=\"".$_text."\" title=\"".$_text."\"":"")." />";
    }
    else if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>]+)\.(gif|jpg|jpe|jpeg|png)$/i", $tag))
    {// external image 
      $_text = strip_tags($text);
      return "<img src=\"".$tag."\" ".($_text?"alt=\"".$_text."\" title=\"".$_text."\"":"")." />";
    }
    else if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>]+)\.(gz|tgz|zip|rar|exe|doc|xls|ppt|tgz|pdf)$/i", $tag))
    {// this is a file link
      $url = $tag; 
      $title = $this->GetResourceValue("IconTitle.file"); 
      $icon = $this->_icon("file");
      // <wbr> patch by kuso
      {
        $__c=0; $__t = array();
        while($__c < strlen($text))
        { $__t[] = substr( $text, $__c, 50);
          $__c+=50;
        }
        $__t[] = substr( $text, $__c);
        $text = implode( "<wbr />", $__t );
      }
    } 
    else if (preg_match("/^(".UPPER.ALPHANUM."+)\.(".ALPHANUM."+)(\#[".ALPHANUM_P."\_]+)?$/s", $tag, $matches)) 
    {// it`s a Tiki link!
//      if (!$text) $text = $this->AddSpaces($tag);
      $tag = "/".$matches[1]."/".$matches[2].$matches[3];
      return $this->Link( $tag, $options, $text, $is_rel, $ignore_state, 1 );
    }
    else if (preg_match("/^(http|https|ftp):\/\/([^\\s\"<>]+)$/i", $tag))
    {// this is a valid external URL
      $url = $tag; 
      
      $title = $this->GetResourceValue("IconTitle.web"); 
      $icon = $this->_icon("web");

      if ($imlink)
      {
        $_text = strip_tags($text);
        $text="<img src=\"".$imlink."\" border=\"0\" title=\"".$_text."\" />";
      }
      else
      {
        // <wbr> patch by kuso
        $__c=0; $__t = array();
        while($__c < strlen($text))
        { $__t[] = substr( $text, $__c, 50);
          $__c+=50;
        }
        $__t[] = substr( $text, $__c);
        $text = implode( "<wbr />", $__t );
      }

      //$this->rh->debug->Error( $text );

    } 
    else if (preg_match("/^([\:\!\.@".ALPHANUM_P."]+)(\#[".ALPHANUM_P."\_]+)?$/", $tag, $matches)) 
     {// it's a NPJ address!

      //$this->rh->debug->Trace("NPJ_ADDRESS1 = $npj_address");
      if ($is_rel) $npj_address = $this->_UnwrapNpjAddress( $matches[1], NPJ_RECOVERABLE );
      $npj_address = $this->NpjTranslit( $npj_address );
      $url_npj_address = $npj_address; // ���-����� ����������, ����� ���������� �� ������
      //$this->rh->debug->Trace("NPJ_ADDRESS2 = $npj_address");

      if ($imlink)
      {
        $_text = strip_tags($text);
        $text="<img src=\"".$imlink."\" border=\"0\" title=\"".$_text."\" />";
      }

      $anchor = $matches[2];
      $lockicon = $this->_icon("lock");
      $keyicon  = $this->_icon("key");  
      $privateicon  = $this->_icon("private");
      $friendsicon  = $this->_icon("friends");
      
      // �������� ������ �����.
      $stag = $this->RipMethods(&$npj_address);
      //$this->rh->debug->Trace("STAG = $stag");

      if (strpos($npj_address, ":") === false)
      {
        $obj = &new NpjObject( &$this->rh, $stag);
        $data = $obj->Load(2);
      }
      else if ($this->class == "record") $data = $this->_Load($stag, 1);
      else if ($this->record) $data = $this->record->_Load($stag, 1);
      else 
      {
        $obj = &new NpjObject( &$this->rh, $stag );
        $data = $obj->Load(1);
      }

      // ��� �������� ����� ������� ��������� ������
      //$this->rh->debug->Trace("NPJ_ADDRESS3 = $npj_address");
      $url = $this->_NpjAddressToUrl( $url_npj_address, NPJ_ABSOLUTE );

      $this->rh->debug->Trace("its npj: $url, $npj_address");

      // ������ ���������� ----------------------------------------------------
      if ((is_array($data) && ($data["id"] > 0)) || $npj_address{0}==="@") 
      {
        if ($data["type"]==1) $security = "groups";
        else if ($data["type"]==2) $security = "acl";
        else $access = true;
        if (!$access && $data["account_type"] == 0) $access = $this->rh->principal->IsGrantedTo($security, "record", $data["id"]); 
        else $access = true;

        $prefix = "";

        if (!$access)
        {
          $text = $lockicon.trim($text);
          $bonus = "class = 'denied'";
          $title = $this->rh->tpl->message_set["AccessDenied"];
        }
        else
        {
          $text = trim($text);
          $p = strpos($npj_address,":");

          if (($p === false) || ($p ==strlen($npj_address)-1)) 
          {
           if ($data["account_type"] >= 0)
           if ($data["user_id"] == 1)
           { // �������� ������� guest@node
             return htmlspecialchars($data["user_name"]);
           } else
           { // ������������ ��� ����������      $$$$

             if ($p === false) 
             {
              $iconno = $data["account_type"];
              $adata =& $data;
             }
             else 
             { 
              $acnt = &new NpjObject( &$this->rh, rtrim($npj_address, ":") ); 
              $adata = $acnt->Load(1);
              $iconno = $adata["account_type"]; 
             }
             $prefix = "<a href=\"".$this->Href( rtrim($npj_address, ":").":profile", NPJ_ABSOLUTE, $ignore_state )."\">".
                       $this->_icon("account".$iconno)."</a>"; 

             if ($npj_address{0}==="@")
             {
              $adata["node_id"] = substr($npj_address, 1);
              $prefix = "<a href=\"".$this->Href( $npj_address, NPJ_ABSOLUTE, $ignore_state )."\">".
                        $this->_icon("node")."</a>"; 
              if ($text{0}==="@") $text = substr($text, 1);
             }

             if (!$this->nodeobject) {
               $this->nodeobject = &new NpjObject( &$this->rh, "show@".$adata["node_id"]);
             }
             $nodedata =& $this->nodeobject->_Load("show@".$adata["node_id"], 1);
             // -- ���� ������ �� ������, ������������ "��� ����"
             if (!is_array($node_data))
               if (isset($this->rh->NPJ_QUASI_NODES[ $adata["node_id"] ]))
                 $nodedata = array( "is_local" => 1 );
             // --
             if (is_array($nodedata)) 
             {
              if ($nodedata["is_local"]!=1) 
              { //����� ������������ ����
               $p = strpos($npj_address,":");
               if ($npj_address{0}==="@")
               { // ����
                 $prefix = "<a href=\"".$this->rh->Href($this->_NpjAddressToUrl( $npj_address, NPJ_RELATIVE).
                                                        "?authto=".$this->rh->principal->data["node_id"], STATE_IGNORE)."\">".
                           $this->_icon("foreignnode")."</a>";
               } 
               else if (($p === false) || ($p ==strlen($npj_address)-1)) 
               { // ������������ ��� ����������
                 $prefix = "<a href=\"".$this->rh->Href($this->_NpjAddressToUrl( rtrim($npj_address, ":").":profile", NPJ_RELATIVE).
                                                        "?authto=".$this->rh->principal->data["node_id"], STATE_IGNORE)."\">".
                           $this->_icon("foreignacc")."</a>";
               } 
               else
               { // ���� ������
                 $prefix = $this->_icon("foreign");
               }
               $url = $this->_NpjAddressToUrl( $npj_address, NPJ_RELATIVE);
               $text = trim($text);
               return ($options["subject"]?"":$prefix).$this->rh->Link( $url."?authto=".$this->rh->principal->data["node_id"].($anchor?$anchor:""), $text, $title, $bonus, 1, $ignore_state );
              }
             }
             else 
             { //���� �� ���������� � ��
               return $tag;
             }
           } 
          }
          else
          {
            if ($data["type"]==1)
            {
             if ($data["group2"]==-2)      $prefix = $friendsicon;
             else if ($data["group2"]==-1) $prefix = $privateicon;
             else if ($data["group1"]!=0)  $prefix = $keyicon;
            }
            else if ($data["type"]==2)
            {
             $acl = $this->rh->cache->Restore( "record_acl_read", $data["id"], 2 );
//  if (preg_match("/testnpj/i", $tag)) $this->rh->debug->Error($npj_address."|".$acl["acl"]."|".$data["id"]);
             
             if ($acl["acl"] != "*") $prefix = $keyicon; //!refactor this
             else if ($acl["acl"] == "") $prefix = $privateicon;
            }
          }
          
        }
        return (($options["subject"] || $options["feed"])?"":$prefix).$this->rh->Link( $url.($anchor?$anchor:""), $text, $title, $bonus, 1, $ignore_state );
      }
      else // ������ �� ���������� -------------------------------------------------------------------
      {
        $p = strpos($npj_address,"@"); $q = strpos($npj_address,":");
        if ($q === false) 
        {
          $npj_account = $npj_address;
          $npj_node = substr($npj_address, $p+1);
        }
        else 
        {
          $npj_account = substr($npj_address, 0, $q);
          $npj_node = substr($npj_address, $p+1, $q-$p-1);
        }
        if (!$this->nodeobject) {
          $this->nodeobject = &new NpjObject( &$this->rh, "show@".$npj_node );
        }
        $nodedata =& $this->nodeobject->_Load("show@".$npj_node, 2);
        if (is_array($nodedata)) 
        {
         if ($nodedata["is_local"]==1) 
         { //������ ����, �������� ���� �� ����� �������
          $accdata =& $this->rh->object->_Load($npj_account, 2, "account");
          if (!is_array($accdata))
          { // ����
           $text = trim($text);
           return (($options["subject"] || $options["feed"])?"":$this->_icon("broken")).$this->rh->Link( $url.($anchor?$anchor:""), $text, $title, $bonus, 1, $ignore_state );
          }
         } 
         else 
         { //����� ������������ ����
          $p = strpos($npj_address,":");
          if (($p === false) || ($p ==strlen($npj_address)-1)) 
          { // ������������ ��� ����������
            $prefix = "<a href=\"".$this->_NpjAddressToUrl( rtrim($npj_address, ":").":profile", NPJ_RELATIVE)."?authto=".$this->rh->principal->data["node_id"]."\">".
                      $this->_icon("foreignacc")."</a>";
          } 
          else
          { // ���� ������
            $prefix = $this->_icon("foreign");
          }
          $url = $this->_NpjAddressToUrl( $npj_address, NPJ_RELATIVE);
          $text = trim($text);
          return (($options["subject"] || $options["feed"])?"":$prefix).$this->rh->Link( $url."?authto=".$this->rh->principal->data["node_id"].($anchor?$anchor:""), $text, $title, $bonus, 1, $ignore_state );
         }
        }
        else 
        { //���� �� ���������� � ��
          return $tag; //!!! � ��� ������ � ������ $options["feed"]?
        }

        //UnWrap ������ ������, �� �������. ������������� �������� ������.
        $tag = str_replace(":/", ":", $tag);
        $tag = str_replace("::", "@:", $tag);

        if ($tag[0] == "!")  
        {
          $context = $this->npj_object_address; 
          $tag = $this->Translit(substr( $tag, 2 ));
        }
        else if (strpos($tag, "../") === 0)
        { 
          $pos = strrpos( $this->npj_context, "/" );
          if ($pos !== false) $context = "/".substr($this->npj_context, 0, $pos );
          else $context = "/";
          $tag = $this->Translit(substr($tag,3));
        }
        else if (strpos($tag, "/") === 0)
        { 
          $context = "/";
          $tag = $this->Translit(substr($tag,1));
        }
        else if (is_numeric($tag{0}))
        {                                     
          $context = "/";
          $tag = $this->Translit($tag);
        }
        else if (strpos($tag, ":") > 0)
        { 
          $tt = explode(":", $tag);
          $context = $tt[0].":";
          $tag = $this->Translit($tt[1]);
        }
        else
        { 
         $context = $this->context; 
         $tag = $this->Translit($tag);
        }
        //����� UnWrap-� ������ ������, �� �������.

        if (($options["feed"]==1) || 
            (($context{ strlen($context)-1 } == ":") && (is_numeric($tag)))
           )
         $result = "<a href=\"".$this->href($context)."/".$tag."\">".$text."</a>";
        else
        $result = "<span class=\"missingpage\">".$text."</span>".
                  ((($this->method!="print")&&($this->method!="msword"))?
                   "<a href=\"".$this->href($context)."/add/".$tag."\" title=\"".$this->GetResourceValue("IconTitle.wanted")."\">?</a>":
                   "");    
        return $result;
      }

    }

    return $url ? "<a ".$aname." href=\"$url\" target=\"_blank\" title=\"$title\" class=\"outerlink\">".($imlink?"<img src=\"$imlink\" border=\"0\" />":"".($options["subject"]?"":$icon).$text."")."</a>" : $text;
  }

  // by kukutz here
  function PreLink($tag, $text = "") 
  {
    if (!$text) $text = $tag;
    if (preg_match("/^([\!\.\@\:".ALPHANUM_P."]+)(\#[".ALPHANUM_P."\_]+)?$/", $tag, $matches))
    {// it's a Wiki link!
      $this->TrackLink( $matches[1], $text);
    }
    return "<!--notypo-->��".$tag." == ".$text."��<!--/notypo-->";
  }

  // by kukutz here. ��� Link ��� ���������� RawHTML
  function RawLink($tag) 
  {
    if (substr($tag, 0, 6)=="npj://")
      $tag = substr($tag, 6);

    if (preg_match("/^(".UPPER.ALPHANUM."+)\.(".ALPHANUM."+)$/s", $tag, $matches)) 
    {// it`s a Tiki link!
      $tag = "/".$matches[1]."/".$matches[2];
    }

    if (preg_match("/^([\:\!\.@".ALPHANUM_P."]+)(\#[".ALPHANUM_P."\_]+)?$/", $tag, $matches)) 
    {// it's a NPJ address!

      $npj_address = $this->_UnwrapNpjAddress( $matches[1] );

      $anchor = $matches[2];

      $p = strpos($npj_address,"@"); 
      $q = strpos($npj_address,":");
      if ($q === false) 
        $npj_node = substr($npj_address, $p+1);
      else 
        $npj_node = substr($npj_address, $p+1, $q-$p-1);

      if (!$this->nodeobject)
        $this->nodeobject = &new NpjObject( &$this->rh, "show@".$npj_node );

      $nodedata =& $this->nodeobject->_Load("show@".$npj_node, 2);
      if (is_array($nodedata)) 
      {
       if ($nodedata["is_local"]==1) 
       { //������ ����
        $url = $nodedata["url"].$this->_NpjAddressToUrl( $npj_address, NPJ_ABSOLUTE);
        return $url.($anchor?$anchor:"");
       } 
       else 
       { //����� ������������ ����
        $url = $this->_NpjAddressToUrl( $npj_address, NPJ_RELATIVE);
        return $url."?authto=".$this->rh->principal->data["node_id"].($anchor?$anchor:"");
       }
      }
      else 
      { //���� �� ���������� � ��
        return $tag; 
      }
      
    }

  }

  function TrackLink($tag, $text) 
  {
   $supertag = $this->backlinks[] = $this->_UnwrapNpjAddress($tag);
   $this->backlinks_text[$supertag] = $text;
   $this->backlinks_tag[$supertag] = $this->_UnwrapNpjAddress($tag, NPJ_RECOVERABLE);
  }
  // ------------------------------------------ ����� ������� ����� ��� ������ ---------------------

  // --- ������ ������ ���� ----
  // ���� �� � ����� ������� ������ � ������������ ����������
  function HasAccess( &$principal, $method="none", $options="" ) 
  { 
    if ($this->data["id"])
     return $principal->IsGrantedTo( $method, $this->class, $this->data["id"], $options );
    else
     return $principal->IsGrantedTo( $method, "npj", $this->npj_object_address, $options );
     //!!!! �� ����� �� ������� ���� � ��� ��� id?
  }

  function Format( $what, $formatter="wiki", $options="") 
   { 
     if (!is_array($options)) 
     {
      $op = $options; $options = array();
      $options["default"] = $op;
     }
     if ($options["default"]=="post") 
     {
      if ($formatter=="wacko") $formatter = "post_wacko";
      else if ($formatter=="rawhtml" || $formatter=="simplebr") $formatter = "post_safehtml";
      else $this->rh->debug->Error("Unknown formatting:".$formatter.".");
     }
     if ($options["default"]=="pre") 
     {
      if ($formatter=="wacko") $formatter = "pre_wacko";
      else return $what;
     }
     if ($options["default"]=="after") 
     {
      if ($formatter=="rawhtml") $formatter = "after_dedit";
      else return $what;
     }
     $o = &$this->rh->object;
     $this->rh->object = &$this;
     $result = $this->rh->tpl->Format( $what, $formatter, NULL, 0, $options ); 
     $this->rh->object = &$o;
     return $result;
   }


  // ������� ��������� � ������. ------------------------------------------------
  // ����� ������ ��������� �������
  function IncludeBuffered( &$principal, $dir, $script_name, $params="" )
  {
    $state     = &$this->rh->state;
    $rh        = &$this->rh;
    $cache     = &$this->rh->cache;
    $tpl       = &$this->rh->tpl;
    $db        = &$this->rh->db;
    $debug     = &$this->rh->debug;
    $object    = &$this;

    $tpl->Assign("IncludeBuffered:404",0);
    $__fullfilename = rtrim($dir,"/")."/".$script_name.".php";
    $this->rh->debug->Trace("Launching handler: ".$__fullfilename);
    if (!file_exists($__fullfilename)) 
    {
      $this->rh->debug->Trace("Unknown method handler! (file: ".__FILE__.", line: ".__LINE__.")");
      $tpl->Assign("IncludeBuffered:404",1);
      $__fullfilename = rtrim($dir,"/")."/_404.php";
      if (!file_exists($__fullfilename)) 
        $this->rh->debug->Error("($__fullfilename) 404 method handler not supplied! (file: ".__FILE__.", line: ".__LINE__.")", 3);
    }

    ob_start();
    $_somedata = include($__fullfilename);
    if ($_somedata===false) $this->rh->debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
    if (!$_somedata) $_somedata = ob_get_contents(); 
    ob_end_clean();

    return $_somedata;
  }
  // <? <- ���� ������� �� �����, ����.
  // ������ � "��������"
  function PassToModule( $module_class, $module_handler, $_params, &$principal )
  {
    $module = &NpjModule::StaticFactory( &$this, $module_class );
    return $module->PassToModule( $module_handler, $_params, &$principal );
  }
  // ------------------------ �����-����� ------------------------
  function Handler( $method, $params, &$principal )
  { 
    // third party module call
    if ($this->rh->modules && isset($this->rh->modules[$this->class]))
       return $this->PassToModule( $this->class, "handler", 
                                   array( "method"=>$method, "params"=>$params, ), 
                                   &$principal );
    // further party module call
    if ($this->rh->modules && $this->module && isset($this->rh->modules[$this->module]))
    {
      $result = $this->PassToModule( $this->module, "handler", 
                                     array( "method"=>$method, "params"=>$params, ), 
                                     &$principal );
      if ($result == GRANTED) return $result;
    }

    $__fullfilename = $this->rh->handlers_dir.$this->class."/".$method.".php";
    if (!file_exists($__fullfilename)) 
      if ($this->class == "node")
      {
        $obj = &new NpjObject( &$this->rh, $this->rh->node_user.":" );
        $obj->method = $this->method;
        $obj->npj_address.= $this->method;
        // module passthru
        $obj->module = &$this->module;
        $obj->module_instance = &$this->module_instance;

        $this->record = &$obj;
        return $obj->Handler( $method, &$params, &$principal );
      } else
      if ($this->class == "account")
      {
        $obj = &new NpjObject( &$this->rh, $this->name.":" );
        $obj->method = $this->method;
        $obj->npj_address.= $this->method;
        // module passthru
        $obj->module = &$this->module;
        $obj->module_instance = &$this->module_instance;

        $this->record = &$obj;
        return $obj->Handler( $method, &$params, &$principal );
      } else
      if ($this->class == "record")
      {
        $obj = &$this;
        $obj->method = "action";
        $this->record = &$obj;
        array_unshift( $params, $method );
        return $obj->Handler( "action", &$params, &$principal );
      } 
    if (!file_exists($__fullfilename)) // ���� �� ��� �� �������, �� ������� ��������� ����������
     if ($this->rh->custom_handlers_dir != $this->rh->handlers_dir)
     { $_t = $this->rh->handlers_dir;
       $this->rh->handlers_dir = $this->rh->custom_handlers_dir;
       $result = &$this->Handler( $method, &$params, &$principal );
       $this->rh->handlers_dir = _t;
       return $result;
     }
    $this->_UsageState( &$principal, "handler", $method, &$params );
    return $this->IncludeBuffered( &$principal, $this->rh->handlers_dir.$this->class, $method, $params ); 
  } 
  function Action( $method, $params, &$principal )
  { 
    // �������� ������ �������� ����� ������
    if ($rh->no_actions_in_posts) if ($this->GetType() == RECORD_POST) return "";

    $m = strtolower($method);
    $m = trim($m);

    $__fullfilename = $this->rh->npj_actions_dir.$m.".php";
    if (!@file_exists($__fullfilename)) // ���� �� ��� �� �������, �� ������� ��������� ����������
     if ($this->rh->custom_actions_dir)
     if ($this->rh->custom_actions_dir != $this->rh->npj_actions_dir)
     { $_t = $this->rh->npj_actions_dir;
       $this->rh->npj_actions_dir = $this->rh->custom_actions_dir;
       $result = &$this->Action( $method, &$params, &$principal );
       $this->rh->npj_actions_dir = _t;
       return $result;
     }

    $this->_UsageState( &$principal, "action", $method, &$params );

    $w = &$this->rh->action_wrappers;
    $this->rh->tpl->Assign("Action:404", $method == "_404");
    $this->rh->tpl->Assign("Action:NoWrap", 0);
    $this->rh->tpl->Assign("Action:NONE", 0);
    $this->rh->tpl->Assign("Action:Name", $method);
    $this->rh->tpl->Assign("Action:TITLE", $this->rh->tpl->message_set["Actions"][$m]);

    
    // check security ACTIONS
    $secure=1;
    if (isset($this->acls_actions_params[$m]))
    {
     $ppp = $this->acls_actions_params[$m];
     if ($params["action_as_handler"]) $ppp[] = "action_target";

     foreach($ppp as $v)
      if (isset($params[$v]))
      {
        $vv = explode(" ",$params[$v]);
        foreach($vv as $vvv)
        {
          $record = &new NpjObject( &$this->rh, $this->_UnwrapNpjAddress($vvv) );
          $d = &$record->Load(3);
          if ($d != "empty")
           if (!$record->HasAccess(&$principal, "acl", "actions")) { $secure=0; break; }
        }
        if (!$secure) break;
      }
    }

    // third party module call
    $action_inside=1;
    if ($this->rh->modules)
    { 
      $_m = explode(">", $m);
      if (isset($this->rh->modules[$_m[0]]) && isset($_m[1]))
      {
        $action_inside=0;
        $value = $this->PassToModule( $_m[0], "action", 
                                      array( "method"=>$method, "module_action" => $_m[1],
                                             "params"=>&$params, ), 
                                      &$principal );
      }
    }
    if ($action_inside)
    {
      if ($secure)
      {

        $value = $this->IncludeBuffered( &$principal, $this->rh->npj_actions_dir, $m, &$params );
      }
      else
      {
        $params["forbidden"] = 1;
        $value = $this->IncludeBuffered( &$principal, $this->rh->npj_actions_dir, "_404", &$params );
      }
    }
    
    if ($this->rh->tpl->GetValue("Action:NONE") && isset($params["hide"])) return ""; // ??? ��� ��� �� �������� ��������� � ������ �����������

    // �� ������ ���� ��� ������ ��� ��������
    if ($this->rh->tpl->GetValue("Action:404")) return $value; // � ��������� �� ������ �� ������ ���� �������� anyway
    if ($this->rh->tpl->GetValue("IncludeBuffered:404")) return $value;
    
    return $this->_Wrapper( $value, $params ); // $params["wrapper"], $params["wrapper_align"] == ?
    
  }

  // {{Action:TITLE}}, {{Action:CONTENT}}, {{Action:WrapperAlign}}
  function _Wrapper( $content, $params )
  {
    if ($params["fullwidth"]) $this->rh->tpl->Assign( "Preparsed:READABLE", 0 );

    $w = &$this->rh->action_wrappers;
    if ($this->rh->tpl->GetValue("Action:NoWrap")) return $content; // ??? ��� ��� ��������� ��������
    if ($params["wrapper"] == "none") return $content;
    $this->rh->tpl->Assign("Action:CONTENT", $content);

    if (!isset($w[$params["wrapper"]])) $params["wrapper"] = "default";
    if ($params["wrapper"] == "menu") 
    {
      $this->rh->tpl->Assign("Action:WrapperAlign", $params["wrapper_align"]=="left"?"left":"right");
    }
    return $this->rh->tpl->Parse( "actions/wrappers.html:wrapper_".$w[$params["wrapper"]] );
  }

  function Forbidden( $message_code="Common", $message_set = "forbidden_common" )
  { 
    $this->rh->tpl->MergeMessageSet( $this->rh->message_set."_".$message_set );
    $this->rh->tpl->Assign( "Preparsed:TITLE", $this->rh->tpl->message_set["Forbidden"] );
    $this->rh->tpl->Assign( "Message", $this->rh->tpl->message_set["Forbidden.".$message_code] );
    $this->rh->tpl->Assign( "MessageCode", $message_code );
    $this->_UsageState( &$principal, "forbidden", "", $message_code );
    return $this->IncludeBuffered( &$this->$rh->principal, $this->rh->handlers_dir.$this->class, "_forbidden" ); 
  }
  function NotFound( $message_code="Common", $message_set = "404_common" )
  { 
    $this->rh->tpl->MergeMessageSet( $this->rh->message_set."_".$message_set );
    $this->rh->tpl->Assign( "Preparsed:TITLE", $this->rh->tpl->message_set["404"] );
    $this->rh->tpl->Assign( "Message", $this->rh->tpl->message_set["404.".$message_code] );
    $this->_UsageState( &$principal, "404", "", $message_code );
    return $this->IncludeBuffered( &$this->$rh->principal, $this->rh->handlers_dir.$this->class, "_404" ); 
  }
  function Save() 
  { return $this->IncludeBuffered( &$this->rh->principal, $this->rh->handlers_dir.$this->class, "_save" );  }

  // ------------------- ������ �������� -----------------------

  // ��������, � ��������������� ��������� �� ����
  function &Load( $cache_level=2 ) 
  { 
    $addr = $this->npj_object_address;
/* deprecated by kukutz @ 07082003 !!!! check that $this->npj_object_address correct for comments & versions
    if ($this->subspace_name) 
    { $addr.= $this->subspace_name;
      if ($this->name) $addr.= "/".$this->name;
    }
*/
    $data = &$this->_Load( $addr, $cache_level, $this->class );
    if ($data && ($this->cache_level < $cache_level)) $this->cache_level = $cache_level;
    $this->data = &$data;
    return $this->data;
  }
  // ���������� 

  // �������� ��� ������������� ���������� �������
  function &_Load( $abs_npj_address, $cache_level=2, $cache_class=NULL, $no_cache=false )
  {
    $debug     = &$this->rh->debug;
    if ($cache_class == NULL) $cache_class=$this->class;

    if ($this->rh->community_filter)
    {
      $by_pos = strpos( $abs_npj_address, "/by/" );
      if ($by_pos) 
        $abs_npj_address = substr( $abs_npj_address, $by_pos+4 );
      if (substr( $abs_npj_address, 0, 3) == "in/")
        $abs_npj_address = substr( $abs_npj_address, 3 );
       
    }

    // subject to change
    // NB: � ��� ������ ������������ � ���-�� ���������������, ��� � ������� ��������
    $data = $this->rh->cache->Restore( "npj", $abs_npj_address, $no_cache?CACHE_LEVEL_NEVER:$cache_level );
    if ($data === false)
    {
       // third party module call
       if ($this->rh->modules && isset($this->rh->modules[$cache_class]))
         $data = $this->PassToModule( $cache_class, "load",
                                      array( "abs_npj_address"=>$abs_npj_address, 
                                             "cache_level"    =>$cache_level,
                                             "cache_class"    =>$cache_class,
                                             "no_cache"       =>$no_cache ),
                                      $this->rh->principal );
       else
       {
          $state     = &$this->rh->state;
          $rh        = &$this->rh;
          $cache     = &$this->rh->cache;
          $tpl       = &$this->rh->tpl;
          $db        = &$this->rh->db;
          $debug     = &$this->rh->debug;
          $object    = &$this;

          $__fullfilename = $this->rh->handlers_dir.$cache_class."/_load.php";
          $this->rh->debug->Trace("Launching handler: ".$__fullfilename);
          if (!file_exists($__fullfilename)) 
            $this->rh->debug->Error("Unknown method handler!", 3);

         ob_start();
         $data = include($__fullfilename);
         if ($data===false) $this->rh->debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
         if (!$data) $data = ob_get_contents(); 
         ob_end_clean();
       }


      if (!$data) 
      { $this->rh->debug->Trace( "NpjObject: Load -- object not found exception "); $data=NOT_EXIST; $cache_level=0; }

      $this->rh->cache->Store( "npj", $abs_npj_address, $no_cache?CACHE_LEVEL_NEVER:$cache_level, &$data );
      if (is_array($data))
        $this->rh->cache->Store( $cache_class, $data["id"], $no_cache?CACHE_LEVEL_NEVER:$cache_level, &$data );
      else $debug->Trace("Caching (npj=$abs_npj_address) as <b>wanted</b>");
      $debug->Trace("Caching (npj=$abs_npj_address) [$cache_level] $cache_class, ".$data["id"]);
      //$debug->Trace_R( $data );
      //$cache->Debug();
    }

    // always store to "id" (workaround of so-called "jfyi bug"
    // commented yet. 16122004
    // $this->rh->cache->Store( $cache_class, $data["id"], $no_cache?CACHE_LEVEL_NEVER:$cache_level, &$data );

    return $data;
  }

  function &_LoadById( $id, $cache_level=2, $cache_class=NULL, $no_cache=false )
  {
    $debug     = &$this->rh->debug;
    if (!$cache_class) $cache_class=$this->class;
    $data = $this->rh->cache->Restore( $cache_class, $id, $no_cache?CACHE_LEVEL_NEVER:$cache_level );
    if ($data === false)
    {
       // third party module call
       if ($this->rh->modules && isset($this->rh->modules[$cache_class]))
         $data = $this->PassToModule( $cache_class, "load_by_id",
                                      array( "id"             =>$id, 
                                             "cache_level"    =>$cache_level,
                                             "cache_class"    =>$cache_class,
                                             "no_cache"       =>$no_cache ),
                                      $this->rh->principal );
       else
       {
         $state     = &$this->rh->state;
         $rh        = &$this->rh;
         $cache     = &$this->rh->cache;
         $tpl       = &$this->rh->tpl;
         $db        = &$this->rh->db;
         $debug     = &$this->rh->debug;
         $object    = &$this;

         $__fullfilename = $this->rh->handlers_dir.$cache_class."/_load_by_id.php";
         $this->rh->debug->Trace("Launching handler: ".$__fullfilename);
         if (!file_exists($__fullfilename)) 
           $this->rh->debug->Error("Unknown method handler!", 3);

        ob_start();
        $data = include($__fullfilename);
        if ($data===false) $this->rh->debug->Error("Problems (file: ".__FILE__.", line: ".__LINE__."): ".ob_get_contents());
        if (!$data) $data = ob_get_contents(); 
        ob_end_clean();
      }

      if (!$data) 
      { $this->rh->debug->Trace( "NpjObject: Load by ID -- object not found exception "); $data=NOT_EXIST; }

      //kuso: ??? ��� �������� �� id ���� ������� ������� ������ ���������� npj-address. ����.
      $this->rh->cache->Store( $cache_class, $data["id"], $no_cache?CACHE_LEVEL_NEVER:$cache_level, &$data );
    }
    return $data;
  }
  
  
  // ----------------------------------------- ����������� � ��������� --------------------------

  function GetFullTag( $tag = NULL, $supertag = NULL)
  {

    if (($supertag === NULL) && !isset($this->data["supertag"])) $this->Load(2);
    if ($tag === NULL) $tag = $this->data["tag"];
    if ($supertag === NULL) $supertag = $this->data["supertag"];
    $pos = strpos($supertag, ":");
    return (($pos == false)?($supertag.":"):(substr($supertag, 0, $pos+1).$tag));
  }

  function _UnwrapNpjAddress( $rel_npj_address, $unrecoverable=NPJ_UNRECOVERABLE )
  {
    // 0000. ���� ��� ���� ���������-����, �� ����� ����.
    $rel_npj_address = str_replace(":/", ":", $rel_npj_address);

    // 000. ���� ��� ���� ��� ���������, �� ������� ������.
    $rel_npj_address = str_replace("::", "@:", $rel_npj_address);

    // 00. ���� ��� ���� @:, �� ������� ����.
    // kuso: suspicious use of $rh->node_name instead of $this->npj_node
    $rel_npj_address = str_replace("@:", "@".$this->rh->node_name.":", $rel_npj_address);

    // 0. ���� ��� ���� ���������, �� ��� ������ ������ ������.
    if (strpos( $rel_npj_address, ":") !== false) return $this->NpjTranslit($rel_npj_address, $unrecoverable);

    // 1. ����� ��������� �� @
    if ($rel_npj_address[ strlen($rel_npj_address)-1 ] == "@")
    // kuso: suspicious use of $rh->node_name instead of $this->npj_node
    { return $this->NpjTranslit($rel_npj_address.$this->rh->node_name, $unrecoverable); }
    // 1a. ���� ������ ���� @, �� ���� ������ ������.
    if (strpos( $rel_npj_address, "@") !== false) return $this->NpjTranslit($rel_npj_address, $unrecoverable);
    
    // 2. ����� ���������� �� �����
    if ($rel_npj_address[ 0 ] === "/")
    { return $this->NpjTranslit($this->npj_account.":".substr($rel_npj_address,1), $unrecoverable); }
    // 2a. ����� ���������� � �����
    if (is_numeric($rel_npj_address[ 0 ]))
    { return $this->NpjTranslit($this->npj_account.":".$rel_npj_address, $unrecoverable); }

    // 3. ����� ���������� � ../
    if (strpos($rel_npj_address, "../") === 0)
    { 
      $pos = strrpos( $this->npj_context, "/" );
      if ($pos !== false) $parent = substr($this->npj_context, 0, $pos )."/";
      else $parent = "";
      return $this->NpjTranslit($this->npj_account.":".$parent.substr($rel_npj_address,3), $unrecoverable); 
    }

    // 4. ����� ���������� � !/
    if (strpos($rel_npj_address, "!/") === 0)
    { 
      if ($this->name == "") return $this->NpjTranslit($this->npj_object_address.substr($rel_npj_address,2), $unrecoverable); 
      else                   return $this->NpjTranslit($this->npj_object_address.substr($rel_npj_address,1), $unrecoverable); 
    }


    // 5. ����� � ��������� � ��� �� ���������, ���
    return $this->NpjTranslit($this->npj_account.":".($this->npj_context?$this->npj_context."/":"").$rel_npj_address, $unrecoverable);
  }

  // ����������� ����������! ���-������ � ���
  function _NpjAddressToUrl( $npj_address, $is_rel=0)
  {
    if (is_array($this->rh->account->data))
     $p_options = &$this->rh->account->data["advanced_options"];
    else
     $p_options = array();

    // todo: !!! ��������� ������ �� ������� � ��������������

    // ��� ������������� ������ ������������� � � ����������. ��� ��������������
    if ($is_rel) $npj_address = $this->_UnwrapNpjAddress( $npj_address );
    $this->rh->debug->Trace( $npj_address );

    $_npj_address = $npj_address;

    //   1 - login   2 - foreign  3 - node  5 - address, 6 - is post
    preg_match( "/^(.*?)@([^:\/]*\/)?([^\/:]*)(\:\/?(([0-9]*)(.*)))?$/", $npj_address, $match );
                 //login@outernode/localnode:stuff
                 //11111 2222222222333333333444444
                 //                          55555
    // Community filtering
    if ($this->rh->community_filter)
    {
      $filter = "";
      $login  = $match[1];
      $no_filter = false;
      // ��������� ������� �� �������� ������� (����� �� ��������� ���?)
      $filter = $this->rh->object->npj_filter;
      // ������������� ��������� �������
      if (substr($login,0,3) == "in/")
      {
        $login_parts = explode( "/", $login );
        if ($login_parts[2] == "by" && $match[6]) 
        { 
          $_npj_address_temp = $_npj_address;
          $_npj_address = substr( $_npj_address, 3+strlen($filter)+4 ); // trim "/in/filter/by/"
          $_temp = &$this->rh->cache->Restore( "npj", $_npj_address );
          if (!is_array($_temp))
          {
            $_npj_address = $_npj_address_temp;
            $no_filter = true; // �� ����� ����������, ����� ������� �������� ��� ������� -- ���������� ��� ����
          }
          else
          {
          $login  = $login_parts[3];
          $filter = $login_parts[1]; // ���������� ������
          }
        }
        else
        {
          $login = $login_parts[1];
          $no_filter = true; // ����������� �������� "�� ��������� ������"
          $_npj_address = substr( $_npj_address, 3 ); // trim "in/"
        }
      }
      // ������������� �������
      if (!$no_filter)
      {
        $_filter = $filter;
        $d = &$this->rh->cache->Restore( "npj", $_npj_address );
        if (is_array($d) && ($d["type"] == RECORD_POST) && $d["filter"] && $match[6])
        {
          //$this->rh->debug->Trace_R( $d );
          //$this->rh->debug->Trace( $_npj_address. " == ". $login."=".$filter );
          $f = explode( ",", $d["filter"]);
          $_f = array_flip($f);
          if ($filter && isset($_f[$filter])) ; // filter is ok!
          else
          {
            $filter = $f[0];
            if (sizeof($f) > 1) // ���� � ��� ���� �����, ������� $rh->object->npj_filter
              if ($this->rh->object) 
                if ($this->rh->object->npj_filter)
                  if (isset($_f[$this->rh->object->npj_filter]))
                    $filter = $this->rh->object->npj_filter;
                  else;
                else
                  if (isset($_f[$this->rh->account->data["login"]]) &&
                      ($this->rh->account->data["node_id"]==$this->rh->node_name))
                    $filter = $this->rh->account->data["login"];
          }
        } else 
          if (is_array($d) || !$match[6]) $filter=""; // ���� ��������� �� �������, ������ � �������� ���� �� ������
      }
      else $filter = "";

      if ($filter) $match[1] = "in/".$filter."/by/".$login;
      else         $match[1] = $login;
    }
    // --
    
    // ���������� ����������� �������������
    $d = &$this->rh->cache->Restore( "npj", $npj_address );
    if (is_array($d) && ($d["type"] == RECORD_DOCUMENT))
    {
      $new_tag = $this->AddSpaces( $d["tag"], "_", "dont obsolete");
      $new_tag = $this->NpjTranslit($new_tag, NPJ_UNRECOVERABLE, NPJ_DECORATIVE);
      if ($new_tag != "") $match[5] = $new_tag; // replace address
    }
    if ($p_options["post_supertag"]) 
    {
      if (is_array($d) && ($d["type"] == RECORD_POST) && (!$d["_post_supertag_cancel"]))
      {
        if (trim($d["subject"]) != "")
        {
          $subj = $this->rh->tpl->Format( $d["subject"], "translit" );
          if ($subj != "")
          {
            if (strlen($subj) > 50)
            { $subj = substr($subj,0,50);
              $rp = strrpos($subj, "_");
              if ($rp) $subj = substr($subj,0,$rp);
            }
          }
          if ($subj != "") $match[5].="_".$subj; // add to address
        }
      }
    }
    $dt_prefix = "";
    if ($p_options["post_date"]) 
    {
      if (is_array($d) && ($d["type"] == RECORD_POST) && (!$d["_post_date_cancel"]))
      {
        $dt = substr($d["user_datetime"], 0, strpos( $d["user_datetime"], " ") );
        $dts = explode("-", $dt);
        $dt_prefix = implode("/",$dts)."/";
      }
    }
    

    // ��� ������ "�����-�����" -- �������
    if ($this->rh->NPJ_QUASI_NODES[$match[3]])
    {
      $match[2] = $match[3]."/";
      $match[3] = $this->rh->node_name;
    }
    // --

    if ($match[2] != "")
     if ($match[2] != $this->rh->node_name) 
       $match[1] = "foreign/".$match[2].$match[1];
                                                  
    //foreign node
    if ($match[3] != $this->rh->node_name) 
    {
      if (!$this->nodeobject) {
        $this->nodeobject = &new NpjObject( &$this->rh, "show@".$match[3]);
      }
      $nodedata =& $this->nodeobject->_Load("show@".$match[3], 1);

      $match[1] = $nodedata["url"].$match[1];
    }
    //absurl
    else if ($this->rh->absolute_urls) $match[1] = trim($this->rh->tpl->GetValue("Host"),"/")."/".$match[1];
    //else
    else if (!$this->rh->ignore_domain_type && (!$match[2] || $match[3] != $this->rh->node_name))
    {
    /*
     ������� ������ != �������� - ���� �� domain_type �������� ������ 
       DOMAIN_NONE        
       DOMAIN_DIR_ONLY    
       DOMAIN_DIR         
       �� ���

       DOMAIN_DOMAIN_ONLY 
       DOMAIN_DOMAIN      
       �� ������

      ���� �� �� ������ �����, �������� ����������

     ������� ������ == �������� - ���� �� domain_type �������� ������ � ������ ������ ���������������
      DOMAIN_NONE        
      DOMAIN_DIR_ONLY    
      �� ��� ����������
      ���� �� �� ������ �����, �������� ����������

      DOMAIN_DOMAIN_ONLY 
      �� ������ ����������

      DOMAIN_DIR         
      DOMAIN_DOMAIN      
      ���������� ��� ����
    */
      $account =& new NpjObject(&$this->rh, $match[1]."@".$match[3]);
      $_data =& $account->Load(1);

      if (!is_array($_data)) 
      { 
        $_data = array();
        $_data["domain_type"] = DOMAIN_DIR_ONLY;
      }

      list($acc, $tmp) = explode( "@", $this->rh->object->npj_account);

      if ($match[1] != $acc) 
      {
        if (($_data["domain_type"] == DOMAIN_DOMAIN) || ($_data["domain_type"] == DOMAIN_DOMAIN_ONLY))
        {
          $match[1] = trim($this->rh->scheme."://".$match[1].".".preg_replace("/:.*$/","",$this->rh->base_domain)."/".$this->base_url, "/");
        }
        else if ($this->rh->current_domain!=$this->rh->base_domain) //�� �� ������ �����
        {
          $match[1] = trim($this->rh->scheme."://".preg_replace("/:.*$/","",$this->rh->base_domain)."/".$this->base_url,"/")."/".$match[1];
        }
      }
      else
      {
        if ($_data["domain_type"] == DOMAIN_DOMAIN_ONLY)
        {
          $match[1] = trim($this->rh->scheme."://".$match[1].".".preg_replace("/:.*$/","",$this->rh->base_domain)."/".$this->base_url, "/");
        }
        else if (($_data["domain_type"] == DOMAIN_DOMAIN) || ($_data["domain_type"] == DOMAIN_DIR))
        {
          if ($this->rh->current_domain!=$this->rh->base_domain) //�� �� ������ �����
          {
            $match[1] = trim($this->rh->scheme."://".$match[1].".".preg_replace("/:.*$/","",$this->rh->base_domain)."/".$this->base_url, "/");
          }
        }
        else if ($this->rh->current_domain!=$this->rh->base_domain) //�� �� ������ �����
        {
          $match[1] = trim($this->rh->scheme."://".preg_replace("/:.*$/","",$this->rh->base_domain)."/".$this->base_url,"/")."/".$match[1];
        }
      }

    }
    
    if ($match[5] == "") return $match[1];

    if ($this->rh->single_account) return $dt_prefix.$match[5];
    return $match[1]."/".$dt_prefix.$match[5];
  }

  // ����������� ����������! ���� � ���-�����. ������� ��������� ����-�� ������
  function _UrlToNpjAddress( $url, $node=NULL )                      
  {
    $this->rh->debug->Milestone( "NpjObject->Url-2-Addr ($url) started" );
    // Single account scenario
    if ($this->rh->single_account)
     $url = $this->rh->single_account."/".$url;

    // UTF & BiDiTranslit decode
    if (!preg_match("/^[".ALPHANUM_P."\!@:]+$/", $url))
     if (preg_match("/^[".ALPHANUM_P."\!@:]+$/", $t1=$this->utf_decode($url)))
      $url = $t1;
    if (stristr($url, "/add/")) $url = $this->Detranslit(str_replace(" ", "+", $url));

    if ($node === NULL) $node = $this->rh->node_name;
    $_node = $node;

    // Domain to URL
    $this->rh->in_domain = "";
    $domain = strtolower($_SERVER["HTTP_HOST"]);
    if (!$this->rh->ignore_domain_type)
    if (($posi = strpos($domain, ".".$_SERVER["SERVER_NAME"]))!==false)
    {
      $match = substr($domain, 0, $posi);
      if ($match != "www")
      {
        $this->rh->in_domain = $match;

        //check for enabled domain_type
        $account = &new NpjObject( &$this->rh, $match."@".$node );
        $_data = & $account->Load( 1 );

        if (!is_array($_data))
        { $_data = array();
          $_data["domain_type"] = DOMAIN_DIR_ONLY;
        }

        if ($_data["domain_type"] == DOMAIN_DIR_ONLY)
        {
          //redirect to dir
          $this->rh->Redirect( $this->rh->node->data["url"].$match."/".$url );
        }
        else if ($_data["domain_type"] != DOMAIN_NONE)
          //preprocess URL
          $url = $match."/".$url;
      }
    }

    if ($url[ strlen($url)-1 ] != "/") $url.="/"; // ??? �������� � �.�. ������

    // 1. ���� �� ����� ����? (foreign)
    $foreign_pos = strpos( $url, "foreign/" );
    if ($foreign_pos === false) ; // home node
    else
      if ($foreign_pos === 0)
    { preg_match( "/^foreign\/([^\/]*)\/(.*)$/i", $url, $match );
      $node = $match[1]."/".$node;
      $url = $match[2];
      } else
        if ($this->rh->community_filter)
        {
          preg_match( "/^(.*?)\/foreign\/([^\/]*)\/(.*)$/i", $url, $match );
          $node = $match[2]."/".$node;
          $url = $match[1]."/".$match[3];
    }

    //$this->rh->debug->Trace( $url );
    // 2. ���� �� �����?
    $in_by_prefix = "";
    if ($url != "")
    {

      // 1. ���� �� ������?
      if ($this->rh->community_filter)
      {
        $by_pos = strpos($url, "/by/");
        if ($by_pos !== false)
        {
           $in_by_prefix = substr( $url, 0, $by_pos+4 );
           $url          = substr( $url, $by_pos+4 );
        }
      }

      $slash = strpos($url, "/");
      if ($slash === false) { $addr = $url."@".$node; }
      else 
      {
        $addr = substr($url, 0, $slash)."@".$node;
        $url = substr($url, $slash+1);
        // 3. ���� �� ���-�� ���?
        if ($url != "") $addr.=":".$url;
      }
    } else $addr = "@".$node;

    $addr = $in_by_prefix.$addr;
    //$this->rh->debug->Error( $addr );
    $this->rh->debug->Milestone( "NpjObject->Url-2-Addr done" );
    return rtrim($addr,"/");
  }

  function _Trace( $what )
  {
$what.=" <b><a href=# onclick='var a=document.getElementById(\"__tracediv".md5($what)."\");a.style.display=(a.style.display==\"none\"?\"block\":\"none\"); return false;'>���-������</a></b><div style='display:none' id='__tracediv".md5($what)."'><table style='margin-left:57px' border=1 cellspacing=0 cellpadding=5 class=_NpjObjectTrace>".      "<tr><td>name:</td><td>".$this->name."&nbsp;</td></tr>".
      "<tr><td>tag:</td><td>".$this->tag."&nbsp;</td></tr>".
      "<tr><td>class:</td><td>".$this->class."&nbsp;</td></tr>".
      "<tr><td>method:</td><td>".$this->method."&nbsp;</td></tr>".
      "<tr><td>params:</td><td>".implode(", ",$this->params)."&nbsp;</td></tr>".
      "<tr><td>subspace:</td><td>".$this->subspace."&nbsp;</td></tr>".
      "<tr><td>subspace_name:</td><td>".$this->subspace_name."&nbsp;</td></tr>".
      "<tr><td>npj_address:</td><td>".$this->npj_address."&nbsp;</td></tr>".
      "<tr><td>npj_object_address: </td><td>".$this->npj_object_address ."&nbsp;</td></tr>".
      "<tr><td>npj_context:</td><td>".$this->npj_context."&nbsp;</td></tr>".
      "<tr><td>npj_account:</td><td>".$this->npj_account."&nbsp;</td></tr>".
      "<tr><td>npj_node:</td><td>".$this->npj_node."&nbsp;</td></tr>".
      "<tr><td>npj_filter:</td><td>".$this->npj_filter."&nbsp;</td></tr>".
      "</table></div>";
    $this->rh->debug->Trace( $what );
  }

  function Detranslit( $tag )
  {
    $tag=$this->Translit($tag, 1);
    return $tag;
  }

  //!!!! ����������.
  function utf_decode($string) {
  //������� ������������� ��� strtr()
  $tran = array(
   "%D0%81"=>"%A8",
   "%D1%91"=>"%B8",
   "%D0%90"=>"%C0",
   "%D0%91"=>"%C1",
   "%D0%92"=>"%C2",
   "%D0%93"=>"%C3",
   "%D0%94"=>"%C4",
   "%D0%95"=>"%C5",
   "%D0%96"=>"%C6",
   "%D0%97"=>"%C7",
   "%D0%98"=>"%C8",
   "%D0%99"=>"%C9",
   "%D0%9A"=>"%CA",
   "%D0%9B"=>"%CB",
   "%D0%9C"=>"%CC",
   "%D0%9D"=>"%CD",
   "%D0%9E"=>"%CE",
   "%D0%9F"=>"%CF",
   "%D0%A0"=>"%D0",
   "%D0%A1"=>"%D1",
   "%D0%A2"=>"%D2",
   "%D0%A3"=>"%D3",
   "%D0%A4"=>"%D4",
   "%D0%A5"=>"%D5",
   "%D0%A6"=>"%D6",
   "%D0%A7"=>"%D7",
   "%D0%A8"=>"%D8",
   "%D0%A9"=>"%D9",
   "%D0%AA"=>"%DA",
   "%D0%AB"=>"%DB",
   "%D0%AC"=>"%DC",
   "%D0%AD"=>"%DD",
   "%D0%AE"=>"%DE",
   "%D0%AF"=>"%DF",
   "%D0%B0"=>"%E0",
   "%D0%B1"=>"%E1",
   "%D0%B2"=>"%E2",
   "%D0%B3"=>"%E3",
   "%D0%B4"=>"%E4",
   "%D0%B5"=>"%E5",
   "%D0%B6"=>"%E6",
   "%D0%B7"=>"%E7",
   "%D0%B8"=>"%E8",
   "%D0%B9"=>"%E9",
   "%D0%BA"=>"%EA",
   "%D0%BB"=>"%EB",
   "%D0%BC"=>"%EC",
   "%D0%BD"=>"%ED",
   "%D0%BE"=>"%EE",
   "%D0%BF"=>"%EF",
   "%D1%80"=>"%F0",
   "%D1%81"=>"%F1",
   "%D1%82"=>"%F2",
   "%D1%83"=>"%F3",
   "%D1%84"=>"%F4",
   "%D1%85"=>"%F5",
   "%D1%86"=>"%F6",
   "%D1%87"=>"%F7",
   "%D1%88"=>"%F8",
   "%D1%89"=>"%F9",
   "%D1%8A"=>"%FA",
   "%D1%8B"=>"%FB",
   "%D1%8C"=>"%FC",
   "%D1%8D"=>"%FD",
   "%D1%8E"=>"%FE",
   "%D1%8F"=>"%FF",
   );
     $string = strtr(urlencode($string),$tran);
     $string = urldecode($string);
  return $string;
  }
  var $Tran = array (
   "�" => "A",  "�" => "B",  "�" => "V",  "�" => "G",  "�" => "D",  "�" => "E",  "�" => "JO",  "�" => "ZH",  "�" => "Z",  "�" => "I",
   "�" => "JJ", "�" => "K",  "�" => "L",  "�" => "M",  "�" => "N",  "�" => "O",  "�" => "P",   "�" => "R",   "�" => "S",  "�" => "T",
   "�" => "U",  "�" => "F",  "�" => "KH",  "�" => "C",  "�" => "CH", "�" => "SH", "�" => "SHH", "�" => "~",   "�" => "Y",  "�" => "_",
   "�" => "EH", "�" => "JU", "�" => "JA", "�" => "a",  "�" => "b",  "�" => "v",  "�" => "g",   "�" => "d",   "�" => "e",  "�" => "jo",
   "�" => "zh", "�" => "z",  "�" => "i",  "�" => "jj", "�" => "k",  "�" => "l",  "�" => "m",   "�" => "n",   "�" => "o",  "�" => "p",
   "�" => "r",  "�" => "s",  "�" => "t",  "�" => "u",  "�" => "f",  "�" => "kh",  "�" => "c",   "�" => "ch",  "�" => "sh", "�" => "shh",
   "�" => "~",  "�" => "y",  "�" => "'",  "�" => "eh", "�" => "ju", "�" => "ja", );
  var $DeTran = array (
   "A"    => "�",   "B"    => "�",  "V"    => "�",  "G"    => "�",  "D"    => "�",  "E"    => "�",  "JO"   => "�",  "ZH"   => "�",
   "Z"    => "�",   "I"    => "�",  "JJ"   => "�",  "K"    => "�",  "L"    => "�",  "M"    => "�",  "N"    => "�",  "O"    => "�",
   "P"    => "�",   "R"    => "�",  "S"    => "�",  "T"    => "�",  "U"    => "�",  "F"    => "�",  "KH"    => "�",  "C"    => "�",
   "CH"   => "�",   "SHH"  => "�",  "SH"   => "�",  "Y"    => "�",  "EH"   => "�",  "JU"   => "�",  "_"=>"�",
   "JA"   => "�",   "a"    => "�",  "b"    => "�",  "v"    => "�",  "g"    => "�",  "d"    => "�",  "e"    => "�",  "jo"   => "�",
   "zh"   => "�",   "z"    => "�",  "i"    => "�",  "jj"   => "�",  "k"    => "�",  "l"    => "�",  "m"    => "�",  "n"    => "�",
   "o"    => "�",   "p"    => "�",  "r"    => "�",  "s"    => "�",  "t"    => "�",  "u"    => "�",  "f"    => "�",  "kh"    => "�",
   "c"    => "�",   "ch"   => "�",  "shh"  => "�",  "sh"   => "�",  "~"    => "�",  "y"    => "�",  "'"    => "�",  "eh"   => "�",
   "ju"   => "�",   "ja"   => "�",  );            
  function Translit($tag, $direction=0) {
   $tag = str_replace( "//", "/", $tag );
   if ($direction==1) {
    $pgs = explode("/", $tag);
    for ($j=0;$j<count($pgs);$j++) {
      $tags = explode("+", $pgs[$j]);
      for ($i=1;$i<count($tags);$i=$i+2)
        $tags[$i] = strtr($tags[$i], $this->DeTran);
      $pgs[$j] = implode("", $tags);
    }
    $tag = implode("/", $pgs);
   } else {
    $russians = preg_split('/[0-9A-Za-z\_\-\.\/\']+/', $tag, -1, PREG_SPLIT_NO_EMPTY);//\xc0-\xff
    for ($i=0;$i<count($russians);$i++)
      $russians[$i] = strtr($russians[$i], $this->Tran);
    $others = preg_split('/[\xc0-\xff\xa8\xb8]+/', $tag, -1, PREG_SPLIT_NO_EMPTY); 
    if (preg_match('/[\xc0-\xff\xa8\xb8]/', $tag[0])) {      
      $fr="russians";
      $sr="others";
      $tag = "+";
    } else { 
      $fr="others";
      $sr="russians";
      $tag = "";
    }
    for ($i=0;$i<min(count($$fr),count($$sr));$i++)
     $tag.=${$fr}[$i]."+".${$sr}[$i]."+";
    if (count($$fr)>count($$sr))
      $tag.=${$fr}[count($$fr)-1];
    else
      $tag=substr($tag,0,strlen($tag)-1);
   }
   return rtrim($tag, "/");
  }

  // ---------------------------------------------------------------------
  // ���������, �� �������� �� ��� ���� ��������� ������, ��� �� �������� ��.
  function validate_reserved_words( $data, $rh )
  {
    $_data = $rh->object->NpjTranslit( $data );
    $_data = "/".$_data."/";
    if (preg_match( $rh->REGEX_NPJ_FUNCTIONS, $_data, $match ))
    {
      return "� �������� ����� ������ �� ������������ ����������������� �����, �� ������� ���"; 
      /// !!! to messageset, function found
    }
    if (preg_match( $rh->REGEX_NPJ_SPACES, $_data, $match ))
    {
      return "� �������� ����� ������ �� ������������ ����������������� �����, �� ������� ���"; 
      /// !!! to messageset, subspace found
    }
    if (preg_match( "/^\/[0-9]+/", $_data, $match ))
    {
      return "������ ��������� ���������, ��� ������� ������� �� ���� ��� ���������� �� ���"; 
      /// !!! to messageset, begins with 0-9
    }
    return 0;
  }


  // ---------------------------------------------------------------------
  // �������� ������ � ���������������, ������ � "�������", ���� �����
  // NB: ������� �������� ������ � ����������� ��������
  function RipMethods( &$npj_address, $always_record = RIP_WEAK )
  {
      $_npj_address = $npj_address;
      // �������� ������ �����.
      $stag = $npj_address;
      // 1. registration@npj -> node@npj:
      if (preg_match( "/^".$this->NPJ_FUNCTIONS."@([^:]*):?(.*)$/i", $stag, $match ))
      {
        $match_size = sizeof($match);
        $q = explode("@", $this->rh->node_user);
        if ($match[$match_size-2]) $node = $match[$match_size-2];
        else $node = $q[1];
        $stag = $q[0]."@".$node.":";
        $npj_address = trim($q[0]."@".$node.":".$match[$match_size-3]."/".$match[$match_size-1],"/");
//        $this->rh->debug->Error($npj_address);
      }
      // 2. kuso@npj:friends -> kuso@npj:
      if (preg_match( "/^(.*?):(|.*?\/)".$this->NPJ_SPACES."(\/.*|)$/i", $stag, $match ))
        $stag = trim($match[1].":".$match[2],"/");

      // 2a. kuso@npj:MyPage/edit -> kuso@npj:MyPage
      if (preg_match( "/^(.*?):(|.*?\/)".$this->NPJ_FUNCTIONS."(\/.*|)$/i", $stag, $match ))
        $stag = trim($match[1].":".$match[2],"/");

      if ($always_record && (strpos($stag, ":") === false))
       return $stag.":";
   
     return $stag;
  }

  // ---------------------------------------------------------------------
  // ��������� ������, ���� �����
  function &SpawnHelper( $weak = HELPER_ALWAYS )
  {
    if (($weak == HELPER_WEAK) && isset($this->helper)) return $this->helper;
    
    // ����� ���� �������
    $this->rh->UseClass("HelperAbstract");
    $this->rh->UseClass("HelperRecord");
    $this->rh->UseClass("HelperPost");
      $this->rh->UseClass("HelperEvent");
       $this->rh->UseClass("HelperAnnounce");
    $this->rh->UseClass("HelperDocument");
      $this->rh->UseClass("HelperDigest");
        $this->rh->UseClass("HelperDigestForm");
 
    // $this->owner
    if (!isset($this->owner))
    {
      $this->owner = &new NpjObject( &$this->rh, $this->npj_account );
      $this->owner->Load(2);
    }

    $this->helper = false;
    if ($this->module_instance) $this->helper = &$this->module_instance->SpawnHelper( &$this );
    if ($this->helper == false) $this->helper = &$this->_SpawnHelper();
    return $this->helper;
  }
  function &_SpawnHelper()
  {
    // ��� HelperPost, ��� HelperDocument
    $type = $this->GetType();
    if ($type == RECORD_MESSAGE)  
    {
      if ($this->data["is_announce"] == 2)
        $helper = &new HelperAnnounce( &$this->rh, &$this );
      else
      if ($this->data["is_announce"] == 1)
        $helper = &new HelperEvent( &$this->rh, &$this );
      else
        $helper = &new HelperPost( &$this->rh, &$this );
    }
    else
    if ($type == RECORD_DOCUMENT) 
    {
      if ($this->data["is_digest"] == 2)
        $helper = &new HelperDigestForm( &$this->rh, &$this );
      else
      if ($this->data["is_digest"] == 1)
        $helper = &new HelperDigest( &$this->rh, &$this );
      else
        $helper = &new HelperDocument( &$this->rh, &$this );
    }
    else $helper = &new HelperAbstract( &$this->rh, &$this );

    $this->rh->debug->Trace("Helper spawned");

    return $helper;
  }

  // ---------------------------------------------------------------------
  // ��������� ������, ������� ���� � �������� �� � �������:
  //  a. "maxrank_".$principal_id, $object_id = maxrank
  //    * ��� ����� "rank >= XX" 
  //  b. "ingroups_".$principal_id, $object_id = array[ group_id => &$group ] 
  //    * ��� ����� "������������ ���� � �����-�� ������� ������-�� �������"
  //  c. "groups", strtolower($group_name) = &$group { "group_id", "group_rank" }
  //    * ��� ����� ������ ������ �� �����
  // <?
  function CacheGroups( &$principal, $id=NULL )
  { $db = &$this->rh->db; $debug = &$this->rh->debug;
    $debug->Milestone( "CacheGroups, started");

    if ($id === NULL) $id = $this->data["id"];

    // phase 0. eliminante recaching
    if ($this->rh->cache->Restore( "maxrank_". $principal->data["user_id"], $id, 1 ) !== false)
     return; 
    // phase 1. get all groups
    //$debug->Milestone( "CacheGroups, phase 0 done");
    $rs = $db->Execute( "select group_id, group_name, user_id, group_rank from ".
                        $this->rh->db_prefix."groups where user_id=".$db->Quote($id).
                        " and is_system < 2 order by pos" );
    //$debug->Milestone( "CacheGroups, sql1 done");
    $a = $rs->GetArray();
    $group_ids = array();
    $all_groups = array();
    $all_groups_id = array();
    //$debug->Milestone( "CacheGroups, before foreach");
    foreach( $a as $k=>$group )
    {
      $group_ids[] = $group["group_id"]; 
      $all_groups   [ $group["group_name"] ] = &$a[$k];
      $all_groups_id[ $group["group_id"] ] = $group["group_name"];
    }
    //$debug->Milestone( "CacheGroups, before implode");
    if (sizeof($group_ids) == 0) return;
    $group_ids = implode(", ",$group_ids);
    //$debug->Milestone( "CacheGroups, phase 1 done (principal:".$principal->data["user_id"].")");
    // phase 2. get all "user_groups" for al groups & selected principal
    $sql = "select group_id, user_id from ".
                        $this->rh->db_prefix."user_groups where group_id in (".$group_ids.") and ".
                        "user_id = ".$db->Quote($principal->data["user_id"]);
    $rs = $db->Execute( $sql );
    //$debug->Milestone( "CacheGroups, sql 2 done");
    $a = $rs->GetArray();
    $groups = array();
    $maxrank = -1;
    foreach( $a as $v )
    {
      $groups[ $v["group_id"] ] = &$all_groups[ $all_groups_id[ $v["group_id"] ] ];
      /*
      $debug->Trace("group (".$v["group_id"].") - ".$all_groups_id[ $v["group_id"] ].", maxrank=".
                    $groups[ $v["group_id"] ]["group_rank"]) ;
      */
      if ($maxrank < $groups[ $v["group_id"] ]["group_rank"])
       $maxrank = $groups[ $v["group_id"] ]["group_rank"];
    }
    //$debug->Milestone( "CacheGroups, phase 2 done");

    $this->rh->cache->Store( "maxrank_". $principal->data["user_id"], $id, 1, &$maxrank );
    $this->rh->cache->Store( "ingroups_".$principal->data["user_id"], $id, 1, &$groups );
    foreach ($all_groups as $k=>$v)
    {
      $name = str_replace(" ","",strtolower($v["group_name"]));
      $cached= &$this->rh->cache->Restore( "groups", $name, 1 );
      if ((cached == "empty") || ($cached["group_rank"] < $v["group_rank"]))
       $this->rh->cache->Store( "groups",   $name, 1, &$all_groups[$k]   );
    }

    $debug->Milestone( "CacheGroups, done for [".$id."]");
//    $debug->Error("wait a sec, debugging");
  }

// ------------------------------------------------------------------------------------
// ������������� ��������, ��������� � �� ����� -- http://npj.ru/kuso/npj/refaktoringactions
   function &_PreparseArray( &$item )
     {
     $uactn = &$this->rh->UtilityAction(); // !! actions refactored << max@jetstyle 2004-11-19 >>
     return $uactn->_PreparseArray( &$item, &$this );
     }
   function &_PreparseAccount( &$acc )
   {
     $uactn = &$this->rh->UtilityAction();
     return $uactn->_PreparseAccount( &$acc, &$this );
   }
   function _ActionOutput( &$data, &$params, $default="list" )
   {
     $uactn = &$this->rh->UtilityAction();
     return $uactn->_ActionOutput( &$data, &$params, $default, &$this );
   }
 
// ------------------------------------------------------------------------------------
// ���������� ���� "������������ �"
   function &CompileCrossposted( $record_id )
   {
     $db = &$this->rh->db; $rh = &$this->rh; $debug = &$this->rh->debug;
     $rs = $db->Execute( "select supertag, tag, login, node_id, keyword_user_id, owner_id, p.security_type from ".
                         $rh->db_prefix."users as u, ".
                         $rh->db_prefix."profiles as p, ".
                         $rh->db_prefix."records as r, ".
                         $rh->db_prefix."records_ref as ref ".
                         " where p.user_id=u.user_id and u.user_id = r.user_id and r.record_id = ref.keyword_id ".
                         " and ref.priority=0 and need_moderation=0 and ref.record_id = ". $db->Quote( $record_id ).
                         " order by node_id, login, tag asc " );
     $keywords = array( array("!"), array() );
     $filter   = array();
     $a = $rs->GetArray();
     foreach( $a as $k=>$v )
     {
       $is_keyword = 1*($v["owner_id"] == $v["keyword_user_id"]);
       $is_root    = 1*($v["tag"]=="");
       // filter always
       if (!$is_keyword) $filter[] = $v["login"];
       // keywords only if not secret
       if ($v["security_type"] != COMMUNITY_SECRET)
       if (!$is_keyword || !$is_root)
       $keywords[ $is_keyword ][] = $this->Link( $v["supertag"], "", $is_keyword?$v["tag"]:
                                               ($v["login"]."@".$v["node_id"].($is_root?"":":".$v["tag"])) );
     }
     if (sizeof($keywords[0]) > 1) array_shift($keywords[0]);

     $keywords[0] = implode(", ",$keywords[0]);
     $keywords[1] = implode(", ",$keywords[1]);
     $filter      = implode(",",$filter);

     $db->Execute( "update ".$rh->db_prefix."records set ".
                   "filter      = ".$db->Quote($filter).", ".
                   "crossposted = ".$db->Quote($keywords[0]).", ".
                   "keywords    = ".$db->Quote($keywords[1]).
                   "where record_id = ".$db->Quote($record_id));

     return $keywords;
   }

// ����������� � �����, ��� ��� ������ ����
  function untag ($xml, $tag)
  {
    $z = strpos ($xml, "<$tag>");

    if ($z !== false)
    {
      $z += strlen ($tag) + 2;
      $z2 = strpos ($xml, "</$tag>");

      if ($z2 !== false)
      {
        $final = substr ($xml, $z, $z2 - $z);

        if (strpos($final, "<![CDATA[")===0)
        {
          $final = substr($final, 9);
          $final = substr($final, 0, strlen($final)-3);
        }

        return $final;
      }
    }

    return '';
  }

  // ���������� ����������
  function _UsageState( &$principal, $event="handler", $method="", $params="" )
  {
    if (!$this->rh->usage_stats) return;
    if ($method == "") $p = $this->_params;
    else               $p = $params;

    $sql = "insert into ".$this->rh->db_prefix."usage_stats ".
           "(principal_user_id, event, object_id, object_address, object_class, object_method, object_params, server_datetime) VALUES ".
           "(".
             $this->rh->db->Quote($principal->data["user_id"]).",".
             $this->rh->db->Quote($event).",".
             $this->rh->db->Quote($this->data["id"]).",".
             $this->rh->db->Quote($this->npj_address).",".
             $this->rh->db->Quote($this->class).",".
             $this->rh->db->Quote(strtolower(($method=="")?$this->method:$method)).",".
             $this->rh->db->Quote(is_array($p)?implode("|",$p):$params).",".
             $this->rh->db->Quote(date("Y-m-d H:i:s")).
           ")";
    $this->rh->db->Execute($sql);

  }

  function prepMail($subject, $html, $text, $from)
  {
   $this->rh->UseLib("HtmlMimeMail2");
   if (!$this->mail) $this->mail = &new HtmlMimeMail2();

   if ($html)
     $this->mail->setHtml($html, $text);
   else
     $this->mail->setText($text);

   $this->mail->setFrom($from);
   $this->mail->setSubject($subject);
   $this->mail->setHeader('X-Mailer', "Net Project Journal (http://www.npj.ru)");

   if ($this->rh->method_mailsend=="smtp")
   {
    $this->mail->setSMTPParams($this->rh->node_mail_smtp, null, null, $this->rh->node_mail_smtpauth, $this->rh->node_mail_login, $this->rh->node_mail_passw);
   }

  }


  function sendMail($recipients)
  {
   $this->mail->buildMessage($this->rh->tpl->message_set["Encodings"], $this->rh->method_mailsend);
   $result = $this->mail->send($recipients, $this->rh->method_mailsend);
   if (!$result) 
   { 
     $this->rh->debug->Trace("Mail Errors:");
     $this->rh->debug->Trace_R($this->mail->errors); 
//     $this->rh->debug->Error(); 
   }
  } 


  // -------------------- COMMUNITY FILTER ROUTINES
  function IsCommunityFilterOk( $filter = NULL, $data = NULL )
  {
    if (!is_array($data)) $data   = &$this->data;
    if ($filter === NULL) $filter = $this->npj_filter;

    if ($filter == "") return true;

    if (!$data["filter:exploded"])
    {
      if ($data["filter"] != "")
        $fe = explode( ",", $data["filter"] );
      else
        $fe  = array();
      $data["filter:exploded"] = array_flip($fe);
    }

    if (isset($data["filter:exploded"][ $filter ])) return true;

    return false;
  }

// EOC{ NpjObject } 
}
   


?>