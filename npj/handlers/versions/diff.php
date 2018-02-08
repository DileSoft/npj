<?php

  $debug->Trace_R( $params );
  // Фаза 1. Определяем, какие объекты грузим ---------------------------------------------
  $r = $object->npj_account.":".$object->npj_context; // адрес записи-контекста
  if ($this->name != "")
  { //  1.1. не в корне
    $v1 = $this->name;
    //  1.1.2. не дана вторая версия -- сравниваем с записью
    if ($params[0] == "") $v2 = $r;
    //  1.1.1. дана вторая версия 
    //  1.1.1.1. один параметр и тот фастдифф
    if ($params[0] == "fastdiff") $v2=$r;
    //  1.1.1.2. нормальная версия
    else                  $v2 = $params[0];
  }
  else
  { //  1.2. в корне
    //  1.2.1  нет параметров
    if (sizeof($params) == 0) return $this->Forbidden("DiffSame");
    //  1.2.2. один параметр
    if ($params[1] == "")  { $v1=$params[0]; $v2=$r; }
    else
    //  1.2.3. два параметра, один из которых -- фастдифф
    if ($params[1] == "fastdiff") { $v1=$params[0]; $v2=$r; }
    //  1.2.4. два параметра
    else                  { $v1=$params[0]; $v2=$params[1]; }
  }
  //  1.3. развёртываем параметры, данные цифрой в полные НПЖ-адреса
  if (is_numeric($v1)) $v1 = $r."/versions/".$v1;
  if (is_numeric($v2)) $v2 = $r."/versions/".$v2;
  if (preg_match("/\/versions\/?$/i", $v1)) $v1 = $r;
  if (preg_match("/\/versions\/?$/i", $v1)) $v2 = $r;
  $debug->Trace("<h4>DIFF: $v1 VS. $v2</h4>");
  //$debug->Error("- $r");

  // Фаза 2. Загружаем объекты ------------------------------------------------------------
  $o = array();   $odata = array();
  $o[0] = &new NpjObject( &$rh, $v1 );
  $o[1] = &new NpjObject( &$rh, $v2 );
  foreach( $o as $k=>$v )
  { 
    if ($o[$k]->class == "") return $this->NotFound("ClassNotRecognized");
    $odata[$k] = &$o[$k]->Load( 4 );
    if ($odata[$k] === "empty") return $this->NotFound("VersionNotFound");
  }

  // Фаза 3. Проверяем, есть ли доступ к объектам ---------------------------------------
  foreach( $o as $k=>$v )
  {
    if (is_numeric($o[$k]->data["type"])) // it is record
     if (!$o[$k]->HasAccess( &$principal, $this->security_handlers[$o[$k]->data["type"]] )) 
      return $o[$k]->Forbidden("VersionsForbidden"); else;
    else
    if ($o[$k]->data["type"] == "version")
    { // it is not a record, we got to find one then
      $rx = $o[$k]->npj_account.":".$o[$k]->npj_context; // адрес записи-контекста
      $o[$k]->record = &new NpjObject( &$rh, $rx );
      $o[$k]->record->Load(2);
      if (!$o[$k]->record->HasAccess( &$principal, $this->security_handlers[$o[$k]->record->data["type"]] )) 
        return $o[$k]->record->Forbidden("VersionsForbidden");
    }
    else
       return $o[$k]->record->Forbidden("VersionsForbidden"); // такое мы не умеем!
    
  }

  // Фаза 4. Загружаем запись, в контексте которой работаем -------------------------------
  $this->record = &new NpjObject( &$rh, $r );
  $rdata = $this->record->Load( 2 ); // нам надо ништяки для вывода записи, а блобы необязательны 
                                     // (но мы их загрузили раньше, если, то просто здесь берём из кэша)
  if ($rdata === "empty") return $this->NotFound("RecordNotFound"); // нет контекста
  // проверяем, есть ли доступ к контексту
  if (!$this->record->HasAccess( &$principal, $this->security_handlers[$rdata["type"]] )) 
    return $this->Forbidden("VersionsForbidden");

  // Фаза 5. Формируем заголовочную часть в TPL -------------------------------------------
  // 5.1. заполняем домен для объектов
  // 5.1.1. Читабельный таг
  // 5.1.2. Хреф
  // 5.1.3. датетиме
  foreach( $o as $k=>$v)
  {
    if (!$o[$k]->record) $tag=$o[$k]->npj_account.":".$o[$k]->data["tag"];
    else                 $tag=$o[$k]->record->npj_account.":".$o[$k]->record->data["tag"]."/versions/".$o[$k]->name;
    $tpl->LoadDomain( array(
     "Version".($k+1)             => $tag,
     "Href:Version".($k+1)        => $this->Href( $o[$k]->npj_address, NPJ_ABSOLUTE, STATE_IGNORE ),
     "Version".($k+1)."_datetime" => $o[$k]->data["edited_datetime"],
     "Subject".($k+1)             => $o[$k]->data["subject"],
                   )      );
  }
  // 5.2. выбор паттерна вывода, если работаем не с текущим рекордом
  if (!$o[0]->record) $tpl->Assign("diff_foreign",1);
  else if ($o[0]->record->npj_address != $this->record->npj_address) $tpl->Assign("diff_foreign",1);
  if (!$o[1]->record) $tpl->Assign("diff_foreign",1);
  else if ($o[1]->record->npj_address != $this->record->npj_address) $tpl->Assign("diff_foreign",1);
  // 5.3. заголовок записи/контекста
  $subject_post = $this->Format($this->record->data["subject_r"], $this->record->data["formatting"], "post");
  $tpl->Assign("Preparsed:TITLE", $subject_post );


  $debug->Trace_R($o[0]->data);
  $debug->Trace_R($o[1]->data);
  $o[0]->_Trace("numero 0");
  $o[1]->_Trace("numero 1");
  //$debug->Error("-");

  // Фаза 6 &soforth. DIFF & output --------------------------------------------------------
  $data   = &$odata[0];
  $second = &$o[1];

   // выбрать какой дифф будем использовать
   $fastdiff = $_REQUEST["fastdiff"];

   if (in_array("fastdiff",$params)) $fastdiff = true;
   if (!$rh->UseLib("wdiff", "", "", 0)) $fastdiff = true;

   // extract text from bodies
   $textA = $data["body"];
   $textB = $second->data["body"];

   if (($data["version_id"] &&  $second->data["version_id"] && ($data["version_id"] > $second->data["version_id"]))
       ||
       ($data["edited_datetime"] > $second->data["edited_datetime"]))
   { $t = $textA; $textA = $textB; $textB = $t; $debug->Trace("flip"); }

   if (!$fastdiff)
   { $t = $textA; $textA = $textB; $textB = $t; $debug->Trace("flip again"); }

if ($fastdiff) {
   
   // Kuso saz: look ma, i`m flying!
    $bodyA = explode("\n", $textB);
    $bodyB = explode("\n", $textA);

    $added = array_diff($bodyA, $bodyB);
    $deleted = array_diff($bodyB, $bodyA);

    //определяем форматтинг
    $formatting = $data["formatting"];

    if ($added)
    {
      // remove blank lines
      $output .= "<br />\n".$tpl->message_set["Diff.SimpleDiffAdditions"]."<br />\n";
      $output .= "<div class=\"additions\">".
                 $this->record->Format(
                   $this->record->Format(implode("\n", $added), $formatting), 
                 $formatting, array("default"=>"post","diff"=>1))."</div>";
    }

    if ($deleted)
    {
      $output .= "<br />\n".$tpl->message_set["Diff.SimpleDiffDeletions"]."<br />\n";
      $output .= "<div class=\"deletions\">".
                 $this->record->Format(
                   $this->record->Format(implode("\n", $deleted), $formatting), 
                 $formatting, array("default"=>"post","diff"=>1))."</div>";
    }

    if (!$added && !$deleted)
    {
      $output .= "<br />\n".$tpl->message_set["Diff.NoDifferences"].".";
    }

}
else 
{

   // Kuso saz: look ma, i`m flying!
   $sideA = new Side($textB);
   $sideB = new Side($textA);

   $bodyA='';
   $sideA->split_file_into_words($bodyA);

   $bodyB='';
   $sideB->split_file_into_words($bodyB);

   // diff on these two file
   $diff = new wdiff(split("\n",$bodyA),split("\n",$bodyB));

   // format output
   $fmt = new DiffFormatter();

   $sideO = new Side($fmt->format($diff));

   $resync_left=0;
   $resync_right=0;

   $count_total_right=$sideB->getposition() ;

   $sideA->init();
   $sideB->init();

   $output='';

     while (1) {
          
         $sideO->skip_line();
         if ($sideO->isend()) {
       break;
         }

         if ($sideO->decode_directive_line()) {
     $argument=$sideO->getargument();
     $letter=$sideO->getdirective();
         switch ($letter) {
         case 'a':
           $resync_left = $argument[0];
           $resync_right = $argument[2] - 1;
           break;

         case 'd':
           $resync_left = $argument[0] - 1;
           $resync_right = $argument[2];
           break;

         case 'c':
           $resync_left = $argument[0] - 1;
           $resync_right = $argument[2] - 1;
           break;

         }

         $sideA->skip_until_ordinal($resync_left);
         $sideB->copy_until_ordinal($resync_right,$output);
   
 // deleted word

         if (($letter=='d') || ($letter=='c')) {
           $sideA->copy_whitespace($output);
           $output .="¤¤";
           $sideA->copy_word($output);
           $sideA->copy_until_ordinal($argument[1],$output);
           $output .="¤¤";
         }

 // inserted word
         if ($letter == 'a' || $letter == 'c') {
       $sideB->copy_whitespace($output);
       $output .="ЈЈ";
       $sideB->copy_word($output);
       $sideB->copy_until_ordinal($argument[3],$output);
       $output .="ЈЈ";
         }

     }

   }

   $sideB->copy_until_ordinal($count_total_right,$output);
   $sideB->copy_whitespace($output);

   $formatting = $this->record->data["formatting"];
   $output = $this->record->Format(
               $this->record->Format($output, $formatting), 
             $formatting, array("default"=>"post","diff"=>1));

   if ($formatting!="wacko")
   {
     $output = preg_replace("/\xA4\xA4((\S.*?\S)|(\S))\xA4\xA4/s", "<span class=\"del\">$1</span>", $output); 
     $output = preg_replace("/\xA3\xA3((\S.*?\S)|(\S))\xA3\xA3/s", "<span class=\"add\">$1</span>", $output); 
   }

   
}
   $textA = $data["body"];
   $textB = $second->data["body"];

   $tpl->LoadDomain( array(
      "Diff_post" => $output
                   )      );

   $tpl->Parse("versions_diff.html", "Preparsed:CONTENT");

return GRANTED;

?>