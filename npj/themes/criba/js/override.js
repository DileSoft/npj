// ����� ����� ���� �����-�� �������, ����������� ��� ������ ����� (�� ����)

// ��������� ��������, ���������� �� BODY onload=
function preloadSkinImages( imageRoot )
{
  if (document.images) 
  {
    preloadPics( imageRoot, "userpic_def", "userpic_set_def", "userpic_set_def_", "userpic_del", "userpic_del_");
  }
  preloadFlag = true;
}

function skin_init( isGuest )
{
  var t = 1*isGuest || (getCookie("flip_criba")=="down");
  if (t){
     flipDown(1);
  }
}

