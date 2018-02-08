<?php
/*

  јвторизует, только если пользователь есть в таблице базы PostgreSQL и 
  пароль совпадает с полученным.

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
    $hostname=$this->rh->modules["authorize"]["dbms_principal_hostname"];
    $port=$this->rh->modules["authorize"]["dbms_principal_port"];
    $database=$this->rh->modules["authorize"]["dbms_principal_database"];
    $username=$this->rh->modules["authorize"]["dbms_principal_username"];
    $password=$this->rh->modules["authorize"]["dbms_principal_password"];
    $table=$this->rh->modules["authorize"]["dbms_principal_table"];
    $user_field=$this->rh->modules["authorize"]["dbms_principal_user_field"];
    $pass_field=$this->rh->modules["authorize"]["dbms_principal_pass_field"];
    $encrypt_method=$this->rh->modules["authorize"]["dbms_principal_encrypt_method"];
    $hide_user=$this->rh->modules["authorize"]["dbms_principal_hide_user"];

    $user_query="select count($user_field) from $table where ($user_field='$login')";
    $pass_query="($pass_field=$encrypt_method('$pwd'))";

    $connect=pg_connect("host=$hostname port=$port dbname=$database user=$username password=$password");

    if ($connect) { 

  $result=pg_query($connect,$user_query);

  if (pg_fetch_result($result,0,"count")or($hide_user)) {
    
      $result=pg_query($connect,$user_query." and ".$pass_query);

      if (pg_fetch_result($result,0,"count")) {
          pg_close($connect);
              return PRINCIPAL_AUTH;
      }
      else {
          pg_close($connect);
          return PRINCIPAL_WRONG_PWD;
      }
  }
  else {
      pg_close($connect);
      return PRINCIPAL_WRONG_LOGIN; 
  }
    }
    else {
  pg_close($connect);
  return PRINCIPAL_WRONG_LOGIN;
    }

   }

// EOC{ NpjCustomPrincipal } 
}


?>