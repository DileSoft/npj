<?php

  $tpl->Assign("Preparsed:TITLE", "Массовый перевод рапортов из состояния в состояние"); // !!! to msgset
  // "MASS" handler --> mass state change
  // ----------------------------------------------------
  $trako = &$this;
  $TE = &$this->GenerateTemplateEngine( $this->config["template_engine"] );

  $account = &new NpjObject( &$rh, $this->object->npj_account );
  $account->Load(2);

  // POST_ONLY for now

  if ($_POST["state"])
  {
     $_state = $_POST["state"];
     if ($this->config["states"][$_state])
     {
       $issues = array();
       foreach( $_POST as $k=>$v )
         if (strpos($k, "no_") === 0)
         {
           $no = substr($k, 3);
           if (is_numeric($no)) $issues[] = 1*$no;
         }
  
       foreach( $issues as $k=>$v )
       {
         // DIRTY CODE. copied & modified a bit from issue_state
  
         // какой номер у бага? ---------------------------------------------
         $issue_no = $v;
         $issue = &$this->LoadIssue( &$account, $issue_no, 4 ); // 4 -- for edit
         if ($issue == NOT_EXIST) continue;

         //  сохраняем изменения в БД ---------------------------------------------
         $this->rh->UseClass("HelperAbstract");
         $this->rh->UseClass("HelperRecord");
         $this->rh->UseClass("HelperTrakoIssue", $this->classes_dir);
         //  Создаём болванку под запись и сохраняем её ---------------------------------------------
         $tag   =  $this->config["subspace"]."/".$issue_no;
         $issue_record =& new NpjObject(&$rh, $account->npj_object_address.":".$tag );
         $issue_record->class = "record";
         $issue_record->Load( 4 ); // load for edit;
         unset($issue_record->data["keywords"]);
         $issue_record->data["issue_no"]             = $issue_no;
         $issue_record->data["issue_state"]          = $_state;
         $issue_record->data["user_datetime"] = date("Y-m-d H:i:s");
         $issue_record->data["&issue"]    = &$issue; // prev. state
         $issue_record->owner = &$account;
         $issue_helper = &new HelperTrakoIssue( &$rh, &$issue_record );
         $issue_record->helper = &$issue_helper;
         $issue_record->Save();
       }
     }

     // redirect to referer for now
     $rh->Redirect( $_SERVER["HTTP_REFERER"], STATE_IGNORE );
  }


?>