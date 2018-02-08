<?php

  if ($params["node_version"]=="R1.8") $name = "setup/update.R1.8.sql";
  else if ($params["node_version"]=="R1.7") $name = "setup/update.R1.7.sql";
  else if ($params["node_version"]=="R1.5") $name = "setup/update.R1.5.sql";
  else $name = "setup/update.R1.sql";
  $fd = @fopen ($name, "r");
  $sql = @fread ($fd, filesize ($name));
  @fclose ($fd);
  $sql = str_replace('%%NODE_ID%%', strtolower($rh->node_name), $sql);
  $sql = str_replace('%%NODE_TITLE%%', $rh->node_title, $sql);
  $sql = str_replace('%%NODE_PREF%%', $rh->db_prefix, $sql);
  $sqls = explode("# %%@%%",$sql);

  print("Извините, но при первом запуске обновленного кода нужно немного изменить вашу БД.<br/><strong>Изменение БД</strong><br />\n");
  //print_r($sqls);
  $rh->db->raiseErrorFn = "DBAL_Error_Silent";
  foreach($sqls as $sql)
   if (trim($sql)!="")
   {
    $res = $rh->db->Execute(rtrim($sql, "\n\r ;"));
    if ($res) echo ".";
    else echo "<span class=\"failed\" title=\"".$sql."\n give error \n".$rh->debug->dbal_errors[count($rh->debug->dbal_errors)-1]."\">!</span>";
   }
  $rh->db->raiseErrorFn = "DBAL_Error";
  die("<br /><br />Пожалуйста, <a href=\"".$rh->Href($rh->url)."\">обновите страницу</a>.");

?>