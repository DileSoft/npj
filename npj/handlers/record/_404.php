<?php
  if ($rh->admins_only_documents)
  if (!$this->HasAccess( &$this->rh->principal, "acl_text", $rh->node_admins))
  {
   $tpl->Assign( "404", 1);
   $tpl->Parse( "404.common.html", "Preparsed:CONTENT" );
   return GRANTED; 
  }

   $tpl->Assign( "Url", $this->Href("/".$this->name,1) );
   $tpl->Assign( "Preparsed:TITLE", "404 Страница не найдена" ); // В мессадж сет
   // Гуёвое преобразование строки 
   // ???? to kukutz: напиши словами что тебе здесь надо, я не могу понять =(
/*
   $tag = (strpos($this->tag, "/")!==false?
          substr(strrchr($this->tag,"/"),1,strlen(strrchr($this->tag,"/"))):
          $this->tag);
*/
   $tpl->Assign( "404", 1);

   $parent_npj = "/".$this->name;
   while (!is_array($pdata)) 
   {
     $ps = strrpos($parent_npj, "/");
     if ($ps !== false) $parent_npj = substr($parent_npj, 0, $ps);
     else
      return $this->Forbidden("BrokenAccount"); 
     $pdata = $this->_Load( str_replace(":/", ":", $this->npj_account.":".$parent_npj), 2 );
   }
   $record = &new NpjObject( &$rh, str_replace(":/", ":", $this->npj_account.":".$parent_npj) );
   $pdata = $record->Load( 2 );
   $this->record = &$record;
   for ($i=0; $i<substr_count($pdata["tag"], "/")+(strlen($pdata["tag"])>0); $i++) 
    if (strpos($this->tag,"/")!==false) $this->tag = substr($this->tag,strpos($this->tag,"/")+1);
   $this->tag = ($pdata["tag"]?$this->NpjTranslit($pdata["tag"])."/":"")."add/".$this->Translit($this->tag);


   if ($this->name != "")
   {
//     $tpl->Assign("Href:Create", $this->Href($this->npj_account.":").($this->npj_context?"/".$this->npj_context."/":"/")."add/".$this->Translit($tag));
     $tpl->Assign("Href:Create", $this->Href($this->npj_account.":")."/".$this->tag);
     if (!$rh->account->fake)
      $tpl->Parse("404.record.html:Granted", "Preparsed:CONTENT" );
     else
      $tpl->Parse("404.record.html:Forbidden", "Preparsed:CONTENT" );
   }
   else
   {
     $tpl->Parse("404.account.html", "Preparsed:CONTENT" );
   }

?>
