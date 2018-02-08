<?php

if (!function_exists("absurl"))
{
 function absUrl ($text)
 {
  $text = 
    preg_replace(
      "/(<a[^\>]*? href=)([\"\']?)([^ \\\\\"\'>]+)[\"\']?/ie", 
      "stripslashes('$1').stripslashes('$2').absurl2('$3',false).stripslashes('$2')", 
      $text
    ); 
  $text = 
    preg_replace(
      "/(<img[^\>]*? src=)([\"\']?)([^ \\\\\"\'>]+)[\"\']?/ie", 
      "stripslashes('$1').stripslashes('$2').absurl2('$3',true).stripslashes('$2')", 
      $text
    ); 
  return $text;
 }

 // uses $rh->base_host_prot, $rh->base_full, $rh->dirty_urls
 function absUrl2 ($url, $isimage)
 {
 GLOBAL $rh;
  if (!stristr ($url, "://"))  //local url. This code unwrap ../, ./ etc. Is not thoroughly tested.
  {
   if ($url{0}=="/") $localabs = true;
   $url = "/".$url;
   $url = str_replace("/./", "/", $url);
   while (stristr ($url, "//")) 
     $url = str_replace ("//", "/", $url);
   while (preg_match ("/\/([^\/\.]{1,})\/\.\.\//", $url)) 
     $url = preg_replace ("/\/([^\/\.]{1,})\/\.\.\//", "/", $url);

   $uri = parse_url($url);
   if ($rh->dirty_urls && !$isimage) 
     $url = $rh->base_full."?page=".urlencode($uri["path"]).((strlen($uri["query"])>0)?"&".$uri["query"]:"");
   else     
     $url = rtrim($localabs?$rh->base_host_prot:$rh->base_full,"/").$uri["path"].
            ((strlen($uri["query"])>0)?"?".$uri["query"]:"");
  }
  
  return $url;
 }
}


$text = absUrl($text);

print($text);

?>