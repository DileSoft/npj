/*
  Rubrika library: http://www.pixel-apes.com/rubrika
  v.2.0
  11 November 2004.
  ---------
  Copyright (c) 2004, Pixel-Apes <mailto:mendokusee@pixel-apes.com>
  All rights reserved.

  For LICENSE see license.txt
  ---------
*/

function Rubrika( no_facets, etc_facet_name, translit_instance )
{
  this.output_separator = " ";
  this.strict_separator   = false;
  this.strict_no_beautify = false;
  this.strict_no_sort     = false;
  this.strict_no_add      = false;
  this.strict_no_iframe   = false;

  this.enabled = true;
  this.no_facets = no_facets?true:false;
  this.etc_facet_mode      = "facet";
  this.etc_facet_container = "plain";
  this.etc_facet_dropdown_always = false;

  if (etc_facet_name != null) 
   this.etc_facet_name = etc_facet_name;
  else
   this.etc_facet_name = "Прочее";

  this.translit_instance = translit_instance;
   
  this.MZ=(document.all?false:true); // isMZ;

}

Rubrika.prototype.Init = function( target_id, debug_id,
                                   input_keywords_id, input_all_id )
{
  if (!this.translit_instance)
   this.translit_instance = this;

  this.target_id = target_id;
  this.target = document.getElementById(target_id);
  this.debug = document.getElementById(debug_id);

  this.sDOM = document.getElementById; //DOM1 browser 
  this.sO   = window.opera && this.sDOM; //Opera 5+
  this.sIE  = document.all && document.all.item && !this.sO; //Microsoft Internet Explorer 4+
  this.sMZ  = this.sDOM && (navigator.appName=="Netscape");
  if (!this.sDOM) 
  {
    this.target.innerHTML = "Извините, в вашем браузере этот интерфейс работы с рубрикацией (пока) не работает. Переключитесь, пожалуйста, на другой интерфейс или браузер.";
    return;
  }
  if (!this.sIE && !this.sMZ)
  {
    this.strict_no_iframe=true; // opera couldnot correctly AND fast handle iframes.
  }

  this.input_keywords_id = input_keywords_id;
  this.input_all_id      = input_all_id;
  this.input_keywords = document.getElementById(input_keywords_id);
  this.input_all      = document.getElementById(input_all_id);

  this.sort_orders = new Object;
  this.keywords = this.SplitKeywords( this.input_keywords.value, "keywords" );
  this.all      = this.SplitKeywords( this.input_all.value, "all" );

  this.facets   = this.FindFacets();
  this.f = new Array(); this.f_hash = new Array();
  this.f_id = new Array();

  for(var i in this.facets["_"].contents)
   if (this.keywords[ this.facets["_"].contents[i].supertag ])
    this.keywords[ this.facets["_"].contents[i].supertag ].facet = "_";


  for (var i in this.facets) 
  {
    var _facet = this.CreateFacetObject( this.facets[i], (i=="_")?this.etc_facet_mode:"facet",
                                                         (i=="_")?this.etc_facet_container:"plain"
                                       );
    this.f[ this.f.length ] = _facet;
    this.facets[i].i = this.f.length-1;

    if (i=="_")
    {
      _facet.container.dropdown_always = this.etc_facet_dropdown_always;
      _facet.container.none_title      = this.etc_facet_none;
    }
  }
  for (var i in this.f) this.f_id[ this.f_id.length ] = this.f[i].Render( this.target, target_id );
  for (var i in this.f) this.f[i].LinkTo( this.f_id[i] );
}

Rubrika.prototype.CreateFacetObject = function( facet_data, mode, container_mode )
{
  var _c;
  var _f;
  switch (container_mode)
  {
    case "dropdown": _c = new RubrikaContainerDropdown( this ); 
                          break;
    default:         _c = new RubrikaContainer( this ); 
  }
  switch (mode)
  {
    case "radio": _f = new RubrikaFacetRadio( this, facet_data, _c ); 
                  break;
    default:      _f = new RubrikaFacet( this, facet_data, _c ); 
  }
  return _f;
}

Rubrika.prototype.BeautifyKeyword = function( raw )
{
  if (this.strict_no_beautify) return raw;

  var a = raw.split(" ");
  for( var i in a )
   a[i] = (a[i].substr(0,1).toUpperCase()) + (a[i].substr(1));
  raw = a.join("");

  var a = raw.split("/");
  for( var i in a )
   a[i] = (a[i].substr(0,1).toUpperCase()) + (a[i].substr(1));
  raw = a.join("/");

  return raw;
}

Rubrika.prototype.AddKeyword = function( raw, target_facet )
{
  raw = this.BeautifyKeyword( raw );
  if (target_facet.facet.is_etc) raw = raw;
  else                           raw = target_facet.facet.raw + "/" + raw;
  var kwd = this._SpawnKeyword( raw );
  if (this.all[kwd.supertag]) { alert("Такая рубрика уже существует!"); return; }
  this.all[ kwd.supertag ] = kwd;
  this.facets[ target_facet.facet.supertag ].contents[ this.facets[ target_facet.facet.supertag ].contents.length ] = kwd;
  this.facets[ target_facet.facet.supertag ] = this.CompleteFacet( this.facets[target_facet.facet.supertag] );
}

Rubrika.prototype.CleanFacet = function( facet_sp )
{
  for (var i in this.facets[facet_sp].contents)
    this.keywords[ this.facets[facet_sp].contents[i].supertag ] = null;
  this.RecompileAfterFlip( facet_sp );
}

Rubrika.prototype.RecompileAfterFlip = function( facet_supertag )
{
  var joined = new Array();
  var new_kwds = new Array();
  for( var i in this.keywords )
  if (this.keywords[i])
  {
    joined[ joined.length ] = this.keywords[i].raw;
    new_kwds[i] = this.keywords[i];
  }
  this.keywords = new_kwds;
  this.input_keywords = document.getElementById(this.input_keywords_id);
  this.input_keywords.value = joined.join(this.output_separator);
  var facet_sp = facet_supertag;
  var f_i = this.facets[facet_sp].i;
  this.f[f_i].RebuildStatic();

}

Rubrika.prototype.FlipKeyword = function( supertag, to_checked )
{
  if (!to_checked)
   this.keywords[supertag] = null;
  else
   this.keywords[supertag] = this.all[supertag];

  var fsp = this.all[supertag]["facet"];

  this.RecompileAfterFlip( fsp );
}

Rubrika.prototype.KeywordsInFacet = function( facet_supertag )
{
  var result = new Array();
  for (var i in this.keywords)
    if (this.keywords[i]["facet"] == facet_supertag) result[ result.length ] = this.keywords[i];
  return result;
}

Rubrika.prototype.CompleteFacet = function( facet )
{
  var complete = new Array();
  var for_sort = new Array();

  var contents;
  
  if (this.strict_no_sort)
  {
    contents = facet.contents;
    if (this.sO) 
    {
      for (var i in contents) contents[i]._rubrika = this;
      contents = contents.sort( rubrika_sort_by_sort_order_all );
    }
  }
  else
    contents = facet.contents.sort( rubrika_sort_by_supertag );

  for (var i in contents)
  {
    var word = contents[i];
    var a_raw      = word.raw.split("/");


    var suber = "";
    var f=0;
    for (var j in a_raw)
    {
      if (f) suber+="/"; else f=1;
      suber+=a_raw[j];
      if (facet.is_etc)
      {
        
      }
      else if (f==1) { f=2; continue; }

      var kwd = this._SpawnKeyword( suber, facet.is_etc );
      if (!complete[kwd.supertag])
      {
        complete[kwd.supertag] = kwd;
        for_sort[for_sort.length] = kwd;
        this.all[kwd.supertag] = kwd;
      }
    }
  }
  if (this.strict_no_sort)
  {
    facet.sorted_contents = for_sort;
    if (this.sO) 
    {
      for (var i in contents) contents[i]._rubrika = this;
      contents = contents.sort( rubrika_sort_by_sort_order_all );
    }
  }
  else
    facet.sorted_contents = this.ResortFacet(for_sort.sort( rubrika_sort_by_supertag ));
  facet.contents = complete;
  return facet;
}

Rubrika.prototype.ResortFacet = function( sorted )
{
  var root = new Array();
  var tree = new Array();
  root.ct = tree;
  root.is_root = true;
  root.supertag = "[root]";
  for(var i in sorted)
  {
     var parent = false;
     for( var j in sorted )
       if (sorted[j].depth == sorted[i].depth-1)
        if (sorted[i].supertag.indexOf(sorted[j].supertag+"/") == 0)
        { parent = sorted[j]; break; }
     if (!parent) parent = root;
     if (!parent.ct) parent.ct = new Array();
     parent.ct[ parent.ct.length ] = sorted[i];
  }

  for (var i in sorted)
   if (sorted.ct)
    sorted.ct = sorted.ct.sort( rubrika_sort_by_last );
  root.ct = root.ct.sort( rubrika_sort_by_last );

  var stack = new Array()
  stack[0] =  root;
  var sp=0;
  while (sp < stack.length)
  {
    if (stack[sp].ct)
    {
      stack = stack.concat( stack[sp].ct );
    }
    sp++;
  }

  sp=stack.length-1;
  for (; sp>=0; sp--)
  {
    stack[sp].ct_ = new Array( );
    if (!stack[sp].is_root) stack[sp].ct_[ stack[sp].ct_.length ] = stack[sp];
    if (stack[sp].ct)
    {
      for(var i in stack[sp].ct)
       stack[sp].ct_ = stack[sp].ct_.concat( stack[sp].ct[i].ct_ );
    }
  }
  var tree_ = new Array();
  for(var i in tree)
  {
    tree_ = tree_.concat( tree[i].ct_ );
  }

  var result = new Array();
  for(var i in tree_)
   if (tree_[i])
    result[ result.length ] = tree_[i];

  return result;
}

Rubrika.prototype.FindFacets = function()
{
  var grouplings = new Array();
  var groups = new Array();
  for (var i in this.all)
  {
    if (this.all[i]["supertag"] != this.all[i]["facet"])
      grouplings[ this.all[i]["facet"] ] = 1;
  }
  for (var i in this.all)
  {
    if (grouplings[ this.all[i]["supertag"] ])
     groups[ this.all[i]["facet"] ] = this.all[this.all[i]["facet"]];
  }

  for (var i in groups)
    groups[i].contents = new Array();

  var last = new Array();
  for (var i in this.all)
   if (!this.no_facets && groups[ this.all[i]["facet"] ])
    if (this.all[i]["facet"] != this.all[i]["supertag"])
     groups[ this.all[i]["facet"] ].contents [groups[ this.all[i]["facet"] ].contents.length] = this.all[i];
    else ;
   else
    last[ last.length ]=this.all[i];

  groups["_"] = new Array();
  groups["_"]["supertag"] = "_";
  groups["_"]["facet"]    = "_";
  groups["_"]["raw"] = this.etc_facet_name;
  groups["_"].contents = last;
  groups["_"].is_etc = true;

  for (var i in groups) groups[i] = this.CompleteFacet( groups[i] );

  if (this.no_facets) 
  { var _g = groups["_"];
    groups = new Array();
    groups["_"] = _g;
  }

  return groups;
}

Rubrika.prototype._SpawnKeyword = function( raw, is_etc )
{
    raw = this.BeautifyKeyword(raw);
    var kwd = new Array();
    var a  = raw.split("/");
    var sp = this.translit_instance.Supertag(raw, "allow slashes");
    kwd["raw"]       = raw;
    kwd["supertag"]  = sp;
    kwd["last"]      = raw.replace( /.*\/(.+)$/, "$1");
    kwd["facet"]     = sp.replace( /^([^\/]+)\/.*$/, "$1");
    kwd["under"]     = raw.replace( /[^\/]+\/(.+)$/, "$1");
    kwd["depth"]     = a.length-2;

    if (this.no_facets)
    {
      kwd["facet"] = "_";
      kwd["under"] = kwd["raw"];
      kwd["depth"] += 1;
    } else
    {
     if (is_etc)
     { 
       kwd["facet"] = "_";
       kwd["depth"] += 1;
       kwd["under"] = kwd["raw"];
     }
     if (kwd["depth"] < 0) kwd["depth"] = 0;
    }

    return kwd;
}

Rubrika.prototype.SplitKeywords = function( raw_keywords, sort_order_var )
{
  if (this.sO && sort_order_var) this.sort_orders[sort_order_var] = new Array();
  var keywords = new Array();
  if (this.strict_separator)
  {
    raw_keywords = raw_keywords.split(this.strict_separator);
  }
  else
  {
    raw_keywords = raw_keywords.replace( /[;,\s\n]+/g, " ");
    raw_keywords = raw_keywords.split(" ");
  }
  for( var i=0; i<raw_keywords.length; i++ )
  {
    var kwd = this._SpawnKeyword( raw_keywords[i] );
    if (kwd["supertag"] != "") 
    {
      keywords[ kwd["supertag"] ] = kwd;
      if (this.sO && sort_order_var) this.sort_orders[sort_order_var][kwd["supertag"]] = i;
    }
  }
  return keywords;
}

Rubrika.prototype.Supertag = function( tag )
{
   var NpjMacros = { "вики" : "wiki", "вака" : "wacko", "швака" : "shwacko",
                     "веб" : "web", "ланс" : "lance", "кукуц" : "kukutz", "мендокуси" : "mendokusee",
                     "яремко" : "iaremko", "николай" : "nikolai", "алексей" : "aleksey", 
                     "анатолий" : "anatoly", "нпж" : "npj"
                   };

   var Vowel = "аеёиоуыэюя";
   var LettersFrom = "абвгдезиклмнопрстуфцы";
   var LettersTo   = "abvgdeziklmnoprstufcy";
   var BiLetters = {  
      "й" : "jj", "ё" : "jo", "ж" : "zh", "х" : "kh", "ч" : "ch", 
      "ш" : "sh", "щ" : "shh", "э" : "je", "ю" : "ju", "я" : "ja"
                   };

   tag = tag.replace( /\/{2}/g, "/" );
   tag = tag.replace( / /g, "" );
   tag = tag.toLowerCase();

   tag = tag.replace( 
      new RegExp( "(ь|ъ)(["+Vowel+"])", "g" ), "j$2");
   tag = tag.replace( /(ь|ъ)/g, "");
   
   for( var i in NpjMacros )
   {
     var r = new RegExp( i, "gi" );
     tag = tag.replace( r, NpjMacros[i] );
   }

   var _tag = "";
   for( var x=0; x<tag.length; x++)
    if ((index = LettersFrom.indexOf(tag.charAt(x))) > -1)
     _tag+=LettersTo.charAt(index);
    else
     _tag+=tag.charAt(x);
   tag = _tag;

   var _tag = "";
   for( var x=0; x<tag.length; x++)
    if (BiLetters[tag.charAt(x)])
     _tag+=BiLetters[tag.charAt(x)];
    else
     _tag+=tag.charAt(x);
   tag = _tag;

   tag = tag.replace( /[^\/0-9a-zA-Z\-]+/g, "");

   return tag.replace( /\/+$/, "" );
}

Rubrika.prototype.Undef = function( never_use_this_param )
{
  return never_use_this_param;
}

// -- rubrika_container.js
function RubrikaContainer( rubrika )
{
  this.rubrika = rubrika;
  this.collapsed = true;
}
RubrikaContainer.prototype.Init = function( facet_object )
{
  this.facet_object = facet_object;
}


RubrikaContainer.prototype.RenderCollapsed = function()
{
  var data = this.rubrika.KeywordsInFacet( this.facet_object.facet.supertag );
  if (typeof(data) != "object") data = new Object();
  var result = new Array();
  if (data.length == 0) return '<span class="none-">(не выбрано)</span>';
  for (var i in data)
   result[result.length] = "<nobr>" + data[i].under + "</nobr>";
  return result.join("; ");
}
RubrikaContainer.prototype.RenderDeployed = function( id6 )
{
  var result = "";
  if (this.rubrika.strict_no_iframe)
    result+='<div class="rubrika-no-iframe" id="'+id6+'"></div>';
  else
    result+='<iframe src="/z.html" id="'+id6+'" class="'+(this.rubrika.f.length==1?"only":"normal")+'" scrolling="auto"></iframe>';
  return result;
}

RubrikaContainer.prototype.Render = function( target, id_prefix )
{
  var id1 = id_prefix+"__"+(this.facet_object.facet.supertag.replace(/\/+/g, "_"));
  var id2 = id1+"__collapsed";
  var id3 = id1+"__deployed";
  var id4 = id1+"__actions";
  var id5 = id1+"__button";
  var id6 = id1+"__iframe";

  this.facet_anchor_id = id5;
  this.static_id = id2;

  var new_link = '';
  if (!this.rubrika.strict_no_add)
    new_link = '<div class="new-"><a onclick="return rubrika_add( document.getElementById(\''+id5+'\')._facet );" '+
                                   ' href="javascript:;">(+)<br /><span style="text-decoration:underline">Создать новую<br />рубрику...</span></a></div>'+
               '<div ><br /></div>';
      
  var result = '';
  result+='<div class="rubrika">';
  result+='<table class="rubrika-collapsed" id="'+id1+'" cellspacing="3" cellpadding="0">';
  result+='<tr >';
  result+='<td class="left-"><button id="'+id5+'" onclick="return rubrika_flip( this._facet );">'+
            this.facet_object.facet.raw+'</button>'+
            '<div class="panel-" id="'+id4+'">'+
              new_link+
              '<div class="clean-"><a onclick="return rubrika_clean( document.getElementById(\''+id5+'\')._facet );" '+
                                    ' href="javascript:;">очистить выбор</a></div>'+
            '</div>'+
          '</td>';
  result+='<td class="mid-"> &mdash; </td>';
  result+='<td class="right-">';
   result+='<div class="collapsed-" id="'+id2+'">';
   result+= this.RenderCollapsed();
   result+='</div>';
   result+='<div class="deployed-" id="'+id3+'">';
   result+= this.RenderDeployed( id6 );
   result+='</div>';
  result+='</td>';
  result+='</tr>';
  result+='</table>';
  result+='</div>';

  target.innerHTML += result;
  return id1;
}

RubrikaContainer.prototype.RebuildStatic = function()
{
  var _static = document.getElementById( this.static_id );
  if (_static) _static.innerHTML = this.RenderCollapsed();
}

RubrikaContainer.prototype.LinkTo = function( id1 )
{
  var id5 = id1+"__button";
  var id6 = id1+"__iframe";

  this.table = document.getElementById(id1);
  document.getElementById(id5)._facet = this.facet_object;
  this.contents_id = id6;
}

RubrikaContainer.prototype.RenderContents = function()
{
  html = this.facet_object.RenderContents();
  this._IframeContents(html);
  this.facet_object.RenderContentsSelection();
}

RubrikaContainer.prototype._IframeContents = function( contents )
{ 
  var css = "";
  css += "<style>\n";
   css += "body {\n";
   css += "  color: #444444;\n";
   css += "  background: #ffffff;\n";
   css += "  margin: 0px;\n";
   css += "  padding: 0px;\n";
   css += "  font:11px Tahoma\n";
   css += "}\n";
   css += "  label { cursor:pointer; cursor:hand; } \n";
   css += "  label:HOVER { color:#ff0000 } \n";
   css += "  table { border: none } \n";
   css += "  table td { font:11px Tahoma; padding:0 1px;border: none; vertical-align:middle } \n";

   css += "  .none- { font-size:1px } \n";
   css += "  .kwd- { } \n";
   css += "  .invisible- { display:none; } \n";
   css += "  .visible- { display:normal; } \n";
  
  css += "</style>\n";

  if (this.rubrika.strict_no_iframe)
  {
    div = document.getElementById(this.contents_id);
    div.innerHTML = contents;
    eval( 'document._facet_'+this.rubrika.target_id+this.facet_object.facet.supertag+' = this.facet_object;' );
  }
  else
  if (document.all)
  {
    var frameHtml = "";
    frameHtml += "<html>\n";
    frameHtml += "<head>\n";
    frameHtml += css;
    frameHtml += "</head>\n";
    frameHtml += "<body class='rubrika-frame'>\n";
    frameHtml += contents;
    frameHtml += "</body>\n";
    frameHtml += "</html>";

    iframe = frames[this.contents_id].document;
    iframe.open();
    iframe.write(frameHtml);
    iframe.close();
    eval( 'iframe._facet_'+this.rubrika.target_id+this.facet_object.facet.supertag+' = this.facet_object;' );
  }
  else
  {
    iframe = document.getElementById(this.contents_id).contentWindow.document;
    iframe.body.innerHTML = css+contents;
    eval( 'iframe._facet_'+this.rubrika.target_id+this.facet_object.facet.supertag+' = this.facet_object;' );
  }
}


RubrikaContainer.prototype.Collapse = function()
{
 if (this.collapsed) return;
 this.table.className = "rubrika-collapsed";
 this.collapsed = true;             
}

RubrikaContainer.prototype.Deploy = function()
{
 if (!this.collapsed) return;
 this.CollapseAll();
 this.table.className = "rubrika-deployed";
 this.RenderContents();
 this.collapsed = false;
}

RubrikaContainer.prototype.CollapseAll = function()
{
  for(var i in this.rubrika.f)
  if (this.rubrika.f[i].facet.raw !== this.facet_object.facet.raw)
  {
    this.rubrika.f[i].container.Collapse();
  }
}

// -- rubrika_container_dropdown.js
function RubrikaContainerDropdown( rubrika )
{
  this.rubrika = rubrika;
  this.collapsed = true;
}
RubrikaContainerDropdown.prototype = new RubrikaContainer();
RubrikaContainerDropdown.prototype.constructor = RubrikaContainerDropdown;

RubrikaContainerDropdown.prototype.Render = function( target, id_prefix )
{
  var id1 = id_prefix+"__"+(this.facet_object.facet.supertag.replace(/\/+/g, "_"));
  var id2 = id1+"__collapsed";
  var id3 = id1+"__deployed";
  var id4 = id1+"__actions";
  var id5 = id1+"__radio_off";
  var id5a = id1+"__radio_on";
  var id6 = id1+"__iframe";

  this.facet_anchor_id = id5;
  this.static_id = id2;

  var in_facet = this.rubrika.KeywordsInFacet( this.facet_object.facet.facet );
  this.collapsed = in_facet.length==0;

  var result = '';
  result+='<div class="rubrika">';
  result+='<div class="rubrika-'+
            (in_facet.length?"deployed":"collapsed")+
                               '" id="'+id1+'">'; 

  var container_dest = "document.getElementById('"+id5+"')._facet.container";

  result+='<table class="dropdown-title-" cellspacing="0" cellpadding="0">'+
          '<tr><td><input type="radio" name="'+id5+'" id="'+id5+'"  onclick="this._facet.container.Collapse();" '+
           (in_facet.length?"":'checked="checked"')+
          ' /></td>'+
              '<td><label onclick="'+container_dest+'.Collapse();" for="'+id5+'">'+this.none_title+'</label></td></tr>'+
          '<tr><td><input type="radio" name="'+id5+'" id="'+id5a+'" onclick="this._facet.container.Deploy();" '+
          (!in_facet.length?"":'checked="checked"')+
          ' /></td>'+
              '<td><label onclick="'+container_dest+'.Deploy();" for="'+id5a+'">'+this.facet_object.facet.raw+'</label></td></tr>'+
          '</table>';
   result+='<div class="deployed-'+(this.dropdown_always?"always-":"")+'" id="'+id3+'">';
   result+= this.RenderDeployed( id6 );
   result+='</div>';
  result+='</div>';
  result+='</div>';

  target.innerHTML += result;

  return id1;
}

RubrikaContainerDropdown.prototype.LinkTo = function( id1 )
{
  var id5  = id1+"__radio_off";
  var id5a = id1+"__radio_on";
  var id6 = id1+"__iframe";

  this.table = document.getElementById(id1);
  document.getElementById(id5)._facet  = this.facet_object;
  document.getElementById(id5a)._facet = this.facet_object;
  this.contents_id = id6;

  if (this.dropdown_always || !this.collapsed) this.RenderContents();
  this.SetCheckboxesDisabledTo( this.collapsed );

}

RubrikaContainerDropdown.prototype.Collapse = function()
{
  if (this.collapsed) return;
  this.table.className = "rubrika-collapsed";
  this.collapsed = true;             

  this.rubrika.CleanFacet( this.facet_object.facet.supertag );
  this.facet_object.radio_checkrubrika_sorted_contents_index = false;
  this.RenderContents();

  this.SetCheckboxesDisabledTo( true );
}

RubrikaContainerDropdown.prototype.Deploy = function()
{
  if (!this.collapsed) return;
  this.CollapseAll();
  this.table.className = "rubrika-deployed";
  this.RenderContents();
  this.collapsed = false;

  this.SetCheckboxesDisabledTo( false );
}

RubrikaContainerDropdown.prototype.SetCheckboxesDisabledTo = function( will_be_disabled )
{
  if (this.dropdown_always) 
  {
    if (this.rubrika.strict_no_iframe)
      iframe = document;
    else
     if (document.all)
       iframe = frames[this.contents_id].document;
     else
       iframe = document.getElementById(this.contents_id).contentWindow.document;
  
   if (this.dropdown_always)
     for (var i in this.facet_object.facet.sorted_contents)
       iframe.getElementById('checkbox_'+this.rubrika.target_id+this.facet_object.facet.sorted_contents[i].supertag)
                  .disabled = will_be_disabled;
  }

}

// -- rubrika_facet.js
function RubrikaFacet( rubrika, facet, container )
{
  this.rubrika = rubrika;
  this.facet   = facet;

  this.container = container;
  if (this.container) this.container.Init( this );
  this.input_mode = "checkbox";
}
RubrikaFacet.prototype.Render = function( target, id_prefix )
{ return this.container.Render( target, id_prefix ); }
RubrikaFacet.prototype.RebuildStatic = function()
{ return this.container.RebuildStatic(); }
RubrikaFacet.prototype.LinkTo = function( id1 )
{
  this.container.LinkTo( id1 );
  this.RenderContents();
}

RubrikaFacet.prototype.CheckRubrika = function( supertag, checked )
{
  if (this.container.collapsed) return false;
  this.rubrika.FlipKeyword( supertag, checked );
  return true;
}

RubrikaFacet.prototype.RenderContentsWord = function( keyword )
{
  var result = "";

  var checked = this.rubrika.keywords[ keyword.supertag ];
  var more = this.RenderContentsWordInputMore();

  var label_onclick = 'onclick="return document._facet_'+this.rubrika.target_id+this.facet.supertag+
                      '.CheckRubrika(\''+keyword.supertag+'\', !(document.getElementById(\'checkbox_'+
                      this.rubrika.target_id+keyword.supertag+'\').checked) );" ';

  result+='<div class="kwd" id="kwd_'+keyword.supertag+'">';
   result+= '<table cellspacing="0" cellpadding="0"><tr>'+
            '<td><div class="none" style="padding:0 '+(keyword.depth*20)+'px 0 0">&nbsp;</div></td>'+
            '<td><input onclick="return document._facet_'+this.rubrika.target_id+this.facet.supertag+'.CheckRubrika(\''+keyword.supertag+'\', '+
            'document.getElementById(\'checkbox_'+this.rubrika.target_id+keyword.supertag+'\').checked);" '+
            'id="checkbox_'+this.rubrika.target_id+keyword.supertag+'" '+
            ' type="'+this.input_mode+'" '+more+
            (checked?' checked="checked" ':'')+ 
            ' /></td><td><label '+label_onclick+
                    'for="checkbox_'+this.rubrika.target_id+keyword.supertag+'">'+keyword.last+'</label></td></tr></table>';
  result+='</div>';

  return result;
}
RubrikaFacet.prototype.RenderContentsWordInputMore = function()
{ return ""; }

RubrikaFacet.prototype.RenderContents = function()
{
  var html = '';
  html += '<div class="rubrika-contents" id="contents'+this.rubrika.target_id+"_"+this.facet.supertag+'">';
  html += this._RenderContents();
  html += '</div>';
  return html;
}
RubrikaFacet.prototype._RenderContents = function()
{
  var html = '';
  for (var i in this.facet.sorted_contents)
   html+=this.RenderContentsWord( this.facet.sorted_contents[i] );
  return html;
}
RubrikaFacet.prototype.RenderContentsSelection = function()
{
}

// -- rubrika_radio.js
function RubrikaFacetRadio( rubrika, facet, container )
{
  this.rubrika = rubrika;
  this.facet   = facet;

  this.container = container;
  if (this.container) this.container.Init( this );
  this.input_mode = "radio";
}
RubrikaFacetRadio.prototype = new RubrikaFacet();
RubrikaFacetRadio.prototype.constructor = RubrikaFacetRadio;

RubrikaFacetRadio.prototype.CheckRubrika = function( supertag, checked )
{
  if (this.container.collapsed) return false;

  this.rubrika.CleanFacet( this.facet.supertag );

  this.rubrika.FlipKeyword( supertag, checked );

  if (this.rubrika.strict_no_iframe)
    iframe = document;
  else
    if (document.all)
      iframe = frames[this.container.contents_id].document;
    else
      iframe = document.getElementById(this.container.contents_id).contentWindow.document;

  var _supertag = supertag+"/";
  for (var i in this.facet.sorted_contents)
  {
    var a = iframe.getElementById("grp_"+this.rubrika.target_id+this.facet.sorted_contents[i].supertag);
    if (a != this.rubrika.Undef()) 
    if (_supertag.indexOf(this.facet.sorted_contents[i].supertag+"/") == 0) 
      a.className = "visible"; 
    else
      a.className = "invisible"; 
  }

  return true;
}

RubrikaFacetRadio.prototype.RenderContentsWordInputMore = function()
{ return ' name ="radio_'+this.rubrika.target_id+this.facet.supertag+'"'; }

RubrikaFacet.prototype.RenderContentsSelection = function()
{
  if (this.radio_checkrubrika_sorted_contents_index)
  {
    var keyword = this.facet.sorted_contents[this.radio_checkrubrika_sorted_contents_index];
    this.CheckRubrika( keyword.supertag, true, keyword.depth );
    this.radio_checkrubrika_sorted_contents_index = false;
  }
}

RubrikaFacetRadio.prototype._RenderContents = function()
{
  var html = '';

  var depth = 0;

  for (var i in this.facet.sorted_contents)
  {
    if (this.facet.sorted_contents[i].depth > depth) 
    {
      html+="<div "+
            (this.facet.sorted_contents[i].depth > 0?"class='invisible'":"")+
            " id='grp_"+this.rubrika.target_id+this.facet.sorted_contents[i-1].supertag+"'>";
      depth = this.facet.sorted_contents[i].depth;
    }
    if (this.facet.sorted_contents[i].depth < depth) 
    {
      var amount;
      amount = depth - this.facet.sorted_contents[i].depth;
      for(var j=0; j<amount; j++)
        html+="</div>";
      depth = this.facet.sorted_contents[i].depth;
    }
    html+=this.RenderContentsWord( this.facet.sorted_contents[i] );

    var keyword = this.facet.sorted_contents[i];
    if (this.rubrika.keywords[ keyword.supertag ])
      this.radio_checkrubrika_sorted_contents_index = i;
  }

  return html;
}

// -- rubrika_helpers.js
function rubrika_flip( facet )
{
  if (facet.container.collapsed) facet.container.Deploy();
  else                           facet.container.Collapse();
  return false;
}
function rubrika_clean( facet )
{
  facet.rubrika.CleanFacet( facet.facet.supertag );
  rubrika_rebuild_view( facet );
  return false;
}
function rubrika_add( facet )
{
  var raw = prompt("Новая рубрика:", "");
  if ((raw !== null) && (raw !== ""))
  {
    facet.rubrika.AddKeyword( raw, facet );
    rubrika_rebuild_view( facet );
  }
  return false;
}
function rubrika_rebuild_view( facet )
{
  if (facet.rubrika.strict_no_iframe)
    iframe = document;
  else
   if (document.all)
     iframe = frames[facet.container.contents_id].document;
   else
     iframe = document.getElementById(facet.container.contents_id).contentWindow.document;
    iframe.getElementById("contents"+facet.rubrika.target_id+"_"+facet.facet.supertag).innerHTML = 
        facet._RenderContents();
}
function rubrika_sort_by_supertag( a, b )
{ 
  if (a.supertag < b.supertag) return -1;
  if (a.supertag > b.supertag) return 1;
  return 0;
}
function rubrika_sort_by_under( a, b )
{ 
  if (a.under < b.under) return -1;
  if (a.under > b.under) return 1;
  return 0;
}
function rubrika_sort_by_last( a, b )
{ 
  if (a.last < b.last) return -1;
  if (a.last > b.last) return 1;
  return 0;
}
function rubrika_sort_by_sort_order_all( a, b )
{ 
  var so = a._rubrika.sort_orders["all"];
  if (so[a.supertag] < so[b.supertag]) return -1;
  if (so[a.supertag] > so[b.supertag]) return 1;
  return 0;
}