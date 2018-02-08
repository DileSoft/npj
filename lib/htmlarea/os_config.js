function hmaProduceConfig()
{
  //default config for HTMLArea
  var _editor_config = new HTMLArea.Config();
  _editor_config.killWordOnPaste = true;
//  _editor_config.baseURL = _base_url;
  //класс на BODY 
  _editor_config.bodyClass = 'td-text';

  //какие шаблоны загрузить по умолчанию
  tpl.to_load = new Array();//['pict','pict_preview'];
  
  //можно присваивать "быстрые шаблоны", например: 
  //tpl.templates['quick1'] = '<b>[text]</b>';

  //тулбар по умолчанию
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

  //русские название для типов текста 
  _editor_config.formatblock = {
    "Параграф": "p",
    "Заголовок": "h2",
    "Подзаголовок": "h3"
  };
//    "Форматирование": "pre"


  //Мануальный фильтр
  _editor_config.ManualFilter = function(htmlarea){ return; }
  return _editor_config;
}
