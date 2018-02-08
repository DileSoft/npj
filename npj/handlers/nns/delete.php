<?php

// ������� ����������� � NNS-�������

/* 
  ��������:

  1. ������� ��� NNS-server � ���� � ��
  2. ���������� ����� HTTP_Request
  3. ������ DELETE-������
  4. ���� 200 Ok, �� 5 ����� 7
  5. ��������� ������ � ����
  6. ���.
  7. �������� �� ������ 

  ����� ������� ��� ����� ���� nns/*:
  1. untag
  2. what_nns_server
*/

 $rs = $db->Execute("SELECT * FROM ".$rh->db_prefix."nodes where is_nns=1");
 if ($rs->RecordCount()>0)
 {

   if ($rh->node->data["passwd"])  //����� ������� ����� ����, � ������� ���� ������. ��� ����� �����������
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
