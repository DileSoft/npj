function hmaProduceConfig()
{
  //default config for HTMLArea
  var _editor_config = new HTMLArea.Config();
  _editor_config.killWordOnPaste = true;
//  _editor_config.baseURL = _base_url;
  //����� �� BODY 
  _editor_config.bodyClass = 'td-text';

  //����� ������� ��������� �� ���������
  tpl.to_load = new Array();//['pict','pict_preview'];
  
  //����� ����������� "������� �������", ��������: 
  //tpl.templates['quick1'] = '<b>[text]</b>';

  //������ �� ���������
  _editor_config.toolbar = [
    [  "formatblock", "space",
      "bold", "italic", 
      /*"underline", */
      "strikethrough", "separator",
      "subscript", "superscript", "separator",
      "copy", "cut", "paste", "space", "undo", "redo" ],

    [ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
      "insertorderedlist", "insertunorderedlist", "separator",
//    "outdent", "indent",
//"button_insert_image",     "button_mark", "button_insert_cit", "button_insert_link", "button_insert_file", "button_insert_files_list", "separator",
      "inserthorizontalrule", "createlink", 
      "inserttable", "separator",
      "htmlmode" ]
  ];

  //������� �������� ��� ����� ������ 
  _editor_config.formatblock = {
    "��������": "p",
    "���������": "h2",
    "������������": "h3"
  };
//    "��������������": "pre"


  //���������� ������
  _editor_config.ManualFilter = function(htmlarea){ return; }
  return _editor_config;
}
