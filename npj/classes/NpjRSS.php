<?php
define("RSS_FEED",            1);
define("RSS_CHANGES",         2);
define("RSS_FACET",           3);
define("RSS_SEARCH",          4);
define("RSS_FORUM",           5);
define("RSS_ACCOUNTS_LAST",   6);
define("RSS_COMMENTS",        7);

class NpjRSS extends RSS
{

 var $rss_cachetime = 1200;
 var $rss_cache_dir = "rss/";


 //Constructor
 function NpjRSS($config_path)
 {
  RSS::RSS($config_path);
 }

 function &AddEntry(&$entry, $entry_type = RSS_FEED)
 {
//   $entry["Href:tag"] = trim($entry["Href:tag"], "/");
   $result = array(
     "guid"        => $entry["supertag"], // $this->base_host_http.$entry["Href:tag"],
     "link"        => $this->base_host_http.$entry["Href:tag"],
     "title"       => str_replace("&", "&amp;", strip_tags($entry["type"]==RECORD_POST?$entry["non_empty_subject"]:$entry["tag"])),
     "author"      => $entry["edited_user_name"]." ".$entry["edited_user_login"]."@".$entry["edited_user_node_id"],
     "description" => "<![CDATA[".trim(
                       $this->rh->tpl->Format(
                        $entry["body_post"], "absurl"
                       )
                      )."]]>",
     "pubDate"     => gmdate('D, d M Y H:i:s \G\M\T', strtotime($entry["datetime"])),
     "comments"    => isset($entry["Href:comments"])?$entry["Href:comments"]:
                      ($this->base_host_http.$entry["Href:tag"]."/comments"),
     "sort_date"   => strtotime($entry["datetime"]),
//     "entry_type"  => $entry_type,
   );
   $result = RSS::AddEntry( &$result, $entry_type );
   return $result;
 }
  //[0] guid, link, title, author, description, pubDate, comments, entry_type


 // struct = array (title, link, description, language=ru, managingEditor=someone@somehost, generator=NPJ)
 function Compile( &$npj_object, $usecache = true)   
 {
   if ($npj_object->class == "account")
    return $this->_CompileAccount( &$npj_object, $use_cache );
   if ($npj_object->class == "record")
    return $this->_CompileRecord( &$npj_object, $use_cache );
 }

 function _CompileRecord( &$npj_object, $usecache=true )
 {
   $data  = $npj_object->Load(3);
   $data2 = $npj_object->_LoadById($data["user_id"], 2, "account");
   $managingEditor = $data2["user_name"]." ".$data2["login"]."@".$data2["node_id"];

   $description = $data["body_post"]; 
   // рипаем?
   if (strlen($description) > $npj_object->rh->rss_comment_parent_maxsize*1024) 
   {
     $description = 
        $npj_object->Format( 
        $npj_object->Format( $description, "auto_abstract", 
            array("default"  => $npj_object->rh->rss_comment_parent_maxsize*1024,
                  "supertag" => $data["supertag"],
                 )
                           )
                 ,"absurl" );
   }
   return RSS::Compile(
     array(
           "title"=>          ($data["subject"]!="")?$data["subject"]
                                                    :($npj_object->npj_account.":".$data["tag"]),
           "link"=>           $this->base_full.$this->url, 
           "description"=>    $description,
           "language"=>       "ru", 
           "managingEditor"=> $managingEditor,
           "generator"=>      "NetProjectJournal",
         ), $usecache);  
 }

 function _CompileAccount( &$npj_object, $usecache=true )
 {
  // здесь происходит формирование информации о структуре RSS.
  $data = &$npj_object->Load(2);
  // если account > 0, это не юзер
  if ( $data["account_type"] > 0 )
  {
    $data2 = $npj_object->_LoadById($data["owner_user_id"], 2, "account");
    $managingEditor = $data2["user_name"]." ".$data2["login"]."@".$data2["node_id"];
  }
  else
    $managingEditor = $data["user_name"]." ".$data["login"]."@".$data["node_id"];


  // <image>...</image>
  $account_data = $npj_object->_Load( $npj_object->npj_account, 2, "account" );
  $image_struct = array(
    "url"   => $npj_object->rh->base_host_prot.$npj_object->rh->user_pictures_dir.$account_data["user_id"]."_big_".$account_data["_pic_id"].".gif",
    "title" => $account_data["journal_name"],
    "link"  => $npj_object->rh->base_host_prot.$npj_object->Href( $npj_object->npj_account ),
                        );
  $image = "";
  foreach ($image_struct as $k=>$v)
   if ($v!="" && $v!="<![CDATA[]]>") 
    $image .= "<".$k.">".$v."</".$k.">\n";

  return RSS::Compile(
    array(
           "title"=>($data["object_tag"]=="")?$account_data["journal_name"]:
                     ($data["login"]."@".$data["node_id"].":".$data["object_tag"]),
           "link"=>$this->base_full.$this->url, 
           "description"=>($data["object_tag"]=="")?$account_data["journal_desc"]
                                                   :$account_data["journal_name"],
           "language"=>"ru", 
           "managingEditor"=>$managingEditor,
           "generator"=>"NetProjectJournal",
           "image" => $image,
         ), $usecache);
 }

}

?>