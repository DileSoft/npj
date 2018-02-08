var console_previous;
var console_context;

function console_Goto() {
 // 1. вывод ява-скрипт окна
 var go_to = prompt("Команда на НПЖ-языке:", "");
 // 2. редирект если непустой результат
 if (go_to !== "")
 {
   var context = console_context.replace(/\/$/, "");
   // если не x@y or x:y or x::y or /xy, go underground.
   if (!go_to.match(/^.*@.*$/i))
   if (!go_to.match(/^.*:{1,2}.*$/i))
   if (!go_to.match(/^\/.*$/i))
    go_to = "!/"+go_to;
   document.location.href = context+"/goto?goto="+go_to;
 }
}

function console_init( contextURL ) {

 console_context  = contextURL;
 if (isIE && !isO){
   console_previous = document.onkeydown;
   document.onkeydown=ieConsole;
 }else //if (isMZ) 
 {
   console_previous = document.onkeyup;
   document.addEventListener("keypress", mzConsole, true);
   document.addEventListener("keyup", mzConsole, true);
   document.onkeypress = mzConsole;
   document.onkeyup = mzConsole;
 }

}

function ieConsole()
{
  if (event)
   thEvent = event;
  else
   thEvent = document._event;
  var Key, t, e;

  Key=thEvent.keyCode;
  //alert(Key);
  if (thEvent.altKey && !thEvent.ctrlKey) Key=Key+1024;

  if (thEvent.shiftKey && thEvent.ctrlKey && (Key == 71))
  {
    try {
      console_Goto();
    }
    catch(e){
    };
  }
  if (console_previous) return console_previous();
//  e = window.thEvent;
//  e.returnValue = true;
//  return true;
}

function mzConsole(event) 
{
  //alert('console:'+thEvent.type);
  if (event)
   thEvent = event;
  else
   thEvent = document._event;
  var Key, processedEvent; 

  Key = thEvent.keyCode;
  if (Key==0) Key = thEvent.charCode;
  if (thEvent.type == "keypress" && Key==71 && thEvent.shiftKey && thEvent.ctrlKey)
  {
    thEvent.preventDefault();
    thEvent.stopPropagation();
    return false;
  }

  //alert(Key);
  processedEvent = false;
  if (thEvent.shiftKey && thEvent.ctrlKey && Key == 71)
  {
    try {
      console_Goto();
      processedEvent = true;
    }
    catch(e){
    }
  }

  if (processedEvent)
  {
    thEvent.cancelBubble = true;
    thEvent.preventDefault();
    thEvent.stopPropagation();
    return false;
  }
  if (thEvent.type=="keyup" && console_previous && document._event) 
  {
    //alert(thEvent.type);
    document._event = thEvent;
    console_previous();
    thEvent.cancelBubble = true;
    thEvent.preventDefault();
    thEvent.stopPropagation();
    return false;
  }
}
