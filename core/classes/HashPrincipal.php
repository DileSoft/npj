<?php
/*

    HashPrincipal( $rh )  -- ��������� ����������, ���������� � include buffered ���������
      - ��������� �� ((Principal))

  ---------

  * &_GetByID( $id )         -- �������� ���-������ ���������� ������������ � id=$id (�����)
  * &_GetByLogin( $login, $_halt_if_fail=1 )  -- �������� ���-������ ���������� ������������ � login=$login (case-sensitive)
  * &_Login( $login, $pwd, $cookie )  -- ������� false, ���� ������ ����� ��� ������, ��� ���-������ ���������� ������������
  * _Logout()               -- ������� ���-�� ������� � �������, ���, ������ false, ��������� ������

  // invalid.

=============================================================== v.1 (Kuso)
*/

class HashPrincipal extends Principal
{

   function HashPrincipal( &$rh )
   {
     $this->Principal( &$rh );

    // ��������� ������������ �������������
    if(!@is_readable($rh->principal_hash_path)) die("Cannot read hash for principal.");
    require_once($rh->principal_hash_path);                                                         
   }

   // ���������� ����������� �������:
   function &_GetByID( $id )         
   {  
     if (!isset($this->users[$id])) $this->rh->debug->Error("HashPrincipal: user <b>#$id</b> not found");
     return $this->users[$id];
   }
   function &_GetByLogin( $login, $_halt_if_fail=1 )
   {  
     foreach ($this->users as $user)
      if ($user["login"] == $login) return $user;
     if ($_halt_if_fail) $this->rh->debug->Error("HashPrincipal: user <b>$login</b> not found");
     return false;
   }
   function &_Login( $login, $pwd, $cookie )  // �� ������������ ����-�����������
   { 
     $user = $this->_GetByLogin( $login, 0 );
     if ($user === false) return false;
     if ($user["password"] == $pwd) return $user; else return false;
   }
   function _Logout()               
   { return true;                                   }


// EOC{ HashPrincipal } 
}



?>