/*
	Copyright (c) 2004-2008, The Dojo Foundation
	All Rights Reserved.

	Licensed under the Academic Free License version 2.1 or above OR the
	modified BSD license. For more information on Dojo licensing, see:

		http://dojotoolkit.org/book/dojo-book-0-9/introduction/licensing
*/


if(!dojo._hasResource["dijit.layout.BorderContainer"]){
dojo._hasResource["dijit.layout.BorderContainer"]=true;
dojo.provide("dijit.layout.BorderContainer");
dojo.require("dijit.layout._LayoutWidget");
dojo.require("dojo.cookie");
dojo.declare("dijit.layout.BorderContainer",dijit.layout._LayoutWidget,{design:"headline",liveSplitters:true,persist:false,_splitterClass:"dijit.layout._Splitter",postCreate:function(){
this.inherited(arguments);
this._splitters={};
this._splitterThickness={};
dojo.addClass(this.domNode,"dijitBorderContainer");
},startup:function(){
if(this._started){
return;
}
dojo.forEach(this.getChildren(),this._setupChild,this);
this.inherited(arguments);
},_setupChild:function(_1){
var _2=_1.region;
if(_2){
_1.domNode.style.position="absolute";
var _3=this.isLeftToRight();
if(_2=="leading"){
_2=_3?"left":"right";
}
if(_2=="trailing"){
_2=_3?"right":"left";
}
this["_"+_2]=_1.domNode;
this["_"+_2+"Widget"]=_1;
if(_1.splitter){
var _4=dojo.getObject(this._splitterClass);
var _5={left:"right",right:"left",top:"bottom",bottom:"top",leading:"trailing",trailing:"leading"};
var _6=dojo.query("[region="+_5[_1.region]+"]",this.domNode);
var _7=new _4({container:this,child:_1,region:_2,oppNode:_6[0],live:this.liveSplitters});
this._splitters[_2]=_7.domNode;
dojo.place(_7.domNode,_1.domNode,"after");
this._computeSplitterThickness(_2);
}
_1.region=_2;
}
},_computeSplitterThickness:function(_8){
var re=new RegExp("top|bottom");
this._splitterThickness[_8]=dojo.marginBox(this._splitters[_8])[(re.test(_8)?"h":"w")];
},layout:function(){
this._layoutChildren();
},addChild:function(_a,_b){
this.inherited(arguments);
this._setupChild(_a);
if(this._started){
this._layoutChildren();
}
},removeChild:function(_c){
var _d=_c.region;
var _e=this._splitters[_d];
if(_e){
dijit.byNode(_e).destroy();
delete this._splitters[_d];
delete this._splitterThickness[_d];
}
this.inherited(arguments);
delete this["_"+_d];
delete this["_"+_d+"Widget"];
if(this._started){
this._layoutChildren(_c.region);
}
},_layoutChildren:function(_f){
var _10=(this.design=="sidebar");
var _11=0,_12=0,_13=0,_14=0;
var _15={},_16={},_17={},_18={},_19=(this._center&&this._center.style)||{};
var _1a=/left|right/.test(_f);
var _1b=!_f||(!_1a&&!_10);
var _1c=!_f||(_1a&&_10);
if(this._top){
_15=_1c&&this._top.style;
_11=dojo.marginBox(this._top).h;
}
if(this._left){
_16=_1b&&this._left.style;
_13=dojo.marginBox(this._left).w;
}
if(this._right){
_17=_1b&&this._right.style;
_14=dojo.marginBox(this._right).w;
}
if(this._bottom){
_18=_1c&&this._bottom.style;
_12=dojo.marginBox(this._bottom).h;
}
var _1d=this._splitters;
var _1e=_1d.top;
var _1f=_1d.bottom;
var _20=_1d.left;
var _21=_1d.right;
var _22=this._splitterThickness;
var _23=_22.top||0;
var _24=_22.left||0;
var _25=_22.right||0;
var _26=_22.bottom||0;
if(_24>50||_25>50){
setTimeout(dojo.hitch(this,function(){
for(var _27 in this._splitters){
this._computeSplitterThickness(_27);
}
this._layoutChildren();
}),50);
return false;
}
var _28={left:(_10?_13+_24:"0")+"px",right:(_10?_14+_25:"0")+"px"};
if(_1e){
dojo.mixin(_1e.style,_28);
_1e.style.top=_11+"px";
}
if(_1f){
dojo.mixin(_1f.style,_28);
_1f.style.bottom=_12+"px";
}
_28={top:(_10?"0":_11+_23)+"px",bottom:(_10?"0":_12+_26)+"px"};
if(_20){
dojo.mixin(_20.style,_28);
_20.style.left=_13+"px";
}
if(_21){
dojo.mixin(_21.style,_28);
_21.style.right=_14+"px";
}
dojo.mixin(_19,{top:_11+_23+"px",left:_13+_24+"px",right:_14+_25+"px",bottom:_12+_26+"px"});
var _29={top:_10?"0":_19.top,bottom:_10?"0":_19.bottom};
dojo.mixin(_16,_29);
dojo.mixin(_17,_29);
_16.left=_17.right=_15.top=_18.bottom="0";
if(_10){
_15.left=_18.left=_13+(this.isLeftToRight()?_24:0)+"px";
_15.right=_18.right=_14+(this.isLeftToRight()?0:_25)+"px";
}else{
_15.left=_15.right=_18.left=_18.right="0";
}
var _2a=dojo.isIE||dojo.some(this.getChildren(),function(_2b){
return _2b.domNode.tagName=="TEXTAREA";
});
if(_2a){
var _2c=function(n,b){
n=dojo.byId(n);
var s=dojo.getComputedStyle(n);
if(!b){
return dojo._getBorderBox(n,s);
}
var me=dojo._getMarginExtents(n,s);
dojo._setMarginBox(n,b.l,b.t,b.w+me.w,b.h+me.h,s);
return null;
};
var _31=function(_32,dim){
if(_32){
_32.resize?_32.resize(dim):dojo.marginBox(_32.domNode,dim);
}
};
var _34=_2c(this.domNode);
var _35=_34.h;
var _36=_35;
if(this._top){
_36-=_11;
}
if(this._bottom){
_36-=_12;
}
if(_1e){
_36-=_23;
}
if(_1f){
_36-=_26;
}
var _37={h:_36};
var _38=_10?_35:_36;
if(_20){
_20.style.height=_38;
}
if(_21){
_21.style.height=_38;
}
_31(this._leftWidget,{h:_38});
_31(this._rightWidget,{h:_38});
var _39=_34.w;
var _3a=_39;
if(this._left){
_3a-=_13;
}
if(this._right){
_3a-=_14;
}
if(_20){
_3a-=_24;
}
if(_21){
_3a-=_25;
}
_37.w=_3a;
var _3b=_10?_3a:_39;
if(_1e){
_1e.style.width=_3b;
}
if(_1f){
_1f.style.width=_3b;
}
_31(this._topWidget,{w:_3b});
_31(this._bottomWidget,{w:_3b});
_31(this._centerWidget,_37);
}else{
var _3c={};
if(_f){
_3c[_f]=_3c.center=true;
if(/top|bottom/.test(_f)&&this.design!="sidebar"){
_3c.left=_3c.right=true;
}else{
if(/left|right/.test(_f)&&this.design=="sidebar"){
_3c.top=_3c.bottom=true;
}
}
}
dojo.forEach(this.getChildren(),function(_3d){
if(_3d.resize&&(!_f||_3d.region in _3c)){
_3d.resize();
}
},this);
}
}});
dojo.extend(dijit._Widget,{region:"",splitter:false,minSize:0,maxSize:Infinity});
dojo.require("dijit._Templated");
dojo.declare("dijit.layout._Splitter",[dijit._Widget,dijit._Templated],{live:true,templateString:"<div class=\"dijitSplitter\" dojoAttachEvent=\"onkeypress:_onKeyPress,onmousedown:_startDrag\" tabIndex=\"0\" waiRole=\"separator\"><div class=\"dijitSplitterThumb\"></div></div>",postCreate:function(){
this.inherited(arguments);
this.horizontal=/top|bottom/.test(this.region);
dojo.addClass(this.domNode,"dijitSplitter"+(this.horizontal?"H":"V"));
this._factor=/top|left/.test(this.region)?1:-1;
this._minSize=this.child.minSize;
this._computeMaxSize();
this.connect(this.container,"layout",dojo.hitch(this,this._computeMaxSize));
this._cookieName=this.container.id+"_"+this.region;
if(this.container.persist){
var _3e=dojo.cookie(this._cookieName);
if(_3e){
this.child.domNode.style[this.horizontal?"height":"width"]=_3e;
}
}
},_computeMaxSize:function(){
var dim=this.horizontal?"h":"w";
var _40=dojo.contentBox(this.container.domNode)[dim]-(this.oppNode?dojo.marginBox(this.oppNode)[dim]:0);
this._maxSize=Math.min(this.child.maxSize,_40);
},_startDrag:function(e){
if(!this.cover){
this.cover=dojo.doc.createElement("div");
dojo.addClass(this.cover,"dijitSplitterCover");
dojo.place(this.cover,this.child.domNode,"after");
}else{
this.cover.style.zIndex=1;
}
if(this.fake){
dojo._destroyElement(this.fake);
}
if(!(this._resize=this.live)){
(this.fake=this.domNode.cloneNode(true)).removeAttribute("id");
dojo.addClass(this.domNode,"dijitSplitterShadow");
dojo.place(this.fake,this.domNode,"after");
}
dojo.addClass(this.domNode,"dijitSplitterActive");
var _42=this._factor,max=this._maxSize,min=this._minSize||10;
var _45=this.horizontal?"pageY":"pageX";
var _46=e[_45];
var _47=this.domNode.style;
var dim=this.horizontal?"h":"w";
var _49=dojo.marginBox(this.child.domNode)[dim];
var _4a=parseInt(this.domNode.style[this.region]);
var _4b=this._resize;
var _4c=this.region;
var mb={};
var _4e=this.child.domNode;
var _4f=dojo.hitch(this.container,this.container._layoutChildren);
var de=dojo.doc.body;
this._handlers=(this._handlers||[]).concat([dojo.connect(de,"onmousemove",this._drag=function(e,_52){
var _53=e[_45]-_46,_54=_42*_53+_49,_55=Math.max(Math.min(_54,max),min);
if(_4b||_52){
mb[dim]=_55;
dojo.marginBox(_4e,mb);
_4f(_4c);
}
_47[_4c]=_42*_53+_4a+(_55-_54)+"px";
}),dojo.connect(de,"onmouseup",this,"_stopDrag")]);
dojo.stopEvent(e);
},_stopDrag:function(e){
try{
if(this.cover){
this.cover.style.zIndex=-1;
}
if(this.fake){
dojo._destroyElement(this.fake);
}
dojo.removeClass(this.domNode,"dijitSplitterActive");
dojo.removeClass(this.domNode,"dijitSplitterShadow");
this._drag(e);
this._drag(e,true);
}
finally{
this._cleanupHandlers();
delete this._drag;
}
if(this.container.persist){
dojo.cookie(this._cookieName,this.child.domNode.style[this.horizontal?"height":"width"]);
}
},_cleanupHandlers:function(){
dojo.forEach(this._handlers,dojo.disconnect);
delete this._handlers;
},_onKeyPress:function(e){
this._resize=true;
var _58=this.horizontal;
var _59=1;
var dk=dojo.keys;
switch(e.keyCode){
case _58?dk.UP_ARROW:dk.LEFT_ARROW:
_59*=-1;
break;
case _58?dk.DOWN_ARROW:dk.RIGHT_ARROW:
break;
default:
return;
}
var _5b=dojo.marginBox(this.child.domNode)[_58?"h":"w"]+this._factor*_59;
var mb={};
mb[this.horizontal?"h":"w"]=Math.max(Math.min(_5b,this._maxSize),this._minSize);
dojo.marginBox(this.child.domNode,mb);
this.container._layoutChildren(this.region);
dojo.stopEvent(e);
},destroy:function(){
this._cleanupHandlers();
delete this.child;
delete this.container;
delete this.fake;
this.inherited(arguments);
}});
}
