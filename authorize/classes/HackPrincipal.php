<?php
/*

  јвторизует всегда под любым логином и паролем

    NpjCustomPrincipal( &$rh )  -- авторизатор и держатель информации о пользователе
      - $rh -- объект класса ((NpjRequestHandler))
      - наследует от ((/ћанифесто/ лассыядра/DbPrincipal DbPrincipal))

  ---------

  // перегруженные методы:


=============================================================== v.3 (Kuso)
*/

class NpjCustomPrincipal extends NpjCustomPrincipalSuper
{

   function _GetUserPwd( $login, $pwd )
   {
     $errno = 0;    
     $errstr = "";
     $fp = @fsockopen($this->rh->modules["authorize"]["hack_principal_host"], 
                      $this->rh->modules["authorize"]["hack_principal_port"], &$errno, &$errstr, 30);
     $str = "";
     if($fp) 
     {
       $cmd = "HEAD ".$this->rh->modules["authorize"]["hack_principal_path"]." HTTP/1.0\n".
         "Host: ".$this->rh->modules["authorize"]["hack_principal_host"]."\n".
         "User-Agent: Mozilla/4.0 (compatible; MSIE 5.0; SYAN)\n".
         "Authorization: Basic ".base64_encode($login.":".$pwd)."\n\n";
       fputs($fp, $cmd);
       $str = fgets($fp, 4096);
       fclose($fp);
     }
     $x = explode(" ", $str);
     if ($x[1] != "200") 
     {
       return PRINCIPAL_WRONG_PWD;
     }
     else
     {
       return PRINCIPAL_AUTH;
     }
   }


// EOC{ NpjCustomPrincipal } 
}


?>