var autosave_previous;

function as_init() {

 autosave_previous = document.onkeydown;
 if (isIE && !isO){
   document.onkeydown=ieAS;
 }else //if (isMZ) 
 {
//   document.addEventListener("keypress", mzAS, true);
   document.addEventListener("keyup", mzAS, false);
   document.onkeyup=mzAS;
 }

}

function ieAS()
{
  if (event)
   thEvent = event;
  else
   thEvent = document._event;

  var Key, t, e;

  Key=thEvent.keyCode;
  if (thEvent.altKey && !thEvent.ctrlKey) Key=Key+1024;

  if (Key==1107) //Alt+S
  {
    try {
      if (asSave!=null) asSave();
    }
    catch(e){
    };
  }

  if (autosave_previous) return autosave_previous();

//  e = window.thEvent;
//  e.returnValue = true;
//  return true;
}

function mzAS(event) 
{
  if (event)
   thEvent = event;
  else
   thEvent = document._event;

  var Key, processedEvent; 

  Key = thEvent.keyCode;
  if (Key==0) Key = thEvent.charCode;
  //if (thEvent.altKey) Key=Key+4096;
  //alert(Key);
  processedEvent = false;
  if (thEvent.altKey && Key==83) //Alt+S 
  {
    try {
      if (asSave!=null) asSave();
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
}
