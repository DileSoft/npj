/*************************************************************************

  SimpleEdit
  v. 2.01

  Copyright (c) 2004, Roman Ivanov
  Copyright (c) 2003-2004, Alexander Babaev
  All rights reserved.

For license see LICENSE.TXT

*************************************************************************/

var SimpleEdit = function(){
 this.buttons = new Array();
}

//"inheriting" from ProtoEdit
SimpleEdit.prototype = new ProtoEdit();
SimpleEdit.prototype.constructor = SimpleEdit;

SimpleEdit.prototype.init = function(id, name, nameClass, imgPath) {

 if (!(isMZ || isIE)) return;
 this._init(id);

 this.imagesPath = (imgPath?imgPath:"images/");
 this.editorName = name;
 this.editorNameClass = nameClass;

 this.actionName = "document.getElementById('" + this.id + "')._owner.insTag";
 this.addButton("h1","h1","'<h1>','</h1>'");
 this.addButton("h2","h2","'<h2>','</h2>'");
 this.addButton(" ");
 this.addButton("bold","bold","'<b>','</b>'");
 this.addButton("italic","italic","'<i>','</i>'");
 this.addButton("underline","underline","'<u>','</u>'");
 this.addButton("strike","strike","'<s>','</s>'");
 this.addButton(" ");
 this.addButton("ul","list","","document.getElementById('" + this.id + "')._owner.li");
// this.addButton("ol","numbered list","'  1. ','',0,1,1");
// this.addButton(" ");
// this.addButton("indent","indent","'  ','',0,1");
// this.addButton("outdent","outdent","","simEdit.unindent");
 this.addButton(" ");
 this.addButton("quote","quote","'\\n<blockquote>','</blockquote>\\n'");
 this.addButton("hr","hr","'','\\n<hr />\\n'");
 this.addButton(" ");
 this.addButton("help","Help & About","","document.getElementById('" + this.id + "')._owner.help");
// this.addButton("textred","textred","'!!','!!',2");
// this.addButton("createlink","hyperlink","","simEdit.createLink");
// this.addButton("createtable","instable","'','\\n#|\\n|| | ||\\n|| | ||\\n|#\\n',2");
 try {
  var toolbar = document.createElement("div");
  toolbar.id = "tb_"+this.id;
  this.area.parentNode.insertBefore(toolbar, this.area);
  toolbar = document.getElementById("tb_"+this.id);
  toolbar.innerHTML = this.createToolbar(1);
 } catch(e){};
}


SimpleEdit.prototype.keyDown = function(event){

 if (!this.enabled) return;
 var Key, k;
 var ctrlKey;
 var shiftKey;
 var pressed;

 Key = event.keyCode;
 if (Key==0) Key = event.charCode;
 ctrlKey = event.ctrlKey;
 shiftKey = event.shiftKey;
 altKey = event.altKey;
 pressed = ctrlKey;

 if (event.altKey && !event.ctrlKey) k=Key+4096;
 if (event.ctrlKey) k=Key+2048;

 if (isMZ && event.type == "keypress" && this.checkKey(k))
 {
   event.preventDefault();
   event.stopPropagation();
   return false;
 }

 //window.status = "IE: event: " + event.type + "! ASCII-value: " + Key + ", Ctrl: " + ctrlKey + ", Shift: " + shiftKey;
 //window.status = "MZ: event: " + event.type + "! ASCII-value: " + Key + " (Char: " + event.charCode + "), Ctrl: " + ctrlKey + ", Shift: " + shiftKey;

 if (pressed)
 {
   processedEvent = false;
   if (isMZ) var ss = this.area.scrollTop;
   switch (Key)
   {
     case 65:
       document.fo.message.select();
       processedEvent = true;
     break;
     case 66: //98
       processedEvent = this.insTag('<b>','</b>');
     break;
     case 76: //108
       //this.insTag('<a href=>','</a>');
       this.li();
       processedEvent = true;
     break;
     case 73: //105
       processedEvent = this.insTag('<i>','</i>');
     break;
     case 85: //117
       processedEvent = this.insTag('<u>','</u>');
     break;
     case 83: //115
       processedEvent = this.insTag('<strike>','</strike>');
     break;
     case 81: //113
       processedEvent = this.insTag('<blockquote>','</blockquote>');
     break;
     case 49:
       processedEvent = this.insTag('<h1>','</h1>');
     break;
     case 50:
       processedEvent = this.insTag('<h2>','</h2>');
     break;
     case 51:
       processedEvent = this.insTag('<h3>','</h3>');
     break;
     case 52:
       processedEvent = this.insTag('<h4>','</h4>');
     break;
   }

   if (processedEvent)
   {
    if (isIE)
    {
     e = window.event;
     e.returnValue = false;
     window.status = "asdf";
     return false;
    } 
    else 
    {
     this.area.scrollTop = ss;
     event.cancelBubble = true;
     event.preventDefault();
     event.stopPropagation();
     return true;
    }
   }
 }
}

SimpleEdit.prototype.insSmile = function(smileName){
  if (isMZ)
  {
   var ss = this.area.scrollTop;
   sel1 = this.area.value.substr(0, this.area.selectionEnd);
   sel2 = this.area.value.substr(this.area.selectionEnd);

   this.area.value = sel1 + " :" + smileName + ": " + sel2;

   selPos = sel1.length + smileName.length + 4;
   this.area.setSelectionRange(selPos, selPos);
   this.area.scrollTop = ss;
  }
  else
  {
   this.area.focus();
   sel = document.selection.createRange();
   sel.text = sel.text + " :" + name + ": ";
   this.area.focus();
  }
}


SimpleEdit.prototype.li = function(){
  if (isMZ)
  {
   var ss = this.area.scrollTop;
   sel1 = this.area.value.substr(0, this.area.selectionStart);
   sel2 = this.area.value.substr(this.area.selectionEnd);
   sel = this.area.value.substr(this.area.selectionStart, 
                       this.area.selectionEnd - this.area.selectionStart);

   selPosStart = this.area.selectionStart;
   selPos = this.area.selectionEnd;

   cnt = 0;
   s = sel;
   while(s.indexOf('\n') >= 0)
   {
     cnt++;
     if (cnt > 50)
       return;
     s = s.replace(/\n/, "<li>");
   }
   re = /\<li\>/g;
   s = s.replace(re, "\n<li>");
   sel = "<ul>\n<li>" + s + "\n</ul>";

   this.area.value = sel1 + sel + sel2;

   selPos += cnt*5 + 14;
   this.area.scrollTop = ss;
  }
  else
  {
   this.area.focus();
   sel = document.selection.createRange();
   s = "<ul>\n" + sel.text;
   s = s.replace(/\n/g, "\n<li>");
   sel.text = s + "\n</ul>\n";
   this.area.focus();
  }
}


SimpleEdit.prototype.help = function ()
{
 s =  "         SimpleEdit 2.00 \n";
 s += "  (c) Roman Ivanov, 2004   \n";
 s += "  (c) Alexander Babaev, 2004   \n";
// s += "  http://wackowiki.com/WikiEdit \n";
 s += "\n";
 s += "         Shortcuts:\n";
 s += " Ctrl+B - Bold\n";
 s += " Ctrl+I - Italic\n";
 s += " Ctrl+U - Underline\n";
 s += " Ctrl+Shift+S - Strikethrough\n";
 s += " Ctrl+Shift+1 - Heading 1\n";
 s += " ...\n";
 s += " Ctrl+Shift+4 - Heading 4\n";
 s += " Ctrl+Q - Blockquote\n";
// s += " Alt+I - Indent\n";
// s += " Alt+U - Unindent\n";
// s += " Ctrl+J - MarkUp (!!)\n";
// s += " Ctrl+H - MarkUp (??)\n";
// s += " Alt+L - Link\n";
// s += " Ctrl+L - Link with description\n";
// s += " Ctrl+Shift+L - Unordered List\n";
// s += " Ctrl+Shift+N - Ordered List\n";
// s += " Ctrl+Shift+O - Ordered List\n";
 alert(s);
}
