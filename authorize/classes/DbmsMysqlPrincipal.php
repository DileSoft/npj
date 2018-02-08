<?php
/*

  јвторизует, только если пользователь есть в таблице базы MySQL и пароль совпадает с полученным.

    NpjCustomPrincipal( &$rh )  -- авторизатор и держатель информации о пользователе
      - $rh -- объект класса ((NpjRequestHandler))
      - наследует от ((/ћанифесто/ лассыядра/DbPrincipal DbPrincipal))

  ---------


=============================================================== v.0.1 (Norguhtar)
*/

class NpjCustomPrincipal extends NpjCustomPrincipalSuper
{

  function _GetUserPwd( $login, $pwd )
  {
    $hostname=$this->rh->modules["authorize"]["dbms_principal_hostname"].":".$this->rh->modules["authorize"]["dbms_principal_port"];
    $username=$this->rh->modules["authorize"]["dbms_principal_username"];
    $password=$this->rh->modules["authorize"]["dbms_principal_password"];
    $database=$this->rh->modules["authorize"]["dbms_principal_database"];
    $table=$this->rh->modules["authorize"]["dbms_principal_table"];
    $user_field=$this->rh->modules["authorize"]["dbms_principal_user_field"];
    $pass_field=$this->rh->modules["authorize"]["dbms_principal_pass_field"];
    $encrypt_method=$this->rh->modules["authorize"]["dbms_principal_encrypt_method"];
    $hide_user=$this->rh->modules["authorize"]["dbms_principal_hide_user"];

    $user_query="select count($user_field) from $table where ($user_field='$login')";
    $pass_query="($pass_field=$encrypt_method('$pwd'))";

    $connect=mysql_connect($hostname,$username,$password);

    if ($connect and mysql_select_db($database,$connect)) 
    { 
      $result=mysql_query($user_query,$connect);
      if (mysql_result($result,0,"count($user_field)")or($hide_user)) 
      {
        $result=mysql_query($user_query." and ".$pass_query,$connect);
        if (mysql_result($result,0,"count($user_field)")) 
        {
          mysql_close($connect);
          return PRINCIPAL_AUTH;
        }
        else 
        {
          mysql_close($connect);
          return PRINCIPAL_WRONG_PWD;
        }
      }
      else 
      {
        mysql_close($connect);
        return PRINCIPAL_WRONG_LOGIN; 
      }
    }
    else 
    {
      mysql_close($connect);
      return PRINCIPAL_WRONG_LOGIN;
    }
  }

// EOC{ NpjCustomPrincipal } 
}


?>