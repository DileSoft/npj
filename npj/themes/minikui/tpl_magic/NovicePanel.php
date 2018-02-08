<?php
  $su = $rh->utility["skin"];
  $context_size = $su->context_panelcount;
  if ($context_size >0) $tab = "3"; else $tab="2";

  $root = $tpl->GetValue("/");
  $ms = &$tpl->message_set;
//  if ($tpl->GetValue("404")) return;
if ($_COOKIE["flip_novice"]=="down") $flip="down";
else $flip="up";
//echo "flip=".$flip;
// ---------------------------------------------------------------------------------------------
?>
<div id="novice_flip_down" style="cursor: hand;<?php if ($flip=="up") echo "display:none";?>" onclick="flipanel('novice',0);">
<img align="right" hspace="5" src="<?php echo $tpl->GetValue("images");?>bulb.gif" width="20" height="30" alt="" border="0" />
<div class="novice_title2">
<?php echo $ms["NovicePanel"];?><br clear="all" />
</div>
<div class="novice_bar">
<?php
  echo '<a onclick="flipanel(\'novice\',0);return false;" href="// '.$ms["Flip.Down.One"].' //" title="'.$ms["Flip.Down.One"].'"><img src="'.$tpl->GetValue("images").'tab_'.$tab.'.gif" width="200" height="7" alt="" title="'.$ms["Flip.Down.One"].'" border="0" /></a>';
?>
</div>
</div>
<?php
// ---------------------------------------------------------------------------------------------
?>
<div id="novice_flip_up" style="<?php if ($flip=="down") echo "display:none";?>">
<div style="cursor: hand;" onclick="flipanel('novice',1);">
<img align="right" hspace="5" src="<?php echo $tpl->GetValue("images");?>bulb.gif" width="20" height="30" alt="" border="0" />
<div class="novice_title">
<?php echo $ms["NovicePanel"];?><br clear="all" />
</div>
</div>
<div class="novice_bg">
<?php
  $hide = array(/* "delete"=>1 */);

  $panel = array(); $g=0;
  foreach ( $su->panel["panel"] as $k=>$v )
   if (!isset($hide[$k])) { $panel[$k]=$v; if ($su->panel["granted"][$v]) $g=1; }

  if ($g==0) 
  { 
    echo "<div class=\"link_\">Судя по всему, ничего конкретного. Попытайте счастья на&nbsp;другой странице</div>";
  } else
  echo $su->ParsePanel( $su->panel["granted"], $panel, $su->panel["base"],
                        $su->panel["links"], $su->panel["method"], $su->panel["Name"],
                        "panel.html:Novice_Item","","","" );
?>
</div>
<?php
  echo '<div class="novice_bar"><a onclick="flipanel(\'novice\',1);return false;" href="// '.$ms["Flip.Up.One"].' //" title="'.$ms["Flip.Down.One"].'"><img src="'.$tpl->GetValue("images").'tab_'.$tab.'_.gif" width="200" height="7" alt="" title="'.$ms["Flip.Up.One"].'" border="0" /></a></div>';
?>
</div>
