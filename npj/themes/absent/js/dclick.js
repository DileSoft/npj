var frame  = "body_bg";
var frame2 = "wrapper";

if(isIE || isO){
  document.ondblclick=function(){
    op = event.srcElement;
    while (op!=null && ((op.className!=frame) && (op.className!=frame2)) && op.tagName!="BODY")
      op=op.parentElement;
    if ((op.className==frame) || (op.className==frame2)) {
     document.location=edit;
    }
    return true;
  }
}else if (isMZ) {
document.addEventListener("dblclick", mouseClick, true);
}

function mouseClick(event) 
{
    op = event.target;
    while (op!=null && ((op.className!=frame) && (op.className!=frame2)) && op.tagName!="BODY")
      op=op.parentNode;
    if (op!=null && ((op.className==frame) || (op.className==frame2))) {
     document.location=edit;
    }
}
