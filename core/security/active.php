<?php

// function IsGrantedTo( $method, $object_class, $object_id, $options="" )
//
// ���������� GRANTED, ���� ����������� ��� �������:
//  * � ���� ���� ����� ������ � ������� ����������� �� ����� �������
//  * � ����� ������� ���� ���� "active" � ��� ����������� � true/�������
// ����� DENIED

   $obj = $this->config->cache->Lookup( $object_class, $object_id, 2 );
   if ($obj === false) return DENIED;
   if ($obj["active"]) return GRANTED;
                  else return DENIED;

?>