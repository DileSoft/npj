<?php

//  from ../config_modules.php
//  $this === $rh

  $this->modules["module-demo"] = array( 

        "subspace"          => "module-demo", // must be strict equal to modules[<SUBSPACE>] subspace
        // "subspace_root_only"=> true, // uncomment, if you want to use only module-demo@npj

        "name"              => "Демомодуль субпространства",
        "module_dir"        => "module-demo/",
        "messageset_prefix" => "DK_Demo",
        "classname"         => "ModuleDemoSubspace",

                                  ); // end of module1


  $this->modules["module-demo2"] = array( 

        "root"              => "node@comm",

        "name"              => "Демомодуль аккаунта",
        "module_dir"        => "module-demo/",
        "messageset_prefix" => "DK_Demo",
        "classname"         => "ModuleDemoAccount",

                                  ); // end of module2
?>
