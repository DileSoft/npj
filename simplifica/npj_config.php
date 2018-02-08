<?php

//  from ../config_modules.php
//  $this === $rh

  // simplification config

  $this->disable_npjlinks  = false;
  $this->disable_wikilinks = true;
  $this->disable_tikilinks = true;

  $this->hide_access_pane_in_new_record = true;
  $this->hide_ref_pane_in_new_record    = true;

  $this->hide_keyword_comments = true;

  $this->disable_subscribe_documents = true;

  $this->keep_alive = 1000*60*5;

  // guest principal 
  $this->guest_override = array(
      "skin_override" => "simplifica",
      "more" => "double_click=0\n".     // no doubleclick
                "sodynamic_off=1\n".    // no triedit in page
                "comments_always=1\n".  // always show comments

                "edit_simple=1\n".  // -\
                "record_stats=0\n". // -\\
                "comments=0",       // --- irrelevant stuff
                );

?>
