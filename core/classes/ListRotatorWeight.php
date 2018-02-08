<?php
/*
    ListRotatorWeight( &$rh, $db_table, $section_id, $url ) -- ��������� ������� � ����� (������ ������)
      - $db_table   -- �������� �������� ��� ��������
      - $section_id -- ������������� �������
      - $url        -- ������� �������� (��� ������� ����������� ����� -- ������, ����� ������ ��������� �� ���� ���)
      - ��������� �� ((ListRotator))

  ---------
  // �����������
  * _Selection( &$input, number ) -- ������� �� ������� ������ ����� ��� ������

=============================================================== v.1 (NikolaiIaremko)
*/

class ListRotatorWeight extends ListRotator 
{
  var $db_table;
  var $section_id;
  var $url;
  var $number;

  function ListRotatorWeight( &$rh, $db_table, $section_id, $url, 
                              $fields = "id, picture, urls, href, more, text, weight" )
  {
    $this->rh = &$rh;
    return ListRotator::ListRotator( &$rh, $db_table, $section_id, $url, 1,
                                      $fields );
  }

  function &_Selection( &$input, $number=1 )
  {
    $data = array();

    $total=0;
    foreach ($input as $k=>$v)
     $total+=$v["weight"];

    $target = rand(0, $total-1);

    $count=0;
    foreach ($input as $k=>$v)
    {
      $data[0] = &$input[$k];
      $count+=$v["weight"];
      if ($count >= $target) break;
    }

    return $data;
  }


// EOC { ListRotatorWeight }
}


?>