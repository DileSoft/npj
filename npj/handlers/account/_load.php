<?php
/*
    ”–ќ¬Ќ»  ЁЎ»–ќ¬јЌ»я

0 - user_id / login
1 - node_id, owner_user_id, alive, password, login_cookie, root_record_id
2 - все прочие настроечные пол€, кроме блобов
3 - все пол€ вообще



  function Load( $cache_level=2 ) 
*/

  $fields = array();
  $fields[] = "user_id as id, user_id, login, user_login";
  $fields[] = $fields[0].", account_type, account_class, populate_type, domain_type, node_id, owner_user_id, alive, ".
              "password, login_cookie, root_record_id, original_user_id";
  $fields[] = $fields[1].", user_name, _formatting, _pic_id, theme, lang, more, email, skin_override, group_versions_override"
              .", last_login_datetime, last_logout_datetime";
  $fields[] = $fields[2];
  $_fields[3] = "journal_name, journal_desc, security_type, email, skin, skin as _skin, friends_template, advanced, ".
                "default_membership, post_membership, announce_membership, owner_membership,".
                "email_confirm, _notify_comments, ".
                "bio, interests, sex, city, region, country, birthday, total_posted, creation_date, ".
                "icq_uin, email, website_url, website_name, number_friends, number_friendof, ".
                "_friends_page_size, _personal_page_size, ".
                "file_url_prefix, ".
                "account_template, parent_id, lft_id, rgt_id, ".
                "template_announce, template_digest, ".
                "temporary_password, temporary_password_created "; // to be continued.

            
  // load page
  $a0 = explode(":", $abs_npj_address);
  $a = explode("@", $a0[0]);
  $a1 = explode("/", $a[1]);
  $a[1] = $a1[0];

  $debug->Trace("<b>LOAD: ".$abs_npj_address."</b>");  

  $rs = $db->Execute( "select ".$fields[$cache_level]." from ".$rh->db_prefix."users where ".
                      "node_id=". $db->Quote($a[1]) ." and login=". $db->Quote($a[0]) );
  $result = $rs->fields;

  if ($result && ($cache_level > 2))
  {
    $rs = $db->Execute( "select ".$_fields[$cache_level]." from ".$rh->db_prefix."profiles where ".
                        "user_id=". $result["user_id"] );

    // если у загружаемого журнала нет профил€,
    // то
    // установить скин и установить оповещение о комментари€х                    
    if (!$rs->fields)
    {
      $rs->fields["skin"] = $rh->skins[0];
      $rs->fields["_notify_comments"] = "1";
    }

    $rs->fields["advanced_options"] = $rh->principal->DecomposeOptions( $rs->fields["advanced"] );
    $rs->fields["options"] = $rh->principal->DecomposeOptions( $result["more"] );

    if ($result["email"] != "") unset($rs->fields["email"]);
    $result = array_merge( (array)$result, (array)$rs->fields );
  }

  // overrides (copied from _load.php)
  if ($rs->fields)
  //if ($rh->principal->data["user_id"] != $result["user_id"]) // removed by kuso to conform with new UI simplification guides
  {
    if ($rh->principal->data["skin_override"] != "")
      $result["skin"] = $rh->principal->data["skin_override"];
    if ($rh->principal->data["group_versions_override"] > -1)
      $result["advanced_options"]["group_versions"] = $rh->principal->data["group_versions_override"];
    if ($rh->principal->data["options"]["post_supertag_ovr"] > -1)
      $result["advanced_options"]["post_supertag"] = $rh->principal->data["options"]["post_supertag_ovr"];
    if ($rh->principal->data["options"]["post_date_ovr"] > -1)
      $result["advanced_options"]["post_date"] = $rh->principal->data["options"]["post_date_ovr"];
  }


  return ($rs->fields?$result:"empty");

?>