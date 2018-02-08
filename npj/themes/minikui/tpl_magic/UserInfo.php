<?php
  $root = $tpl->GetValue("/");
  $ms = &$tpl->message_set;
  if ($rh->principal->data["user_id"] == 1) 
  { ?>
    <strong><?php echo $ms["YouAreGuest"];?></strong><br />
    <a class="no" href="<?php echo $root;?>registration"><?php echo $ms["Registration"];?></a>
<?php } else if ($rh->principal->data["node_id"] == $rh->node_name) { ?>
    <strong><?php echo $ms["YouAre"];?>&nbsp;<?php echo $tpl->GetValue("Link:Principal");?></strong><br />
    <a class="no" href="<?php echo $tpl->GetValue("Href:Principal");?>/manage"><?php echo $ms["Manage"];?></a> |
    <a class="no" href="<?php echo $tpl->GetValue("Href:Principal");?>/settings"><?php echo $ms["Settings"];?></a> |
    <a class="no" onclick="return confirm('<?php echo $ms["LogoutSure"]; ?>')" 
       href="<?php echo $root;?>login/?_logout=yes"><?php echo $ms["Logout"];?></a>
<?php } else { ?>
    <strong><?php echo $ms["YouAre"];?>&nbsp;<?php echo $tpl->GetValue("Link:Principal");?></strong><br />
    <a class="no" onclick="return confirm('<?php echo $ms["LogoutSure"]; ?>')" 
       href="<?php echo $root;?>login/?_logout=yes"><?php echo $ms["Logout"];?></a>
<?php }
            // !!! refactor logout link in no mod_rewrite mode
 
 ?>