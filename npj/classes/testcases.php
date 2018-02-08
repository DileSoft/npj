<?php

 $str = array (

  "in/devnews/by/kuso@npj:265012",

  "in/mega/by/kuso@synpj:4565",
  "in/mega/by/kuso@synpj:80832",
  "in/mega/by/kuso@synpj",
  "in/mega/by/kuso@synpj:80832/comments",

  "pixelapes-henry@rss",

  "mynews@rss/synpj:30890",
  "mynews@rss/comm:profile",

  "project@comm:trako",
  "project@comm:trako/add",
  "project@comm:section/trako/add",
  "project@comm:sub/section/trako/add",
  "project@comm:trako/212",
  "project@comm:trako/212/edit",
  "project@comm:trako/212/comments",
  "project@comm:trako/212/comments/8712",
  "project@comm:trako/212/comments/add",
  "project@comm:trako/212/comments/8712/add",
  "project@comm:trako/212/comments/8712/add/9999",

  "manage@npj",
  "manage@npj:users",
  "aqaqaq@npj:add/test/shmest" ,                        
  "kukutz" ,                            
  "kukutz:" ,                            
  "kukutz@npj" ,                         
  "kukutz@npj:/feed" ,                        
  "kukutz@npj:" ,                        
  "kukutz@npj:test" ,                    
  "kukutz@npj:test/shmest" ,             
  "kukutz@npj:“ест" ,                    
  "ку уц@нѕж:“е—т/едит/парам1/парам2" ,  
  "ку уц@нѕж:пост/парам1/парам2" ,       
  "kukutz@npj:test/comments" ,           
  "kukutz@npj:test/comments/25" ,        
  "kukutz@npj:test/comments/25/add" ,    
  "kukutz@npj:test/comments/25/add/512" ,
  "kuso@npj:ѕроверкајдреса/√де≈сть/ акие“о ластеры",
  "kuso@npj:ѕроверкајдреса/√де≈сть/ акие“о ластеры/comments/25",
  "kuso@npj:ѕроверкајдреса/√де≈сть/ акие“о ластеры/versions/25/diff/7",
  "kuso@npj:comments/25/add/7",
  "kuso@npj:3098",
  "kuso@npj:3098_‘л€н€159_тест",
  "kuso@npj:3098_‘л€н€159_тест/comments/10/add",
  "kuso@npj:3098_‘л€н€159_тест/edit",
  "kuso@npj:ѕроверка/add/-Proverka-English",
  "kuso@npj:—ъешь≈щЄЁтихЅулокЌочью",
 );

 
 $this->debug->Trace( "<h1>NPJ address syntax test</h1>" );
 for ($i=0;$i<count($str);$i++)
 {
  $o = &new NpjObject( &$this, $str[$i] );
  $this->debug->Trace( "----" );
  $o->_Trace($i.":".$str[$i]);
//  $this->debug->Error( $o->Link( $str[$i] ));
 }

 $this->debug->Error( "TESTCASES: done" );

?>
