// dedit 1.22

var ErrorFunction = "Эта функци\я не доступна в режиме редактировани\я кода!";
var ImgRegx = new RegExp("(.gif)|(.jpg)|(.png)","i");
var ImgMenuFile = "images.htm";
var ColorMenuFile = "selcolor.htm";
var TableMenuFile = "instable.htm";
var ParMenuFile = "inspar.htm";
var doc = document.all;
var rg, dr;
var BaseDir = "";

DECMD_BOLD = 5000;
DECMD_COPY = 5002;
DECMD_CUT =  5003;
DECMD_DELETE = 5004;
DECMD_DELETECELLS = 5005;
DECMD_DELETECOLS =  5006;
DECMD_DELETEROWS =  5007;
DECMD_FINDTEXT = 5008;
DECMD_HYPERLINK = 5016;
DECMD_IMAGE = 5017;
DECMD_INDENT = 5018;
DECMD_INSERTCELL=5019;
DECMD_INSERTCOL=5020;
DECMD_INSERTROW=5021;

DECMD_INSERTTABLE = 5022;

DECMD_ITALIC = 5023;
DECMD_JUSTIFYCENTER = 5024;
DECMD_JUSTIFYLEFT = 5025;
DECMD_JUSTIFYRIGHT = 5026;
DECMD_LOCK_ELEMENT = 5027;
DECMD_MERGECELLS =  5029;

DECMD_ORDERLIST = 5030;
DECMD_OUTDENT = 5031;
DECMD_PASTE = 5032;
DECMD_REDO = 5033;
DECMD_REMOVEFORMAT = 5034;
DECMD_SELECTALL = 5035;
DECMD_SETFORECOLOR = 5046;
DECMD_SPLITCELL = 5047;
DECMD_UNDERLINE = 5048;
DECMD_UNDO = 5049;
DECMD_UNLINK = 5050;
DECMD_UNORDERLIST = 5051;
DECMD_PROPERTIES = 5052;

// OLECMDEXECOPT  
OLECMDEXECOPT_DODEFAULT = 0;
OLECMDEXECOPT_PROMPTUSER = 1;
OLECMDEXECOPT_DONTPROMPTUSER = 2;

// DHTMLEDITCMDF
DECMDF_NOTSUPPORTED = 0;
DECMDF_DISABLED = 1;
DECMDF_ENABLED = 3;
DECMDF_LATCHED = 7;
DECMDF_NINCHED = 11;

//DHTMLEDITAPPEARANCE
DEAPPEARANCE_FLAT = 0;
DEAPPEARANCE_3D = 1;

//OLE_TRISTATE
OLE_TRISTATE_UNCHECKED = 0;
OLE_TRISTATE_CHECKED = 1;
OLE_TRISTATE_GRAY = 2;

var SHOW_TOOLBAR = "";
var GeneralContextMenu = new Array();
var ContextMenu = new Array();
//var CannotUndo = new Array();
var TableContextMenu = new Array();
var MENU_SEPARATOR = ""; // Context menu separator

function ContextMenuItem(string, cmdId) 
{ 
 this.string = string;
 this.cmdId = cmdId;
}

GeneralContextMenu[0] = new ContextMenuItem("Вырезать (Shift+Delete)", DECMD_CUT);
GeneralContextMenu[1] = new ContextMenuItem("Копировать (Ctrl+Insert)", DECMD_COPY);
GeneralContextMenu[2] = new ContextMenuItem("Вставить (Shift+Insert)", DECMD_PASTE);
GeneralContextMenu[3] = new ContextMenuItem(MENU_SEPARATOR, 0);
GeneralContextMenu[4] = new ContextMenuItem("Жирный (Ctrl+B)", DECMD_BOLD);
GeneralContextMenu[5] = new ContextMenuItem("Курсив (Ctrl+I)", DECMD_ITALIC);
GeneralContextMenu[6] = new ContextMenuItem("Подчеркнутый (Ctrl+U)", DECMD_UNDERLINE);
GeneralContextMenu[7] = new ContextMenuItem(MENU_SEPARATOR, 0);
GeneralContextMenu[8] = new ContextMenuItem("По левому краю", DECMD_JUSTIFYLEFT);
GeneralContextMenu[9] = new ContextMenuItem("По центру", DECMD_JUSTIFYCENTER);
GeneralContextMenu[10] = new ContextMenuItem("По правому краю", DECMD_JUSTIFYRIGHT);
GeneralContextMenu[11] = new ContextMenuItem(MENU_SEPARATOR, 0);
GeneralContextMenu[12] = new ContextMenuItem("Ссылка (Ctrl+L)", DECMD_HYPERLINK);
GeneralContextMenu[13] = new ContextMenuItem("Выделить все (Ctrl+A)", DECMD_SELECTALL);
GeneralContextMenu[14] = new ContextMenuItem(MENU_SEPARATOR, 0);
GeneralContextMenu[15] = new ContextMenuItem("Удалить форматирование", DECMD_REMOVEFORMAT);
GeneralContextMenu[16] = new ContextMenuItem(MENU_SEPARATOR, 0);
GeneralContextMenu[17] = new ContextMenuItem("Откат (Ctrl+Z)", DECMD_UNDO);
GeneralContextMenu[18] = new ContextMenuItem("Отменить откат (Ctrl+Y)", DECMD_REDO);
GeneralContextMenu[19] = new ContextMenuItem("Свойства изображения", DECMD_IMAGE);

TableContextMenu[0] = new ContextMenuItem("Вставить строку", DECMD_INSERTROW);
TableContextMenu[1] = new ContextMenuItem("Удалить строки", DECMD_DELETEROWS);
TableContextMenu[2] = new ContextMenuItem(MENU_SEPARATOR, 0);
TableContextMenu[3] = new ContextMenuItem("Вставить столбец", DECMD_INSERTCOL);
TableContextMenu[4] = new ContextMenuItem("Удалить столбцы", DECMD_DELETECOLS);
TableContextMenu[5] = new ContextMenuItem(MENU_SEPARATOR, 0);
TableContextMenu[6] = new ContextMenuItem("Вставить ячейку", DECMD_INSERTCELL);
TableContextMenu[7] = new ContextMenuItem("Удалить ячейки", DECMD_DELETECELLS);
TableContextMenu[8] = new ContextMenuItem("Объединить ячейки", DECMD_MERGECELLS);
TableContextMenu[9] = new ContextMenuItem("Разбить ячейки", DECMD_SPLITCELL);
TableContextMenu[10] = new ContextMenuItem(MENU_SEPARATOR, 0);


function tbContentElement_ShowContextMenu(ConID) 
{ 
 var menuStrings = new Array();
 var menuStates = new Array();
 var state;
 var i;
 var idx = 0;
 ContextMenu.length = 0;
 // Is the selection inside a table? Add table menu if so
 if (doc["DHTMLEditControl"+ConID].QueryStatus(DECMD_INSERTROW) != DECMDF_DISABLED) 
 {
  for (i=0; i<TableContextMenu.length; i++) 
   ContextMenu[idx++] = TableContextMenu[i];
 }
 for (i=0; i<GeneralContextMenu.length; i++) 
  ContextMenu[idx++] = GeneralContextMenu[i];
 
 for (i=0; i<ContextMenu.length; i++) 
 { 
  menuStrings[i] = ContextMenu[i].string;

  if (menuStrings[i] != MENU_SEPARATOR)
   state = doc["DHTMLEditControl"+ConID].QueryStatus(ContextMenu[i].cmdId);
  else
   state = DECMDF_ENABLED;
  
  if (state == DECMDF_DISABLED || state == DECMDF_NOTSUPPORTED) 
   menuStates[i] = OLE_TRISTATE_GRAY;
  else 
   if (state == DECMDF_ENABLED || state == DECMDF_NINCHED)
    menuStates[i] = OLE_TRISTATE_UNCHECKED;
   else 
    menuStates[i] = OLE_TRISTATE_CHECKED;
   
 }
 doc["DHTMLEditControl"+ConID].SetContextMenu(menuStrings, menuStates);
}

function tbContentElement_ContextMenuAction(itemIndex, ConID)
{
 switch (ContextMenu[itemIndex].cmdId) {
  case DECMD_PASTE:
    OnPaste(ConID);
    break;
  case DECMD_UNDO:
    OnUndo(ConID);
    break;
  default:
    doc["DHTMLEditControl"+ConID].ExecCommand(ContextMenu[itemIndex].cmdId, OLECMDEXECOPT_DODEFAULT);
 }
}
 

function HTMLimport(UseFormName, UseFieldName, UseControlName)
{ 
  TextAreaObj = eval("document."+UseFormName+"."+UseFieldName);
  if (TextAreaObj!=null)
  {
   TextAreaObj.style.display="none";
   if(TextAreaObj.value)
    { doc["DHTMLEditControl"+UseControlName].DocumentHTML = TextAreaObj.value;
    }
  }
}

function HTMLexport()
{ FnM = event.srcElement.name;
  for(i=1; i < FormAndFieldNames.length; i++)
   { FormName = FormAndFieldNames[i][0];
     FieldName = FormAndFieldNames[i][1];
     OldTextArea = eval("document."+FormName+"."+FieldName);
     if (FormName==FnM)
      { if(doc["TEXTEditControl"+i].style.display == "block")
         { CurrentText = doc["TEXTEditControl"+i].value;
   } else
         { CurrentText = doc["DHTMLEditControl"+i].DocumentHTML;
   }
  OldTextArea.value = CurrentText;
      }
   }
 return true;
}

function TextEditControl(ControlName)
{ TeC = doc["TEXTEditControl"+ControlName];
  DeC = doc["DHTMLEditControl"+ControlName];
  if(TeC.style.display == "none")
   { TeC.value = DeC.DocumentHTML;
     DeC.style.display="none";
     TeC.style.display = "block";
     doc["HTMLEditButton"+ControlName].className="ToolButtonOn";
   } else 
   { DeC.DocumentHTML = " "+doc["TEXTEditControl"+ControlName].value;
     TeC.style.display = "none";
     DeC.style.display="block";
     doc["HTMLEditButton"+ControlName].className="ToolButtonOff";
   }
}

function ShowSpaceTabs(ControlName)
{  DeC = doc["DHTMLEditControl"+ControlName];
    if(DeC.ShowDetails)
     { DeC.ShowDetails = 0;
 DeC.ShowBorders = 0;
 doc["ShowSpaceTabs"+ControlName].className = "ToolButtonOff";
     } else
     { DeC.ShowDetails = 1;
 DeC.ShowBorders = 1;
 doc["ShowSpaceTabs"+ControlName].className = "ToolButtonOn";
     }
}

function OnUndo(ControlName)
{
//if (!CannotUndo(ControlName)) {
  doc["DHTMLEditControl"+ControlName].ExecCommand(DECMD_UNDO, OLECMDEXECOPT_DODEFAULT); 
  var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
  range.expand("textedit");
  var a = range.findText("#ins_point#");
  if (a) {
   range.pasteHTML("");
   range.collapse(false);
  } 
}

function OnPaste(ControlName)
{
  doc["DHTMLEditControl"+ControlName].ExecCommand(DECMD_PASTE, OLECMDEXECOPT_DODEFAULT); 
  i=0;
  found=-1;
  DeC = doc["DHTMLEditControl"+ControlName];
  stringObj = DeC.DocumentHTML;
  while (i<CleaningRegexps.length && found==-1) {
   re = new RegExp(CleaningRegexps[i], "ig");
   found = stringObj.search(re);
   i++;
  };
  if (found!=-1)
  {
   var range = doc["DHTMLEditControl"+ControlName].DOM.selection.createRange(); 
   range.pasteHTML("#ins_point#"); 
   HtmlCleaner(ControlName); 
  };
}

function KeyDown(ControlName)
{
 var e = doc["DHTMLEditControl"+ControlName].DOM.parentWindow.event; 
 if ((e.ctrlKey==1 && e.keyCode==86) || (e.shiftKey==1 && e.keyCode==45)) 
  {
   e.keyCode=0;
   OnPaste(ControlName);
  }
 if ((e.ctrlKey==1 && e.keyCode==90) || (e.altKey==1 && e.keyCode==8)) 
  {
   e.keyCode=0;
   OnUndo(ControlName);
  }
}

function SupClick(ControlName)
{
 var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
 range.pasteHTML("<sup>"+range.htmlText+"</sup>");
}

function SubClick(ControlName)
{
 var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
 range.pasteHTML("<sub>"+range.htmlText+"</sub>");
}

function HrClick(ControlName)
{
 var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
 range.pasteHTML("\n<hr />\n");
}

function SymbolClick(ControlName)
{
 document.all["SymbolMenu"+ControlName].style.display="";
}

function JustifyClick(ControlName)
{

  function parseP(par, ControlName)
  { //alert(par.tagName);
    switch (par.tagName) {
      case "P":
        var t = par.outerHTML;
        re = new RegExp("(\<P[^\>]*)ALIGN=[^ \>]*(| [^\>]*)\>","ig");
        t = t.replace(re, "$1align=\"justify\"$2>");
        re = new RegExp("\<P\>","ig");
        t = t.replace(re, "<p align=\"justify\">");
        par.outerHTML = t;
       break;
      
      case "TD":
         doc["DHTMLEditControl"+ControlName].ExecCommand(DECMD_JUSTIFYLEFT, OLECMDEXECOPT_DODEFAULT); 
         var par = range.parentElement();
         parseP(par, ControlName);
       break;

     default:
         par = par.parentElement;
         if (par!=null) {
          parseP(par, ControlName);
         }
       break;
    }
  };

  doc["JustifyButton"+ControlName].className="ToolButtonOn";
  var sel = document.all["DHTMLEditControl"+ControlName].DOM.selection;
  if ( "Text" == sel.type || "None" == sel.type){
     range = sel.createRange();
     range.collapse();
     var par = range.parentElement();
     parseP(par, ControlName);
  }
  else 
   if ( "Control" == sel.type) {
     range = sel.createRange();
     var par = range.commonParentElement();
     if (par.tagName=="P") {
      re = new RegExp("\<P[ ]*(|ALIGN=[^\>]*)\>","ig");
      par.outerHTML = par.outerHTML.replace(re, "<p align=\"justify\">");
     }
    }
  doc["JustifyButton"+ControlName].className="ToolButtonOff";
}


function restoreInsPoint(ControlName)
{
 var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
 range.expand("textedit");
//range.select;
 var a = range.findText("#ins_point#");
 if (a) {
  range.pasteHTML("");
  range.select();
  range.collapse(false);
//CannotUndo[ControlName]=true;
//document.all["DHTMLEditControl"+ControlName].refresh();
 };

}

function HtmlCleaner(ControlName)
{ if(doc["TEXTEditControl"+ControlName].style.display == "block")
   { doc["DHTMLEditControl"+ControlName].DocumentHTML = doc["TEXTEditControl"+ControlName].value;
     doc["TEXTEditControl"+ControlName].disabled=true;
   }
  document.body.style.cursor="wait";
  //doc["TagCleanerButton"+ControlName].className="ToolButtonOn";
  tm = setTimeout("Html2Cleaner("+ControlName+")", 100);
}

OLE = new Array(OLECMDEXECOPT_DONTPROMPTUSER, OLECMDEXECOPT_DODEFAULT);

function SetColor(ControlName)
{  if(doc["TEXTEditControl"+ControlName].style.display == "none")
    { doc["ColorSetButton"+ControlName].className="ToolButtonOn";
      var arr = showModalDialog( CUseImageDir[ControlName]+ColorMenuFile,"","font-family:Verdana; font-size:12; dialogWidth:30em; dialogHeight:30em");
     if (arr != null)
       { doc["DHTMLEditControl"+ControlName].ExecCommand(DECMD_SETFORECOLOR,OLECMDEXECOPT_DODEFAULT, arr);
       }
      doc["ColorSetButton"+ControlName].className="ToolButtonOff";       
    } else
    { alert(ErrorFunction);
    }
}

function ButtonActionControl()
{ ButID = event.srcElement.id;
  if (ButID.indexOf("DECMD_")>0)
   { cName = event.srcElement.className;
     if(ButID.substring(0,1) == "b")
      { ControlName = parseInt(ButID.substring(1, ButID.length));
      } else
      { ControlName = parseInt(ButID);
      }
     CMDs = eval(""+ButID.substring(ButID.indexOf("_")+1, ButID.length));
     if(doc["TEXTEditControl"+ControlName].style.display == "none")
      { 
//doc["DHTMLEditControl"+ControlName].focus();     zks
//        alert(CMDs);
         switch (CMDs) {
          case DECMD_PASTE:
            OnPaste(ControlName);
            break;
          case DECMD_UNDO:
            OnUndo(ControlName);
            break;
          default:
           doc["DHTMLEditControl"+ControlName].ExecCommand(CMDs, OLECMDEXECOPT_PROMPTUSER);
         }
      } else
      { alert(ErrorFunction);
      }
     event.srcElement.className="ToolButtonOff";
   }
}

function ButtonSetUp()
{ ButID = event.srcElement.id;
  if ( (ButID.indexOf("DECMD_")>0)&&(event.srcElement.style.backgroundImage.indexOf("_off")<=0))
   { event.srcElement.className="ToolButtonOn";
   }
}

function ResetControl()
{ tm = setTimeout("ResetControl2("+event.srcElement.name+")", 10);
}

function ResetControl2(FnM, ControlName)
{ if(typeof(FnM)=="object")
  { FnM = FnM.name;
  }
  for (i=1; i< FormAndFieldNames.length; i++)
   { if(FormAndFieldNames[i][0] == FnM)
      { if(doc["TEXTEditControl"+i].style.display == "none")
         { HTMLimport(FnM, FormAndFieldNames[i][1], i);
   } else
         { TextAreaObjValue = eval("document."+FormAndFieldNames[i][0]+"."+FormAndFieldNames[i][1]).value;
           doc["TEXTEditControl"+i].value = TextAreaObjValue;
         }
      }
   }
}

function ResetControl3(ControlName)
 { doc["RefreshControl"+ControlName].className = "ToolButtonOn";
   if (confirm("Отменить все изменения\nи вернуть данные в исходное состояние?"))
    { if(doc["TEXTEditControl"+ControlName].style.display == "none")
       { HTMLimport(FormAndFieldNames[ControlName][0], FormAndFieldNames[ControlName][1], ControlName);
       }else
       { doc["TEXTEditControl"+ControlName].value = eval("document."+FormAndFieldNames[ControlName][0]+"."+FormAndFieldNames[ControlName][1]).value;
       }
    }
   tm = setTimeout("doc[\"RefreshControl"+ControlName+"\"].className = \"ToolButtonOff\"", 100);
 }

var ControlParam = new Array("DECMD_UNDO", "DECMD_REDO");

function ControlChanged(ControlName)
 { for(i=0; i<ControlParam.length; i++)
    { iname = CUseImageDir[ControlName]+"t-"+ControlParam[i].substring(ControlParam[i].indexOf("_")+1, ControlParam[i].length).toLowerCase();
      stat = doc["DHTMLEditControl"+ControlName].QueryStatus(eval(ControlParam[i]));
      elm = doc["b"+ControlName+"_"+ControlParam[i]];
      if(stat == DECMDF_ENABLED || stat == DECMDF_NINCHED)
       { if (elm.style.backgroundImage != "url("+iname+".gif)")
          { elm.style.backgroundImage = "url("+iname+".gif)";
          }
 } else
       { if(elm.style.backgroundImage != "url("+iname+"_off.gif)")
          { elm.style.backgroundImage = "url("+iname+"_off.gif)";
          }
       }
    }

//   if (CannotUndo[ControlName]) {
//   iname = CUseImageDir[ControlName]+"t-undo";
//   doc["b"+ControlName+"_DECMD_UNDO"].style.backgroundImage = "url("+iname+"_off.gif)"; };
}

if (isIE)
{
 document.onmouseup=ButtonActionControl;
 document.onmousedown=ButtonSetUp;
 document.writeln("<style>\n.ToolButtonOn, .ToolButtonOff {cursor: default; background-repeat: no-repeat; background-attachment: fixed; background-position: center center; font-size: 12px}\n.ToolButtonOn {background-color: buttonface; margin: 1; border-left: 1 solid buttonshadow; border-right: 1 solid buttonhighlight; border-top: 1 solid buttonshadow; border-bottom: 1 solid buttonhighlight}\n.ToolButtonOff {background-color: buttonface; margin: 1; border-left: 1 solid buttonhighlight; border-right: 1 solid buttonshadow; border-top: 1 solid buttonhighlight; border-bottom: 1 solid buttonshadow}\n</style>\n");
}
 
var CountControls = 0;
var FormAndFieldNames = new Array();
var Csize = new Array();
var CUseImageDir = new Array();
var CItemPath = new Array();
var CRemTagList = new Array();
var CRemAttribList = new Array();
var ActivateActiveXstatus = false;

function CreateEditControl(UseFormName, UseFieldName, UseImageDir, SizeControl, ItemPath, Activate)
{ 
   if (!isIE) return;
   CountControls++;
   //alert (CountControls);
   CUseImageDir[CountControls] = UseImageDir;
   CItemPath[CountControls] = ItemPath;
   if(SizeControl.length > 3)
   { 
    Csize[CountControls] = SizeControl.split(",");
    Csize[CountControls][1] = parseInt(Csize[CountControls][1]) - 28;
   } else
   { 
    FrM = eval("document."+UseFormName+"."+UseFieldName);
    if ((parseInt(FrM.style.width)) && (parseInt(FrM.style.height)) )
    { 
     Wdth = parseInt(FrM.style.width);
     Hght = parseInt(FrM.style.height);
    } else 
    { 
     Wdth = 450;
     Hght = 250;
    }
    Csize[CountControls] = new Array(Wdth, (Hght - 28));
   }
   //CRemTagList[CountControls] = RemTagList.replace(/ /g,"");
   //CRemAttribList[CountControls] = RemAttribList.replace(/ /g,"");
   FormAndFieldNames[CountControls] = new Array(UseFormName, UseFieldName);
   dvvar="<table id=\"DHTMLEditControlTable"+CountControls+"\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin: 1; margin-left: -1; border-top: 1 solid buttonface; border-left: 1 solid buttonface\" width=\""+Csize[CountControls][0]+"\" height=\""+Csize[CountControls][1]+"\">\n<tr><td class=\"ToolButtonOff\">\n<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\">\n<tr>";
   dvvar=dvvar+"\n<td id=\"RefreshControl"+CountControls+"\" background=\""+UseImageDir+"t-refresh.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" onclick=\"ResetControl3("+CountControls+")\" title=\"Отменить все изменения\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_COPY\"          background=\""+UseImageDir+"t-copy.gif\"   width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Копировать\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_CUT\"           background=\""+UseImageDir+"t-cut.gif\"    width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Вырезать\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_PASTE\"         background=\""+UseImageDir+"t-paste.gif\"  width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Вставить из буфера\">&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-undo_off.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Откат\" id=\"b"+CountControls+"_DECMD_UNDO\">&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-redo_off.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Отменить откат\" id=\"b"+CountControls+"_DECMD_REDO\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-link.gif\"     width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Гиперссылка\" id=\""+CountControls+"_DECMD_HYPERLINK\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-image.gif\"    width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Добавить картинку\" id=\"AddImages_"+CountControls+"\" onclick=\"InsertImage("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"HrButton"+CountControls+"\" background=\""+UseImageDir+"t-hr.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Горизонтальная линия\" onclick=\"HrClick("+CountControls+")\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"SymbolButton"+CountControls+"\" background=\""+UseImageDir+"t-symbol.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Вставка символа\" onclick=\"SymbolClick("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"ShowSpaceTabs"+CountControls+"\" background=\""+UseImageDir+"t-paragraph.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" onclick=\"ShowSpaceTabs("+CountControls+")\" title=\"Скрытые элементы\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"TableInsertButton"+CountControls+"\" background=\""+UseImageDir+"instable.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Вставка таблицы\" onclick=\"InsertTable("+CountControls+")\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td >&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"TextConvertButton_1"+CountControls+"\" background=\""+UseImageDir+"quote.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Преобразовать абзац, тип 1\" onclick=\"ZH_ConvertText("+CountControls+",'1')\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"TextConvertButton_2"+CountControls+"\" background=\""+UseImageDir+"quote.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Преобразовать абзац, тип 2\" onclick=\"ZH_ConvertText("+CountControls+",'2')\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"QuotesButton"+CountControls+"\" background=\""+UseImageDir+"quote.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Цитата\" onclick=\"ZH_Quotes("+CountControls+")\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"ContentLinkButton"+CountControls+"\" background=\""+UseImageDir+"quote.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Ссылка на раздел\" onclick=\"ZH_ContentLink("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_BOLD\"          background=\""+UseImageDir+"t-bold.gif\"   width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Жирный\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_ITALIC\"        background=\""+UseImageDir+"t-italic.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Курсив\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_UNDERLINE\"     background=\""+UseImageDir+"t-u.gif\"      width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Подчеркнутый\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"ColorSetButton"+CountControls+"\" background=\""+UseImageDir+"fgcolor.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Цвет\" onclick=\"SetColor("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
/*   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_JUSTIFYLEFT\"   background=\""+UseImageDir+"t-left.gif\"   width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"По левому краю\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_JUSTIFYCENTER\" background=\""+UseImageDir+"t-center.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"По центру\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\""+CountControls+"_DECMD_JUSTIFYRIGHT\"  background=\""+UseImageDir+"t-right.gif\"  width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"По правому краю\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td id=\"JustifyButton"+CountControls+"\" background=\""+UseImageDir+"t-justify.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"По ширине\" onclick=\"JustifyClick("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
*/ 
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-ol.gif\"       width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Нумерация\" id=\""+CountControls+"_DECMD_ORDERLIST\">&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-ul.gif\"       width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Маркеры\" id=\""+CountControls+"_DECMD_UNORDERLIST\">&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-block.gif\"    width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Увеличить отступ\" id=\""+CountControls+"_DECMD_INDENT\">&nbsp;</td>";
   dvvar=dvvar+"\n<td background=\""+UseImageDir+"t-unblock.gif\"  width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Уменьшить отступ\" id=\""+CountControls+"_DECMD_OUTDENT\">&nbsp;</td>";
//   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"SupButton"+CountControls+"\" background=\""+UseImageDir+"t-sup.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Верхний индекс\" onclick=\"SupClick("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"SubButton"+CountControls+"\" background=\""+UseImageDir+"t-sub.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Нижний индекс\" onclick=\"SubClick("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n<td >&nbsp;</td>";
   dvvar=dvvar+"\n<td id=\"HTMLEditButton"+CountControls+"\" class=\"ToolButtonOff\" onclick=\"TextEditControl("+CountControls+")\" title=\"Редактировать код\"><b>&nbsp;HTML&nbsp;</b></td>";
//   dvvar=dvvar+"\n<td id=\"TagCleanerButton"+CountControls+"\" background=\""+UseImageDir+"t-word.gif\" width=\"20\" height=\"23\" class=\"ToolButtonOff\" title=\"Очистка нежелательных Тэгов\" onclick=\"HtmlCleaner("+CountControls+")\">&nbsp;</td>";
   dvvar=dvvar+"\n</tr>\n</table>\n</td></tr>\n";
   dvvar=dvvar+"<tr>\n<td class=\"ToolButtonOff\" width=\""+Csize[CountControls][0]+"\" height=\""+Csize[CountControls][1]+"\">\n";
   dvvar=dvvar+"<object classid=\"clsid:2D360201-FFF5-11D1-8D03-00A0C959BC0A\" id=\"DHTMLEditControl"+CountControls+"\" width=\"100%\" height=\"100%\" CODEBASE=\""+UseImageDir+"dhtmled.cab#Version=6,1,0,8244\">\n";
   dvvar=dvvar+"<param name=\"ActivateApplets\" value=\"1\">\n<param name=\"ActivateActiveXControls\" value=\"1\">\n";
   dvvar=dvvar+"<param name=\"UseDivOnCarriageReturn\" value=\"0\">\n";
   dvvar=dvvar+"</object>";
   dvvar=dvvar+"<object ID=\"tblInfo"+CountControls+"\" CLASSID=\"clsid:47B0DFC7-B7A3-11D1-ADC5-006008A5848C\" style=\"display: none; \">";
   dvvar=dvvar+"</object>\n";
   dvvar=dvvar+"<TEXTAREA id=\"TEXTEditControl"+CountControls+"\" style=\"display: none; width: 100%; height: 100%\">";
   dvvar=dvvar+"</TEXTAREA>";
   dvvar=dvvar+"</td>\n</tr>\n</table>\n<";
   dvvar=dvvar+"script LANGUAGE=\"javascript\" FOR=\"DHTMLEditControl"+CountControls+"\" EVENT=\"ShowContextMenu\">\nreturn tbContentElement_ShowContextMenu("+CountControls+")\n<"+"/script>\n<"+"script LANGUAGE=\"javascript\" FOR=\"DHTMLEditControl"+CountControls+"\" EVENT=\"ContextMenuAction(itemIndex)\">\nreturn tbContentElement_ContextMenuAction(itemIndex,"+CountControls+")\n</"+"script>\n<"+"script LANGUAGE=\"javascript\" FOR=\"DHTMLEditControl"+CountControls+"\" EVENT=\"DisplayChanged()\">\nControlChanged("+CountControls+")\n</"+"script>";
   dvvar=dvvar+"\n<"+"script LANGUAGE=\"javascript\" FOR=\"DHTMLEditControl"+CountControls+"\" EVENT=\"onkeydown()\">\nKeyDown("+CountControls+")\n</"+"script>";
   dvvar=dvvar+"<div id=\"SymbolMenu"+CountControls+"\" style=\"position:absolute; left:0; top:0; z-index:100; display:none; width:44px; height:140px; background-color: #D4D0C8; layer-background-color: #D4D0C8; border: 1px none #000000\"><table width=100% border=0><tr><td>nbsp</td><td>&nbsp;</td></tr><tr><td>&amp;</td><td>&nbsp;</td></tr><tr><td>&copy;</td><td>&nbsp;</td></tr><tr><td>&#153;</td><td>&nbsp;</td></tr><tr><td>&reg;</td><td>&nbsp;</td></tr><tr><td>&#136;</td><td>&nbsp;</td></tr></table></div>";
   //dvvar=dvvar+"<iframe id=\"ZH_template\" style=\"display:none;\"></iframe>";
   if (Activate) document.write(dvvar);
   else return dvvar;
   if (Activate && doc["DHTMLEditControl"+CountControls].getAttribute("ActivateActiveXControls"))
   { HTMLimport(UseFormName,UseFieldName,CountControls);
     ActivateActiveXstatus = true;
       doc["DHTMLEditControlTable"+CountControls].setAttribute("ActivateActiveXControls", 1);
     eval("document."+UseFormName).onsubmit=HTMLexport;
     eval("document."+UseFormName).onreset=ResetControl;
       doc["DHTMLEditControlTable"+CountControls].style.display="block";
   } else
   { doc["DHTMLEditControlTable"+CountControls].style.display="none";
   }
}


/*** Kukutz ***/

var CleaningRegexps = new Array();

CleaningRegexps[1] = "lang\=[A-Za-z0-9\-]*";
CleaningRegexps[2] = "style\=\"[^\"]+\"";
CleaningRegexps[3] =  "x\:[\w]+(\=\"[^\"]*\"|)"
// CleaningRegexps[4] =  "[ ]\r\n"                  --> " "
CleaningRegexps[4] =  "\<(|\/)SPAN[^\>]*\>"
CleaningRegexps[5] =  "\<(|\/)FONT[^\>]*\>"
CleaningRegexps[6] =  "\<(|\/)OBJECT[^\>]*\>"
CleaningRegexps[7] =  "\<(|\/)PARAM[^\>]*\>"
CleaningRegexps[8] =  "\<(|\/)V\:[^\>]*\>"
CleaningRegexps[9] =  "\<(|\/)O\:[^\>]*\>"
CleaningRegexps[10] =  "\<(|\/)W\:[^\>]*\>"
CleaningRegexps[11] =  "\<[^A-Za-z]xml[^\>]*\>"
CleaningRegexps[12] =  "\<\/b\>\<b\>"
CleaningRegexps[13] = "class\=(mso|xl)[^\s\>]*";

function Html2Cleaner(ControlName)
{ DeC = doc["DHTMLEditControl"+ControlName];
  stringObj = DeC.DocumentHTML;

  for (i=1; i<=CleaningRegexps.length; i++) {
   re = new RegExp(CleaningRegexps[i], "ig");
   stringObj = stringObj.replace(re,"");              
  }

  re = new RegExp("[ ]\r\n", "ig");
  stringObj = stringObj.replace(re, " ");

  re = new RegExp("\&lt\;([^\&]?([^\&][^g])*)[ ]DESIGNTIMESP\=[0-9]*\&gt\;", "ig");
  stringObj = stringObj.replace(re, "&laquo;$1&raquo;");

//  re = new RegExp("\&lt\;(([^D][^E][^S][^I][^G][^N][^T][^I])*)DESIGNTIMESP\=[0-9]*\&gt\;", "ig");
//  stringObj = stringObj.replace(re, "&laquo;$1&raquo;");

  re = new RegExp("\<p[ ]+", "ig");
  stringObj = stringObj.replace(re, "<p ");

  re = new RegExp("\<td[ ]+", "ig");
  stringObj = stringObj.replace(re, "<td ");

//  re = new RegExp("\<tr[^\>]*height\=0((([^\>]*\<[^t])*t[^r])*r[^\>])*\>", "ig"); //-  .*?\<\/tr\>  \w\f\s=\/&;<> (?!\<\/tr\>)
//  re = new RegExp("\<tr[^\>]*height\=0([^\<][^\/][^t][^r][^\>]|[^\/][^t][^r][^\>][^\<]|[^t][^r][^\>][^\<][^\/]|[^r][^\>][^\<][^\/][^t]|[^\>][^\<][^\/][^t][^r])*\>", "ig"); 
//  stringObj = stringObj.replace(re, "");   

  re = new RegExp("\<tr[^\>]*height\=0([^\<]*(\<[^\/])*(\<\/([^t]))*(\<\/t([^r]))*(\<\/tr([^\>]))*)*\<\/tr\>", "ig")
  str = stringObj.match(re);   
  stringObj = stringObj.replace(re, "<here>");   

  re = new RegExp("\<td([^\>]*)\>\<\/td\>", "ig");
  stringObj = stringObj.replace(re, "<td$1>&nbsp;</td>");   

  stringObj = stringObj.replace("<here>", str);   

  re = new RegExp("[ ][ ]", "ig");
  stringObj = stringObj.replace(re, " ");

  re = new RegExp("[ ]+\>", "ig");
  stringObj = stringObj.replace(re, ">");

  re = new RegExp("(\<br[^\>]*\>)", "ig");
  stringObj = stringObj.replace(re, "<br />\r\n");//was $1\r\n


  DeC.DocumentHTML = stringObj;
  DeC.refresh();
  document.body.style.cursor="default";
  //doc["TagCleanerButton"+ControlName].className="ToolButtonOff";

  tm = setTimeout("restoreInsPoint("+ControlName+")", 100);
}

/*** / Kukutz ***/


/*** ZHARIK ***/
function ZH_Unpack(str)
{
  var ar1 = new Array();
  var ar3;
  var ar2 = str.split('&');
  for(i=0;i<ar2.length;i++){
    ar3 = ar2[i].split('=');
    ar1[ ar3[0] ] = ar3[1];
  }
  return ar1;
}

function ZH_collectFields(par)
{
  var arr = new Array();
  var stack = new Array(); 
  stack[0] = par;   
  var s = 0;
  while(s>=0){  
    par = stack[s--];
    if(par.field){
      if(!arr[par.field]) arr[par.field] = new Array();
      switch(par.field){
        case "bg_image":
          arr[par.field][ arr[par.field].length ] = par.image;
        break;
        case "href":
          arr[par.field][ arr[par.field].length ] = par.href;
        break;
        default:
          arr[par.field][ arr[par.field].length ] = par.innerHTML;
        break;
      }
//      alert(par.field+":"+arr[par.field][ arr[par.field].length-1 ]);
    }
    for(i=0;i<par.children.length;i++) stack[++s] = par.children[i];
  }
  return arr;
}


/*** ZH modifications ***/

function InsertTable(ControlName)
{  
  var pVar = doc["tblInfo"+ControlName];
  var args = new Array();
  var arr = null;
  if(doc["TEXTEditControl"+ControlName].style.display == "none")
   { doc["TableInsertButton"+ControlName].className="ToolButtonOn";
     args["NumRows"] = doc["tblInfo"+ControlName].NumRows;
     args["NumCols"] = doc["tblInfo"+ControlName].NumCols;
     args["TableAttrs"] = doc["tblInfo"+ControlName].TableAttrs;
     args["CellAttrs"] = doc["tblInfo"+ControlName].CellAttrs;
     args["Caption"] = doc["tblInfo"+ControlName].Caption;
     arr = null;
     arr = showModalDialog( CUseImageDir[ControlName]+TableMenuFile,args,"font-family:Verdana; font-size:12; dialogWidth:37em; dialogHeight:27em");
     if (arr != null) 
      { for ( elem in arr ) 
         { if ("NumRows" == elem && arr["NumRows"] != null) 
            { doc["tblInfo"+ControlName].NumRows = arr["NumRows"];
            } else if ("NumCols" == elem && arr["NumCols"] != null) 
            { doc["tblInfo"+ControlName].NumCols = arr["NumCols"];
            } else if ("TableAttrs" == elem) 
            { doc["tblInfo"+ControlName].TableAttrs = arr["TableAttrs"];
            } else if ("CellAttrs" == elem) 
            { doc["tblInfo"+ControlName].CellAttrs = arr["CellAttrs"];
            } else if ("Caption" == elem) 
            { doc["tblInfo"+ControlName].Caption = arr["Caption"];
            }
         } 
         doc["tblInfo"+ControlName].TableAttrs = "class=border border=1 cellPadding=1 cellSpacing=1";
         doc["tblInfo"+ControlName].CellAttrs = "class=border1";
         doc["DHTMLEditControl"+ControlName].ExecCommand(DECMD_INSERTTABLE, OLECMDEXECOPT_DODEFAULT, pVar);
      }
   doc["TableInsertButton"+ControlName].className="ToolButtonOff";
  } else
  { alert(ErrorFunction);
  }
}

function InsertImage(ControlName)
{ 
  if(doc["TEXTEditControl"+ControlName].style.display == "none")
   { doc["AddImages_"+ControlName].className="ToolButtonOn";
     //collect file-inputs from the page?
     var a = 0;
     FormObj = eval("document."+FormAndFieldNames[ControlName][0]);
     iarr = new Array();
     iList = FormObj.tags("INPUT");
     for(i=0; i<iList.length; i++)
      { if((iList[i].type=="file")&&(iList[i].value.substring(iList[i].value.length -4,iList[i].value.length).search(ImgRegx)!=-1))
         { iarr[a] = iList[i].value;
           a++;
         }
      }
     //show modal dialog
     GetParam = false;
     startCmd = 0;
//       dlgPath=CUseImageDir[ControlName]+"/images.htm";
     dlgPath = "?page=images";
     GetParam = showModalDialog(dlgPath, iarr, "font-family:Verdana; font-size:12; dialogWidth:34em; dialogHeight:25em");
     if (GetParam){ 
        
        var range = document.all["DHTMLEditControl"+ControlName].DOM.selection.createRange();
        var template;
        var image = ZH_Unpack(GetParam[1]);

        var h;
        href = (image["pict"])? "javascript:pictwnd('?page=pict&id="+image["id"]+"','pict_view','top=100,left=100,width="+image["width"]+",height="+image["height"]+"')" : "#";

//          var par = range.parentElement();
//          while(par.tagName!="P" && par.tagName!="p" && par.tagName!="BODY") par = par.parentElement;

        ZH_tpl.Set("IMAGE","images/"+image["pict_small"]);
        ZH_tpl.Set("TITLE",image["title"]);
        ZH_tpl.Set("ALIGN",GetParam[2]);
//          ZH_tpl.Set("TEXT", (par.tagName=="P" || par.tagName=="p")? par.innerHTML : "");              

        if(image["pict"]){
          ZH_tpl.Set("HREF",href);
          template = "pict_preview";
        }else template = "pict";
        range.pasteHTML(ZH_tpl.Parse(template));
//          if(par.tagName=="P" || par.tagName=="p") par.outerHTML = ZH_tpl.Parse(template);
//          else par.innerHTML = ZH_tpl.Parse(template);

      }
     doc["AddImages_"+ControlName].className="ToolButtonOff";
   } else
   { alert(ErrorFunction);
   }
}

