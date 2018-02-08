<?php

// �������� ������ ����� � �������

/* 
  ��������:

  1. ������� ��� NNS-server � ���� � ��
  2. ���������� ����� HTTP_Request
  3. ������ GET-������
  4. ���� 200 Ok, �� 5 ����� 9
  5. xplod-�� ����� �� <entry>
  6. � ����� �������� ��������� untag-��
  7. UPDATE/INSERT (������ ��������� �����)
  8. ���.
  9. �������� �� ������ (����, ����� ����� ����� �������� ������� �� ���������� �������

  ����� ������� ��� ����� ���� nns/*:
  1. untag
  2. what_nns_server
*/

 $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where is_nns=1");
 if ($rs->RecordCount()>0 && $rh->node->data["node_id"]!=$rs->fields["node_id"])
 {
   $rh->UseLib("Net_Socket", "PEAR");
   $rh->UseLib("Net_URL", "PEAR");
   $rh->UseLib("HTTP_Request", "PEAR");

   if ($rh->node->data["passwd"])  //����� ������� ����� ����, � ������� ���� ������. ��� ����� �����������
     $add = "?node=".$rh->node->data["node_id"]."&password=".$rh->node->data["passwd"];
   else
     $add = "";

   $req = &new HTTP_Request($rs->fields["url"]."node/nns/manage".$add);
   $req->setMethod(HTTP_REQUEST_METHOD_GET);
   $req->sendRequest();
   if ($req->getResponseCode()=="200")
   {
     $response = $req->getResponseBody();
     $xmls = explode("<entry>", $response);
     $this_id  = $db->Quote($rh->node->data["node_id"]);
     foreach ($xmls as $xml)
     {
       $id       = $db->Quote($this->untag($xml, "id"));
       if (trim($id,"'") && $id!=$this_id)
       {
        $title    = $db->Quote($this->untag($xml, "title"));
        $url      = $db->Quote($this->untag($xml, "url"));
        $can_nns  = $db->Quote($this->untag($xml, "can_nns"));
        $created  = $db->Quote($this->untag($xml, "created"));
        $email    = $db->Quote($this->untag($xml, "email"));
        $ip       = $db->Quote($this->untag($xml, "ip"));
        $user_pictures_dir = $db->Quote($this->untag($xml, "user_pictures_dir"));
        //is_nns??

        $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where node_id=".$id);
        if ($rs->RecordCount()>0)
        {
         //update
         $rs = $db->Execute("UPDATE ".$rh->db_prefix."nodes SET title=".$title.", url=".$url.
                          ", can_nns=".$can_nns.", email=".$email.", ip=".$ip.", created_datetime=".$created.
                          ", user_pictures_dir=".$user_pictures_dir." where node_id=".$id);
        }
        else
        {
         //insert
         $rs = $db->Execute("INSERT INTO ".$rh->db_prefix."nodes (node_id, title, url, can_nns, created_datetime, email, ip, user_pictures_dir) ".
                          "values (".$id.", ".$title.", ".$url.", ".$can_nns.", ".$created.", ".$email.", ".$ip.", ".$user_pictures_dir.")");
        }
       }
     }
//     $debug->Error("SELECT");
     return GRANTED;
   }
 }

 return DENIED;

?>