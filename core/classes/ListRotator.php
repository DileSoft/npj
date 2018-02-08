<?php
/*
    ListRotator( &$rh, $db_table, $section_id, $url, $number=1 ) -- случайная выборка
      - $db_table   -- название страницы без префикса
      - $section_id -- идентификатор раздела
      - $url        -- текущая страница (для которой формируется набор -- нельзя, чтобы баннер показывал на этот урл)
      - $number     -- сколько показывать за раз
      - наследует от ((ListSimple))

  ---------
  // перегружено
  * Parse( $tpl_root, $store_to, $append ) -- отпарсить по коллекции шаблонок

  // для override рекомендуется:
  * &_ParseOne( $tpl, $pos ) -- парсит один элемент списка

=============================================================== v.3sdm (Kuso)
*/

class ListRotator extends ListSimple
{
  var $db_table;
  var $section_id;
  var $url;
  var $number;

  function ListRotator( &$rh, $db_table, $section_id, $url, $number=1, 
                        $fields = "id, picture, urls, href, more, text" )
  {
    $this->db_table = $db_table;
    $this->section_id = $section_id;
    $this->url = $url;
    $this->number = $number;

    // get from db
    $query = "SELECT $fields FROM ".$rh->db_prefix.$db_table." WHERE active=1 AND section_id = ".
             $rh->db->Quote($section_id);

    $rs = $rh->db->Execute( $query );
    $all_data = &$rs->GetArray();
    $by_links = array();
    foreach ($all_data as $k=>$v)
    {
      $all_data[$k]["urls_list"] = explode("\n", $all_data[$k]["urls"]);
      foreach( $all_data[$k]["urls_list"] as $k1=>$v1)
      { $v1 = trim($v1);
        $by_links[ $v1 ][] = &$all_data[$k];
      }
    }

    // decompose url
    $url = trim($url, "/");
    $slashes = explode("/", $url);
    $url_parts = array("/");
    if ($url != "") 
    for( $i=0; $i<sizeof($slashes); $i++)
     $url_parts[] = $url_parts[$i].$slashes[$i]."/";
    $url_parts[0] = "//";

    $this->all_data = &$all_data;
    for( $i=sizeof($url_parts)-1; $i>=0; $i--)
     if (isset($by_links[$url_parts[$i]]))
     {
       $this->all_data = $by_links[$url_parts[$i]];
       break;
     }


    return $this->ListSimple( &$rh, $this->_Selection(&$this->all_data, $number), $cache_id );
  }

  function &_Selection( &$input, $number )
  {
    if (sizeof($input) < $number) $number = sizeof($input);
    $f=1;
    if ($number > 0)
    while ($f)
    { $f=0;
      $data = array_rand( $input, $number );
      if (!is_array($data)) $data = array( $data );
      foreach ($data as $k=>$v)
      {
       $data[$k] = &$input[$v];
       if ($data[$k]["href"] == $url) $f=1;
      }
      if ((sizeof($input) <= $number+1) && $f) break;
    }
    return $data;
  }

  // перегружаемая функция та же:
  function &_ParseOne( $tpl_name, $pos, &$obj, $count=0 )
  {
    $this->rh->tpl->Assign("_Count",    $count    );
    $this->rh->tpl->Assign("_Href",    $obj["href"]    );
    $this->rh->tpl->Assign("_Text",    $obj["text"]    );
    $this->rh->tpl->Assign("_Picture", preg_replace("/^(.*)\..*?$/i", "$1", $obj["picture"]) );

    $this->rh->tpl->Assign("href",       $obj["href"]    );
    $this->rh->tpl->Assign("alt",        $obj["alt"]    );
    $this->rh->tpl->Assign("more",       $this->rh->tpl->Format($obj["more"] , "_typografica")    );
    $this->rh->tpl->Assign("text",       $this->rh->tpl->Format($obj["text"] , "_typografica")    );
    $this->rh->tpl->Assign("subject",    $this->rh->tpl->Format($obj["subject"] , "_typografica")    );

    return $this->rh->tpl->Parse( $tpl_name );
  } 


// EOC { ListRotator }
}


?>