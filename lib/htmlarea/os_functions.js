  //движок для чистки ворда
  var word_cleaner = new WordCleaner();
  
  //шаблонный движок
  var tpl = new OSTemplates();
  tpl.base_url = _base_url;
  tpl.Assign('BASE_DIR',_base_url);

  function GenerateEditors(editors,plugins){
    var i;
    var j;
    var a;
    for(i=0;i<editors.length;i++){
      if( plugins[i]!='' ){
        a = plugins[i].split(',');
        for(j=0;j<a.length;j++)
          editors[i].registerPlugin(a[j]);
      }
      editors[i].generate();
    }
  }
  //форматирование блоков
  function OSTFormatBlock( editor, tpl_name) {
    tpl.Assign('text',editor.getSelectedHTML());
    editor.insertHTML(tpl.Parse(tpl_name));
  }

  //returns copy of object
  function cloneObject(what) {
      for (i in what) {
          this[i] = what[i];
      }
  }
    
  //универсальная вставка
  function JSTInsert( editor, id_module ) {
    var node = editor.getParentElement();
    var Text = node.innerHTML;
    editor._popupDialog("../../../.."+_project_url+"module/htmlarea_"+id_module, function(param) {
      //нажаили "отмена"
      if (!param) return false;
      
      var node = editor.getParentElement();
      editor.selectNodeContents(node);
      editor.insertHTML(param + editor.getSelectedHTML());
    });
  }

  //вставка иллюстраций
  function OSTInsertImage( editor, tpl_pict, tpl_preview ) {
//    alert('Insert image: ' + tpl_pict + ', ' + tpl_preview );
    editor._popupDialog("../../../.."+_project_url+"module/htmlarea_pictures", function(param) {
      //нажаили "отмена"
      if (!param) return false;
      //вставляем картинку
      var image = _zh_unpack(param);
      
      var href,_height,_width;
      _height = parseInt(image["height"]) + 100;
      _width = parseInt(image["width"]) + 50;
      href = (image["pict"])? "javascript:pictwnd('/?page=pict&id="+image["id"]+"','pict_view','top=100,left=100,width="+_width+",height="+_height+"')" : "#";
      
      tpl.Assign("IMAGE","images/"+image["pict_small"]);
      tpl.Assign("TITLE",image["title"]);

      var template;
      if(href!="#"){
        tpl.Assign("HREF",href);
        template = tpl_preview;
      }else template = tpl+pict;
      
      var node = editor.getParentElement();
      tpl.Assign("TEXT",node.innerHTML);
      
      editor.selectNodeContents(node);
      
      editor.insertHTML( '</p>'+tpl.Parse(template)+'<p>' );
    });
  }
  
  function _zh_unpack(str){
    var ar1 = new Array();
    var ar3;
    var ar2 = str.split('&');
    for(i=0;i<ar2.length;i++){
      ar3 = ar2[i].split('=');
      ar1[ ar3[0] ] = ar3[1];
    }
    return ar1;
  }
