<?php


$communities = array();
foreach ($params as $v)
 if ($v) $communities[] = $v;

 // не забыть сбросить в пост-то текущий аккаунт

return $this->Handler( "post", 
    array("announce"=>1, "announce_to"=>$communities, "post_to"=>$communities), 
                        &$principal );

?>