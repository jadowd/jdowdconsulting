/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dojox.flash._base"]){
dojo._hasResource["dojox.flash._base"]=true;
dojo.provide("dojox.flash._base");
dojo.require("dijit._base.place");
dojox.flash=function(){
};
dojox.flash={ready:false,url:null,_visible:true,_loadedListeners:new Array(),_installingListeners:new Array(),setSwf:function(_1,_2){
this.url=_1;
if(typeof _2!="undefined"){
this._visible=_2;
}
this._initialize();
},addLoadedListener:function(_3){
this._loadedListeners.push(_3);
},addInstallingListener:function(_4){
this._installingListeners.push(_4);
},loaded:function(){
dojox.flash.ready=true;
if(dojox.flash._loadedListeners.length>0){
for(var i=0;i<dojox.flash._loadedListeners.length;i++){
dojox.flash._loadedListeners[i].call(null);
}
}
},installing:function(){
if(dojox.flash._installingListeners.length>0){
for(var i=0;i<dojox.flash._installingListeners.length;i++){
dojox.flash._installingListeners[i].call(null);
}
}
},_initialize:function(){
var _7=new dojox.flash.Install();
dojox.flash.installer=_7;
if(_7.needed()==true){
_7.install();
}else{
dojox.flash.obj=new dojox.flash.Embed(this._visible);
dojox.flash.obj.write();
dojox.flash.comm=new dojox.flash.Communicator();
}
}};
dojox.flash.Info=function(){
if(dojo.isIE){
document.write(["<script language=\"VBScript\" type=\"text/vbscript\">","Function VBGetSwfVer(i)","  on error resume next","  Dim swControl, swVersion","  swVersion = 0","  set swControl = CreateObject(\"ShockwaveFlash.ShockwaveFlash.\" + CStr(i))","  if (IsObject(swControl)) then","    swVersion = swControl.GetVariable(\"$version\")","  end if","  VBGetSwfVer = swVersion","End Function","</script>"].join("\r\n"));
}
this._detectVersion();
};
dojox.flash.Info.prototype={version:-1,versionMajor:-1,versionMinor:-1,versionRevision:-1,capable:false,installing:false,isVersionOrAbove:function(_8,_9,_a){
_a=parseFloat("."+_a);
if(this.versionMajor>=_8&&this.versionMinor>=_9&&this.versionRevision>=_a){
return true;
}else{
return false;
}
},_detectVersion:function(){
var _b;
for(var _c=25;_c>0;_c--){
if(dojo.isIE){
_b=VBGetSwfVer(_c);
}else{
_b=this._JSFlashInfo(_c);
}
if(_b==-1){
this.capable=false;
return;
}else{
if(_b!=0){
var _d;
if(dojo.isIE){
var _e=_b.split(" ");
var _f=_e[1];
_d=_f.split(",");
}else{
_d=_b.split(".");
}
this.versionMajor=_d[0];
this.versionMinor=_d[1];
this.versionRevision=_d[2];
var _10=this.versionMajor+"."+this.versionRevision;
this.version=parseFloat(_10);
this.capable=true;
break;
}
}
}
},_JSFlashInfo:function(_11){
if(navigator.plugins!=null&&navigator.plugins.length>0){
if(navigator.plugins["Shockwave Flash 2.0"]||navigator.plugins["Shockwave Flash"]){
var _12=navigator.plugins["Shockwave Flash 2.0"]?" 2.0":"";
var _13=navigator.plugins["Shockwave Flash"+_12].description;
var _14=_13.split(" ");
var _15=_14[2].split(".");
var _16=_15[0];
var _17=_15[1];
if(_14[3]!=""){
var _18=_14[3].split("r");
}else{
var _18=_14[4].split("r");
}
var _19=_18[1]>0?_18[1]:0;
var _1a=_16+"."+_17+"."+_19;
return _1a;
}
}
return -1;
}};
dojox.flash.Embed=function(_1b){
this._visible=_1b;
};
dojox.flash.Embed.prototype={width:215,height:138,id:"flashObject",_visible:true,protocol:function(){
switch(window.location.protocol){
case "https:":
return "https";
break;
default:
return "http";
break;
}
},write:function(_1c){
var _1d="";
_1d+=("width: "+this.width+"px; ");
_1d+=("height: "+this.height+"px; ");
if(!this._visible){
_1d+="position: absolute; z-index: 10000; top: -1000px; left: -1000px; ";
}
var _1e;
var _1f=dojox.flash.url;
var _20=_1f;
var _21=_1f;
var _22=dojo.baseUrl;
if(_1c){
var _23=escape(window.location);
document.title=document.title.slice(0,47)+" - Flash Player Installation";
var _24=escape(document.title);
_20+="?MMredirectURL="+_23+"&MMplayerType=ActiveX"+"&MMdoctitle="+_24+"&baseUrl="+escape(_22);
_21+="?MMredirectURL="+_23+"&MMplayerType=PlugIn"+"&baseUrl="+escape(_22);
}else{
_20+="?cachebust="+new Date().getTime();
}
if(_21.indexOf("?")==-1){
_21+="?baseUrl="+escape(_22);
}else{
_21+="&baseUrl="+escape(_22);
}
_1e="<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" "+"codebase=\""+this.protocol()+"://fpdownload.macromedia.com/pub/shockwave/cabs/flash/"+"swflash.cab#version=8,0,0,0\"\n "+"width=\""+this.width+"\"\n "+"height=\""+this.height+"\"\n "+"id=\""+this.id+"\"\n "+"name=\""+this.id+"\"\n "+"align=\"middle\">\n "+"<param name=\"allowScriptAccess\" value=\"sameDomain\"></param>\n "+"<param name=\"movie\" value=\""+_20+"\"></param>\n "+"<param name=\"quality\" value=\"high\"></param>\n "+"<param name=\"bgcolor\" value=\"#ffffff\"></param>\n "+"<embed src=\""+_21+"\" "+"quality=\"high\" "+"bgcolor=\"#ffffff\" "+"width=\""+this.width+"\" "+"height=\""+this.height+"\" "+"id=\""+this.id+"Embed"+"\" "+"name=\""+this.id+"\" "+"swLiveConnect=\"true\" "+"align=\"middle\" "+"allowScriptAccess=\"sameDomain\" "+"type=\"application/x-shockwave-flash\" "+"pluginspage=\""+this.protocol()+"://www.macromedia.com/go/getflashplayer\" "+"></embed>\n"+"</object>\n";
dojo.connect(dojo,"loaded",dojo.hitch(this,function(){
var div=document.createElement("div");
div.setAttribute("id",this.id+"Container");
div.setAttribute("style",_1d);
div.innerHTML=_1e;
var _26=document.getElementsByTagName("body");
if(!_26||!_26.length){
throw new Error("No body tag for this page");
}
_26=_26[0];
_26.appendChild(div);
}));
},get:function(){
if(dojo.isIE||dojo.isSafari){
return document.getElementById(this.id);
}else{
return document[this.id+"Embed"];
}
},setVisible:function(_27){
var _28=dojo.byId(this.id+"Container");
if(_27==true){
_28.style.position="absolute";
_28.style.visibility="visible";
}else{
_28.style.position="absolute";
_28.style.x="-1000px";
_28.style.y="-1000px";
_28.style.visibility="hidden";
}
},center:function(){
var _29=this.width;
var _2a=this.height;
var _2b=dijit.getViewport();
var x=_2b.l+(_2b.w-_29)/2;
var y=_2b.t+(_2b.h-_2a)/2;
var _2e=dojo.byId(this.id+"Container");
_2e.style.top=y+"px";
_2e.style.left=x+"px";
}};
dojox.flash.Communicator=function(){
};
dojox.flash.Communicator.prototype={_addExternalInterfaceCallback:function(_2f){
var _30=dojo.hitch(this,function(){
var _31=new Array(arguments.length);
for(var i=0;i<arguments.length;i++){
_31[i]=this._encodeData(arguments[i]);
}
var _33=this._execFlash(_2f,_31);
_33=this._decodeData(_33);
return _33;
});
this[_2f]=_30;
},_encodeData:function(_34){
if(!_34||typeof _34!="string"){
return _34;
}
var _35=/\&([^;]*)\;/g;
_34=_34.replace(_35,"&amp;$1;");
_34=_34.replace(/</g,"&lt;");
_34=_34.replace(/>/g,"&gt;");
_34=_34.replace("\\","&custom_backslash;");
_34=_34.replace(/\0/g,"\\0");
_34=_34.replace(/\"/g,"&quot;");
return _34;
},_decodeData:function(_36){
if(_36&&_36.length&&typeof _36!="string"){
_36=_36[0];
}
if(!_36||typeof _36!="string"){
return _36;
}
_36=_36.replace(/\&custom_lt\;/g,"<");
_36=_36.replace(/\&custom_gt\;/g,">");
_36=_36.replace(/\&custom_backslash\;/g,"\\");
_36=_36.replace(/\\0/g," ");
return _36;
},_execFlash:function(_37,_38){
var _39=dojox.flash.obj.get();
_38=(_38)?_38:[];
for(var i=0;i<_38;i++){
if(typeof _38[i]=="string"){
_38[i]=this._encodeData(_38[i]);
}
}
var _3b=function(){
return eval(_39.CallFunction("<invoke name=\""+_37+"\" returntype=\"javascript\">"+__flash__argumentsToXML(_38,0)+"</invoke>"));
};
var _3c=_3b.call(_38);
if(typeof _3c=="string"){
_3c=this._decodeData(_3c);
}
return _3c;
}};
dojox.flash.Install=function(){
};
dojox.flash.Install.prototype={needed:function(){
if(dojox.flash.info.capable==false){
return true;
}
if(!dojox.flash.info.isVersionOrAbove(8,0,0)){
return true;
}
return false;
},install:function(){
dojox.flash.info.installing=true;
dojox.flash.installing();
if(dojox.flash.info.capable==false){
var _3d=new dojox.flash.Embed(false);
_3d.write();
}else{
if(dojox.flash.info.isVersionOrAbove(6,0,65)){
var _3d=new dojox.flash.Embed(false);
_3d.write(true);
_3d.setVisible(true);
_3d.center();
}else{
alert("This content requires a more recent version of the Macromedia "+" Flash Player.");
window.location.href=+dojox.flash.Embed.protocol()+"://www.macromedia.com/go/getflashplayer";
}
}
},_onInstallStatus:function(msg){
if(msg=="Download.Complete"){
dojox.flash._initialize();
}else{
if(msg=="Download.Cancelled"){
alert("This content requires a more recent version of the Macromedia "+" Flash Player.");
window.location.href=dojox.flash.Embed.protocol()+"://www.macromedia.com/go/getflashplayer";
}else{
if(msg=="Download.Failed"){
alert("There was an error downloading the Flash Player update. "+"Please try again later, or visit macromedia.com to download "+"the latest version of the Flash plugin.");
}
}
}
}};
dojox.flash.info=new dojox.flash.Info();
}
