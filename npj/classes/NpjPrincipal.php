<?php
/*

    NpjPrincipal( &$rh )  -- авторизатор и держатель информации о пользователе
      - $rh -- объект класса ((NpjRequestHandler))
      - наследует от ((/Манифесто/КлассыЯдра/DbPrincipal DbPrincipal))

  ---------

  * BookmarkIt( $npj_address, $title="" ) -- добавить страницу в "персональное меню"
      - $npj_address -- абсолютный адрес добавляемой страницы (kuso@npj:MyPage)
      - $title       -- подпись к адресу. Если пустая, то подписью становится сам адрес
  * &LoadMenu()     -- загрузить "персональное меню" из БД, сохранить в сессии
  * &LoadOptions()  -- сформировать ((РасширенныеНастройкиПользователя)), сохранить в сессии
  * CheckOptions()  -- переключает некоторые из этих настроек в зависимости от $_GET
  * &DecomposeOptions( $more ) -- непосредственное формирование массива настроек из строки
      - $more -- строка с настройками формата "param1=value1\nparam2=value2\n"
  * ComposeOptions( $options ) -- сборка строки из массива настроек
      - $options -- массив вида ( "param1"=>"value1", "param2"=>"value2" )
  * LoadGlobalGroups()     -- загрузить членство в сообществах/рг

  // Унаследованные и overriden методы
  * Login( $redirect = 1, $strict_login=NULL, $strict_pwd=NULL ) -- расширенный логин, который также вызывает 
                                                                    всякие специфич. свойства
  * Identify( $try_login = 1 ) -- расширенное восстановление из сессии
  * LoginCookie( $login, $pwd, $strict = 0, $magic="nomagic" ) -- защита от хака.

  // Критичные такие свойства (сменив эти два символа можно лишить всех пользователей их настроек. оп-ля!)
  * $this->optionSplitter = "\n"; -- разделитель настроек пользователя
  * $this->valueSplitter  = "=";  -- разделитель между названием и значением настройки

=============================================================== v.3 (Kuso)
*/

class NpjPrincipal extends DbPrincipal
{
   // сменив эти два символа можно лишить всех пользователей их настроек. оп-ля!
   var $optionSplitter = "\n";
   var $valueSplitter  = "=";

   function NpjPrincipal( &$rh )
   {
     DbPrincipal::DbPrincipal( &$rh );
     $this->check_account_type = true;
   }

   function LoginCookie( $login, $pwd, $strict = 0, $magic="nomagic" )
   {
     $magic = $magic.$this->rh->node_secret_word;
     return DbPrincipal::LoginCookie( $login, $pwd, $strict, $magic );
   }

   function Login( $redirect = 1, $strict_login=NULL, $strict_pwd=NULL )
   {
     $result = DbPrincipal::Login( 0, $strict_login, $strict_pwd );
     if ($result) 
     {  
       $this->LoadMenu(); 
       $this->LoadOptions();
       $this->LoadGlobalGroups();
     }

     if ($redirect && !$cookie_mode && $result) 
     {  // БлокРЧ.5.3.мерж
        // !!! подозрение на отсутствие реврайт-мода
        $this->rh->state->Free();
        $this->rh->state->Set("cookietest", 1);
        $this->rh->state->Set(session_name(), session_id());
//        $this->rh->debug->Trace($this->rh->url);
//        $this->rh->debug->Error($this->rh->Href($this->rh->url));
        $this->rh->Redirect( $this->rh->Href($this->rh->url), STATE_USE ); 
        //header("Location: $PHP_SELF?cookietest=1&".session_name()."=".session_id());
        //exit; 
     }

     return $result;
   }

   function Identify( $try_login = 1 )
   {
     $this->rh->debug->Milestone( "NpjPrincipal::Identify, on enter" );
     $result = DbPrincipal::Identify( $try_login );
     if (isset($_GET["option"]) )
     {
       $this->CheckOptions();
       $this->rh->state->Free( "option" );    $this->rh->state->Free( "value" );
       unset($_GET["option"]);       unset($_GET["value"]);
       unset($_REQUEST["option"]);   unset($_REQUEST["value"]);
     }
     if (isset($_GET["menu"] ))
     {
       $title="";
       if ((($this->rh->object->class == "comments") || ($this->rh->object->class == "friends")
            || ($this->rh->object->class == "versions"))
           && ($this->rh->object->name == "")) ; 
       else {
         $data = $this->rh->object->Load(2);
         if ($data["subject"])   $title = $data["subject"];
         if ($data["title"])     $title = $data["title"];
         if ($data["user_name"]) $title = $data["user_name"];
       }

       $this->BookmarkIt( $this->rh->object->npj_address, $title ); 
       $this->rh->state->Free( "menu" );
       unset($_GET["menu"]);
       unset($_REQUEST["menu"]);
       $this->rh->Redirect();
     }

     // additional maxrank cache
     if ($result && is_array($this->data["global_membership"]))
     {
       $this->rh->cache->Store("global_membership", $this->data["user_id"], 1, &$this->data["global_membership"]);
//  this is not a maxrank yet
//       foreach($this->data["global_membership"] as $npj=>$v)
//         $this->rh->cache->Store( "maxrank_". $this->data["user_id"], $v["user_id"], 1, &$v["group_rank"] );
     }

     return $result;
   }


   function BookmarkIt( $npj_address, $title="" )
   {
     if ($this->data["user_id"] == 1) return false;
     // проверить, нет ли уже
     $sql = "select item_id from ".$this->rh->db_prefix.
            "user_menu where npj_address=".$this->rh->db->Quote($npj_address).
            " and user_id=".$this->rh->db->Quote( $this->data["user_id"] ).
            " order by pos";
     $rs = $this->rh->db->Execute( $sql );
     if ($rs->RecordCount() == 0)
     { // insert
       if ($title === "") $title =  $this->rh->object->AddSpaces( $npj_address, " " );
       $sql = "insert into ".$this->rh->db_prefix."user_menu set ".
              "pos = -1, user_id=".$this->rh->db->Quote( $this->data["user_id"] ).
              ", title=".$this->rh->db->Quote($title).
              ", npj_address=".$this->rh->db->Quote($npj_address);
       $this->rh->db->Execute( $sql );
       // rearrange (move down @ low cost)
       $sql = "update ".$this->rh->db_prefix."user_menu set pos = item_id where pos<0 ";
       $this->rh->db->Execute( $sql );
     }
     else
     { // delete
       $sql = "delete from ".$this->rh->db_prefix."user_menu where npj_address=".$this->rh->db->Quote($npj_address);
       $this->rh->db->Execute( $sql );
     }
     $this->LoadMenu();
     return true;
   }

   function &LoadMenu()
   {
       // загружаем меню из базы данных, складываем в сессию
       $sql = "select item_id, user_id, pos, title, npj_address from ".$this->rh->db_prefix.
              "user_menu where user_id=".$this->rh->db->Quote( $this->data["user_id"] ).
              " order by pos";
       $rs = $this->rh->db->Execute( $sql );
       $a = $rs->GetArray(); $b = array();
       foreach($a as $k=>$v)
       {
         $b[$k]["user_id"] = $v["user_id"];
         $b[$k]["item_id"] = $v["item_id"];
         $b[$k]["pos"] = $v["pos"];
         $b[$k]["title"] = $v["title"];
         $b[$k]["npj_address"] = $v["npj_address"];
       }
       $this->data["user_menu"] = &$b;
       $this->Store();
       return $b;
   }

   function &LoadOptions()
   {
     $this->data["options"] = &$this->DecomposeOptions( $this->data["more"] );
     $this->Store();
   }

   function CheckOptions()
   {
    switch ($_GET["option"])
    {
      case "user_menu": 
                        $this->data["options"]["user_menu"] = !$this->data["options"]["user_menu"];
                        break;
      case "novice_panel": 
                        $this->data["options"]["novice_panel"] = !$this->data["options"]["novice_panel"];
                        break;
      case "double_click": 
                        $this->data["options"]["double_click"] = !$this->data["options"]["double_click"];
                        break;
    }
    // we can change values for guest, but not to store in DB. only for a session
    if ($this->data["user_id"] == 1) return false;
    if (!isset($this->data["options"])) $this->rh->debug->Error("NpjPrincipal: custom options not found");
    $this->data["more"] = $this->ComposeOptions( $this->data["options"] );
    $sql = "update ".$this->rh->db_prefix."users set more=".$this->rh->db->Quote($this->data["more"]).
           "where user_id=".$this->rh->db->Quote($this->data["user_id"]);
    $this->rh->db->Execute( $sql );
    $this->LoadOptions();
    return true;
   }

   function &DecomposeOptions( $more )
   {
     $b = array();
     $opts = explode( $this->optionSplitter, $more );
     foreach( $opts as $o )
     {
       $params = explode( $this->valueSplitter, trim($o) );
       $b[ $params[0] ] = $params[1];
     }
     return $b;
   }
   function ComposeOptions( $options )
   {
     $opts = array();
     foreach ($options as $k=>$v)
       $opts[] = $k.$this->valueSplitter.$v;
     return implode( $this->optionSplitter, $opts );
   }


   // принадлежность глобальным группам
   function LoadGlobalGroups()
   {
       // загружаем из базы данных, складываем в сессию
       $sql = "select u.login, u.node_id, u.user_id, g.group_rank from ".
              $this->rh->db_prefix."users as u,".
              $this->rh->db_prefix."user_groups as ug,".
              $this->rh->db_prefix."groups as g ".
              "where ug.user_id=".$this->rh->db->Quote( $this->data["user_id"] ).
              " and ug.group_id = g.group_id and g.user_id=u.user_id ".
              " and u.account_type > ".ACCOUNT_USER.
              " and g.group_rank >= ".GROUPS_POWERMEMBERS;
       $rs = $this->rh->db->Execute( $sql );
       $a = $rs->GetArray(); $b = array();
       foreach($a as $k=>$v)
       {
         $npj = $v["login"]."@".$v["node_id"];
         $data = array( "npj"        => $npj, 
                        "user_id"    => $v["user_id"], 
                        "group_rank" => $v["group_rank"], 
                      );
         $b[ $npj ] = $data;
       }
       $this->data["global_membership"] = $b;
       $this->Store();
       return;
   }


// EOC{ NpjPrincipal } 
}


?>