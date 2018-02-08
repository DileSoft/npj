function flipUp()
{
 document.getElementById('idBody').className='short-userpanel'; 
 var a = document.getElementById('menu_block_full');
 if (a) a.className='hidden'; 
 var a = document.getElementById('menu_block_short');
 if (a) a.className='visible'; 
 document.cookie = 'flip_criba=up; expires="01.01.2025"; path=/';
 if (isMZ || isO)
   document.getElementById('content-block').style.paddingTop='30px';

 return false;
}
function flipDown(donotsetcookie)
{
 document.getElementById('idBody').className='long-userpanel';  
 var a = document.getElementById('menu_block_full');
 if (!a) return false;
 a.className='visible'; 
 var a = document.getElementById('menu_block_short');
 if (a) a.className='hidden'; 
 if (!donotsetcookie) document.cookie = 'flip_criba=down; expires="01.01.2025"; path=/';
 var tp = 0; var lf = 0;
 var op = document.getElementById('headbottom');
 do {
   tp+=op.offsetTop;
   lf+=op.offsetLeft;
 } while (op=op.offsetParent)
 if (isMZ || isO)
  document.getElementById('content-block').style.paddingTop=(tp+5)+'px';

 return false;
}
function getCookie(name) 
{
 var prefix = name + "="
 var cookieStartIndex = document.cookie.indexOf(prefix)
 if (cookieStartIndex == -1)
         return null
 var cookieEndIndex = document.cookie.indexOf(";", cookieStartIndex + prefix.length)
 if (cookieEndIndex == -1)
         cookieEndIndex = document.cookie.length
 return unescape(document.cookie.substring(cookieStartIndex + prefix.length, cookieEndIndex))
}
