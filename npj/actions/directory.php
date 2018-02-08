<?php

// {{directory show="all|users|communities|workgroups|lightmembers|powermembers|members|beholders|moderators|managers" 
//             style="br|ul|ol|comma|td"
//             class="account_class_value"
//             order="login|name|creation|update" }}
//
//  $debug->Trace_R( $params );
//  $debug->Error( $script_name );
  $rh->UseClass( "ListObject", $rh->core_dir );

  $account = &$rh->account;  // enhancement candidate
  $adata = $account->Load(2);
  $node_id = $rh->node_name; // enhancement_candidate

  $orders    = array( "login"=>1, "name"=>1, "creation"=>"creation_date", "update" => "last_updated" );

  $orders_dir = array( "creation"=>"desc", "update" => "desc" );
  $is_dts = array( "creation"=>1, "update" => 1 );
  $order   = "login";        
  $order_dir = "asc";
  $is_dt = 0;
  if ($params["order"])
  {
   if (isset($orders[ $params["order"] ]))
   {
     $order = $orders[ $params["order"] ];
     if ($order == 1) $order = $params["order"];
   }
   if (isset($orders_dir[ $params["order"] ]))
     $order_dir = $orders_dir[ $params["order"] ];
   if (isset($is_dts[ $params["order"] ]))
     $is_dt = $is_dts[ $params["order"] ];
  }

  $templates = array( "br", "ul", "ol", "comma", "td");
  if (!isset($params["style"])) $params["style"] = "td";

  if (!$object->HasAccess( &$principal, "acl_text", $rh->node_admins ))
   $secret = " and p.security_type < ". $db->Quote(COMMUNITY_SECRET);


  $in_node = array( "all"         => ">=0",    "users"      => "=0", 
                    "communities" => "=1",     "workgroups" => "=2" );
  $in_group = array_flip($rh->group_ranks[ $adata["account_type"] ]);
  
  if (isset($params["show"])) $params["show"] = strtolower( $params["show"] );
  else                        $params["show"] = "all";
  
  $account_class = array( "sql" => "", );
  if (isset($params["class"]))
   if (isset($rh->account_classes[$params["class"]]))
   {
     $account_class = $rh->account_classes[$params["class"]];
     $account_class["supertag"] = $params["class"];
     $account_class["sql"] = " and account_class=". $db->Quote( $account_class["supertag"] )." ";
   }
  
  
  // 1. gather accounts
  if (isset( $in_node[ $params["show"] ] ))
  {
    $rs = $db->Execute( "select u.account_type, u.user_name, u.user_id, u.login, u.node_id, ".$order." from ".$rh->db_prefix.
                        "users as u, ".$rh->db_prefix."profiles as p where u.user_id=p.user_id and ".
                        " node_id=".$db->Quote($node_id).
                        " and account_type ".$in_node[ $params["show"] ].
                        " and alive=1 ".
                        $account_class["sql"].
                        $secret.
                        " order by ".$order." ".$order_dir );
    $a = $rs->GetArray();
  }
  else return $this->Action( "_404", array(), &$principal ); // !!! директори для коммунити не реализовано

  // 2. prepare list data
  $data = array();
  foreach ($a as $v)
  if ($v["user_id"] > 1)
  {
    $data[] = array( "Link:user"     => $this->Link( $v["login"]."@".$v["node_id"] ),
                     "user_name"     => $v[ "user_name" ],
                     "dt"            => $v[ $order ],
                     "is_dt"         => $is_dt && ($v[ $order ] != "0000-00-00 00:00:00"),
                     "account_type"  => $tpl->message_set[$v[ "account_type" ]] );
  }

  // 4. choose template
  foreach( $templates as $k=>$v )
   if (($params["style"] == $k) || ($params["style"] == $v))
    $tplt = "List_".$v; 

// --------------- заполняем Action:TITLE
   if ($account_class["name"] != "")
     // !!!! to message_set
     $tpl->Append("Action:TITLE", " вида &laquo;".$account_class["name"]."&raquo;");

  // 5. parse 
  $list = &new ListObject( &$rh, &$data );
  $list->implode = $params["style"] == "comma";
  return $list->Parse( "actions/directory.html:".$tplt );

?>