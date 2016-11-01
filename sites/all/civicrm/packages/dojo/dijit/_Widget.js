/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit._Widget"]){
dojo._hasResource["dijit._Widget"]=true;
dojo.provide("dijit._Widget");
dojo.require("dijit._base");
dojo.declare("dijit._Widget",null,{id:"",lang:"",dir:"","class":"",style:"",title:"",srcNodeRef:null,domNode:null,attributeMap:{id:"",dir:"",lang:"","class":"",style:"",title:""},postscript:function(_1,_2){
this.create(_1,_2);
},create:function(_3,_4){
this.srcNodeRef=dojo.byId(_4);
this._connects=[];
this._attaches=[];
if(this.srcNodeRef&&(typeof this.srcNodeRef.id=="string")){
this.id=this.srcNodeRef.id;
}
if(_3){
this.params=_3;
dojo.mixin(this,_3);
}
this.postMixInProperties();
if(!this.id){
this.id=dijit.getUniqueId(this.declaredClass.replace(/\./g,"_"));
}
dijit.registry.add(this);
this.buildRendering();
if(this.domNode){
for(var _5 in this.attributeMap){
var _6=this[_5];
if(typeof _6!="object"&&((_6!==""&&_6!==false)||(_3&&_3[_5]))){
this.setAttribute(_5,_6);
}
}
}
if(this.domNode){
this.domNode.setAttribute("widgetId",this.id);
}
this.postCreate();
if(this.srcNodeRef&&!this.srcNodeRef.parentNode){
delete this.srcNodeRef;
}
},postMixInProperties:function(){
},buildRendering:function(){
this.domNode=this.srcNodeRef||dojo.doc.createElement("div");
},postCreate:function(){
},startup:function(){
this._started=true;
},destroyRecursive:function(_7){
this.destroyDescendants();
this.destroy();
},destroy:function(_8){
this.uninitialize();
dojo.forEach(this._connects,function(_9){
dojo.forEach(_9,dojo.disconnect);
});
dojo.forEach(this._supportingWidgets||[],function(w){
w.destroy();
});
this.destroyRendering(_8);
dijit.registry.remove(this.id);
},destroyRendering:function(_b){
if(this.bgIframe){
this.bgIframe.destroy();
delete this.bgIframe;
}
if(this.domNode){
dojo._destroyElement(this.domNode);
delete this.domNode;
}
if(this.srcNodeRef){
dojo._destroyElement(this.srcNodeRef);
delete this.srcNodeRef;
}
},destroyDescendants:function(){
dojo.forEach(this.getDescendants(),function(_c){
_c.destroy();
});
},uninitialize:function(){
return false;
},onFocus:function(){
},onBlur:function(){
},_onFocus:function(e){
this.onFocus();
},_onBlur:function(){
this.onBlur();
},setAttribute:function(_e,_f){
var _10=this[this.attributeMap[_e]||"domNode"];
this[_e]=_f;
switch(_e){
case "class":
dojo.addClass(_10,_f);
break;
case "style":
if(_10.style.cssText){
_10.style.cssText+="; "+_f;
}else{
_10.style.cssText=_f;
}
break;
default:
if(/^on[A-Z]/.test(_e)){
_e=_e.toLowerCase();
}
if(typeof _f=="function"){
_f=dojo.hitch(this,_f);
}
dojo.attr(_10,_e,_f);
}
},toString:function(){
return "[Widget "+this.declaredClass+", "+(this.id||"NO ID")+"]";
},getDescendants:function(){
if(this.containerNode){
var _11=dojo.query("[widgetId]",this.containerNode);
return _11.map(dijit.byNode);
}else{
return [];
}
},nodesWithKeyClick:["input","button"],connect:function(obj,_13,_14){
var _15=[];
if(_13=="ondijitclick"){
if(!this.nodesWithKeyClick[obj.nodeName]){
_15.push(dojo.connect(obj,"onkeydown",this,function(e){
if(e.keyCode==dojo.keys.ENTER){
return (dojo.isString(_14))?this[_14](e):_14.call(this,e);
}else{
if(e.keyCode==dojo.keys.SPACE){
dojo.stopEvent(e);
}
}
}));
_15.push(dojo.connect(obj,"onkeyup",this,function(e){
if(e.keyCode==dojo.keys.SPACE){
return dojo.isString(_14)?this[_14](e):_14.call(this,e);
}
}));
}
_13="onclick";
}
_15.push(dojo.connect(obj,_13,this,_14));
this._connects.push(_15);
return _15;
},disconnect:function(_18){
for(var i=0;i<this._connects.length;i++){
if(this._connects[i]==_18){
dojo.forEach(_18,dojo.disconnect);
this._connects.splice(i,1);
return;
}
}
},isLeftToRight:function(){
if(!("_ltr" in this)){
this._ltr=dojo.getComputedStyle(this.domNode).direction!="rtl";
}
return this._ltr;
},isFocusable:function(){
return this.focus&&(dojo.style(this.domNode,"display")!="none");
}});
}
