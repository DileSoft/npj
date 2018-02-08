<?php

  // action ���������������� of a digest
  /*

    {{Digest 
             [ feed/for="kuso@npj:aspirantura" ]
             [ dtfrom="23.09.2003" dtto="21.12.2003" ]
             / [ dtlast=1 ] -- "� ������� �������� ���������� ���������"
             [filter="announce|events|documents"]

             [ targetmask = "���������/" ] -- ����� ��� ���� ���������

             [hide_feed=1, hide_dt=1, hide_filter=1, hide_targetmask=1] -- �������� ����
             [hide_build=1] -- ������ �������� ������
             [hide_mail, hide_url, hide_action, hide_quick] -- ���. ������

             [mode="simple|form"] -- ������������ ��������

             simple mode:
             [template="default|default_users|full|full_users"] -- ������ ���������
             [formatting="default|wacko|html|simplebr"]

             form mode:
             [html="default|users"] -- html-���������� ������ ���������
    }}
  */

  // ��������� ��������� ���������� ------------------------------------------------------------------------------
  $rh->UseClass("HelperAbstract");
  $rh->UseClass("HelperRecord");
  $rh->UseClass("HelperDocument");
  $rh->UseClass("HelperDigest");
  $params = HelperDigest::ValidityCheck( $params, &$object );

  $filters = array( 0, "announce", "events", "documents" ); 
  $modes = array( "simple", "form" ); 
  $formatters = array( "default", "wacko", "rawhtml", "simplebr" ); 
  $templates = array( "default", "default_users", "full", "full_users", ); 
  $html = array( "default", "users", ); 
  $filters_selected[ $params["filter"] ] = "SELECTED";
  $modes_selected[ $params["mode"] ] = "CHECKED";
  $templates_selected[ $params["template"] ] = "SELECTED";
  $formatters_selected[ $params["formatting"] ] = "SELECTED";
  $html_selected[ $params["html"] ] = "SELECTED";

  if ($params["hide_mode"])
  {
    $params["hide_html"] = 1;
    $params["hide_template"] = $params["hide_formatting"] =1;
  }

  // �������� ��������� � tpl ------------------------------------------------------------------------------------
  $pass_thru = array( "feed", "targetmask", "dtlast", "dtfrom", "dtto", "filter", 
                      "mode", "template", "formatting", "html" );
  $pass_binary = array( "quick", "email", "url", "action", "build" );
  foreach( $pass_thru as $v )
  {
    $tpl->Assign( $v, $params[$v] );
    $tpl->Assign( "hide_".$v, 1*$params["hide_".$v] );
  }
  foreach( $pass_binary as $v )
    $tpl->Assign( "hide_".$v, 1*$params["hide_".$v] );
  foreach( $filters as $k=>$v )
    $tpl->Assign( "filter_".$v, $filters_selected[$v]);
  foreach( $modes as $k=>$v )
    $tpl->Assign( "mode_".$v, $modes_selected[$v]);
  if ($params["mode"] == "simple")
  {
    foreach( $templates as $k=>$v )
      $tpl->Assign( "template_".$v, $templates_selected[$v]);
    foreach( $formatters as $k=>$v )
      $tpl->Assign( "formatting_".$v, $formatters_selected[$v]);
  }
  if ($params["mode"] == "form")
  {
    foreach( $html as $k=>$v )
      $tpl->Assign( "html_".$v, $html_selected[$v]);
  }

  // ����� ����������� �������� ����� ----------------------------------------------------------------------------
  $destination = $object->_NpjAddressToUrl( $object->npj_account.":add/digest", NPJ_ABSOLUTE );
  $tpl->Assign( "Form:Digest", $state->FormStart(MSS_POST, $destination, " name=\"DigestForm\" ") );
  $tpl->Assign( "destination", $rh->base_host_prot.$rh->Href($destination, STATE_IGNORE).$state->q );
  $tpl->Assign( "/Form", $state->FormEnd() );

  // ���������� �����
  return $tpl->Parse( "actions/digest.html:Body" );

?>