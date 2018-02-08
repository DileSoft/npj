<?php
/*
    ObjectCache( &$config ) -- итеративный кэш объектов в пределах запроса
      - $config -- ссылка на RequestHandler, в котором содержится конфигурация проекта

  ---------
  * &Restore( $object_class, $object_id, $cache_level=0 ) -- вернуть ссылку на объект из кэша. === false, если его там нет
      - $object_class -- строка-псевдокласс объекта, например "page"
      - $object_id    -- идентификатор (желательно численный) объекта, например, "/products/ak74"
      - $cache_level  -- уровень детализации, необходимый для выполнения дальнейших операций
                         хранимый в кэше объект возвращается, только если его cache_level не меньше

  * Store( $object_class, $object_id, $cache_level, &$object, $strength=2 ) -- сохранить ссылку на объект в кэш 
      - $object_class -- строка-псевдокласс объекта, например "page"
      - $object_id    -- идентификатор (желательно численный) объекта, например, "/products/ak74"
      - $cache_level  -- уровень детализации данного объекта. Рекомендуется: 0=id, 1=id+name, 2=id+name+fkeys, 3=*
      - $object       -- сохраняемый объект
      - $strength     -- нужно ли перезаписывать, если уже есть запись в кэше
                          * 0 -- нет
                          * 1 -- только, если запись в кэше имеет меньший уровень детализации
                          * 2 -- только, если запись в кэше имеет меньший или такой же уровень детализации
                          * 3 -- в любом случае

  * Clear( $object_class="", $object_id="" ) -- очистить кэш от объекта/класса объектов/совсем
      - $object_class -- если пустой, то кэш очищается полностью
      - $object_id    -- если пустой, то очищается кэш для всего класса, иначе удаляется только один объект

  * Debug() -- выбрасывает содержимое кэша с уровнями в поток для отладки

  * в следующих версиях появится Dump / FromDump -- преобразование кэша из запросового в сеансовый


=============================================================== v.3 (Kuso)
*/
define("CACHE_LEVEL_NEVER", -5);

class ObjectCache
{
  var $config;
  var $data;
  var $levels;

  function ObjectCache( &$config )
  {
    $this->data = array();
    $this->config = &$config;
  }

  function Debug()
  {
    $this->config->debug->Trace("<h3>ObjectCache dump</h3>");
    $this->config->debug->Trace_R($this->data);
    $this->config->debug->Trace("<h3>ObjectCache dump levels</h3>");
    $this->config->debug->Trace_R($this->levels);
  }
  // прочитать объект из кэша 
  function &Restore( $object_class, $object_id, $cache_level=0 )
  {
    if ($cache_level == CACHE_LEVEL_NEVER) return false;

    if (is_array($this->levels[$object_class]))
      if (isset($this->levels[$object_class][$object_id]))
        if ($this->levels[$object_class][$object_id] >= $cache_level)
         return $this->data[$object_class][$object_id];
    return false;
  }

  // сохранить объект в кэше 
  function Store( $object_class, $object_id, $cache_level, &$object, $strength=2 )
  {
    if ($cache_level == CACHE_LEVEL_NEVER) 
    {
      $this->config->debug->Trace("<span style='color:#ff0000'>cache level never!</span>");
      return;
    }

    $level=-1;
    if (is_array($this->levels[$object_class]))
      if (isset($this->levels[$object_class][$object_id]))
        if ($this->levels[$object_class][$object_id] >= $cache_level)
         $level = $this->levels[$object_class][$object_id];

    if (($strength==3) || ($level<0) || ($level+1 < $cache_level+$strength))
    {   $this->levels[$object_class][$object_id] = $cache_level;
        $this->data  [$object_class][$object_id] = &$object;
    }
  }

  // очистить кэш от объекта/-ов класса/-ов
  function Clear( $object_class="", $object_id="" )
  {
    if ($object_class && $object_id) $this->levels[$object_class][$object_id] = -2; 
    else
    if ($object_class ) $this->levels[$object_class] = array(); 
    else                $this->levels                = array();
  }

// EOC{ ObjectCache } 
// ForR2-3: Dump, FromDump
}



?>