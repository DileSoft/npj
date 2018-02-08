<?php
/*
    ListRotatorWeight( &$rh, $db_table, $section_id, $url ) -- случайная выборка с весом (только одного)
      - $db_table   -- название страницы без префикса
      - $section_id -- идентификатор раздела
      - $url        -- текущая страница (для которой формируется набор -- нельзя, чтобы баннер показывал на этот урл)
      - наследует от ((ListRotator))

  ---------
  // перегружено
  * _Selection( &$input, number ) -- выбрать из массива нужное число для показа

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