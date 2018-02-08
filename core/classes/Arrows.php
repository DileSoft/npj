<?php
/*
    Arrows( &$state, $where, $table="", $page_size=10, $page_frame_size=10, $prefix="")  -- �������� ��� ������� �� �� ��� �� �������
      - $state -- ������ StateSet, ���������� �������� ������ �� ������
      - $where -- WHERE clause ��� ������������ SQL-�������
      - $table -- �������� ������� � ��, ���� ������, �� $where ��������������� ��� ������ ������
                  (NB: ������� ��� ��������, �������� "users")
      - $page_size -- ������ ����� ��������
      - $page_frame_size -- ������ "����" ������ �������
      - !!! -- �� ������������� �� pagesize="" (view all)
      - ��������� �� ((/���������/����������/StateSet))

  ---------

  * Parse( $tpl_filename, $store_to=NULL ) -- ����� ����� ������� � ��������� "���������"
      - $tpl_filename -- ��� ����� � ���������� �������� ��� �����
      - $store_to     -- ���� �����������, �� ��������� ����� ����������� � ���������� ������ � ����� ������
  * ParsePageSizes( $tpl_filename, $store_to=NULL ) -- ����� ��������� ������� �������
      - $tpl_filename -- ��� ����� � ���������� �������� ��� �����. ����� ������ ":PageSizes"
      - $store_to     -- ���� �����������, �� ��������� ����� ����������� � ���������� ������ � ����� ������

  // ��������� �������� ��������� �������
  * GetItemCount()      -- ������� ����� � ����������
  * GetPageCount()      -- ������� ������� ��� ��
  * GetPageSize()       -- ������ ��������
  * GetPageNo()         -- �� ����� �� ������ ���������
  * GetPageFrameCount() -- ������� "����" � ������ �������
  * GetPageFrameSize()  -- ������� ������� ����� � "����"
  * GetPageFrameNo()    -- � ����� "����" �� ���������

  // ���������� ��� ADOdb -- DBAL
  * GetSqlLimit()  -- ����������� LIMIT
  * GetSqlOffset() -- �������� OFFSET

  // ���������� ������
  * _GetSqlData( $where, $table ) -- �������� �� sql ���������� � ���-�� �������
      - $where, $table -- ������� �� ������������
  * _FillValues()                 -- ��������� ���������� ����������, ���������������� � ����������� �� stateset

  // �������� ������
  * $this->block_page_size       -- �� ������ ������ ������ �������� �� _GET
  * $this->block_page_frame_size -- �� ������ ������ ������ ���� �� _GET
  * $this->page_frame_slip       -- ���� ���������� � false, �� ����� ���� ���������� �������������.
                                    ��-��������� �� -- �� ���.
  * $this->implode               -- ������������ List_Separator �������� � ������ �������

=============================================================== v.9 (Kuso)
*/

class Arrows extends StateSet
{
   var $tpl;

   var $where;
   var $table;
   var $page_size;
   var $page_frame_size;
   var $prefix;

   var $_itemcount;
   var $_pageno;
   var $_pagesize;
   var $_pagecount;
   var $_pageframeno;
   var $_pageframesize;
   var $_pageframecount;

   var $page_frame_slip = true; // "����������" �����

   // ���������� ��������� ������������� �������� �������� � ����
   var $block_page_size       = false;
   var $block_page_frame_size = false;

   // ����� ��������
   var $pagesizes = array( "10", "20", "50" );


   function Arrows( &$state, $where, $table, $page_size=10, $page_frame_size=10, $prefix="" )
   {
     StateSet::StateSet( &$state->rh, $state->q, $state->s, &$state );

     $this->tpl = &$state->rh->tpl;

     $this->where = $where;
     $this->table = $table;
     $this->page_size = $page_size;
     $this->page_frame_size = $page_frame_size;
     $this->prefix = $prefix;

     if ($table == "") $this->_itemcount = $this->where;
     else $this->_itemcount = $this->_GetSqlData( $where, $table );
     $this->_FillValues();
   }


   function _GetSqlData( $where, $table )
   {
     $sql = "SELECT count(*) as amount FROM ".$this->rh->db_prefix.$table." WHERE ".$where;
     $rs = $this->rh->db->Execute( $sql );
     if (!$rs || ($rs->RecordCount() == 0) )
     { 
       $this->rh->debug->Trace("ARROWS: Suspicious recordset { $table, $where }");
       return 0;
     }
     return $rs->fields["amount"];
   }

   function _FillValues()
   {
     // �� ����� �����: itemcount
     // defaults:
     $this->_pagesize = $this->page_size;
     $this->_pageframesize = $this->page_frame_size;
     $this->_pageno = 1;
     $this->_pageframeno = 1;

     // adjust size
     if (!$this->block_page_size) 
       if ($this->Get( $this->prefix."pagesize" ))
        $this->_pagesize = $this->Get( $this->prefix."pagesize" );
     if (!$this->block_page_frame_size) 
       if ($this->Get( $this->prefix."framesize" ))
         $this->_pageframesize = $this->Get( $this->prefix."framesize" );

     // set counts
     if ($this->_itemcount && $this->_pagesize)
     {
       if ($this->_itemcount > $this->_pagesize)      
         $this->_pagecount = ceil( $this->_itemcount / $this->_pagesize );
       if ($this->_pageframesize)
       if ($this->_pagecount > $this->_pageframesize) 
         $this->_pageframecount = ceil( $this->_pagecount / $this->_pageframesize );
     } 

     // adjust positions
     $this->_pageno = $_REQUEST[ "_".$this->prefix."pageno" ];
     if (!$this->_pageno) $this->_pageno = 1;
     if ($this->_pageno > $this->_pagecount) $this->_pageno = $this->_pagecount;
     if ($this->_pageframesize)
      $this->_pageframeno = floor(($this->_pageno-1) / $this->_pageframesize +1);
     else 
      $this->_pageframeno = 1;
     if ($this->_pageframesize)
     if ($this->_pageframeno > $this->_pageframesize) $this->_pageframeno = $this->_pageframesize;

   }

  function GetItemCount()       { return $this->_itemcount; }
  function GetPageCount()       { return $this->_pagecount; }
  function GetPageSize()        { return $this->_pagesize; }
  function GetPageNo()          { return $this->_pageno; }
  function GetPageFrameCount()  { return $this->_pageframecount; }
  function GetPageFrameSize()   { return $this->_pageframesize; }
  function GetPageFrameNo()     { return $this->_pageframeno; }

  function GetSqlOffset()       { return ($this->_pagesize?($this->_pagesize*($this->_pageno-1)):-1); }
  function GetSqlLimit()        { return ($this->_pagesize?$this->_pagesize:-1); }

  function Parse( $tpl_filename, $store_to = NULL )
  {
    $tpl = &$this->tpl;
    $debug = &$this->rh->debug;
    $this->rh->UseClass("ListObject", $this->rh->core_dir);

    $no = $this->GetPageNo();
    $size = $this->GetPageSize();
    $count = $this->GetPageCount();

    $fno = $this->GetPageFrameNo();
    $fsize = $this->GetPageFrameSize();
    $fcount = $this->GetPageFrameCount();
    if (!$fsize) $fsize = $count*20+20;

    // ����� ��� ���� ��������
    $tpl->LoadDomain( array(
     "ItemCount"       => $this->GetItemCount(),
     "PageFrameNo"      => $fno,
     "PageFrameSize"    => $fsize,
     "PageFrameCount"   => $fcount,
     "PageNo"      => $no,
     "PageSize"    => $size,
     "PageCount"   => $count,

     "PageFirst" => 1,                       "Link:PageFirst" => $this->Plus( "_".$this->prefix."pageno" , 1 ),
     "PageLast"  => $count,                  "Link:PageLast"  => $this->Plus( "_".$this->prefix."pageno" , $count ),
     "PageNext"  => ($count>$no)?($no+1):"", "Link:PageNext"  => $this->Plus( "_".$this->prefix."pageno" , $no+1 ),
     "PagePrev"  => ($no>1)?($no-1):"",      "Link:PagePrev"  => $this->Plus( "_".$this->prefix."pageno" , $no-1 ),

     "PageFrameNext"  => ($fcount>$fno)?($fno+1):"", "Link:PageFrameNext" => $this->Plus( "_".$this->prefix."frameno" , $fno+1 ),
     "PageFramePrev"  => ($fno>1)?($fno-1):"",       "Link:PageFramePrev" => $this->Plus( "_".$this->prefix."frameno" , $fno-1 ),
                    ) );

    // ������ ������ �������
    $pages = array();

    if ($this->page_frame_slip)
    {
      $i = $no-$fsize/2;
      $endi = $i+$fsize;
      if ($i<1) 
      { $i=1; $endi = $fsize; }
      if ($endi > $this->_pagecount) 
      { $i=$this->_pagecount-$fsize; 
        if ($i<1) $i=1;
        $endi = $this->_pagecount; 
      }
    }
    else
    {
      $i = ($fno-1)*$fsize;
      $endi = $i+$fsize;
      if ($i<1) { $i=1; $endi = $fsize; }
      if ($endi > $this->_pagecount) $endi = $this->_pagecount;
    }


    for (; $i<=$endi; $i++)
    {
      $pages[$i]["_Current"] = $i == $this->_pageno;
      $pages[$i]["_PageNo"] = $i;
      $pages[$i]["Link:_PageNo"] = $this->Plus( "_".$this->prefix."pageno", $i);
      $pages[$i]["_First"] = ($i-1)*$size;
      $pages[$i]["_Last"]  = ($i)*$size;
      if ($pages[$i]["_Last"] > $this->_itemcount) $pages[$i]["_Last"] = $this->item_count;
    }
    $list = &new ListObject( &$this->rh, &$pages );
    $list->tpl = &$tpl;
    $list->implode = $this->implode; // 20-12-2004 max@ : ��������� ����������� ������
    $list->Parse( $tpl_filename.":PagesList" , "PagesList" );
    
    // ������ ������ ����

    // !!! �� ����������� � R1

    // ������ ������ $tpl_filename:Arrows
    if (sizeof($pages) < 2)
      return $tpl->Parse( $tpl_filename.":Arrows_Empty", $store_to );
    else
      return $tpl->Parse( $tpl_filename.":Arrows", $store_to );
  }

  // ����� ��������� ������� �������
  function ParsePageSizes( $tpl_filename, $store_to=NULL )
  {
    $this->rh->tpl->LoadDomain( array(
        "_Prefix" => $this->prefix,
        "Form"    => $this->FormStart( MSS_GET, $this->rh->url ),
                    )      );
    $size = $this->GetPageSize();

    $data = array();
    foreach( $this->pagesizes as $v )
    {
      $data[] = array(
                  "_Value"   => $v,
                  "_Current" => $v == $size,
                     );
    }
    $this->rh->UseClass("ListObject", $this->rh->core_dir);
    $list = &new ListObject( &$this->rh, $data );
    return $list->Parse( $tpl_filename.":PageSizes", $store_to );
  }

// EOC{ Arrows } 
}



?>