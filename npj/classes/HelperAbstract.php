<?php
/*
    HelperAbstract( &$rh, &$obj ) -- Абстрактный хелпер для форм редактирования всего
      * у $obj:
          $obj->helper
          $obj->owner -- must be set! // to account

  ---------
  - &TweakForm( &$form_fields, &$group_state, $edit=false ) -- видоизменить коллекцию полей для формы 
      * в наследованных метод родителя вызывается ПЕРЕД своими действиями
      * возвращает новый, правильный вариант списка form_fields, меняет group_state
  - PreSave( &$data, &$principal, $is_new=false ) -- выполнить шампанские действия по видоизменению $data
  - Save( &$data, &$principal, $is_new=false ) -- выполнить шаманские действия по сохранению данных из $data, 
                     где последний - хэш-массив вида <поле-значение>, получаемый 
                     перегонным кубом из $form->hash[...]
      * в наследованных метод родителя вызывается ПОСЛЕ своих действий
  - ParseRequest( $request ) -- заполнить какие-то данные из $_REQUEST
      * вызывается где-то сразу после конструктора дайджеста, до TweakForm
  - _UpdateRef() -- занимается тем, что сливает из подготовленного массива $this->ref в БД
  - &CreateAccessFields( &$access_group, &$record, $is_new, $automate=NULL, $selgroups = NULL ) 
                 -- создаёт необходимые поля ограничений доступа (пока только для записей)
                    и добавляет их в выбранную группу.
                    Используется в handlers/record: edit, rights, automate
      * &$access_group -- массив-группа полей для формы
      * &$record       -- из какой записи брать Default values
      * $is_new        -- запись создаётся, а не редактируется
      * $automate      -- выполняется автоматизация, а не настройка для конкретной записи
      * $selgroups     -- содержимое $rules["_groups"] для автоматизации

  // Важные свойства
  // Что умеет делать

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