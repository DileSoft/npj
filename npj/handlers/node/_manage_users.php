<?php

 $tpl->theme = $rh->theme;

  /*
   ���� ��������� ���������� ������� �������������:
     * ������ ����� ����� (����������/�������) manage/users/new
     * ������ ���� ������������� (�������/���) manage/users/
     * ������ "�����" (�������/���)            manage/users/foreign
     * ������ "�������" (�������/���)          manage/users/local
     * ������ ������������                     manage/users/frozen
     * ������ ���������                        manage/users/banned
     * ������ �����                            manage/users/people
     * ������ ���������                        manage/users/community
     * ������ ��                               manage/users/workgroup
  */
   if ( $params[0] == "users" ) $user = "=0";
                           else $user = ">0";
   $alive = ">0";
   if ( $params[1] == "new"      ) $alive = "=0";
   if ( $params[1] == "alive"    ) $alive = "=1";
   if ( $params[1] == "frozen"   ) $alive = "=2";
   if ( $params[1] == "banned"   ) $alive = "=3";
   $node_id = "<>".$db->Quote("");
   if ( $params[1] == "foreign"    ) $node_id = "<>".$db->Quote( $rh->node_name );
   if ( $params[1] == "local"      ) $node_id = "=".$db->Quote( $rh->node_name );

   $where = "node_id".$node_id." and alive".$alive." and owner_user_id".$user;
   $table = "users";

   $rh->UseClass( "Arrows", $rh->core_dir );
   $arrows = &new Arrows( &$state, $where, $table, 20 );

   $arrows->Parse( "manage.arrows.html", "ARROWS" );

   $sql = "select alive, user_name, login, user_id, node_id from ".$rh->db_prefix.$table.
          " where ".$where;

   $rs = $db->SelectLimit( $sql, $arrows->GetSqlLimit(), $arrows->GetSqlOffset() );
   $a = $rs->GetArray();
   foreach ($a as $k=>$v)
   {
     $a[$k]["is_banned"] = $a[$k]["alive"] == 3;
     $a[$k]["is_frozen"] = $a[$k]["alive"] == 2;
     $a[$k]["is_local"] = $a[$k]["node_id"] == $rh->node_name;
   }

   $rh->UseClass( "ListObject", $rh->core_dir );
   $list = &new ListObject( &$rh, &$a );
   $list->Parse( "manage.users.list.html:List", "Preparsed:CONTENT" );

 $tpl->theme = $rh->skin;
?>
