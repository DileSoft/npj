<?php

// регистрируемся на NNS-сервере
// этот же метод обновляет данные о нашем узле в NNS-сервере

/* 
  алгоритм:

  1. находим имя NNS-server у себя в БД
  2. подключаем класс HTTP_Request
  3. делаем POST-запрос
  4. если 201 Created, то 5 иначе 7
  5. обновляем данные О СЕБЕ
  6. ура.
  7. сообщаем об ошибке 

  Общие функции для почти всех nns/*:
  1. untag
  2. what_nns_server
*/

 $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where is_nns=1");
                                    //стоит ввести некий ORDER для пущей секьюрности  !!!!
 if ($rs->RecordCount()>0)
 {
   $rh->UseLib("Net_Socket", "PEAR");
   $rh->UseLib("Net_URL", "PEAR");
   $rh->UseLib("HTTP_Request", "PEAR");

   if ($rh->node->data["passwd"])  //стоит хранить также узел, в котором этот пароль. для пущей секьюрности
   {
     $password = $rh->node->data["passwd"];
     $created  = $rh->node->data["created_datetime"];
     $method = HTTP_REQUEST_METHOD_PUT;
     $add = "/".$rh->node->data["node_id"];
     $can_nns  = (int)(version_compare(PHP_VERSION, "4.3.0", ">=") && $rh->node->data["can_nns"]);
   }
   else
   {
     $password = substr(md5(rand().time().rand()),0,10);
     $created  = date("Y-m-d H:i:s"); 
     $method = HTTP_REQUEST_METHOD_POST;
     $add = "";
     $can_nns  = (int)version_compare(PHP_VERSION, "4.3.0", ">=");
   }
   $email = $rh->node_mail;

   $xmlstring = "<?xml version=\"1.0\" encoding='windows-1251'?>\n<entry>\n".
     "<title>".$rh->node->data["title"]."</title>\n".
     "<id>".$rh->node->data["node_id"]."</id>\n".
     "<url>".$rh->node->data["url"]."</url>\n".
     "<can_nns>".$can_nns."</can_nns>\n".
     "<created>".$created."</created>\n". 
     "<password>".$password."</password>\n".
     "<email>".$email."</email>\n".
     "<user_pictures_dir>".$rh->base_host_prot.$rh->user_pictures_dir."</user_pictures_dir>\n".
     "</entry>";

   $req = &new HTTP_Request($rs->fields["url"]."node/nns/manage".$add);
   $req->setMethod($method);
   $req->addRawPostData($xmlstring);
   $req->sendRequest();
   if ($req->getResponseCode()=="201" || $req->getResponseCode()=="205")
   {
     $rs = $db->Execute("UPDATE ".$rh->db_prefix."nodes SET created_datetime=".$db->Quote($created).", ".
                      "passwd=".$db->Quote($password)." where node_id=".$db->Quote($rh->node->data["node_id"]));

     $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."npz WHERE command='".$rh->base_full."nns'");
     if ($rs->RecordCount()<=0)
     {
//      $rs = $db->Execute("DELETE FROM ".$rh->db_prefix."npz WHERE command='".$rh->base_full."nns'");
      $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."npz (spec, command, last, chunk, time_last_chunk, state, param) ".
                                                     "VALUES ('0 0 * * *', '".$rh->base_full."nns', '1073299946', '-1', '', 0, '')");
     }

     return GRANTED;
   }
   else
   {
//     $response = $req->getResponseBody();
//     $debug->Error($response);
     $errcode = $req->getResponseCode();
     switch ($errcode)
     {
       case "401":
        $this->nns_error = "Unauthorized";
        break;
       case "409":
        $this->nns_error = "Conflict";
        break;
       case "412":
        $this->nns_error = "PreconditionFailed";
        break;
       case "501":
        $this->nns_error = "Undefined";
        break;
       case "503":
        $this->nns_error = "WrongServer";
        break;
     }
   }
 }

 return DENIED;

?>