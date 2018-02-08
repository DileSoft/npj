<?php

// удаляем регистрацию с NNS-сервера

/* 
  алгоритм:

  1. находим имя NNS-server у себя в БД
  2. подключаем класс HTTP_Request
  3. делаем DELETE-запрос
  4. если 200 Ok, то 5 иначе 7
  5. обновляем данные О СЕБЕ
  6. ура.
  7. сообщаем об ошибке 

  Общие функции для почти всех nns/*:
  1. untag
  2. what_nns_server
*/

 $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where is_nns=1");
 if ($rs->RecordCount()>0)
 {

   if ($rh->node->data["passwd"])  //стоит хранить также узел, в котором этот пароль. для пущей секьюрности
   {

     $rh->UseLib("Net_Socket", "PEAR");
     $rh->UseLib("Net_URL", "PEAR");
     $rh->UseLib("HTTP_Request", "PEAR");

     $req = &new HTTP_Request($rs->fields["url"]."node/nns/manage/".$rh->node->data["node_id"].
                              "?password=".$rh->node->data["passwd"] );
     $req->setMethod(HTTP_REQUEST_METHOD_DELETE);
     $req->sendRequest();
     if ($req->getResponseCode()=="200")
     {

       $rs = $db->Execute("UPDATE ".$rh->db_prefix."nodes SET passwd='', created_datetime='0000-00-00 00:00:00' where node_id=".$db->Quote($rh->node->data["node_id"]));

       return GRANTED;
     }
     else
     {
       $response = $req->getResponseBody();
       $debug->Error("NNS DELETE: ".$response);
     }
   }
 }

 return DENIED;

?>
