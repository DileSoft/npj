<?php

  $this->modules = array();
  $this->modules_dir = "";   // путь к месту, где лежат модули

  // module TRAKO
  include( "trako/npj_config.php" );

  // module CHANNELS
  include( "channels/npj_config.php" );

  // module HTMLAREA
  // include( "htmlarea/npj_config.php" );

  // DEMO MODULES
  // include( "module-demo/npj_config.php" );

  // SIMPLIFICA
  // include( "simplifica/npj_config.php" );

  // module AUTHORIZE 
  // include( "authorize/npj_config.php" );

  // extending npj_spaces --------------------------------------------------------------------------------
  if (is_array($this->modules))
  {
    $npjadd="";
    foreach($this->modules as $module_id=>$module)
    {
      if ($module["subspace"])
      $npjadd .= $module["subspace"]."|";

      if ($module["root"])
         $this->NPJ_ROOT_SPACES[$module["root"]] = $module_id;

      if ($module["as_foreign"])
      {
        if (!is_array($module["as_foreign"])) $module["as_foreign"] = array($module["as_foreign"]);
        foreach($module["as_foreign"] as $node)
         $this->NPJ_QUASI_NODES[ $node ] = $module_id; 
      }
    }
   
    $this->NPJ_SPACES          = substr( $this->NPJ_SPACES, 0, 1 ).
                                 $npjadd.
                                 substr( $this->NPJ_SPACES, 1 );
    $this->REGEX_NPJ_SPACES    = '/^(.*)\/'.$this->NPJ_SPACES.'\/(.*?)$/i';
  }

?>