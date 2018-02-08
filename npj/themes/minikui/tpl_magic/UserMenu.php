<?php
  $root = $tpl->GetValue("/");
  $ms = &$tpl->message_set;
  if ($rh->principal->data["user_id"] == 1) 
  { ?>
<div class="right_bg">
<div class="right_titleX"><?php echo $ms["PleaseLogin"];?></div>
<div class="user_login_table">
<table border=0 cellspacing=5 align="center" cellpadding="0">
<?php echo $state->FormStart( MSS_POST, "login", "id=loginFormMinikui name=loginFormMinikui" ); ?>
<input type="hidden" name="freturn" value="<?php echo $rh->Href( $rh->url, STATE_IGNORE );?>" />


<tr><td><?php echo $ms["Login"];?>:</td>
    <td><input type=text size=15 name=_flogin value='<?php echo ($principal->cookie_login?$principal->cookie_login:"");?>'></td>
</tr>
<tr><td><?php echo $ms["Password"];?>:</td>
    <td><input size=15 type=password name=_fpassword value=''></td>
</tr>
<tr><td>&nbsp;</td>
    <td class="_small"><input type=submit value='<?php echo $ms["DoLogin"];?>'>
    </td>
</tr>
<tr>
    <td colspan="2" class="_small">
    <input id=id_fpermanent2 type=checkbox name="_logout_at" value="cookie" checked="checked" /><label for=id_fpermanent2>запомнить вход</label>
    </td>
</tr>
<tr>
    <td colspan="2" class="_small">
    <input id=id_freturn type=checkbox name=freturn2 checked><label for=id_freturn>вернуться после авторизации</label>
    </td>
</tr>
<?php if (1==2) {
?>
<tr>
    <td colspan="2" class="_small">
    <input id=idCookie2 type=checkbox name=_save_password ><label for=idCookie2><?php echo $ms["StorePassword"];?></label>
    </td>
</tr>
<?php } ?>
</form>
</table>
</div>
</div>
<?php }  { 
if ($_COOKIE["flip_right"]=="down") $flip="down";
else $flip="up";
if ($_COOKIE["flip_search"]=="down") $flipS="down";
else $flipS="up";
//echo "flip=".$flip;
?>
<div title="<?php echo $ms["Flip.Down.One"]?>" id="right_flip_down" style="cursor: hand;<?php if ($flip=="up") echo "display:none";?>" onclick="flipanel('right',0);">
<div class="right_title2um"><?php echo $ms["UserMenu"];?></div>
<div class="right_bg2">
<?php
  echo '<a onclick="flipanel(\'right\',0);return false;" href="// '.$ms["Flip.Down.One"].' //" title="'.$ms["Flip.Down.One"].'"><img src="'.$tpl->GetValue("images").'tab_1.gif" width="200" height="7" alt="" title="'.$ms["Flip.Down.One"].'" border="0" /></a>';
?>
</div>
</div>
<div id="right_flip_up" style="<?php if ($flip=="down") echo "display:none";?>">
<div title="<?php echo $ms["Flip.Up.One"]?>" onclick="flipanel('right',1);" class="right_title"><?php echo $ms["UserMenu"];?></div>
<div class="right_bg">
<?php
  $f=0; 
  
  if ($rh->principal->data["user_id"] != 1) 
  { 
    $hp = $tpl->GetValue("Href:Principal");
    ?>
    <div class="link3_"><a title="Создать новый документ" href="<?php echo $hp;?>/add">Создать в своём журнале<br /><b>новый документ...</b></a></div>
    <div class="link3_"><a title="Опубликовать новое сообщение" href="<?php echo $hp;?>/post">Опубликовать в лентах<br /><b>новое сообщение...</b></a></div>
    <div class="link3_"><a title="Опубликовать анонс события" href="<?php echo $hp;?>/post/event"><b>Опубликовать анонс...</b></a></div>
    <div class="link3_"><a title="Опубликовать анонс документа" href="<?php echo $hp;?>/post/announce"><b>Анонсировать документ...</b></a></div>
    <?php
  }

   if (isset($rh->principal->data["user_menu"]))
   {
    foreach ($rh->principal->data["user_menu"] as $item)
    {
      if ($item["title"] == "") $item["title"] = $rh->object->AddSpaces( $item["title"] , " " );
      if ($rh->object->npj_address == $item["npj_address"]) $f=1;
      echo "<div class=\"link_\">";
       echo $rh->object->Link($item["npj_address"], "", $item["title"]);
       // !!! NpjTranslit refactor
      echo "</div>";
    }
   }
  if ($rh->principal->data["user_id"] != 1) 
  { 
    echo "<div class=\"add_\">";
     if ($f)
      echo "<a href=\"?menu=1\">[х] ".$ms["UserMenu.Remove"]."</a>";
     else
      echo "<a href=\"?menu=1\">[&raquo;] ".$ms["UserMenu.Add"]."</a>";
    echo "</div>";
    ?>
    <div class="full_"><a href="<?php echo $hp."/manage/usermenu"; ?>"><?php echo $ms["UserMenu.Edit"]; ?></a></div>
    <?php
  }

    echo '<a onclick="flipanel(\'right\',1);return false;" href="// '.$ms["Flip.Up.One"].' //" title="'.$ms["Flip.Up.One"].'"><img src="'.$tpl->GetValue("images").'tab_1_.gif" width="200" height="7" alt="" title="'.$ms["Flip.Up.One"].'" border="0" /></a>';
?>
</div></div>
<?php } ?>

