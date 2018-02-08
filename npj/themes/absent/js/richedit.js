/***************************************

RichEdit 2.08

  Copyright (c) 2005, Roman Ivanov
  Based on Cross-Browser Rich Text Editor
  Copyright (c) 2003-2004, Kevin Roth
  All rights reserved.

For license see LICENSE.TXT

****************************************/

var allRTEs = "";

var RichEdit = function(){
 //init variables
 this.isRichText = false;
 this.rng = "";

 this.imagesPath = "";
 this.dlgPath = "";
 this.cssFile = "";
 this.buttons = new Array();
}

//"inheriting" from ProtoEdit
RichEdit.prototype = new ProtoEdit();
RichEdit.prototype.constructor = RichEdit;

RichEdit.prototype.init = function(id, name, nameClass, imgPath, dlgPath, css){
 if (!(isMZ || isIE)) return;
 
 //check to see if designMode mode is available
 if (document.getElementById && document.designMode && !isSafari && !isKonqueror) {
   this.isRichText = true;
 }
 //set paths vars
 this.imagesPath = (imgPath?imgPath:"images/");
 this.dlgPath = (dlgPath?dlgPath:"dialogs/");
 this.cssFile = css;
 this.editorName = name;
 this.editorNameClass = nameClass;
 this.id = id;
 this.rte = '_rte_'+id;
 //for testing standard textarea, uncomment the following line
 //this.isRichText = false;

 this.area = document.getElementById(id);
 if (this.isRichText) {
  if (allRTEs.length > 0) allRTEs += ";";
  allRTEs += this.rte;
  var w = this.area.offsetWidth;
  var h = this.area.offsetHeight;
  if (w<50) w = "100%";
  if (h<50) h = 500;
  this.area.style.visibility = "hidden";
  this.area.style.display = "none";
  this.writeRTE(this.area.value, w, h, true, false);
 }
}

RichEdit.prototype.writeRTE = function(html, width, height, buttons, readOnly){
 if (readOnly) buttons = false;
 if (buttons == true) {
  select  = '<td><select id="fmtsel_' + this.id + '" onchange="document.getElementById(\'' + this.id + '\')._owner.Select(\'fmtsel_'+this.id+'\');">';
  select += ' <option value="<p>">Normal</option>';
  select += ' <option value="<p>">Paragraph</option>';
  select += ' <option value="<h1>">Heading 1</option>';
  select += ' <option value="<h2>">Heading 2</option>';
  select += ' <option value="<h3>">Heading 3</option>';
  select += ' <option value="<h4>">Heading 4</option>';
  select += ' <option value="<h5>">Heading 5</option>';
  select += ' <option value="<h6>">Heading 6</option>';
  select += ' <option value="<address>">Address</option>';
  select += ' <option value="<pre>">Formatted</option>';
  select += '</select></td>';

  this.actionName = "document.getElementById('" + this.id + "')._owner.FormatText";
  this.addButton("customhtml",select);
  this.addButton("bold", "Bold", "'bold'");
  this.addButton("italic", "Italic", "'italic'");
  this.addButton("underline", "Underline", "'underline'");
  this.addButton("strike", "Strikethrough", "'strikethrough'");
  this.addButton(" ");
  this.addButton("left", "Align Left", "'justifyleft'");
  this.addButton("center", "Center", "'justifycenter'");
  this.addButton("right", "Align Right", "'justifyright'");
  this.addButton("justify", "Justify Full", "'justifyfull'");
  this.addButton(" ");
  this.addButton("hr", "Horizontal Rule", "'inserthorizontalrule'");
  this.addButton(" ");
  this.addButton("ol", "Ordered List", "'insertorderedlist'");
  this.addButton("ul", "Unordered List", "'insertunorderedlist'");
  this.addButton(" ");
  this.addButton("outdent", "Outdent", "'outdent'");
  this.addButton("indent", "Indent", "'indent'");
//  this.addButton("forecolor", "Text Color", "'forecolor'");
//  this.addButton("hilitecolor", "Background Color", "'hilitecolor'");
  this.addButton(" ");
  this.addButton("createlink", "Insert Link", "'createlink'");
  this.addButton("createtable", "Insert Table", "'createtable'");
  this.addButton("createimage", "Insert Image", "'createimage'");
  this.addButton(" ");
  this.addButton("help","Help & About","","document.getElementById('" + this.id + "')._owner.help");
//  this.addButton("word", "Clean Word", "","document.getElementById('" + this.id + "')._owner.cleanWord");
/*
  if (isIE)     this.addButton("spellcheck", "Spell Check", "''", "document.getElementById('" + this.id + "')._owner.checkspell");
  this.addButton(" ");
  this.addButton("cut", "Cut", "'cut'");
  this.addButton("copy", "Copy", "'copy'");
  this.addButton("paste", "Paste", "'paste'");
  this.addButton(" ");
  this.addButton("undo", "undo", "'undo'");
  this.addButton("redo", "redo", "'redo'");
*/
 }
 var wd = width.toString();
 var ht = height.toString();
 if (wd.indexOf("%")==-1 && wd.indexOf("px")==-1) wd = wd+"px";
 if (ht.indexOf("%")==-1 && ht.indexOf("px")==-1) ht = ht+"px";
 genhtml = '<iframe id="' + this.rte + '" src="' + this.imagesPath + 'z.html" name="' + this.rte + '" width="' + wd + '" height="' + ht + '"></iframe>\n';
 if (!readOnly) genhtml += '<br /><input type="checkbox" id="chkSrc' + this.rte + '" onclick="document.getElementById(\'' + this.id + '\')._owner.toggleHTMLSrc(\'' + this.rte + '\');" />&nbsp;<label for="chkSrc' + this.rte + '">View Source</label>\n';
 var ifcode = 'frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="visibility:hidden; display: none; border: solid 1px; position: absolute;"></iframe>\n';
// genhtml += '<iframe width="254" height="174" id="cp' + this.id + '" src="' + this.dlgPath + 'palette.htm" '+ifcode;
 genhtml += '<iframe width="230" height="100" id="cpl' + this.id + '" src="' + this.dlgPath + 'link.htm" ' + ifcode;
 genhtml += '<iframe width="230" height="100" id="tbl' + this.id + '" src="' + this.dlgPath + 'table.htm" '+ ifcode;
 genhtml += '<iframe width="230" height="100" id="img' + this.id + '" src="' + this.dlgPath + 'image.htm" '+ ifcode;

 try {
  var toolbar = document.createElement("div");
  toolbar.id = "tb_"+this.id;
  toolbar.className = "toolbar";
  toolbar.style.width  = wd;
  this.area.parentNode.insertBefore(toolbar, this.area);
  toolbar = document.getElementById("tb_"+this.id);
  toolbar.innerHTML = this.createToolbar(this.id, wd);
  var iframes = document.createElement("div");
  iframes.id = "if_"+this.id;
  this.area.parentNode.insertBefore(iframes, this.area);
  iframes = document.getElementById("if_"+this.id);
  iframes.innerHTML = genhtml;
 } catch(e){};

 this.html = html;
 this.readOnly = readOnly;
 this.enableDesignMode();
}

RichEdit.prototype.getHTML = function(){
 if (document.all)
   this.oRTE = frames[this.rte].document;
 return this.makeXHTML(this.oRTE.body.innerHTML);
}

RichEdit.prototype.setSize = function(w, h){
 oRTE = document.getElementById(this.rte);
 oRTE.style.width = w+'px';
 oRTE.style.height= h+'px';
}

RichEdit.prototype.setHTML = function(html){
 if (document.all)
  this.oRTE = frames[this.rte].document;
 else
  this.oRTE = document.getElementById(this.rte).contentWindow.document;

 var frameHtml = "<html id=\"" + this.rte + "\">\n";
 frameHtml += "<head>\n";
 //to reference your stylesheet, set href property below to your stylesheet path and uncomment
 if (this.cssFile && (this.cssFile.length > 0)) {
   frameHtml += "<link media=\"all\" type=\"text/css\" href=\"" + this.cssFile + "\" rel=\"stylesheet\">\n";
 }
 frameHtml += "<style>\n";
 frameHtml += "body {\n";
 frameHtml += "  background: #FFFFFF;\n";
 frameHtml += "  margin: 0px;\n";
 frameHtml += "  padding: 0px;\n";
 frameHtml += "}\n";
 frameHtml += "  strike, s { color:#999999 }    \n";
 frameHtml += "  s img { filter:Gray } \n";
 frameHtml += "  table { border-collapse: collapse; } \n";
 frameHtml += "  table td { border: solid black 1px; } \n";
 frameHtml += "</style>\n";
 frameHtml += "</head>\n";
 frameHtml += "<body class='wrte'>\n";
 frameHtml += html + "\n";
 frameHtml += "</body>\n";
 frameHtml += "</html>";

  this.oRTE.open();
  this.oRTE.write(frameHtml);
  this.oRTE.close();
}

RichEdit.prototype.enableDesignMode = function(){
 
 if (document.all) {
   oRTE = frames[this.rte];
   this.setHTML(this.html);
   if (!this.readOnly) oRTE.document.designMode = "On";
 } else {
   try {
     if (!this.readOnly) document.getElementById(this.rte).contentDocument.designMode = "on";
     try {
       this.setHTML(this.html);
     } catch (e) {
       alert("Error preloading content.");
     }
   } catch (e) {
     //gecko may take some time to enable design mode.
     //Keep looping until able to set.
     if (isMZ) {
       setTimeout("document.getElementById('" + this.id + "')._owner.enableDesignMode()", 10);
     } else {
       return false;
     }
   }
 }
 this._init(this.id, this.rte);

}

RichEdit.prototype.updateRTEs = function(){
 var vRTEs = allRTEs.split(";");
 for (var i = 0; i < vRTEs.length; i++) 
 {
   this.updateRTE(vRTEs[i]); 
 }
}

RichEdit.prototype.updateRTE = function(){
  if (!this.isRichText) return;

 //set message value
 var oHdnMessage = document.getElementById(this.id);
 var readOnly = false;
 
 //check for readOnly mode
 if (document.all) {
   if (frames[this.rte].document.designMode != "On") readOnly = true;
 } else {
   if (document.getElementById(this.rte).contentDocument.designMode != "on") readOnly = true;
 }
 
 if (this.isRichText && !readOnly) {
   //if viewing source, switch back to design view
   if (document.getElementById("chkSrc" + this.rte).checked) {
     document.getElementById("chkSrc" + this.rte).checked = false;
     this.toggleHTMLSrc();
   }
   
   if (oHdnMessage.value == null) oHdnMessage.value = "";
   if (document.all) {
     oHdnMessage.value = this.makeXHTML(frames[this.rte].document.body.innerHTML);
   } else {
     oHdnMessage.value = document.getElementById(this.rte).contentWindow.document.body.innerHTML;
   }
    
   //if there is no content (other than formatting) set value to nothing
   if (this.stripHTML(oHdnMessage.value.replace("&nbsp;", " ")) == "" 
     && oHdnMessage.value.toLowerCase().search("<hr") == -1
     && oHdnMessage.value.toLowerCase().search("<img") == -1) oHdnMessage.value = "";
   //fix for gecko
   if (escape(oHdnMessage.value) == "%3Cbr%3E%0D%0A%0D%0A%0D%0A") oHdnMessage.value = "";
 }
}

RichEdit.prototype.toggleHTMLSrc = function(){
 //contributed by Bob Hutzel (thanks Bob!)
 if (document.all)
   this.oRTE = frames[this.rte].document;

 if (document.getElementById("chkSrc" + this.rte).checked) {
   document.getElementById("tb_"+this.id).style.visibility = "hidden";
   if (document.all) {
     var text = this.oRTE.body.innerHTML;
     this.oRTE.body.innerText = this.makeXHTML(text);
   } else {
     var htmlSrc = this.oRTE.createTextNode(this.oRTE.body.innerHTML);
     this.oRTE.body.innerHTML = "";
     this.oRTE.body.appendChild(htmlSrc);
   }
 } else {
   document.getElementById("tb_"+this.id).style.visibility = "visible";
   if (document.all) {
     // was: this.oRTE.body.innerHTML = this.oRTE.body.innerText;
     //fix for IE
     var output = escape(this.oRTE.body.innerText);
     output = output.replace("%3CP%3E%0D%0A%3CHR%3E", "%3CHR%3E");
     output = output.replace("%3CHR%3E%0D%0A%3C/P%3E", "%3CHR%3E");
     
     this.oRTE.body.innerHTML = unescape(output);
   } else {
     var htmlSrc = this.oRTE.body.ownerDocument.createRange();
     htmlSrc.selectNodeContents(this.oRTE.body);
     this.oRTE.body.innerHTML = htmlSrc.toString();
   }
 }
}

RichEdit.prototype.runDialog = function(command, dialog){
 //save current values
 parent.command = command;
 var dlg = document.getElementById(dialog + this.id);

 if (document.all) 
   frames[dialog + this.id].document.rte = this;
 else 
   dlg.contentWindow.document.rte = this;

 //position and show color palette
 buttonElement = document.getElementById(command + '_' + this.id);
 dlg.style.left = this.getOffsetLeft(buttonElement) + "px";
 dlg.style.top = (this.getOffsetTop(buttonElement) + buttonElement.offsetHeight) + "px";
 if (dlg.style.visibility == "hidden") {
   dlg.style.visibility = "visible";
   dlg.style.display = "inline";
 } else {
   dlg.style.visibility = "hidden";
   dlg.style.display = "none";
 }
}

RichEdit.prototype.closeAllDialogs = function(){
 this.closeDialog("cp");
 this.closeDialog("cpl");
 this.closeDialog("tbl");
 this.closeDialog("img");
}

RichEdit.prototype.closeDialog = function(dialog){
 var dlg = document.getElementById(dialog + this.id);
 if (dlg)
 {
  dlg.style.visibility = "hidden";
  dlg.style.display = "none";
 }
}

//Function to format text in the text box
RichEdit.prototype.FormatText = function(command, option){
 var oRTE;
 if (document.all) {
   oRTE = frames[this.rte];
   
   //get current selected range
   var selection = oRTE.document.selection; 
   if (selection != null) {
     rng = selection.createRange();
   }
 } else {
   oRTE = document.getElementById(this.rte).contentWindow;
   
   //get currently selected range
   var selection = oRTE.getSelection();
   rng = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
 }

 this.closeAllDialogs();
 
 try {
   if ((command == "forecolor") || (command == "hilitecolor")) {
     this.runDialog(command, 'cp');
   } else if (command == "createlink") {
     this.runDialog(command, 'cpl');
   } else if (command == "createtable") {
     this.runDialog(command, 'tbl');
   } else if (command == "createimage") {
     this.runDialog(command, 'img');
   }
   else {
     oRTE.focus();
     oRTE.document.execCommand(command, false, option);
     oRTE.focus();
   }
 } catch (e) {
   alert(e);
 }
}

//Function to set color
RichEdit.prototype.setColor = function(color){
 if (document.all)
   this.oRTE = frames[this.rte].document;
 
 var parentCommand = parent.command;                 // !!!!!! ?????? wtf !!-marks?
 if (document.all) {
   //retrieve selected range
   var sel = this.oRTE.selection; 
   if (parentCommand == "hilitecolor") parentCommand = "backcolor";
   if (sel != null) {
     var newRng = sel.createRange();
     newRng = rng;
     newRng.select();
   }
 }
 this.oRTE.focus();
 this.oRTE.execCommand(parentCommand, false, color);
 this.oRTE.focus();
 this.closeDialog('cp');
}

//Function to set link
RichEdit.prototype.setLink = function(url){
 if (document.all)
  this.oRTE = frames[this.rte].document;
 
 if (document.all) {
  //retrieve selected range
  var sel = this.oRTE.selection; 
  if (sel != null) {
   var newRng = sel.createRange();
   newRng = rng;
   newRng.select();
  }
 }
 this.oRTE.focus();
 try {
  //ignore error for blank urls
  this.oRTE.execCommand("Unlink", false, null);
  this.oRTE.execCommand("CreateLink", false, url);
 } catch (e) {
  //do nothing
 }
 this.oRTE.focus();
 this.closeDialog('cpl');
}

RichEdit.prototype.insTable = function(rows, cols, border){ //todo - attribs setting
 if (document.all)
   this.oRTE = frames[this.rte].document;
 
 if (document.all) {
   //retrieve selected range
   var sel = this.oRTE.selection; 
   if (sel != null) {
     var newRng = sel.createRange();
     newRng = rng;
     newRng.select();
   }
   win = frames[this.rte];
 } else {
   win = document.getElementById(this.rte).contentWindow;
 }
 rows = parseInt(rows);
 cols = parseInt(cols);
 border = 1;
// cellpadding = 2;
// cellspacing = 2;
 if ((rows > 0) && (cols > 0)) {
   table = this.oRTE.createElement("table");
   table.setAttribute("border", border);
//  table.setAttribute("cellpadding", cellpadding);
//  table.setAttribute("cellspacing", cellspacing);
   tbody = this.oRTE.createElement("tbody");
   for (var i=0; i < rows; i++) {
     tr = this.oRTE.createElement("tr");
     for (var j=0; j < cols; j++) {
       td = this.oRTE.createElement("td");
       br = this.oRTE.createElement("br");
       td.appendChild(br);
       tr.appendChild(td);
     }
     tbody.appendChild(tr);
   }
   table.appendChild(tbody);
   this.insertNodeAtSelection(win, table);
  }
 this.oRTE.focus();
 this.closeDialog('tbl');
}

RichEdit.prototype.insImage = function(url, alt, border, align){
 if (document.all)
   this.oRTE = frames[this.rte].document;

 this.closeDialog('img');
 if ((url == null) || (url == "")) return;
 frames[this.rte].focus();
 
 var newRng;

 if (document.all) {
   //retrieve selected range
   var sel = this.oRTE.selection; 
   if (sel != null) {
     newRng = sel.createRange();
   }
 } else {
   var sel = document.getElementById(this.rte).contentWindow.getSelection();
   document.getElementById(this.rte).contentWindow.focus();
   if (typeof sel != "undefined") {
    try {
      newRng = sel.getRangeAt(0);
    } catch(e) {
      newRng = this.oRTE.createRange();
    }
   } else {
    newRng = this.oRTE.createRange();
   }

 }

 this.oRTE.execCommand("InsertImage", false, url);

 if (document.all) {
  img = newRng.parentElement();
  if (img.tagName.toLowerCase() != "img") {
    img = img.previousSibling;
  }
 } else {
  img = newRng.startContainer.previousSibling;
 }

 if (alt) img.alt    = alt;
 if (border) img.border = parseInt(border || "0");
 if (align) img.align  = align;
}

//function to perform spell check
RichEdit.prototype.checkspell = function(){
 try {
  var tmpis = new ActiveXObject("ieSpell.ieSpellExtension");
  tmpis.CheckAllLinkedDocuments(document);
 }
 catch(exception) {
  if(exception.number==-2146827859) {
    if (confirm("ieSpell not detected.  Click Ok to go to download page."))
      window.open("http://www.iespell.com/download.php","DownLoad");
  } else {
    alert("Error Loading ieSpell: Exception " + exception.number);
  }
 }
}

RichEdit.prototype.getOffsetTop = function(elm){
 var t = elm.offsetTop;
 var p = elm.offsetParent;
 
 while(p){
  t += p.offsetTop;
  p = p.offsetParent;
 }
 
 return t;
}

RichEdit.prototype.getOffsetLeft = function(elm){
 var l = elm.offsetLeft;
 var p = elm.offsetParent;
 
 while(p) {
  l += p.offsetLeft;
  p = p.offsetParent;
 }
 
 return l;
}

RichEdit.prototype.Select = function(selectname){
 var oRTE;
 if (document.all) {
  oRTE = frames[this.rte];
  
  //get current selected range
  var selection = oRTE.document.selection; 
  if (selection != null) {
    rng = selection.createRange();
  }
 } else {
  oRTE = document.getElementById(this.rte).contentWindow;
  
  //get currently selected range
  var selection = oRTE.getSelection();
  rng = selection.getRangeAt(selection.rangeCount - 1).cloneRange();
 }
 
 var idx = document.getElementById(selectname).selectedIndex;
 // First one is always a label
 if (idx != 0) {
  var selected = document.getElementById(selectname).options[idx].value;
  oRTE.focus();
  oRTE.document.execCommand('formatblock', false, selected);
  oRTE.focus();
  document.getElementById(selectname).selectedIndex = 0;
 }
}

RichEdit.prototype.keyDown = function (thEvent) {
 var Key = thEvent.keyCode;
 if (Key==0) Key = thEvent.charCode;
 if (thEvent.altKey && !thEvent.ctrlKey) Key=Key+4096;
 if (thEvent.ctrlKey)  Key=Key+2048;
 if (thEvent.shiftKey && Key==45) Key=Key+8192;

 if (isMZ && thEvent.type == "keypress" && this.checkKey(Key))
 {
   thEvent.preventDefault();
   thEvent.stopPropagation();
   return false;
 }

 var cmd = '';
 switch (Key) {
  case 4181: cmd = "outdent"; break; //U
  case 4169: cmd = "indent"; break; //I
  case 2131: cmd = "strikethrough"; break; //S
  case 2114: cmd = "bold"; break;
  case 2121: cmd = "italic"; break;
  case 2133: cmd = "underline"; break;
  case 2124: //L
  case 4172:
    if (thEvent.shiftKey && thEvent.ctrlKey) {
      cmd = 'insertunorderedlist';
    } else if (thEvent.altKey || thEvent.ctrlKey) {
      cmd = 'createlink';
    }
    break;
  case 2127: //O
  case 2126: //N
   if (thEvent.ctrlKey && thEvent.shiftKey)
      cmd = 'insertorderedlist';
  break;
  case 2134: 
  case 8237: setTimeout("document.getElementById('" + this.id + "')._owner.cleanWord()", 10); break;
 };

 if (cmd) {
  if (isIE) {
   this.FormatText(cmd, true);
   e = frames[this.rte].event;
   try {e.returnValue = false} catch(e) {};
   return false;
  } else {
   this.FormatText(cmd, true);
   thEvent.cancelBubble = true;
   thEvent.preventDefault();
   thEvent.stopPropagation();
   return false;
  }
 }

 // go parent
 document._event = thEvent;
 if (document.onkeydown)
  document.onkeydown();

 if (isMZ && thEvent.type == "keypress" && document.onkeypress)
  document.onkeypress();

 if (document.onkeyup)
  document.onkeyup();

 return true;
}

RichEdit.prototype.stripHTML = function(s2){
 var s = s2.replace(/(<([^>]+)>)/ig,"");
 
 //replace carriage returns and line feeds
 s = s.replace(/\r\n/g," ");
 s = s.replace(/\n/g," ");
 s = s.replace(/\r/g," ");
 
 //trim string
 s = this.trim(s);
 
 return s;
}

RichEdit.prototype.insertNodeAtSelection = function(win, insertNode) {
 if (document.all) {
  //retrieve selected range
  var sel = win.document.selection; 
  if (sel != null) {
   var newRng = sel.createRange();
   var el = newRng.parentElement();
   while (el!=null && el.className!="wrte")
   {
    el = el.parentElement;
   }
   if (el==null || el.className!="wrte")
   {
     var newRng = win.document.body.createTextRange();
     newRng.expand("textedit");
     newRng.collapse(false);
   }
   var html = insertNode.outerHTML;
   try {
     newRng.pasteHTML(html);        
   } catch (e) {
   }
  }
 } else {

  var sel = win.getSelection();
  var range = sel.getRangeAt(0);
  sel.removeAllRanges();
  range.deleteContents();
  var box = range.startContainer;
  var pos = range.startOffset;

  range=document.createRange();

  if (box.nodeType==3 && insertNode.nodeType==3) {

   box.insertData(pos, insertNode.nodeValue);
   range.setEnd(box, pos+insertNode.length);
   range.setStart(box, pos+insertNode.length);

  } else {

   var afterNode;
   if (box.nodeType==3) {

     var textNode = box;
     box = textNode.parentNode;
     var text = textNode.nodeValue;

     textBefore = text.substr(0,pos);
     textAfter = text.substr(pos);
     beforeNode = document.createTextNode(textBefore);
     afterNode = document.createTextNode(textAfter);

     box.insertBefore(afterNode, textNode);
     box.insertBefore(insertNode, afterNode);
     box.insertBefore(beforeNode, insertNode);
     box.removeChild(textNode);
   } else {
     afterNode = box.childNodes[pos];
     box.insertBefore(insertNode, afterNode);
   }
   range.setEnd(afterNode, 0);
   range.setStart(afterNode, 0);
  }
  sel.addRange(range);
 }
}

RichEdit.prototype.oneToLower = function(s) {
 var re = /<[^>~][^>]*>/i;    //beware of title= values
 arr = re.exec(s);
 if (arr === null) return s;

 arrs = String(arr);
 var li = arr.index + arrs.length;
 arrs = "<~" + arrs.substr(1);
 
 s = s.substr(0,arr.index) + arrs.toLowerCase() + s.substr(li);

 return s;
}

RichEdit.prototype.allToLower = function(s) {
 var s1 = s;
 var s2 = s+"+";
 while (s1 != s2)
 { s2 = s1;
   s1 = this.oneToLower(s1);
 }
 return s1.replace( /<~/g, "<" );
}

RichEdit.prototype.makeXHTML = function(s) {         //dont work in Mozilla!
 s = this.allToLower(s);
 var re = /align=(justify|center|right)/ig;
 s = s.replace(re, 'align="$1"');
 var re = /<(br|hr)>/ig;
 s = s.replace(re, '<$1 />');
 var re = /(cellspacing|cellpadding|border)=("|'|)([^ "'><]*)("|'|)/ig;
 s = s.replace(re, '$1="$3"');
 return s;
}

var msCrap = new Array();
i = 0;

msCrap[++i] = "lang=[A-Za-z0-9\-]*";
msCrap[++i] = "style=\"[^\"]+\"";          
msCrap[++i] = "x\:\w+(=\"[^\"]*\"|)"
msCrap[++i] = "<(|\/)SPAN[^>]*>"        
msCrap[++i] = "<(|\/)FONT[^>]*>"        
msCrap[++i] = "<(|\/)OBJECT[^>]*>"
msCrap[++i] = "<(|\/)PARAM[^>]*>"
msCrap[++i] = "<(|\/)V\:[^>]*>"         
msCrap[++i] = "<(|\/)O\:[^>]*>"         
msCrap[++i] = "<(|\/)W\:[^>]*>"
msCrap[++i] = "<[^A-Za-z]xml[^>]*>"     
msCrap[++i] = "\s*class=(mso|xl)[^\s>]*"; 
msCrap[++i] = "<(|\/)st1:[^>]*>"; 
//msCrap[++i] = "<(|\/)[a-z]\:[^>]*>"; ALL namespaced tags

RichEdit.prototype.makeCleanHTML = function(s) {

 for (i=1; i<=msCrap.length; i++) {
  re = new RegExp(msCrap[i], "ig");
  s = s.replace(re,"");              
 }

 //clean up tags
 s = s.replace(/<b [^>]*>/gi,'<b>').                            
   replace(/<i [^>]*>/gi,'<i>').
   replace(/<li [^>]*>/gi,'<li>');

 s = this.cleanUpAttributesAll(s, "table|thead|tbody|tr|td", "colspan|rowspan|align|border|width|class|valign|nowrap");
 s = this.cleanUpAttributesAll(s, "ul|ol", "class|type");

 // replace outdated tags
 s = s.replace(/<b>/gi,'<strong>').
   replace(/<\/b>/gi,'</strong>');

 // mozilla doesn't like <em> tags
 s = s.replace(/<em>/gi,'<i>').
   replace(/<\/em>/gi,'</i>');

 // remove strange DESIGNTIMESP strings
 re = new RegExp("\&lt\;([^\&]?([^\&][^g])*)[ ]DESIGNTIMESP=[0-9]*\&gt\;", "ig");
 s = s.replace(re, "&laquo;$1&raquo;");

 s = s.replace(/<\!--\[[^>]*-->/gi, '');

 //add &nbsp; in empty <td>s
 re = new RegExp("<tr[^>]*height=0([^<]*(<[^\/])*(<\/([^t]))*(<\/t([^r]))*(<\/tr([^>]))*)*<\/tr>", "ig")
 str = s.match(re);   
 s = s.replace(re, "<here>");   

 s = s.replace(/<td([^>]*)><\/td>/gi, "<td$1>&nbsp;</td>").
   replace("<here>", str);

 // nuke double tags
 oldlen = s.length + 1;
 while(oldlen > s.length) {
   oldlen = s.length;
   s = s.replace(/<([a-z][a-z]*)> *<\/\1>/gi,' ').
     replace(/<([a-z][a-z]*)> *<([a-z][^>]*)> *<\/\1>/gi,'<$2>');
 }
 s = s.replace(/<([a-z][a-z]*)><\1>/gi,'<$1>').
   replace(/<\/([a-z][a-z]*)><\/\1>/gi,'<\/$1>');
 
 s = s.replace(/[ ]\r\n/ig, " ").
   replace(/[ ]+/ig, " ").
   replace(/[ ]+>/ig, ">").
   replace(/<br[^>]*>/ig, "<br />\r\n");

 s = s.replace(/<a name=([^>]+)>/gi, "<a class=toc name=$1>");


 return s;

}

RichEdit.prototype.cleanWord = function() {

 if (document.all)
   this.oRTE = frames[this.rte].document;

 var text = this.oRTE.body.innerHTML;
 var found=-1;
 var i = 0;
 while (i<msCrap.length && found==-1) {
  re = new RegExp(msCrap[i], "ig");
  found = text.search(re);
  i++;
 };
 if (found!=-1)
 {
  this.oRTE.body.innerHTML = this.makeCleanHTML(text);
 }
}

RichEdit.prototype.cleanUpAttributesTagOne = function( tag, reAttr, reverseAttr )
{
 var re = /([a-z\:]+)=(("[^"]*")|([^ >]*))/i;
 var arr = re.exec(tag);
 if (arr === null) return tag;

 arrs = String(arr[0]);
 var eq = arrs.indexOf("=");
 var name  = arrs.substr(0,eq);

 var li = arr.index + arrs.length;

 var t = reAttr.test(name);
 if ((!reAttr.test(name) && !reverseAttr) || (reAttr.test(name) && reverseAttr)) 
  return tag.substr(0,arr.index) + tag.substr(li);

 var value = arrs.substr(eq+1);
 if (value.charAt(0) == '"') value = value.substr(1);
 if (value.charAt(value.length-1) == '"') value = value.substr(0,value.length-1);
 value = '"'+ value.replace('"',"\\\"") +'"';

 return tag.substr(0,arr.index) + name + "~=" + value + tag.substr(li);
}

RichEdit.prototype.cleanUpAttributesTag = function( tag, reAttr, reverseAttr )
{
 var s1 = tag;
 var s2 = tag+"+";
 while (s1 != s2)
 { s2 = s1;
   s1 = this.cleanUpAttributesTagOne(s1, reAttr, reverseAttr);
 }
 s1 = s1.replace( /[ ]+/, " ");
 s1 = s1.replace( /[ ]+>/, ">");
 return s1.replace( /~=/g, "=" );
  
}

// standard MSIE s1-s2 tag walker
RichEdit.prototype.cleanUpAttributesOne = function( s, reTag, reAttr, reverseTag, reverseAttr )
{
 var re = /<[^>~][^>]*>/i;
 arr = re.exec(s);
 if (arr === null) return s;

 arrs = String(arr);
 new_arrs = "<~" + arrs.substr(1);
 var li = arr.index + arrs.length;

 var t = reTag.test(arrs)
 if ((t && !reverseTag) || (!t &&reverseTag))
   s = s.substr(0,arr.index) + this.cleanUpAttributesTag( new_arrs, reAttr, reverseAttr ) + s.substr(li);
 else
   s = s.substr(0,arr.index) + new_arrs + s.substr(li);

 return s;
}

RichEdit.prototype.cleanUpAttributesAll = function( s, restrTag, restrAttr, reverseTag, reverseAttr )
{
  // create res
  var reTag  = new RegExp( "<("+restrTag+")[ >]", "i" );
  var reAttr = new RegExp( "^("+restrAttr+")$", "i" );

  // s1-s2 standard MSIE5 workaround
  var s1 = s;
  var s2 = s+"+";
  while (s1 != s2)
  { s2 = s1;
    s1 = this.cleanUpAttributesOne(s1, reTag, reAttr, reverseTag, reverseAttr);
  }
  return s1.replace( /<~/g, "<" );
}

RichEdit.prototype.help = function ()
{
 s =  "              RichEdit 2.00 \n";
 s += "  (c) Roman Ivanov, 2004   \n";
 s += "  based on Cross-Browser Rich Text Editor  \n";
 s += "    (c) Kevin Roth, 2003-2004   \n";
// s += "  http://wackowiki.com/WikiEdit \n";
 s += "\n";
 s += "         Shortcuts:\n";
 s += " Ctrl+B - Bold\n";
 s += " Ctrl+I - Italic\n";
 s += " Ctrl+U - Underline\n";
 s += " Ctrl+Shift+S - Strikethrough\n";
 s += " Alt+I - Indent\n";
 s += " Alt+U - Unindent\n";
 s += " Alt+L - Link\n";
 s += " Ctrl+Shift+L - Unordered List\n";
 s += " Ctrl+Shift+N - Ordered List\n";
 s += " Ctrl+Shift+O - Ordered List\n";
 alert(s);
}
