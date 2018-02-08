<?php
/*
    HelperAbstract( &$rh, &$obj ) -- ����������� ������ ��� ���� �������������� �����
      * � $obj:
          $obj->helper
          $obj->owner -- must be set! // to account

  ---------
  - &TweakForm( &$form_fields, &$group_state, $edit=false ) -- ������������ ��������� ����� ��� ����� 
      * � ������������� ����� �������� ���������� ����� ������ ����������
      * ���������� �����, ���������� ������� ������ form_fields, ������ group_state
  - PreSave( &$data, &$principal, $is_new=false ) -- ��������� ���������� �������� �� ������������� $data
  - Save( &$data, &$principal, $is_new=false ) -- ��������� ��������� �������� �� ���������� ������ �� $data, 
                     ��� ��������� - ���-������ ���� <����-��������>, ���������� 
                     ���������� ����� �� $form->hash[...]
      * � ������������� ����� �������� ���������� ����� ����� ��������
  - ParseRequest( $request ) -- ��������� �����-�� ������ �� $_REQUEST
      * ���������� ���-�� ����� ����� ������������ ���������, �� TweakForm
  - _UpdateRef() -- ���������� ���, ��� ������� �� ��������������� ������� $this->ref � ��
  - &CreateAccessFields( &$access_group, &$record, $is_new, $automate=NULL, $selgroups = NULL ) 
                 -- ������ ����������� ���� ����������� ������� (���� ������ ��� �������)
                    � ��������� �� � ��������� ������.
                    ������������ � handlers/record: edit, rights, automate
      * &$access_group -- ������-������ ����� ��� �����
      * &$record       -- �� ����� ������ ����� Default values
      * $is_new        -- ������ ��������, � �� �������������
      * $automate      -- ����������� �������������, � �� ��������� ��� ���������� ������
      * $selgroups     -- ���������� $rules["_groups"] ��� �������������

  // ������ ��������
  // ��� ����� ������

=============================================================== v.0 (Kuso)
*/

class HelperAbstract
{
  var $request_params;
  var $ref;
  var $rare;

  function HelperAbstract( &$rh, &$obj )
  {
    $this->rh = &$rh;
    $this->obj = &$obj;
    $this->tpl = &$rh->tpl;
    $this->ref = array();
    $this->request_params = array();
  }

  // -----------------------------------------------------------------
  function &TweakForm( &$form_fields, &$group_state, $edit=false )
  {
    return $form_fields;
  }

  // -----------------------------------------------------------------
  function Save( &$data, &$principal, $is_new=false ) 
  { 
  }

  // -----------------------------------------------------------------
  function &PreSave( &$data, &$principal, $is_new=false ) 
  {
    return $data;
  }

  // -----------------------------------------------------------------
  function ParseRequest( $request ) 
  { 
  }

  // ---------------- -------------------------------------------
  function &CreateAccessFields( &$access_group, &$record, $is_new, $automate=NULL, $selgroup=NULL )
  {
    return $access_group;
  }

// EOC { HelperAbstract }
}


?>