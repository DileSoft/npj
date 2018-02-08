// определимся, с кем имеем дело
var isDOM = document.getElementById //DOM1 browser 
var isO   = isO5 = window.opera && isDOM; //Opera 5+
var isO6  = isO && window.print //Opera 6+
var isO7  = isO && document.readyState //Opera 7+
var isO8  = isO && document.createProcessingInstruction && (new XMLHttpRequest()).getAllResponseHeaders //Opera 8+
var isIE  = document.all && document.all.item && !isO //Microsoft Internet Explorer 4+
var isIE5 = isIE && isDOM //MSIE 5+
var isMZ  = isDOM && (navigator.appName=="Netscape")
var _useragent = navigator.userAgent.toLowerCase()
var isSafari = (_useragent.indexOf("safari") != -1);
var isKonqueror = (_useragent.indexOf("konqueror") != -1);

// всякая хрень, которую прибить жалко
function popupIcon( icon ) { alert( "Подсказка:\n\n" + icon.alt, "") ; }
function undef(param) { return param; }
function sign(x) { if (x > 0) return 1; if (x < 0) return -1; return 0; }

// окно для edit1click
function NewWindow(mypage,myname,w,h,scroll,resize)
{
LeftPosition=(screen.width)?(screen.width-w)/2:100;
TopPosition=(screen.height)?(screen.height-h)/2:100;
settings='width='+w+',height='+h+',top='+TopPosition+',left='+LeftPosition+',scrollbars='+scroll+',location=no,directories=no,status=no,menubar=no,toolbar=no,resizable='+resize;
win=window.open(mypage,myname,settings);
}

// функция cI( imagename, libname, image2name, lib2name ) -- <image name=imagename src=libname.gif )
var picArray = new Array();
var preloadFlag = false;
var ok = false;
function cI()
{
  if (document.images && (preloadFlag == true)) 
    {
      for (var i=0; i<cI.arguments.length; i+=2) 
        {
          if (cI.arguments[i] && cI.arguments[i+1])
            if ( document.images[ cI.arguments[i] ] && picArray[ cI.arguments[i+1] ] )
              document.images[ cI.arguments[i] ].src = picArray[ cI.arguments[i+1] ].src;
        }
    }
  return true;
}

// важная часть прелоадера картинок. Работает только с гифами, а потому что нефиг jpeg прелоадить
function preloadPics()
{
  var dir = ""+preloadPics.arguments[0];
  for (var i=1; i<preloadPics.arguments.length; i++)
    {
      picArray[preloadPics.arguments[i]] = new Image();
      picArray[preloadPics.arguments[i]].src = dir + preloadPics.arguments[i] + ".gif";
      if (ok)
      ok = confirm( "preload:" + dir + preloadPics.arguments[i] + ".gif" );
    }
}

// прелоадер картинок, вызывается из BODY onload=
function preloadImages( imageRoot )
{
  if (document.images) 
  {
    preloadPics( imageRoot, "hidegroup0_", "hidegroup1_",  "hidegroup0", "hidegroup1" );//"search", "search_sent", "search_sent_over", "search_sent_down");
  }
  preloadFlag = true;
}


// проверка на то, what -- это email адрес. Для подписки например.
function is_email( what )
{
  if (what.match(/^[a-z0-9\._\-]+@[a-z0-9\._\-]+\.[a-z]+$/i, "")) return true;
  return false;
}

// эмулирует нажатие на ссылку по пробелу или вводу -- использовалось в логине например.
function enterFunction()
{
  if ((window.event.keyCode == 32) || (window.event.keyCode == 13))
    enterTarget.click();
}

// в формах скрыть-показать группу ====================================================================================================
var flipOnlyOne = false; // режим, когда видима только одна группа
var flippingGroups = new Array(); // список всех сворачиваемых групп
function flipGroup( groupID, norecursion )
{
  if (flipOnlyOne && !norecursion) return flipCloseAll(groupID);

    var matches = document.images['group_'+groupID+'_icon'].src.match("hidegroup([0-9])(_?).gif");
    if (matches[1] != "0")
    {
      cI('group_'+groupID+'_icon','hidegroup0');
      var a = document.getElementById('group_'+groupID+'_body');
      a.style.display='none';
      var a = document.getElementById('group_'+groupID+'_title');
      var matches = a.className.match("form_group_title((_hidden)?)((_current)?)");
      a.className = "form_group_title_hidden"+matches[3];
    }
    else
    {
      cI('group_'+groupID+'_icon','hidegroup1');
      var a = document.getElementById('group_'+groupID+'_body');
      a.style.display='block';
      var a = document.getElementById('group_'+groupID+'_title');
      var matches = a.className.match("form_group_title((_hidden)?)((_current)?)");
      a.className = "form_group_title"+matches[3];
    }
}
function flipGroupAdd( groupID )
{ flippingGroups = flippingGroups.concat(groupID);
}
function flipCloseAll( openID )
{
  for(var i in flippingGroups)
   if (flippingGroups[i] != openID)
   {
    var matches = document.images['group_'+flippingGroups[i]+'_icon'].src.match("hidegroup([0-9])(_?).gif");
    if (matches[1] != "0")
     flipGroup( flippingGroups[i], "norecursion" );
   }
  if (openID != undef())
    flipGroup( openID, "norecursion" ); // was flipOpenOne
}
function flipOpenAll( openID )
{
  var matches1 = document.images['group_'+openID+'_icon'].src.match("hidegroup([0-9])(_?).gif");
  for(var i in flippingGroups)
   {
    var matches = document.images['group_'+flippingGroups[i]+'_icon'].src.match("hidegroup([0-9])(_?).gif");
    if (matches[1] != matches1[1])
     flipGroup( flippingGroups[i], "norecursion" );
   }
}
function flipOpenOne( openID )
{
  var matches = document.images['group_'+openID+'_icon'].src.match("hidegroup([0-9])(_?).gif");
  if (matches[1] != "0") ;
  else flipGroup( openID, "norecursion" );
}
function flipGroupKey( keyCode, groupID )
{
  if (keyCode == 32)
  {
    flipGroup( groupID );
    return false;
  } 
  return true;
}
function flipGroupLit( groupID )
{
  var matches = document.images['group_'+groupID+'_icon'].src.match("hidegroup([0-9])(_?).gif");
  cI('group_'+groupID+'_icon','hidegroup' + matches[1] + "_");
  var a = document.getElementById('group_'+groupID+'_title');
  var matches = a.className.match("form_group_title((_hidden)?)((_current)?)");
  a.className = "form_group_title"+matches[1]+"_current";
}
function flipGroupDim( groupID )
{
  var matches = document.images['group_'+groupID+'_icon'].src.match("hidegroup([0-9])(_?).gif");
  cI('group_'+groupID+'_icon','hidegroup' + matches[1]);
  var a = document.getElementById('group_'+groupID+'_title');
  var matches = a.className.match("form_group_title((_hidden)?)((_current)?)");
  a.className = "form_group_title"+matches[1];
}
// end: в формах скрыть-показать группу ================================================================================================

function flipanel( name, state )
{
    if (state==1)
    {
      var a = document.getElementById(name+'_flip_up');
      a.style.display='none';
      var a = document.getElementById(name+'_flip_down');
      a.style.display='block';
      document.cookie = "flip_"+name+"=down; expires=\"01.01.2025\"; path=/";
    }
    else
    {
      var a = document.getElementById(name+'_flip_up');
      a.style.display='block';
      var a = document.getElementById(name+'_flip_down');
      a.style.display='none';
      document.cookie = "flip_"+name+"=up; expires=\"01.01.2025\"; path=/";
    }
}


// в формах FieldSelect
function Select_PasteValueTo( field, value, is_add )
{
  var a = document.getElementById( field );
  if (a) 
  {
   if (a.value == "" ||
       !(String(a.value).match( new RegExp( "^"+value+"[\\s\\n,;]" , "i")) ||
         String(a.value).match( new RegExp( "^"+value+"$", "i")) ||
         String(a.value).match( new RegExp( "[\\s\\n,;]"+value+"$", "i")) ||
         String(a.value).match( new RegExp( "[\\s\\n,;]"+value+"[\\s\\n,;]", "i"))
      ))
   {
    if (!is_add) a.value = value;
    else
    {
      value = a.value+" "+value;
      value = value.replace( /\s+/g, " " );
      value = value.replace( /^\s+/g, "" );
      a.value= value+" ";
    }
   }
   if (is_add) a.focus();
  }
  var e = new Object;
  e.currentTarget = a;
  set_modified(e, 1);
}


// для работы с причудливыми группами
 var selectedGroup = 0;
 var fmForm;

 function initMultiple(id, form) {
  fmForm = form;
  if(isIE || isO){
   document.forms[fmForm].elements["list_out"+id].ondblclick=mDblClick;
   document.forms[fmForm].elements["list_in"+id].ondblclick=mDblClick;
  }else if (isMZ) {
   document.forms[fmForm].elements["list_out"+id].addEventListener("dblclick", mDblClick, true);
   document.forms[fmForm].elements["list_in"+id].addEventListener("dblclick", mDblClick, true);
  }
 }

 function mDblClick(ev) {
  if (isMZ) var e = ev.target.parentNode;
  else e = event.srcElement;
  //alert (e.name);
  if (e.name.substr(0,8)=='list_out') var id = e.name.substr(8);
  else var id = e.name.substr(7);
  if (e.name.substr(0,8)=='list_out') moveItems(document.forms[fmForm].elements["list_out"+id], document.forms[fmForm].elements["list_in"+id]);
  else moveItems(document.forms[fmForm].elements["list_in"+id], document.forms[fmForm].elements["list_out"+id]);
  dumpState(id);
 }

 function dumpState(id)
 {
  var se = document.forms[fmForm].elements["list_in"+id];
  var g = document.forms[fmForm].elements["_items_in"+id];
  var groups = new Array();
  for (j=0; j<se.options.length; j++) 
  { 
   if (se.options[j].value!='') groups = groups.concat(se.options[j].value); 
  }
  g.value = groups.join("|");
 }


 function moveItems (from, to)
 {
     var selindex;
     while ((selindex=from.selectedIndex) != -1)
     {
         var i;
         var item = new Option(from.options[selindex].text,
                               from.options[selindex].value,
                               false, true);

         from.options[selindex] = null;
         //to.options[to.options.length] = item;

         // find spot to put new item
         for (i=0; i<to.options.length && to.options[i].text < item.text; i++) { }
         var newindex = i;

         // move everything else down
         for (i=to.options.length; i>newindex; i--) {
                  to.options[i] = new Option(to.options[i-1].text,
                                        to.options[i-1].value,
                                        false,
                                        to.options[i-1].selected);
         }
         to.options[newindex] = item;

     }
 }

 function moveIn (id)
 {
  moveItems(document.forms[fmForm].elements["list_out"+id], document.forms[fmForm].elements["list_in"+id]);
  dumpState(id);
 }
 function moveOut (id)
 {
  moveItems(document.forms[fmForm].elements["list_in"+id], document.forms[fmForm].elements["list_out"+id]);
  dumpState(id);
 }

 var editorswiki = new Array();
 var editorsrich = new Array();
 var editorssimple = new Array();
 var deInit;
 var as_init = false;
 var theme_init = false;
 var console_init = false;
 var skin_init = false;
 var as_update = new Array();
 var DOTS = "#define x_width 2\n#define x_height 1\nstatic char x_bits[]={0x01}";

 if (isIE) var doc = document.all;

 function seteditor(we, type)
 {
  if (type==null) type = "wiki";
  eval('var e = editors'+type);
  e[e.length] = we;
 }

 function npjInit ( contextURL, isGuest ) //инициализация всяких штук, которые требуют инициализации
 {

  if (editorswiki.length>0) 
  {
   for (i=0;i<editorswiki.length;i++)
   {
    var re = new WikiEdit(); 
    re.init(editorswiki[i],'Wiki','edname-w',themeurl+'/images/vseedit/');
   }
//   weSwitchTab();
  }

  if (editorssimple.length>0) 
  {
   for (i=0;i<editorssimple.length;i++)
   {
    var re = new SimpleEdit(); 
    re.init(editorssimple[i],'Текст','edname-s',themeurl+'/images/vseedit/');
   }
  }

  if (editorsrich.length>0) 
  {
   for (i=0;i<editorsrich.length;i++)
   {
    var re = new RichEdit(); 
    re.init(editorsrich[i],'HTML','edname-r',themeurl+'/images/vseedit/',themeurl+'/images/vseedit/dialogs/');
    as_update[as_update.length] = re;
   }
  }

  if (!(isIE || isMZ)) 
  {
   var a = document.getElementById("chk_formatting_2");
   if (a!=null)
     a.disabled = true;
  } 

  if (as_init)       as_init();
  if (console_init)  console_init( contextURL );
  if (theme_init)    theme_init();
  if (skin_init)     skin_init( isGuest );
    
 }

 function asSave()
 {
  if (confirm("Really save?"))
  {
//   if (as_update) as_update.updateRTEs();
   var button = document.getElementById("save");
   button.click();
  }
 }

 function soDynamic(form, name, selected) 
 {
  if (soLast=="simplebr" || soLast=="_body_simpleedit") 
  {
   var o = document.getElementById("id__body_simpleedit");
   var text = o.value;
   var w = o.offsetWidth;
   var h = o.offsetHeight;
  }
  if (soLast=="rawhtml" || soLast=="_body_richedit" ) 
  {
   var rte = document.getElementById("id__body_richedit");
   if (rte==undef()) //htmlArea
     rte = document.getElementById("_body_richedit");

   var text = rte._owner.getHTML();
   var w, h;
  }
  if (soLast=="wacko" || soLast=="_body_wikiedit" ) 
  { 
   var o = document.getElementById("id__body_wikiedit");
   var text = o.value;
   var w = o.offsetWidth;
   var h = o.offsetHeight;
  }
  document.getElementById("rdv_body_simpleedit").style.display = "none";
  document.getElementById("rdv_body_wikiedit").style.display = "none";
  document.getElementById("rdv_body_richedit").style.display = "none";
  //alert (soLast+"|"+text);
  if (selected.value=="simplebr") 
  {
   document.getElementById("rdv_body_simpleedit").style.display = "block";
   document.getElementById("id__body_simpleedit").value = text;
  }
  if (selected.value=="rawhtml") 
  {
   document.getElementById("rdv_body_richedit").style.display = "block";
   var rte = document.getElementById("id__body_richedit");
   if (rte==undef()) //htmlArea
     rte = document.getElementById("_body_richedit");
   if (rte._owner==undef())
   {
    var re = new RichEdit(); 
    re.init("id__body_richedit",'HTML','edname-r',themeurl+'/images/vseedit/',themeurl+'/images/vseedit/dialogs/');
    as_update[as_update.length] = re;
    var rte = document.getElementById("id__body_richedit");
   }  
   
   rte.value = text;
   if (isIE || isMZ) 
   {
     if (text) rte._owner.setHTML(text);
     if (w && rte._owner.setSize) rte._owner.setSize(w, h);
   }
  }
  if (selected.value=="wacko") 
  { 
   document.getElementById("rdv_body_wikiedit").style.display = "block";
   document.getElementById("id__body_wikiedit").value = text;
  }
  soLast = selected.value;
 }

// -----------------------------------------------------------------------------------------------
// ниже расположен код конфирмации изменений критичных полей. 
// У нас они блин все критичные в Форм-Процессоре
// Courtesy of http://htmlcoder.visions.ru/JavaScript/?26 
// slightly modified by Kuso Mendokusee
// slightly modified by Kukutz
var root = window.addEventListener || window.attachEvent ? window : document.addEventListener ? document : null;
var cf_modified = false;
var WIN_CLOSE_MSG = "\nВы не сохранили изменения. Действительно хотите уйти отсюда?\n";

function set_modified(e, strict_e){
  if (window.event && !strict_e)
   var el = window.event.srcElement;
  else if (e!=null)
   var el = e.currentTarget;
//  var el = (window.event && !strict_e) ? window.event.srcElement : e.currentTarget;
  //el.className = "modified";
  if (el!=null)
  {
   el.style.borderColor = "#eecc99";
   el.title = "(поле изменено, не забудьте сохранить изменения)";
  }
  cf_modified = true;
}

function ignore_modified(){
  if (typeof(root.onbeforeunload) != "undefined") root.onbeforeunload = null;
}

function check_cf(){
  if (cf_modified) return WIN_CLOSE_MSG;
}

function crit_init(){
  if (typeof(root.onbeforeunload) != "undefined") root.onbeforeunload = check_cf;
  else return;

  var thisformcf;
  for (var i = 0; oCurrForm = document.forms[i]; i++){
    if (oCurrForm.getAttribute("cf")) thisformcf=true;
    else thisformcf =false;
    if (oCurrForm.getAttribute("nocf")) thisformcf=false;
    for (var j = 0; oCurrFormElem = oCurrForm.elements[j]; j++){
      if (thisformcf || oCurrFormElem.getAttribute("cf"))
      if (!oCurrFormElem.getAttribute("nocf"))
      {
        if (oCurrFormElem.addEventListener) oCurrFormElem.addEventListener("change", set_modified, false);
        else if (oCurrFormElem.attachEvent) oCurrFormElem.attachEvent("onchange", set_modified);
        if (oCurrFormElem.addEventListener) oCurrFormElem.addEventListener("keypress", set_modified, false);
        else if (oCurrFormElem.attachEvent) oCurrFormElem.attachEvent("onkeypress", set_modified);
      }
    }
    if (oCurrForm.addEventListener) oCurrForm.addEventListener("submit", ignore_modified, false);
    else if (oCurrForm.attachEvent) oCurrForm.attachEvent("onsubmit", ignore_modified);
  }
}

if (root){
  if (root.addEventListener) root.addEventListener("load", crit_init, false);
  else if (root.attachEvent) root.attachEvent("onload", crit_init);
}


// Keep Alive сессии
var next = false;
function refreshTicker( root, keep_alive )
{
  if (next) document.images[ "sessionTicker" ].src = root+"z.php?id="+Math.random();
  next = true;
  window.setTimeout( "refreshTicker( '"+root+"', "+keep_alive+", 1 )", keep_alive );
}
