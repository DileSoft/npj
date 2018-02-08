<?php

 // �������� add.php

 // ���� $this->params[0], ��� �������� ������������
 // ���� $account -- ���, ���� ���������
 $this->Handler("_count_friends", array(), &$principal );


 $user = &new NpjObject( &$rh, $this->params[0] );
 $udata = $user->Load(2);
 if (!is_array($udata)) return $this->Forbidden( "NoSuchUser" );

 $tpl->Assign( "Friend", $udata["user_name"] );
 $tpl->Assign( "Npj:Friend", $this->params[0] );
 $tpl->Assign( "Href:Friend", $rh->Href($user->_NpjAddressToUrl($this->params[0],NPJ_ABSOLUTE), IGNORE_STATE) );
 $tpl->theme = $rh->theme;
   $tpl->Parse( "friends.add.html:Done", "Preparsed:CONTENT" );
   $tpl->Assign( "Preparsed:TITLE", "�������� ���������" ); // !!! to messageset
 $tpl->theme = $rh->skin;
 return GRANTED;

?>