<?php
/*

    DbPrincipal( $rh )  -- ��������� ����������, ���������� � �������� � �� �������� users.  
      - ��������� �� ((Principal))
      - ������ ������� ���������:
        * user_id   -- ��������� ������������� ������������ (�������� ������ � �������� ��)
        * login     -- ����� ������������ (�������� ��� ��������� ��) (kuso)
        * password  -- md5( ������ ) (32 ���������� �������)
        * user_name -- ��� ������������ (���� ���������)
        * alive     -- !=0, ������ ������������ ������ ���, ��� ����
      - ���� � $rh->principal_profiles �������, �� ����� ����������� ������ �� ������� � ��������� ��� ������
        ��� ������� (��� ����) � merge � $this->data


  ---------

  * &_GetByID( $id )         -- �������� ���-������ ���������� ������������ � id=$id (�����)
  * &_GetByLogin( $login, $node_id="", $_halt_if_fail=1 )   -- �������� ���-������ ���������� ������������ � login=$login (case-sensitive)
      - $_halt_if_fail -- ���� ���������� � ������� ��, �� ����� ������������, ������ �������� ����������
      - !!! redocument
  * &_GetProfile( &$user, $_halt_if_fail=1 ) -- �������� �������, ���� �����. ���������� �� ����� _GetBy*
      - $user          -- ��� ������, ���������� � ���������� ������ _Get
      - $_halt_if_fail -- ���� ���������� � ������� ��, �� ����� ������������, ������ �������� ����������
  * &_Login( $login, $pwd, $cookie, $node_id )  -- ������� false, ���� ������ ����� ��� ������, ��� ���-������ ���������� ������������
      - !!! redocument
  * _Logout()               -- ������� ���-�� ������� � �������, ���, ������ false, ��������� ������

=============================================================== v.4 (Kuso)
*/

class DbPrincipal extends Principal
{
   var $check_account_type = false;

   function DbPrincipal( &$rh )
   {
     Principal::Principal( &$rh );
   }

   // ��������� ���������� �� ��
   function &_GetByID( $id )
   {
     $id=$this->rh->db->Quote($id);
     $node_id=$this->rh->db->Quote( $this->rh->node_name );
     $user = &$this->rh->cache->Restore("user", $id);
     if ($user === false)
     {
       $rs= &$this->rh->db->Execute( "select *, user_id as id from ".$this->rh->db_prefix."users where user_id=".$id );
       if ($rs === false) $this->rh->debug->Error( "DbPrincipal:  user <b>#$id</b> not found" );
       $user = &$rs->fields;
       $this->rh->cache->Store("user", $id, 2, &$user);
       $this->rh->cache->Store("user", "_".$user["login"]."_".$user["node_id"], 2, &$user);
     } 
     return $this->_GetProfile(&$user);
   }
   function &_GetByLogin( $id, $node_id="", $_halt_if_fail=1 )
   {
     $id=$this->rh->db->Quote( $id );
     if (!$node_id) $node_id=$this->rh->node_name;
     $node_id = $this->rh->db->Quote( $node_id );
     $user = &$this->rh->cache->Restore("user", "_".$id);

     if ($user === false)
     {
       $rs= &$this->rh->db->Execute( "select *, user_id as id from ".$this->rh->db_prefix."users where ".
                                     ($this->check_account_type?"account_type=0 and ":"").
                                     " login=".$id." and node_id=".$node_id  );
       if ($rs === false) 
         if ($_halt_if_fail) $this->rh->debug->Error( "DbPrincipal:  user <b>$id</b> not found" );
         else return false;
       if ($rs->RecordCount() == 0) return false;
       $user = &$rs->fields;

       $this->rh->cache->Store("user", "_".$id."_".$node_id, 2, &$user);
       $this->rh->cache->Store("user", $user["user_id"], 2, &$user);
     } 
     return $this->_GetProfile(&$user, $_halt_if_fail);
   }

   // �������� ������� (��� ��������� �������������)
   function &_GetProfile( &$user, $_halt_if_fail=1  )
   {
     if (!$this->rh->principal_profiles) return $user;

     $rs= &$this->rh->db->Execute( "select * from ".$this->rh->db_prefix.$this->rh->principal_profiles.
                                   " where user_id=".$this->rh->db->Quote($user["id"])  );
     if ($rs === false) 
       if ($_halt_if_fail) $this->rh->debug->Error( "DbPrincipal:  profile for user <b>".$user["login"]."</b> not found" );
       else return false;
     $user = array_merge( (array)$user, (array)$rs->fields );

     $this->rh->cache->Store("user", "_".$user["login"], 3, &$user);
     $this->rh->cache->Store("user", $user["user_id"], 3, &$user);

     return $user;
   }


   // ���������� ����������� �������:
   function &_Login( $login, $pwd, $cookie="", $node_id="" )  
   { $debug = &$this->rh->debug;
     
     // ��������� ������ ���� kuso@npj, kuso@sh
     if (strpos($login,"@") !== false)
     {
       $p = explode("@", $login);
       $login = $p[0];
       $node_id = $p[1];
     }
     
     $debug->Trace("before <b>$login,$node_id</b>");
     $this->state = PRINCIPAL_WRONG_LOGIN;
     $user = &$this->_GetByLogin( $login, $node_id, 0 );
     if ($user === false) return false;
     $debug->Trace("after <b>$login,$node_id</b>");

     $this->state = PRINCIPAL_WRONG_PWD;
     if ($this->cheat_mode || // �����, ����� �� ����� ������ ������� ������
         $user["password"] == md5($pwd) || 
         (($pwd == "") && ($cookie != "") && 
         ($this->LoginCookie( $login, $pwd, 2, $user["login_cookie"] ) == $cookie))
        ) 
     {
       $debug->Trace("<b>$login,$node_id</b>");
       $id=$this->rh->db->Quote( $user["user_id"] );
       $node_id=$this->rh->db->Quote( $this->rh->node_name );
       $this->rh->db->Execute( "update ".$this->rh->db_prefix."users set last_login_datetime=".
                                $this->rh->db->DBTimeStamp(time())." where user_id=".$id." and node_id=".$node_id );
  
       return $user; 
     } 
     else return false;
   }

   function _Logout()               
   { 
       $id=$this->rh->db->Quote( $user["user_id"] );
       $empty = $this->rh->db->Quote( "" );
       $node_id=$this->rh->db->Quote( $this->rh->node_name );
       $this->rh->db->Execute( "update ".$this->rh->db_prefix."users set last_logout_datetime=".
                                $this->rh->db->DBTimeStamp(time())." where user_id=".$id." and node_id=".$node_id );
       return true;                                   
   }


// EOC{ DbPrincipal } 
}



?>
