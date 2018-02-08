<?php

  $root = $tpl->GetValue("/");
  $ms = &$tpl->message_set;
  $su = $rh->utility["skin"];
  $count = $su->context_panelcount;

  for( $i=0; $i<$count; $i++)
  {
    if ($i == 0) $color = "2"; else $color = "3";

    switch ($i)
    {
     case 0:
             $panel_action = $su->context_panel1;
             $panel_param  = $su->context_panel1_param;
             break;
     case 1:
             $panel_action = $su->context_panel2;
             $panel_param  = $su->context_panel2_param;
             break;
    }

    $panel_name = $ms["Actions"][$panel_action];
    $params = array("style"=>"context","wrapper"=>"none");
    if (isset($rh->context_params[$panel_action]) )
     $params = array_merge( (array)$rh->context_params[$panel_action], (array)$params );
    if ($panel_action == "search" && $panel_param) $params["form"] = 0;

    if ($_COOKIE["flip_context".$i]=="down") $flip="down";
    else $flip="up";
    ?>
    <div title="<?php echo $ms["Flip.Down.One"]?>" id="context<?php echo $i; ?>_flip_down" 
         style="cursor: hand;<?php if ($flip=="up") echo "display:none";?>" onclick="flipanel('context<?php echo $i; ?>',0);">
     <div class="context_titleX<?php echo $color; ?>"><?php echo $panel_name;?></div>
     <div class="context_bg<?php echo $color; ?>"><?php
       echo '<a onclick="flipanel(\'context'.$i.'\',0);return false;" href="// '.$ms["Flip.Down.One"].' //" title="'.$ms["Flip.Down.One"].'"><img vspace=0 src="'.$tpl->GetValue("images").'tab_2.gif" width="200" height="7" alt="" title="'.$ms["Flip.Down.One"].'" border="0" /></a>';
     ?></div></div>
    <div id="context<?php echo $i; ?>_flip_up" style="<?php if ($flip=="down") echo "display:none";?>">
     <div title="<?php echo $ms["Flip.Up.One"]?>" onclick="flipanel('context<?php echo $i; ?>',1);" 
          class="context_title<?php echo $color; ?>"><?php echo $panel_name;?></div>
     <div class="context_bg<?php echo $color; ?>"><?php
       $f=0; 
 
           echo $rh->object->Action( $panel_action, 
                                     $params, 
                                     &$rh->principal );

     ?><div class="full_"><a href="<?php 
       $o = &$rh->object;
       if ($o->record) $o = &$o->record;
       echo $o->Href($o->npj_object_address."/".$panel_action, NPJ_ABSOLUTE );
     ?>"><?php echo $ms["ContextMenu.Full"]; ?></a></div><?php       
 
         echo '<a onclick="flipanel(\'context'.$i.'\',1);return false;" href="// '.$ms["Flip.Up.One"].' //" title="'.$ms["Flip.Up.One"].'"><img vspace=0 src="'.$tpl->GetValue("images").'tab_2_.gif" width="200" height="7" alt="" title="'.$ms["Flip.Up.One"].'" border="1" /></a>';
     ?></div></div>
    <?php


  }


?>