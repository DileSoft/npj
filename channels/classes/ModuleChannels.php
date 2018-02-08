<?php
/*

    Трансляция, RSS+Mailbox

    ModuleChannels( &$rh, $base_href, $message_set, $section_id=0, $handlers_dir="", $messageset_dir="" )
      - $message_set -- какой присоединить набор с сообщениями для вывода?
      - $section_id -- идентификатор гигантского раздела сайта (не группы внутри модуля)
      - $handlers_dir, $messageset_dir -- в замену стандартным из $rh->..

  ---------

========================================= v.1 (kuso@npj)
*/
define( "CHANNELS_NPJ_ID",      0 );
define( "CHANNELS_NPJ_ADDRESS", 1 );
define( "CHANNELS_ID",          2 );
define( "CHANNELS_STATE_OK",     0 );
define( "CHANNELS_STATE_PAUSED", 1 );
define( "CHANNELS_STATE_ERROR",  2 );

class ModuleChannels extends NpjModule
{
  var $module_name       = "ModuleChannels"; // for use in debug
  var $npz_keycode       = "channels cron";
  var $address_handler   = "channels";
  var $channel_types   = array( "rss-feed"  => "rss", 
                                "mailbox"   => "mailbox",
                                "file"      => "file",
                              );
  var $channel_classes = array( "file"    => "ChannelFile",
                                "rss"     => "ChannelRss",
                                "mailbox" => "ChannelMailbox",
                              );

  function Init( $rel_url )
  {
    $this->rh->UseClass("ChannelAbstract", $this->classes_dir);
    $this->method = "default";
    $this->params = array();
    $parts = explode("/", trim($rel_url,"/"));

    if ($this->config["subspace"])  // connected as kuso@npj:smth/channels/*
    {

      if ($this->channel_types[$parts[0]])
      {
        $this->method = "new";
        $this->params = array( "add-param" => $parts[0], "type" => $this->channel_types[$parts[0]] );
        $passthru = 0;
      } else // -------------------------------------------
      if (is_numeric($parts[0]))
      {
        $channel_data =& ChannelAbstract::_LoadStatic( &$this->rh, $parts[0], CHANNELS_ID ); 
        if ($channel_data != NOT_EXIST)
        {
          $this->method = "edit";
          $this->params = array( "id" => $parts[0], "type"=> $channel_data["channel:channel_type"] );
        }
      } else // -------------------------------------------
      if ($parts[0] == "aggregate")
      {
        $this->method = "aggregate";
        $this->params = $parts;
        array_shift($this->params);
      } else // -------------------------------------------
      if ($parts[0] == "startup")
      {
        $this->method = "startup";
      } else return NpjModule::Init( $rel_url );
    }
    else // connected by another rule.
    {
      $passthru = 1;
      if ($this->object->class == "record")
        if ($this->object->name == "channel")
        {
          $channel_data =& ChannelAbstract::_LoadStatic( &$this->rh, $this->object->npj_account, 
                                                         CHANNELS_NPJ_ADDRESS );
          // if ($this->object->method == "edit")
          {
            $this->method = "edit";
            $this->params = array( "id" => $channel_data["channel:channel_id"], "type"=> $channel_data["channel:channel_type"] );
            $passthru = 0;
          }
        }
      if ($passthru) return NpjModule::Init( $rel_url );
    }
  }

  function &SpawnChannel( $type="file", $id=-1, $is_npj_address = CHANNELS_NPJ_ID)
  {
    if (!$this->channel || ($id != -1)) 
    {
      $this->rh->UseClass("ChannelAbstract", $this->classes_dir);
      $this->channel =& ChannelAbstract::Factory( &$this, $type, $id, $is_npj_address );
    }
    return $this->channel;
  }

// EOC { ModuleChannels }
}


?>