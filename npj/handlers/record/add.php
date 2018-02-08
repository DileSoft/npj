<?php

// !!! workaround for bad initpanel - нельзя сослаться на "от корня"
if ($params[0]=="replication")
{
  $_SESSION["rep_back"] = $object->npj_object_address;
  $rh->Redirect
//  $debug->Error
  ($object->Href
      ($principal->data["login"]."@".$principal->data["node_id"].
                  ($principal->data["node_id"]==$rh->node_name?"":"/".$rh->node_name).
                  ":replication/add/".str_replace(":","/",$object->npj_object_address), 
                 NPJ_ABSOLUTE, IGNORE_STATE)
  );
}


//Волшебный экшн добавления документа.
//Бывает вызван из комстроки, как её кусо называет, с параметром - именем создаваемого документа или без оного,
// и - обязательно - в контексте документа-родителя. Я прилагал усилия чтобы так было.

$data = $this->Load( 2 ); // все поля без блобов

//--------- ДАЙДЖЕСТЫ-1/2 ----------------------------------------------------
if ($params[0] == "digest")
{
  // #1. если targetmask нас не устраивает, то переходим к документу, где он нас будет устраивать
  //     (с сохранением querystring)
  $targetmask = $state->Get("targetmask");
  $full = $this->_UnwrapNpjAddress( $targetmask, NPJ_RECOVERABLE );
  $target_acc = substr( $full, 0, strpos( $full, ":" ) );
  $target_account = &new NpjObject( &$rh, $target_acc );
  $target_data = $target_account->Load(2);
  if ($target_data["account_type"] == ACCOUNT_COMMUNITY) // пытаемся создать дайджест в коммьюнити
  {
    $_target_acc = $target_acc;
    $target_acc = $principal->data["login"]."@".$principal->data["node_id"];
    $full = str_replace($_target_acc, $target_acc, $full);
    $short = substr( $full, strpos( $full, ":" )+1 );

    $feed = $state->Get("feed");
    if ($feed == "") $feed = $this->npj_account;
    $full_feed = $this->_UnwrapNpjAddress( $feed, NPJ_RECOVERABLE );
    $state->Set( "feed", $full_feed );
    $state->Set( "targetmask", $short );
  }

  if (strpos($full, $this->npj_account) === false) // это ж в другом журнале!
  {
    $rh->Redirect( $this->Href( $target_acc.":add/digest", NPJ_ABSOLUTE, STATE_USE ) );
  }
  // #2. устраняем досадную надпись
  $is_digest = 1;
  array_shift($params);
  $debug->Trace("is_digest for ". $is_digest );
}

//======= ищем ближайший существующий документ вверх по строке пути. То есть то, что может быть родителем.
$parent_npj = "/".$this->name;
if (is_array($data))  $record = &$this;
else
{
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
}
$this->record = &$record;
if (is_array($data))
 $this->tag = $data["tag"];
else 
{
 for ($i=0; $i<substr_count($pdata["tag"], "/")+(strlen($pdata["tag"])>0); $i++) 
  if (strpos($this->tag,"/")!==false) $this->tag = substr($this->tag,strpos($this->tag,"/")+1);
 $this->tag = $pdata["tag"]."/".$this->tag;
}

//теперь у нас есть:
//  $this->tag - "правильный" (русскоязычный типа) таг существующей записи из родительской иерархии
//  $this->record - ссылка на существующую запись из родительской иерархии

// если надо, создаём кейворд
if ($params[0] == "keyword")
{
   
   include($rh->handlers_dir."record/add_keyword.php");
   return GRANTED;
}

//==== конструируем tag создаваемой записи
$p = implode ("/", $params);
if ($p) 
{
 $tag = $this->tag."/".$p;
 $type = 2;
 if ($tag{0}=="/") $tag=substr($tag,1);
 if ($tag{strlen($tag)-1}=="/") $tag=substr($tag,0,strlen($tag)-1);
}
else
{
 // если tag не передан нам в параметрах, то нужно сделать временный таг, который мы сотрём в edit.
 // зачем это, Кусо объяснит лучше.
 // !!! видимо, поведение add в кластерах не совсем верное и нуждается в рефакторинге - kukutz does refactoring
 $tag = "MyMagicDate".date("Ymdhis").rand(0,1000); 
 $tag = $this->tag."/".$tag;
 $type = 2;
 if ($tag{0}=="/") $tag=substr($tag,1);
 if ($tag{strlen($tag)-1}=="/") $tag=substr($tag,0,strlen($tag)-1);
};


//==== создаем объект создаваемой записи
$newrecord = &new NpjObject( &$rh, $this->npj_account.":".$tag );
$_data = $newrecord->Load(1);
$newrecord->parent = &$this->record; //и выставляем ссылку на parent-а. вот нам и понадобился this->record.

// когда создаём новую запись "внутри модуля", надо передать модуль для использования новой записью
if ($this->module) 
{
  $newrecord->module = &$this->module;
  $newrecord->module_instance = &$this->module_instance;
}



//--------- ДАЙДЖЕСТЫ-2/2 ----------------------------------------------------
if ($is_digest)
{
  $digest_modes = array("simple" =>1, "form" =>2);
  if ($_REQUEST["mode"] == "") $_REQUEST["mode"] = "simple";
  $newrecord->data = array();
  $newrecord->data["is_digest"] = $digest_modes[strtolower($_REQUEST["mode"])]*1;

  if ($newrecord->data["is_digest"] == "2")
   $newrecord->data["formatting"]       = "rawhtml";
  else
   if ($_REQUEST["formatting"] != "default")
    $newrecord->data["formatting"]       = $_REQUEST["formatting"];

}
else
{
   //если такая запись уже есть в природе - то умираем.
   if (is_array($_data)) return $this->Forbidden("ThereIsSuchRecord"); // !!! add reason in messageset
}

//великий и ужасный edit.
return $newrecord->Handler( "edit", array("add"), &$principal );
?>