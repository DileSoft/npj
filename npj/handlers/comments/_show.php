<?php

  // ����� �������� show.php / add.php
  //  * show.php ����������, ����� �������� ��������, ����� ������������� ��������� ���-�� � �������
  //  * add.php  ����������, ����� �������� �� �� ��������, ���� �������� �� �����������
  //                               ����� ���������� ��� ����������� ���� �����������, ����.
  // ��� ��������������� ������� �� ����������

  // �������� ���� ������ �� ������


   // $debug->Trace_R( $this->_b );
   // $debug->Error("dhskdfh");

   if ($params["dummy"])
   {
     $tpl->Assign( "Preparsed:TITLE", $tpl->message_set["TitleCommentsDeep"] );
     $tpl->Assign( "Link:Record", $this->Link( $this->record->npj_account.":".$this->record->data["tag"] ));
     
     if (($this->record->GetType() == RECORD_POST) && ($this->record->data["subject"] != ""))
      $tpl->Append( "Link:Record", " &#151; ". $this->Format($this->record->data["subject_r"], 
                                                             $this->record->data["formatting"], "post")
                                                             ); 
  
     $tpl->Assign( "Preparsed:ModRef", $tpl->Parse("comments.html:Record") );
     $tpl->Assign( "Preparsed:TIGHT", 1 );
   }

    // ���������� �������������� ������ ������������
    $rh->UseClass( "ListObject", $rh->core_dir );
    $rh->UseClass( "ListObjectTree", $rh->core_dir );
    $list = &new ListObjectTree( &$rh, $this->_b );
    $list->item2_level = 0;
    if ($this->_comment_mode == COMMENTS_TREE) $list->item2_level = $rh->comments_show_depth;

    $result = $list->Parse( "comments.show.tree.html:List" );
    $tpl->Append( "Preparsed:COMMENTS", $result );

?>