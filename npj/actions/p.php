<?php
/*
   {{P style="before|after|left|RIGHT"                 
                                                       // ����� ��� � /wakka.php
       align="left|right" // ��� ���, ��� �������� �� ������
       name="absolute|toc-relative|DOCUMENT-RELATIVE"  // �� ����������� ���� "toc-relative"
   }}
*/
  $context = $object->npj_object_address;
  $link = "";

  // ��� ��������� ������ � ������ ���� � ����������� ���� ���� �� �������� ������� P
  if ($object->wrong_body) return;

  // ��������� � ��� �������� � ������� $params[ xxx ]
  if (!$params["name"])  $params["name"]  = "document-relative";
  if (!$params["style"]) $params["style"] = $params["align"];
  if (!$params["style"]) $params["style"] = "right";

// ����������� ����� ���, ������ �������������
{
  if ($object->post_wacko_toc) 
    $toc = &$object->post_wacko_toc; // ���� ��� ������� ������� ���
  else
  {
    $uactn = &$rh->UtilityAction(); // actions ������ ����� � ��������� ������. << max@jetstyle 2004-11-18 >>
    $toc = $uactn->BuildToc( $context, $params["start_depth"], $params["end_depth"], $params["numerate"], $link, 
                             &$this );
  }

  { // ---------------------- p numeration ------------------------
    // ��������, ����� ������ ��� �����
    $toc_p   = array();
    $toc_len = sizeof($toc);
    $numbers = array(); $depth = 0; $pnum=0;
    for($i=0;$i<$toc_len;$i++)
     if ($toc[$i][2] > 66666)
     { // ����������� ������� ����������
       $pnum++;
       if ($params["name"] == "document-relative") $num = $pnum;
       else                              $num = str_replace("-", "&#0150;&sect;",
                                                str_replace("p", "�", $toc[$i][0] ));
       // ������ ���������� TOC @66
       $toc_p[ $toc[$i][0] ] = $num;
     }
     // ������� � � ��� �������� ������������� ������
     // ??? ��������, ��� � ������, ������ ��� �������� � ��������� ��������� ����
     // ??? ���� ��� -- ��: ������� (���), ��� �� ��� ���������� ���� ���
     // ??? ��������������� �������� ��������, ��� �� ����� �������� =)
     // $uactn->tocs[ $context ] = &$toc;

     // ������ ���� ��������� ������ � ���, ��� ������� �� ���������� � ����-���� 
     // �������� ���������, ������� ���� �������
     $object->post_wacko_toc_p = &$toc_p; 
     $object->post_wacko_action["p"] = $params["style"];
     $object->post_wacko_maxp = $pnum; 
  } // --------------------------------------------------------------
}

$tpl->Assign("Action:NoWrap", 1);
return "";

?>