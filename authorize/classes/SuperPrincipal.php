<?php
/*
    Это суперпринципал, нужен для инкапсуляции общей для всех кустомных принципалов логики.
    Вы, впрочем, можете её произвольно перегружать тоже.


    NpjCustomPrincipalSuper( &$rh )  -- авторизатор и держатель информации о пользователе
      - $rh -- объект класса ((NpjRequestHandler))
      - наследует от ((/Манифесто/КлассыЯдра/DbPrincipal DbPrincipal))

  ---------

  //

  * _GetUserPwd( $login, $pwd ) -- возвращает
        PRINCIPAL_WRONG_LOGIN
        PRINCIPAL_WRONG_PWD
        PRINCIPAL_AUTH

  * _SpawnAccountData( &$user_data, &$profile_data, $original_login )

  // перегруженные методы:

  * &_Login( $login, $pwd, $cookie="", $node_id="" )  -- вернуть false, если кривой логин или пароль, или хэш-массив параметров пользователя

=============================================================== v.3 (Kuso)
*/

class NpjCustomPrincipalSuper extends NpjPrincipal
{
   // реализация абстрактных методов:
   function &_Login( $login, $pwd, $cookie="", $node_id="" )  
   { $debug = &$this->rh->debug;

     // вскрываем адреса вида kuso@npj, kuso@sh
     if (strpos($login,"@") !== false)
     {
       $p = explode("@", $login);
       $login = $p[0];
       $node_id = $p[1];
     }

     if ($node_id == "")
       if (isset( $this->rh->modules["authorize"]["node_id"] ))
         $node_id = $this->rh->modules["authorize"]["node_id"];
       else
         $node_id = $this->rh->node_name;
     
     // 0. приводим логин в транслит
     $dummy = &new NpjObject( &$this->rh, "login@".$this->rh->node_name );
     $__login = $dummy->NpjTranslit( $login );
     $_login = $login; // этот логин для создания/проверки
     // проверяем, нет ли у нас уже таких нехороших и неудобных логинов
     // 1. валидатор
     // 2. существующие "не-пользователи"
     $test_account = &new NpjObject( &$this->rh, $_login."@".$node_id );
     $test_account->Load(2);
     if (($test_account->class != "account") || is_array($test_account->data))
     {
       if (is_array($test_account->data) && ($test_account->data["account_type"] == ACCOUNT_USER));
       else $_login = $__login."-user";
     }

     $debug->Trace("before <b>$login,$node_id</b>");

     $this->state = PRINCIPAL_WRONG_LOGIN;

     $pwd_md5 = $this->_GetUserPwd( $login, $pwd );
     if ($pwd_md5 === PRINCIPAL_WRONG_LOGIN) return false;

     $debug->Trace("after <b>$login,$node_id</b>");

     $this->state = PRINCIPAL_WRONG_PWD;

     // override only this
     if ($pwd_md5 == PRINCIPAL_AUTH && !$cookie)
     {
       // success!
       $debug->Trace("<b>SUCCESS $login,$node_id</b>");

       // авторегистрация
       if ($this->rh->modules["authorize"]["autoregistration"])
       {
         $principal_data = $this->_GetByLogin( $_login, $node_id, 
                                               0 ); // dont halt if fail
         if ($principal_data === false)
           $this->SpawnAccount( $_login, $node_id, $login );
       }

       $_cm = $this->cheat_mode;
       $this->cheat_mode=true;
       $user = &parent::_Login( $_login, $pwd, $cookie, $node_id );
       $this->cheat_mode=$_cm;
     } 
     else
     {
       $user = &parent::_Login( $_login, $pwd, $cookie, $node_id );
     }

     
     return $user;

   }

   // проверка авторегистрации
   function SpawnAccount( $login, $node_id="", $original_login="" )
   {
     if (!$node_id) 
       $node_id = $this->rh->node_name;

     // hotfix spaghetti for some settings
     $p = &$this;
     if (isset($this->rh->guest_override))
     {
       foreach($this->rh->guest_override as $k=>$v)
         $this->data[$k] = $v;
     }


     $user_data = array(
                    "login"     => $login,
                    "node_id"   => $node_id,
                    "user_name" => $login."@".$node_id,
                    "account_type" => ACCOUNT_USER,
                    "account_class" => "", // ??????? here we can implement account-class support
                    "alive" => 1,          // ??????? here we can implement premoderation
                    "password" => "custom",        // see, it is unbreakable!
                    "login_cookie" => md5(time()), // random init

                    "skin_override" => $this->data["skin_override"],
                    "more"          => $this->data["more"],
                       );
     $profile_data = array(
                    "email" =>         "",
                    "email_confirm" => "", // empty line means no need to confirm
                    "advanced" => $this->data["advanced"],
                          );
     if (isset($this->rh->modules["authorize"]["email_postfix"]))
       $profile_data["email"] = $original_login.$this->rh->modules["authorize"]["email_postfix"];

     // enhance sample data in descendants
     $this->_SpawnAccountData( $user_data, $profile_data, $original_login );

     // following code is tightly based on ModuleChannels::ChannelAbstract::CreateAccount
     $rh =& $this->rh;
     $db =& $rh->db;

     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."users where login=".$db->Quote($login)." AND node_id=".$db->Quote($node_id));
     if ($rs->RecordCount()!=0) return false; // accidental call.

     // insert to users
     $fields = $values = array();
     foreach( $user_data as $k=>$v )
     { 
       $fields[] = $k;
       $values[] = $db->Quote($v);
     }
     $fields = implode(",", $fields);
     $values = implode(",", $values);
     $sql = "INSERT INTO ".$rh->db_prefix."users (".$fields.") VALUES (".$values.")";
     $db->Execute($sql);
     $user_id = $db->Insert_ID(); 

     $rh->debug->Trace( "--- users ---" );

     // insert to profiles
     $profile_data[ "user_id" ] = $user_id;
     $fields = $values = array();
     foreach( $profile_data as $k=>$v )
     { 
       $fields[] = $k;
       $values[] = $db->Quote($v);
     }
     $fields = implode(",", $fields);
     $values = implode(",", $values);
     $sql = "INSERT INTO ".$rh->db_prefix."profiles (".$fields.") VALUES (".$values.")";
     $db->Execute($sql);

     $rh->debug->Trace( "--- profiles ---" );

     // population
     $_nu = $this->rh->node_user;
     $this->rh->node_user = $this->rh->node_user."@".$this->rh->node_name;
     $this->rh->account = &new NpjObject( $rh, $this->rh->node_user );
     $this->rh->object  = &new NpjObject( $rh, "registration@".$this->rh->node_name );

     $account = &new NpjObject( $rh, $login."@".$node_id );
     $node_principal = &new NpjPrincipal( &$rh );
     $rh->principal->MaskById( 2 );
     $account->Handler( "populate", array(), &$node_principal );
     $rh->principal->UnMask();

     $this->rh->node_user = $_nu;

     $rh->debug->Trace( "--- populate ---" );

     return $user_id;
   }

   // кустомное дозаполнение юзердаты
   function _SpawnAccountData( &$user_data, &$profile_data, $original_login )
   {
   }


   // для перегрузки в ваших принципалах
   function _GetUserPwd( $login, $pwd )
   {
     return PRINCIPAL_WRONG_LOGIN;
   }

// EOC{ NpjCustomPrincipalSuper } 
}


?>