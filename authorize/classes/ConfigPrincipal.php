<?php
/*

  јвторизует, только если есть в конфиге модул€ логин-пароль

    NpjCustomPrincipal( &$rh )  -- авторизатор и держатель информации о пользователе
      - $rh -- объект класса ((NpjRequestHandler))
      - наследует от ((/ћанифесто/ лассыядра/DbPrincipal DbPrincipal))

  ---------


=============================================================== v.3 (Kuso)
*/

class NpjCustomPrincipal extends NpjCustomPrincipalSuper
{

   function _GetUserPwd( $login, $pwd )
   {
     if (!isset($this->rh->modules["authorize"]["config_user_passwords"][$login]))      return PRINCIPAL_WRONG_LOGIN;
     if ($this->rh->modules["authorize"]["config_user_passwords"][$login] != md5($pwd)) return PRINCIPAL_WRONG_PWD;

     return PRINCIPAL_AUTH;
   }

// EOC{ NpjCustomPrincipal } 
}


?>