<?php

// {{FacetFilter 

//         [cols="3"]
//         [root="WackoWiki"]
//         [keywords="WackoWiki Test WebDesign"] 
//         [dest="same|handler"]

//         [style="br|ol|ul|indent"] 
//         [operator="or|and"] 
//         [filter="posts|documents|both"] 
//         [order="user|server|edited|tag|subject"] 
//         [subject="0|1"] 
//   }}
//
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
  $rh->UseClass( "ListObject", $rh->core_dir );

    $ops = array("and", "or");

  $root_name = $object->npj_account.":";
  if ($params["root"]) $root_name .= $params["root"];

  if ($object->method == "action") 
  {
    $root_name = $object->npj_object_address;
    $root = &$object;
  }
  else $root = &new NpjObject( &$rh, $root_name );

  $rootdata = &$root->Load(2);
  if (!is_array($rootdata))
  {
    $root = &new NpjObject( &$rh, $object->npj_account.":" );
    $root_name = $object->npj_account.":";
    $rootdata = &$root->Load(2);
  }
/*
   этот action делает буквально следующее: если ему пришло ?_do=facetfilter, то
   он вызывает акшн фасет (сопровождая его своим надшаблончиком)
   а если нет, то выводит все ключслова ниже root (или все ключслова журнала)
   с чекбоксами и тип условия "OR" или "AND" (дефолт-значение в парамсах)
*/

  $showform = 1;

  if ($_REQUEST["_do"] == $script_name)
  {
    // 1. получить все ключслова из запроса
    $ids = array();
    foreach ($_REQUEST as $k=>$v)
     if ($v == "z")
      if ($k{0} == "_")
      { $id = 1*substr($k,1);
       if ($id) $ids[] = $id;
      }
    if (sizeof($ids))
    {
      $showform = 0;
      $rs = $db->Execute( "select subject, tag, supertag from ".$rh->db_prefix."records where ".
                          "record_id in (".implode(",", $ids).")" );
      $a = $rs->GetArray();
      $kwds = array();
      $stricts = "";
      foreach( $a as $v )
      {
       $kwds[] = $v["supertag"];
       $stricts[] = $object->Link( $v["supertag"], "", $v["subject"] );
      }
      $stricts = implode(", ", $stricts);
      $kwds = implode(" ", $kwds);


      // 2. дозаполнить парамсы
      if ($params["keywords"]) $params["keywords"].=" ";
      $params["keywords"] .= $kwds;
      $params["0"] = $params["keywords"];
      if (in_array($_REQUEST["_operator"], $ops)) $params["operator"] = $_REQUEST["_operator"];

      // 3. вызвать фасет
      $title = $tpl->GetValue( "Action:TITLE" );
      $result = $object->Action( "facet", &$params, &$principal );
      $title = $tpl->Assign( "Action:TITLE", $title );
      $tpl->Assign("STRICTS", $stricts);
      $tpl->Assign("RESULTS", $result);
      $tpl->Assign("url", $object->Href($object->npj_object_address."/".$script_name, NPJ_ABSOLUTE) );
      return $tpl->Parse( "actions/facetfilter.html:Results" );;
    }
  }

  if ($showform)
  {
    if (! (1*$params["cols"])) $params["cols"] = 3;
    // (формируем-то POST-запрос, хотя данные можем взять и из GET)
    // 1. получить все ключслова под корнем
    $rs = $db->Execute( "select subject, supertag, tag, record_id as id from ".$rh->db_prefix."records where ".
                        " is_keyword=1 and supertag <> ". $db->Quote($root_name).
                        " and supertag LIKE ". $db->Quote($root_name."%").
                        " and user_id = ".$db->Quote($rootdata["user_id"]).
                        " order by tag " );
    $kwds = $rs->GetArray();
    $c=0; $s=0; $step = sizeof($kwds) / $params["cols"];
    $kwds2 = array();
    foreach( $kwds as $k=>$v )
    {
     $kwds2[$s][] = &$kwds[$k];
     $c++;
     if ($c > $step) { $s++; $c=0; }
    }
    /*
    $debug->Trace_R( $kwds2 );
    $debug->Trace(   $params["cols"] );
    $debug->Error(   $step );
    */
    // 2. отпарсить список полученных ключслов
    if (sizeof($kwds) == 0) $checkboxes = $tpl->Parse( "actions/facetfilter.html:Checkboxes_Empty" );
    else
    { $rows = "";
      $tpl->Assign("pct", floor(100/$params["cols"]) );
      foreach( $kwds2[0] as $k=>$v )
      {
        $rows.="<tr>";
         for ($i=0; $i< $params["cols"]; $i++)
          if ($kwds2[$i][$k])
          {
            $tpl->LoadDomain( $kwds2[$i][$k] );
            $rows.= $tpl->Parse( "actions/facetfilter.html:Checkboxes_Item" );
          }
          else $rows.="<td>&nbsp;</td><td>&nbsp;</td>";
        $rows.="</tr>";
      }
      $tpl->Assign("ROWS", $rows);
      $checkboxes = $tpl->Parse( "actions/facetfilter.html:Checkboxes" );
    }
    // 3. отпарсить список "готовых" ключслов
    if ($params["keywords"])
    {
      $strict = explode( " ", $params["keywords"] );
      $stricts = array();
      foreach ( $strict as $k )
        $stricts[] = $object->Link( $root->npj_account.":".$k, "", $k );
      $stricts = implode( ", ", $stricts );
    } else $stricts = "";
    // 4. отпарсить основной шаблон
    $tpl->Assign( "CHECKBOXES", $checkboxes );
    $tpl->Assign( "STRICTS", $stricts );
    $tpl->Assign( "and", 0 ); $tpl->Assign( "or", 0 );
    if (in_array( $params["operator"], $ops)) $tpl->Assign($params["operator"], 1);
    $tpl->Assign( "operator", $params["operator"]?$params["operator"]:"and" );
    if ($params["dest"] == "same") $dest = $object->npj_address;
    else                           $dest = $object->npj_object_address."/".$script_name;

    $tpl->Assign( "Form:FacetFilter", $state->FormStart(MSS_GET, $object->_NpjAddressToUrl($dest, NPJ_ABSOLUTE) ) );
    $tpl->Assign( "/Form", $state->FormEnd() );

    return $tpl->Parse( "actions/facetfilter.html:Form" );

  }



?>