<?php


$communities = array();
foreach ($params as $v)
 if ($v) $communities[] = $v;

 // �� ������ �������� � ����-�� ������� �������

return $this->Handler( "post", 
    array("announce"=>1, "announce_to"=>$communities, "post_to"=>$communities), 
                        &$principal );

?>