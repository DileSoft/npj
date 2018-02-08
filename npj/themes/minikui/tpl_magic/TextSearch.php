<?php

  $root = $tpl->GetValue("/");
  $ms = &$tpl->message_set;

  {
    $i="search";
    $color=2;
    if ($i == 0) $color = "2"; else $color = "3";

    $panel_action = "search";
    $panel_name = $ms["Actions"][$panel_action];
    $params = array("style"=>"context","wrapper"=>"none");

    if ($_COOKIE["flip_context".$i]=="down") $flip="down";
    else $flip="up";
    ?>
    <div title="<?php echo $ms["Flip.Down.One"]?>" id="context<?php echo $i; ?>_flip_down" 
         style="cursor: hand;<?php if ($flip=="up") echo "display:none";?>" onclick="flipanel('context<?php echo $i; ?>',0);">
     <div class="context_titleX<?php echo $color; ?>"><?php echo $panel_name;?></div>
    </div>
    <div id="context<?php echo $i; ?>_flip_up" style="<?php if ($flip=="down") echo "display:none";?>">
     <div title="<?php echo $ms["Flip.Up.One"]?>" onclick="flipanel('context<?php echo $i; ?>',1);" 
          class="context_title<?php echo $color; ?>"><?php echo $panel_name;?></div>
     <div class="context_bg<?php echo $color; ?>"><?php
       $f=0; 
 
           echo $rh->account->Action( $panel_action, 
                                     $params, 
                                     &$rh->principal );

     ?></div></div>
    <?php


  }


?>