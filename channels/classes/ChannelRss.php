<?php
/*

    Трансляция, один канал RSS

    ChannelRss( &$module )
      - $module -- модуль ModuleChannels

  ---------
========================================= v.1 (kukutz@npj)
*/

class ChannelRss extends ChannelAbstract
{
  var $templates = array(
          "subject"   => "{subject}",
          "body"      => "{body}<div align=\"right\"><small><a href=\"{original}\">Оригинал</a> | <a href=\"{comments}\">Комментарии там</a></small></div>",
          "body_post" => "{description}<div align=\"right\"><small><a href=\"{original}\">Оригинал</a> | <a href=\"{comments}\">Комментарии там</a></small></div>",
                        );

  function &ComposeFormGroup()
  {
    $rh = &$this->rh;
    $channel = &$this;
    $group2 = array();
    $rh->UseClass("FieldPassword",       $rh->core_dir);

    $group2[] = &new FieldString( &$rh, array(
                          "field"   => "rss_url",
                          "default" => $channel->data["channel:source"],
                          "nessesary" => 1,
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Described",
                          "http" => 1,
                          "regexp" => "/^http\:/",
                          "regexp_help" => "Адрес должен начинаться с http://",
                           ) ); 

    $group2[] = &new FieldString( &$rh, array(
                          "field"   => "rss_login",
                          "default" => $channel->data["channel:access_login"],
                          "nessesary" => 0,
                          "db_ignore" => 1,
                          "tpl_data" => "field_string.html:Plain",
                           ) ); 

    $group2[] = &new FieldPassword( &$rh, array(
                          "field"   => "rss_pwd",
                          "default" => $channel->data["channel:access_pwd"],
                          "nessesary" => 0,
                          "db_ignore" => 1,
                          "tpl_row" => "form.html:Row_Described",
                           ) ); 

    // build form group
    return $group2;
  }

  // мапим поля из формы для сохранения в БД
  function MapFormToChannel (&$form, &$channel_data)
  {
    $channel_data[ "source" ]     = $form->hash["rss_url"]->data;
    $channel_data[ "formatting" ] = "rawhtml";

    $channel_data[ "access_login" ] = $form->hash["rss_login"]->data;
    $channel_data[ "access_pwd"   ] = $form->hash["rss_pwd"]->data;
    $channel_data[ "access_more"  ] = "";
  }

  function _Post( $record_data )
  {
    $rh = &$this->rh;
    $db = &$rh->db;

    $record_id = ChannelAbstract::_Post( $record_data );

    // make saves to its own custom tables
     $sql = "INSERT INTO ".$rh->db_prefix."channels_items_rss (record_id,link,comments,channel_id) VALUES (".
       $db->Quote($record_id).", ".
       $db->Quote($record_data["link"]).", ".
       $db->Quote($record_data["comments"]).", ".
       $db->Quote($this->data["channel:channel_id"]).
       ")";
     $db->Execute($sql);

    return $record_id; 
  }

  // returns array(..) or ERROR-CONSTANT
  function _GetChannelContents()
  {
    $rh = &$this->rh;
    $db = &$rh->db;

    // 1. get XML
    $url = $this->data["channel:source"];
    $more = explode("\n", $this->data["channel:access_more"]);
    $lastModified = $more[0];
    $eTag = $more[1];

    $rh->UseLib("Net_Socket", "PEAR");
    $rh->UseLib("Net_URL", "PEAR");
    $rh->UseLib("HTTP_Request", "PEAR");

    $req = &new HTTP_Request($url);
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    $req->addHeader("If-Modified-Since", $lastModified);
    $req->addHeader("If-None-Match", $eTag);
    $req->sendRequest();
    $code = $req->getResponseCode();
    $headers = $req->getResponseHeader();

    if ($code=="304") //not modified
      return array();
    else if ($code=="200") ;//ok
    else 
      return $code;

    $response = $req->getResponseBody();
    // 2. parse XML

    $rh->UseLib("HTMLSax");
    $rh->UseLib("MagpiePlus");
    $rss = &new MagpiePlus( $response );

    $sql = "select guid_hash from ".$rh->db_prefix."channels_items where channel_id = ".$this->data["channel:channel_id"]." order by record_id DESC";
    $rs  = $db->SelectLimit( $sql, 100 ); //can be increased
    $a   = $rs->GetArray();
    $guids = array();
    if (is_array($a))
     foreach ($a as $line) $guids[] = $line["guid_hash"];

    //$rh->debug->Trace_R($rss->channel);
    //$rh->debug->Error_R($rss->items);
    foreach ($rss->items as $item)
    {
      $hash = md5( $item["guid"]?$item["guid"]:$item["title"] );

      if (!in_array($hash, $guids))
       $result[] = array(
                "subject"        => $item["title"],
                "body"           => $item["content"]?$item["content"]:$item["description"],
                "description"    => $item["description"]?$item["description"]:$item["content"],
                "user_datetime"  => $item["date"],

                "author"         => $item["author"],
                "guid_hash"      => $hash,

                "link"          => $item["link"],
                "original"      => $item["link"],
                "comments"      => $item["comments"]?$item["comments"]:$item["link"],
                    );

    }

    //3. update channel data
    $TE = &$this->module->GenerateTemplateEngine();
    $TE->LoadDomain( $rss->channel );
    $TE->Assign( "channel:source", $this->data["channel:source"] );

    $account_data["bio"] = $TE->Parse( "rss_profile.wacko" );
    $account_data["user_id"] = $this->data["user_id"];
    $channel_data["access_more"] = $headers["last-modified"]."\n".$headers["etag"];

    $this->Save($channel_data, $account_data);

    return $result;
  }

// EOC { ChannelRss }
}


?>