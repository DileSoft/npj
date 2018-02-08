<?php
/*
    ObjectCache( &$config ) -- ����������� ��� �������� � �������� �������
      - $config -- ������ �� RequestHandler, � ������� ���������� ������������ �������

  ---------
  * &Restore( $object_class, $object_id, $cache_level=0 ) -- ������� ������ �� ������ �� ����. === false, ���� ��� ��� ���
      - $object_class -- ������-����������� �������, �������� "page"
      - $object_id    -- ������������� (���������� ���������) �������, ��������, "/products/ak74"
      - $cache_level  -- ������� �����������, ����������� ��� ���������� ���������� ��������
                         �������� � ���� ������ ������������, ������ ���� ��� cache_level �� ������

  * Store( $object_class, $object_id, $cache_level, &$object, $strength=2 ) -- ��������� ������ �� ������ � ��� 
      - $object_class -- ������-����������� �������, �������� "page"
      - $object_id    -- ������������� (���������� ���������) �������, ��������, "/products/ak74"
      - $cache_level  -- ������� ����������� ������� �������. �������������: 0=id, 1=id+name, 2=id+name+fkeys, 3=*
      - $object       -- ����������� ������
      - $strength     -- ����� �� ��������������, ���� ��� ���� ������ � ����
                          * 0 -- ���
                          * 1 -- ������, ���� ������ � ���� ����� ������� ������� �����������
                          * 2 -- ������, ���� ������ � ���� ����� ������� ��� ����� �� ������� �����������
                          * 3 -- � ����� ������

  * Clear( $object_class="", $object_id="" ) -- �������� ��� �� �������/������ ��������/������
      - $object_class -- ���� ������, �� ��� ��������� ���������
      - $object_id    -- ���� ������, �� ��������� ��� ��� ����� ������, ����� ��������� ������ ���� ������

  * Debug() -- ����������� ���������� ���� � �������� � ����� ��� �������

  * � ��������� ������� �������� Dump / FromDump -- �������������� ���� �� ����������� � ���������


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
  // ��������� ������ �� ���� 
  function &Restore( $object_class, $object_id, $cache_level=0 )
  {
    if ($cache_level == CACHE_LEVEL_NEVER) return false;

    if (is_array($this->levels[$object_class]))
      if (isset($this->levels[$object_class][$object_id]))
        if ($this->levels[$object_class][$object_id] >= $cache_level)
         return $this->data[$object_class][$object_id];
    return false;
  }

  // ��������� ������ � ���� 
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

  // �������� ��� �� �������/-�� ������/-��
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