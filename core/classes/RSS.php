<?php
/*
    RSS( $config_path="config/default.php"  )  -- ����� ��� ������������ RSS (� ������������ ������)
      - $config-path -- ���� � ����������������� ����� (������������ ��� � ���� � ������������)

  ---------
  * GetCached( $url ) -- ������� ������� �� ����
      - $url       -- ����� (�������������) ��������

  * GetCachedTime( $url ) -- ������� timestamp �������� �� ����
      - $url       -- ����� (�������������) ��������

  * StoreToCache( $url, $data ) -- �������� ������� � ��� 
      - $url       -- ����� (�������������) ��������
      - $data      -- ��� �������

  * CacheInvalidate ( $url ) -- �������� ���
      - $url       -- ����� (�������������) ��������

  * CheckHttpRequest() -- ��������� http-������. ��������, ����� RSS �� ����.

  * IsRss ($url) -- ��������, ������ �� �� ������ ������������ ���� ������
                    ���������� �� CheckHttpRequest()
      - $url       -- ����� �������, �� �� ������� ������������� ��������

  * &AddEntry(&$entry, $entry_type = RSS_PLAIN) -- ����� ���������� ������� ����� �������� ������ �� ���� RSS
      - $entry       -- ������ ����� ��� ��������� � RSS
      - $entry_type  -- ��� ������� �����

  * Compile($struct, $usecache = true)   -- ����� ���������� ������������ RSS.
      - $struct       -- ������ ����� �������� ������
      - $use_cache    -- ����� �� ������ ���� RSS � ���

  * Output()    -- ������� �������������� � $this->result_rss RSS.

  // ��������:
  * $this->rss_cachetime    -- ����� ������� ������� � �������� ������ � ���� ���������� ����������
  * $this->rss_cache_dir    -- ����� � �����
=============================================================== v.2 (Kukutz)
*/
define("RSS_PLAIN", 0);

class RSS
{

 var $rss_cachetime = 1200;
 var $rss_cache_dir = "rss/";


 //Constructor
 function RSS($config_path)
 {
  //��������� ������
  if(!@is_readable($config_path)) die("Cannot read local configuration.");
  require($config_path);

  if (!$this->scheme) $this->scheme = "http";

  $this->base_host = $_SERVER["HTTP_HOST"];
  $this->base_full = rtrim($this->scheme."://".$this->base_host."/".$this->base_url,"/");
  $this->base_host_http = $this->scheme."://".$this->base_host;
 }

 //������� ������� �� ����
 function GetCached($url)
 {
  $filename = $this->rss_cache_dir.md5($url);
  if (!@file_exists($filename))
    return false;
  if ((time()-@filemtime($filename)) > $this->rss_cachetime)
    return false;
  $fp = fopen ($filename, "r");
  $contents = fread ($fp, filesize ($filename));
  fclose ($fp); 
  return $contents;
 }

 //������� timestamp �������� �� ����
 function GetCachedTime($url)
 {
  $filename = $this->rss_cache_dir.md5($url);
  if (!@file_exists($filename))
    return false;
  
  if ((time()-@filemtime($filename)) > $this->rss_cachetime)
    return false;
  
  return @filemtime($filename);
 }

 //�������� ������� � ��� 
 function StoreToCache($url, $data)
 {
  if ($this->rh && $this->rh->rss_no_cache) return true; // ���������, ����������� ���

  $filename = $this->rss_cache_dir.md5($url);
  //die($filename);
  $fp = fopen ($filename, "w");
  fputs ($fp, $data);
  fclose ($fp); 
  @chmod($newname, octdec('0777'));
  return true;
 }

 //�������� ���
 function CacheInvalidate($url)
 {
  $filename = $this->rss_cache_dir.md5($url);
  if (@file_exists($filename))
   if (@unlink($filename))
    return true;
  return false;
 }

 //��������� http-������. ��������, ����� RSS �� ����.
 function CheckHttpRequest()
 {
   //�������� url
   $this->base_full = $this->scheme."://".$this->base_host."/".$this->base_url; 
   $this->base_dir = $_SERVER["DOCUMENT_ROOT"]."/".$this->base_url;

   if ($this->rewrite_mode == 2 && $_SERVER["REQUEST_METHOD"]!="POST" 
       && strpos($_SERVER["REQUEST_URI"],"/".$this->base_url)===0) 
   {
     $url = substr($_SERVER["REQUEST_URI"], strlen("/".$this->base_url));
     if (strpos($url,"?")!==false) 
     {
       $_url = explode("?", $url);
       $url = $_url[0];
       $query = $_url[1];
     }
   }
   else
   {
    $query = $_SERVER["QUERY_STRING"];
    $url = $_REQUEST["page"];
   }

   //�������� �� url "rss"
   $url = $this->IsRss($url);
   if ($url === false) return false;

   //��������� ���
   if ($mtime = $this->GetCachedTime($url)) //�������� ��������, query ���� � �����!!!
   {
     $gmt = gmdate('D, d M Y H:i:s \G\M\T', $mtime);
     $etag = $_SERVER["HTTP_IF_NONE_MATCH"];
     $lastm = $_SERVER["HTTP_IF_MODIFIED_SINCE"];

     if ($p = strpos($lastm,";")) $lastm=substr($lastm,0,$p);

     if ($_SERVER["REQUEST_METHOD"]=="GET") //���������� HEAD ???
     {
//       if (($gmt==$lastm) && ($gmt==trim($etag, '\"')))
       if (!$lastm && !$etag);
       else
       if ($lastm && $gmt!=$lastm);
       else
       if ($etag && $gmt!=trim($etag, '\"'));
       else
       {
         header ("HTTP/1.1 304 Not Modified");
         die();
       }
//       else
       {
         $rss = $this->GetCached($url);
         header ("Last-Modified: ".$gmt);
         header ("ETag: \"".$gmt."\"");
         header ("Content-Type: text/xml");
         //header ("Content-Length: ".strlen($rss));
         //header ("Cache-Control: max-age=0");
         //header ("Expires: ".gmdate('D, d M Y H:i:s \G\M\T', time()));
         echo ($rss);
         die();
       }
     }
   }
   
   //���� �� ��������, ���� � ���� ��� ������ ������
   //����� �� ������ ������� ���������� url � �������� isrss.
   //��������� ���:
   $this->url = $url;
   $this->query = $query;
   $this->isrss = true;
   return $this;
   //index.php ������ �������� � $rh->rss ��, ��� ������� ��� �-� - �� ���� ������ �� $rss ��� false
   //RH ������ ��������� $this->rss, � ���� �� �� false,
   //�� ����� $url � $query �� $this->rss->
   //� � ����� ��������� ������� ����� $this->rss->compile
 }

 //��������, ������ �� �� ������ ������������ ���� ������
 //���������� �� check_http_request()
 function IsRss($url)   
 {
  if ($url == "rss") return "";
  if (preg_match("/^(.*?)\/rss(\.xml)?$/i", $url, $matches))   //�������� ����� �������!!!
   return $matches[1];
  return false;
 }

 function &AddEntry(&$entry, $entry_type = RSS_PLAIN)
 {
  //����� ���������� ������� ����� �������� ������ �� ���� RSS
   $result = array(
     "guid"        => $entry["guid"],
     "link"        => $entry["link"],
     "title"       => $this->rh->tpl->Format($entry["title"],  "html2text"),
     "author"      => $this->rh->tpl->Format($entry["author"], "html2text"),
     "description" => $entry["description"],
     "pubDate"     => $entry["pubDate"],
     "comments"    => $entry["comments"],
     "entry_type"  => $entry_type,
   );
   if (isset($entry["sort_date"])) $result["sort_date"] = $entry["sort_date"];
   else                            $result["sort_date"] = strtotime($entry["pubDate"]);

   if (!isset($this->rss_array[ $result["guid"] ]))
     $this->rss_array[ $result["guid"] ] = &$result;

   return $result;
 }
  //[guid1] guid1, link, title, author, description, pubDate, comments, entry_type
  //[guid2] guid2, link, title, author, description, pubDate, comments, entry_type
  //[guid3] guid3, link, title, author, description, pubDate, comments, entry_type
  //{ignored} [guid1] guid1, link, title, author, description, pubDate, comments, entry_type


 // struct = array (title, link, description, language=ru, managingEditor=someone@somehost, generator=NPJ)
 function Compile($struct, $usecache = true)   
 {//����� ���������� ������������ RSS.  tpl???
  
  $rss = '<'.'?xml version="1.0" encoding="windows-1251" ?'.">\n".
         '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">'."\n".'<channel>';

  $struct["description"] = "<![CDATA[".trim($struct["description"])."]]>";
  $struct["title"] = str_replace("&", "&amp;", $this->rh->tpl->Format($struct["title"], "html2text"));
  $struct["managingEditor"] = $this->rh->tpl->Format($struct["managingEditor"], "html2text");

  foreach ($struct as $k=>$v)
   if ($v!="" && $v!="<![CDATA[]]>") 
    $rss .= "<".$k.">".$v."</".$k.">\n";

  if (@count($this->rss_array))
  {
    usort ($this->rss_array, array(&$this, "dsort")); 

    $this->last_modified = $this->rss_array[0]["sort_date"];

    foreach ($this->rss_array as $q=>$w)
    {
      $rss .= "<item>\n";
      foreach ($w as $k=>$v)
      {
        if ($k!="entry_type" && $k!="sort_date" && $v!="" && $v!="<![CDATA[]]>") 
        {
          if ($k=="guid") 
            $rss .= "<".$k." isPermaLink=\"false\">".$v."</".$k.">\n";
          else
            $rss .= "<".$k.">".$v."</".$k.">\n";
        }  
      }    
      $rss .= "</item>\n";
    }
  }

  $rss .= "</channel>\n</rss>";

  if ($usecache) $this->StoreToCache($this->url, $rss); //�������� ��������, query ���� � �����!!!

  $this->result_rss = &$rss;

  return $rss;
 }

 //��������������� ������� ����������
 function dsort (&$a, &$b) { 
   if ($a["sort_date"] == $b["sort_date"]) return 0; 
   return ($a["sort_date"] > $b["sort_date"]) ? -1 : 1; 
 } 

 function Output()
 {
   clearstatcache();
   if (!($mtime = $this->GetCachedTime($this->url))) //�������� ��������, query ���� � �����!!!
     $mtime = time();
   {
     $mtime = $this->last_modified;
     $gmt = gmdate('D, d M Y H:i:s \G\M\T', $mtime);
     $rss = &$this->result_rss;
     header ("Last-Modified: ".$gmt);
     header ("ETag: \"".$gmt."\"");
     header ("Content-Type: text/xml");
     //header ("Content-Length: ".strlen($rss));
     //header ("Cache-Control: max-age=0");
     //header ("Expires: ".gmdate('D, d M Y H:i:s \G\M\T', time()));
     echo ($rss);
     die();
   }
 }

}

?>