/* $Id$ */
/* Copyright (c) 2006, Yahoo! Inc. All rights reserved. Code licensed under the BSD License:http://developer.yahoo.net/yui/license.txt Version: 0.11.4 */
YAHOO.util.DragDrop=function(id,_2,_3){if(id){this.init(id,_2,_3);}};YAHOO.util.DragDrop.prototype={id:null,config:null,dragElId:null,handleElId:null,invalidHandleTypes:null,invalidHandleIds:null,invalidHandleClasses:null,startPageX:0,startPageY:0,groups:null,locked:false,lock:function(){this.locked=true;},unlock:function(){this.locked=false;},isTarget:true,padding:null,_domRef:null,__ygDragDrop:true,constrainX:false,constrainY:false,minX:0,maxX:0,minY:0,maxY:0,maintainOffset:false,xTicks:null,yTicks:null,primaryButtonOnly:true,available:false,b4StartDrag:function(x,y){},startDrag:function(x,y){},b4Drag:function(e){},onDrag:function(e){},onDragEnter:function(e,id){},b4DragOver:function(e){},onDragOver:function(e,id){},b4DragOut:function(e){},onDragOut:function(e,id){},b4DragDrop:function(e){},onDragDrop:function(e,id){},b4EndDrag:function(e){},endDrag:function(e){},b4MouseDown:function(e){},onMouseDown:function(e){},onMouseUp:function(e){},onAvailable:function(){},getEl:function(){if(!this._domRef){this._domRef=YAHOO.util.Dom.get(this.id);}return this._domRef;},getDragEl:function(){return YAHOO.util.Dom.get(this.dragElId);},init:function(id,_7,_8){this.initTarget(id,_7,_8);YAHOO.util.Event.addListener(this.id,"mousedown",this.handleMouseDown,this,true);},initTarget:function(id,_9,_10){this.config=_10||{};this.DDM=YAHOO.util.DDM;this.groups={};this.id=id;this.addToGroup((_9)?_9:"default");this.handleElId=id;YAHOO.util.Event.onAvailable(id,this.handleOnAvailable,this,true);this.setDragElId(id);this.invalidHandleTypes={A:"A"};this.invalidHandleIds={};this.invalidHandleClasses=[];this.applyConfig();},applyConfig:function(){this.padding=this.config.padding||[0,0,0,0];this.isTarget=(this.config.isTarget!==false);this.maintainOffset=(this.config.maintainOffset);this.primaryButtonOnly=(this.config.primaryButtonOnly!==false);},handleOnAvailable:function(){this.available=true;this.resetConstraints();this.onAvailable();},setPadding:function(_11,_12,_13,_14){if(!_12&&0!==_12){this.padding=[_11,_11,_11,_11];}else{if(!_13&&0!==_13){this.padding=[_11,_12,_11,_12];}else{this.padding=[_11,_12,_13,_14];}}},setInitPosition:function(_15,_16){var el=this.getEl();if(!this.DDM.verifyEl(el)){return;}var dx=_15||0;var dy=_16||0;var p=YAHOO.util.Dom.getXY(el);this.initPageX=p[0]-dx;this.initPageY=p[1]-dy;this.lastPageX=p[0];this.lastPageY=p[1];this.setStartPosition(p);},setStartPosition:function(pos){var p=pos||YAHOO.util.Dom.getXY(this.getEl());this.deltaSetXY=null;this.startPageX=p[0];this.startPageY=p[1];},addToGroup:function(_22){this.groups[_22]=true;this.DDM.regDragDrop(this,_22);},removeFromGroup:function(_23){if(this.groups[_23]){delete this.groups[_23];}this.DDM.removeDDFromGroup(this,_23);},setDragElId:function(id){this.dragElId=id;},setHandleElId:function(id){this.handleElId=id;this.DDM.regHandle(this.id,id);},setOuterHandleElId:function(id){YAHOO.util.Event.addListener(id,"mousedown",this.handleMouseDown,this,true);this.setHandleElId(id);},unreg:function(){YAHOO.util.Event.removeListener(this.id,"mousedown",this.handleMouseDown);this._domRef=null;this.DDM._remove(this);},isLocked:function(){return (this.DDM.isLocked()||this.locked);},handleMouseDown:function(e,oDD){var EU=YAHOO.util.Event;var _26=e.which||e.button;if(this.primaryButtonOnly&&_26>1){return;}if(this.isLocked()){return;}this.DDM.refreshCache(this.groups);var pt=new YAHOO.util.Point(EU.getPageX(e),EU.getPageY(e));if(!this.DDM.isOverTarget(pt,this)){}else{var _28=EU.getTarget(e);if(this.isValidHandleChild(_28)&&(this.id==this.handleElId||this.DDM.handleWasClicked(_28,this.id))){this.setStartPosition();this.b4MouseDown(e);this.onMouseDown(e);this.DDM.handleMouseDown(e,this);this.DDM.stopEvent(e);}}},addInvalidHandleType:function(_29){var _30=_29.toUpperCase();this.invalidHandleTypes[_30]=_30;},addInvalidHandleId:function(id){this.invalidHandleIds[id]=id;},addInvalidHandleClass:function(_31){this.invalidHandleClasses.push(_31);},removeInvalidHandleType:function(_32){var _33=_32.toUpperCase();delete this.invalidHandleTypes[_33];},removeInvalidHandleId:function(id){delete this.invalidHandleIds[id];},removeInvalidHandleClass:function(_34){for(var i=0,len=this.invalidHandleClasses.length;i<len;++i){if(this.invalidHandleClasses[i]==_34){delete this.invalidHandleClasses[i];}}},isValidHandleChild:function(_36){var _37=true;var _38;try{_38=_36.nodeName.toUpperCase();}catch(e){_38=_36.nodeName;}_37=_37&&!this.invalidHandleTypes[_38];_37=_37&&!this.invalidHandleIds[_36.id];for(var i=0,len=this.invalidHandleClasses.length;_37&&i<len;++i){_37=!YAHOO.util.Dom.hasClass(_36,this.invalidHandleClasses[i]);}return _37;},setXTicks:function(_39,_40){this.xTicks=[];this.xTickSize=_40;var _41={};for(var i=this.initPageX;i>=this.minX;i=i-_40){if(!_41[i]){this.xTicks[this.xTicks.length]=i;_41[i]=true;}}for(i=this.initPageX;i<=this.maxX;i=i+_40){if(!_41[i]){this.xTicks[this.xTicks.length]=i;_41[i]=true;}}this.xTicks.sort(this.DDM.numericSort);},setYTicks:function(_42,_43){this.yTicks=[];this.yTickSize=_43;var _44={};for(var i=this.initPageY;i>=this.minY;i=i-_43){if(!_44[i]){this.yTicks[this.yTicks.length]=i;_44[i]=true;}}for(i=this.initPageY;i<=this.maxY;i=i+_43){if(!_44[i]){this.yTicks[this.yTicks.length]=i;_44[i]=true;}}this.yTicks.sort(this.DDM.numericSort);},setXConstraint:function(_45,_46,_47){this.leftConstraint=_45;this.rightConstraint=_46;this.minX=this.initPageX-_45;this.maxX=this.initPageX+_46;if(_47){this.setXTicks(this.initPageX,_47);}this.constrainX=true;},clearConstraints:function(){this.constrainX=false;this.constrainY=false;this.clearTicks();},clearTicks:function(){this.xTicks=null;this.yTicks=null;this.xTickSize=0;this.yTickSize=0;},setYConstraint:function(iUp,_49,_50){this.topConstraint=iUp;this.bottomConstraint=_49;this.minY=this.initPageY-iUp;this.maxY=this.initPageY+_49;if(_50){this.setYTicks(this.initPageY,_50);}this.constrainY=true;},resetConstraints:function(){if(this.initPageX||this.initPageX===0){var dx=(this.maintainOffset)?this.lastPageX-this.initPageX:0;var dy=(this.maintainOffset)?this.lastPageY-this.initPageY:0;this.setInitPosition(dx,dy);}else{this.setInitPosition();}if(this.constrainX){this.setXConstraint(this.leftConstraint,this.rightConstraint,this.xTickSize);}if(this.constrainY){this.setYConstraint(this.topConstraint,this.bottomConstraint,this.yTickSize);}},getTick:function(val,_52){if(!_52){return val;}else{if(_52[0]>=val){return _52[0];}else{for(var i=0,len=_52.length;i<len;++i){var _53=i+1;if(_52[_53]&&_52[_53]>=val){var _54=val-_52[i];var _55=_52[_53]-val;return (_55>_54)?_52[i]:_52[_53];}}return _52[_52.length-1];}}},toString:function(){return ("DragDrop "+this.id);}};if(!YAHOO.util.DragDropMgr){YAHOO.util.DragDropMgr=new function(){this.ids={};this.handleIds={};this.dragCurrent=null;this.dragOvers={};this.deltaX=0;this.deltaY=0;this.preventDefault=true;this.stopPropagation=true;this.initalized=false;this.locked=false;this.init=function(){this.initialized=true;};this.POINT=0;this.INTERSECT=1;this.mode=this.POINT;this._execOnAll=function(_56,_57){for(var i in this.ids){for(var j in this.ids[i]){var oDD=this.ids[i][j];if(!this.isTypeOfDD(oDD)){continue;}oDD[_56].apply(oDD,_57);}}};this._onLoad=function(){this.init();var EU=YAHOO.util.Event;EU.on(document,"mouseup",this.handleMouseUp,this,true);EU.on(document,"mousemove",this.handleMouseMove,this,true);EU.on(window,"unload",this._onUnload,this,true);EU.on(window,"resize",this._onResize,this,true);};this._onResize=function(e){this._execOnAll("resetConstraints",[]);};this.lock=function(){this.locked=true;};this.unlock=function(){this.locked=false;};this.isLocked=function(){return this.locked;};this.locationCache={};this.useCache=true;this.clickPixelThresh=3;this.clickTimeThresh=1000;this.dragThreshMet=false;this.clickTimeout=null;this.startX=0;this.startY=0;this.regDragDrop=function(oDD,_59){if(!this.initialized){this.init();}if(!this.ids[_59]){this.ids[_59]={};}this.ids[_59][oDD.id]=oDD;};this.removeDDFromGroup=function(oDD,_60){if(!this.ids[_60]){this.ids[_60]={};}var obj=this.ids[_60];if(obj&&obj[oDD.id]){delete obj[oDD.id];}};this._remove=function(oDD){for(var g in oDD.groups){if(g&&this.ids[g][oDD.id]){delete this.ids[g][oDD.id];}}delete this.handleIds[oDD.id];};this.regHandle=function(_63,_64){if(!this.handleIds[_63]){this.handleIds[_63]={};}this.handleIds[_63][_64]=_64;};this.isDragDrop=function(id){return (this.getDDById(id))?true:false;};this.getRelated=function(_65,_66){var _67=[];for(var i in _65.groups){for(j in this.ids[i]){var dd=this.ids[i][j];if(!this.isTypeOfDD(dd)){continue;}if(!_66||dd.isTarget){_67[_67.length]=dd;}}}return _67;};this.isLegalTarget=function(oDD,_69){var _70=this.getRelated(oDD,true);for(var i=0,len=_70.length;i<len;++i){if(_70[i].id==_69.id){return true;}}return false;};this.isTypeOfDD=function(oDD){return (oDD&&oDD.__ygDragDrop);};this.isHandle=function(_71,_72){return (this.handleIds[_71]&&this.handleIds[_71][_72]);};this.getDDById=function(id){for(var i in this.ids){if(this.ids[i][id]){return this.ids[i][id];}}return null;};this.handleMouseDown=function(e,oDD){this.currentTarget=YAHOO.util.Event.getTarget(e);this.dragCurrent=oDD;var el=oDD.getEl();this.startX=YAHOO.util.Event.getPageX(e);this.startY=YAHOO.util.Event.getPageY(e);this.deltaX=this.startX-el.offsetLeft;this.deltaY=this.startY-el.offsetTop;this.dragThreshMet=false;this.clickTimeout=setTimeout(function(){var DDM=YAHOO.util.DDM;DDM.startDrag(DDM.startX,DDM.startY);},this.clickTimeThresh);};this.startDrag=function(x,y){clearTimeout(this.clickTimeout);if(this.dragCurrent){this.dragCurrent.b4StartDrag(x,y);this.dragCurrent.startDrag(x,y);}this.dragThreshMet=true;};this.handleMouseUp=function(e){if(!this.dragCurrent){return;}clearTimeout(this.clickTimeout);if(this.dragThreshMet){this.fireEvents(e,true);}else{}this.stopDrag(e);this.stopEvent(e);};this.stopEvent=function(e){if(this.stopPropagation){YAHOO.util.Event.stopPropagation(e);}if(this.preventDefault){YAHOO.util.Event.preventDefault(e);}};this.stopDrag=function(e){if(this.dragCurrent){if(this.dragThreshMet){this.dragCurrent.b4EndDrag(e);this.dragCurrent.endDrag(e);}this.dragCurrent.onMouseUp(e);}this.dragCurrent=null;this.dragOvers={};};this.handleMouseMove=function(e){if(!this.dragCurrent){return true;}if(YAHOO.util.Event.isIE&&!e.button){this.stopEvent(e);return this.handleMouseUp(e);}if(!this.dragThreshMet){var _74=Math.abs(this.startX-YAHOO.util.Event.getPageX(e));var _75=Math.abs(this.startY-YAHOO.util.Event.getPageY(e));if(_74>this.clickPixelThresh||_75>this.clickPixelThresh){this.startDrag(this.startX,this.startY);}}if(this.dragThreshMet){this.dragCurrent.b4Drag(e);this.dragCurrent.onDrag(e);this.fireEvents(e,false);}this.stopEvent(e);return true;};this.fireEvents=function(e,_76){var dc=this.dragCurrent;if(!dc||dc.isLocked()){return;}var x=YAHOO.util.Event.getPageX(e);var y=YAHOO.util.Event.getPageY(e);var pt=new YAHOO.util.Point(x,y);var _78=[];var _79=[];var _80=[];var _81=[];var _82=[];for(var i in this.dragOvers){var ddo=this.dragOvers[i];if(!this.isTypeOfDD(ddo)){continue;}if(!this.isOverTarget(pt,ddo,this.mode)){_79.push(ddo);}_78[i]=true;delete this.dragOvers[i];}for(var _84 in dc.groups){if("string"!=typeof _84){continue;}for(i in this.ids[_84]){var oDD=this.ids[_84][i];if(!this.isTypeOfDD(oDD)){continue;}if(oDD.isTarget&&!oDD.isLocked()&&oDD!=dc){if(this.isOverTarget(pt,oDD,this.mode)){if(_76){_81.push(oDD);}else{if(!_78[oDD.id]){_82.push(oDD);}else{_80.push(oDD);}this.dragOvers[oDD.id]=oDD;}}}}}if(this.mode){if(_79.length){dc.b4DragOut(e,_79);dc.onDragOut(e,_79);}if(_82.length){dc.onDragEnter(e,_82);}if(_80.length){dc.b4DragOver(e,_80);dc.onDragOver(e,_80);}if(_81.length){dc.b4DragDrop(e,_81);dc.onDragDrop(e,_81);}}else{var len=0;for(i=0,len=_79.length;i<len;++i){dc.b4DragOut(e,_79[i].id);dc.onDragOut(e,_79[i].id);}for(i=0,len=_82.length;i<len;++i){dc.onDragEnter(e,_82[i].id);}for(i=0,len=_80.length;i<len;++i){dc.b4DragOver(e,_80[i].id);dc.onDragOver(e,_80[i].id);}for(i=0,len=_81.length;i<len;++i){dc.b4DragDrop(e,_81[i].id);dc.onDragDrop(e,_81[i].id);}}};this.getBestMatch=function(dds){var _87=null;var len=dds.length;if(len==1){_87=dds[0];}else{for(var i=0;i<len;++i){var dd=dds[i];if(dd.cursorIsOver){_87=dd;break;}else{if(!_87||_87.overlap.getArea()<dd.overlap.getArea()){_87=dd;}}}}return _87;};this.refreshCache=function(_88){for(var _89 in _88){if("string"!=typeof _89){continue;}for(var i in this.ids[_89]){var oDD=this.ids[_89][i];if(this.isTypeOfDD(oDD)){var loc=this.getLocation(oDD);if(loc){this.locationCache[oDD.id]=loc;}else{delete this.locationCache[oDD.id];}}}}};this.verifyEl=function(el){try{if(el){var _91=el.offsetParent;if(_91){return true;}}}catch(e){}return false;};this.getLocation=function(oDD){if(!this.isTypeOfDD(oDD)){return null;}var el=oDD.getEl(),pos,x1,x2,y1,y2,t,r,b,l;try{pos=YAHOO.util.Dom.getXY(el);}catch(e){}if(!pos){return null;}x1=pos[0];x2=x1+el.offsetWidth;y1=pos[1];y2=y1+el.offsetHeight;t=y1-oDD.padding[0];r=x2+oDD.padding[1];b=y2+oDD.padding[2];l=x1-oDD.padding[3];return new YAHOO.util.Region(t,r,b,l);};this.isOverTarget=function(pt,_92,_93){var loc=this.locationCache[_92.id];if(!loc||!this.useCache){loc=this.getLocation(_92);this.locationCache[_92.id]=loc;}if(!loc){return false;}_92.cursorIsOver=loc.contains(pt);var dc=this.dragCurrent;if(!dc||!dc.getTargetCoord||(!_93&&!dc.constrainX&&!dc.constrainY)){return _92.cursorIsOver;}_92.overlap=null;var pos=dc.getTargetCoord(pt.x,pt.y);var el=dc.getDragEl();var _94=new YAHOO.util.Region(pos.y,pos.x+el.offsetWidth,pos.y+el.offsetHeight,pos.x);var _95=_94.intersect(loc);if(_95){_92.overlap=_95;return (_93)?true:_92.cursorIsOver;}else{return false;}};this._onUnload=function(e,me){this.unregAll();};this.unregAll=function(){if(this.dragCurrent){this.stopDrag();this.dragCurrent=null;}this._execOnAll("unreg",[]);for(i in this.elementCache){delete this.elementCache[i];}this.elementCache={};this.ids={};};this.elementCache={};this.getElWrapper=function(id){var _97=this.elementCache[id];if(!_97||!_97.el){_97=this.elementCache[id]=new this.ElementWrapper(YAHOO.util.Dom.get(id));}return _97;};this.getElement=function(id){return YAHOO.util.Dom.get(id);};this.getCss=function(id){var el=YAHOO.util.Dom.get(id);return (el)?el.style:null;};this.ElementWrapper=function(el){this.el=el||null;this.id=this.el&&el.id;this.css=this.el&&el.style;};this.getPosX=function(el){return YAHOO.util.Dom.getX(el);};this.getPosY=function(el){return YAHOO.util.Dom.getY(el);};this.swapNode=function(n1,n2){if(n1.swapNode){n1.swapNode(n2);}else{var p=n2.parentNode;var s=n2.nextSibling;if(s==n1){p.insertBefore(n1,n2);}else{if(n2==n1.nextSibling){p.insertBefore(n2,n1);}else{n1.parentNode.replaceChild(n2,n1);p.insertBefore(n1,s);}}}};this.getScroll=function(){var t,l;if(document.documentElement&&document.documentElement.scrollTop){t=document.documentElement.scrollTop;l=document.documentElement.scrollLeft;}else{if(document.body){t=document.body.scrollTop;l=document.body.scrollLeft;}}return {top:t,left:l};};this.getStyle=function(el,_102){return YAHOO.util.Dom.getStyle(el,_102);};this.getScrollTop=function(){return this.getScroll().top;};this.getScrollLeft=function(){return this.getScroll().left;};this.moveToEl=function(_103,_104){var _105=YAHOO.util.Dom.getXY(_104);YAHOO.util.Dom.setXY(_103,_105);};this.getClientHeight=function(){return YAHOO.util.Dom.getClientHeight();};this.getClientWidth=function(){return YAHOO.util.Dom.getClientWidth();};this.numericSort=function(a,b){return (a-b);};this._timeoutCount=0;this._addListeners=function(){var DDM=YAHOO.util.DDM;if(YAHOO.util.Event&&document){DDM._onLoad();}else{if(DDM._timeoutCount>2000){}else{setTimeout(DDM._addListeners,10);if(document&&document.body){DDM._timeoutCount+=1;}}}};this.handleWasClicked=function(node,id){if(this.isHandle(id,node.id)){return true;}else{var p=node.parentNode;while(p){if(this.isHandle(id,p.id)){return true;}else{p=p.parentNode;}}}return false;};}();YAHOO.util.DDM=YAHOO.util.DragDropMgr;YAHOO.util.DDM._addListeners();}YAHOO.util.DD=function(id,_109,_110){if(id){this.init(id,_109,_110);}};YAHOO.extend(YAHOO.util.DD,YAHOO.util.DragDrop);YAHOO.util.DD.prototype.scroll=true;YAHOO.util.DD.prototype.autoOffset=function(_111,_112){var x=_111-this.startPageX;var y=_112-this.startPageY;this.setDelta(x,y);};YAHOO.util.DD.prototype.setDelta=function(_113,_114){this.deltaX=_113;this.deltaY=_114;};YAHOO.util.DD.prototype.setDragElPos=function(_115,_116){var el=this.getDragEl();this.alignElWithMouse(el,_115,_116);};YAHOO.util.DD.prototype.alignElWithMouse=function(el,_117,_118){var _119=this.getTargetCoord(_117,_118);if(!this.deltaSetXY){var _120=[_119.x,_119.y];YAHOO.util.Dom.setXY(el,_120);var _121=parseInt(YAHOO.util.Dom.getStyle(el,"left"),10);var _122=parseInt(YAHOO.util.Dom.getStyle(el,"top"),10);this.deltaSetXY=[_121-_119.x,_122-_119.y];}else{YAHOO.util.Dom.setStyle(el,"left",(_119.x+this.deltaSetXY[0])+"px");YAHOO.util.Dom.setStyle(el,"top",(_119.y+this.deltaSetXY[1])+"px");}this.cachePosition(_119.x,_119.y);this.autoScroll(_119.x,_119.y,el.offsetHeight,el.offsetWidth);};YAHOO.util.DD.prototype.cachePosition=function(_123,_124){if(_123){this.lastPageX=_123;this.lastPageY=_124;}else{var _125=YAHOO.util.Dom.getXY(this.getEl());this.lastPageX=_125[0];this.lastPageY=_125[1];}};YAHOO.util.DD.prototype.autoScroll=function(x,y,h,w){if(this.scroll){var _128=this.DDM.getClientHeight();var _129=this.DDM.getClientWidth();var st=this.DDM.getScrollTop();var sl=this.DDM.getScrollLeft();var bot=h+y;var _133=w+x;var _134=(_128+st-y-this.deltaY);var _135=(_129+sl-x-this.deltaX);var _136=40;var _137=(document.all)?80:30;if(bot>_128&&_134<_136){window.scrollTo(sl,st+_137);}if(y<st&&st>0&&y-st<_136){window.scrollTo(sl,st-_137);}if(_133>_129&&_135<_136){window.scrollTo(sl+_137,st);}if(x<sl&&sl>0&&x-sl<_136){window.scrollTo(sl-_137,st);}}};YAHOO.util.DD.prototype.getTargetCoord=function(_138,_139){var x=_138-this.deltaX;var y=_139-this.deltaY;if(this.constrainX){if(x<this.minX){x=this.minX;}if(x>this.maxX){x=this.maxX;}}if(this.constrainY){if(y<this.minY){y=this.minY;}if(y>this.maxY){y=this.maxY;}}x=this.getTick(x,this.xTicks);y=this.getTick(y,this.yTicks);return {x:x,y:y};};YAHOO.util.DD.prototype.applyConfig=function(){YAHOO.util.DD.superclass.applyConfig.call(this);this.scroll=(this.config.scroll!==false);};YAHOO.util.DD.prototype.b4MouseDown=function(e){this.autoOffset(YAHOO.util.Event.getPageX(e),YAHOO.util.Event.getPageY(e));};YAHOO.util.DD.prototype.b4Drag=function(e){this.setDragElPos(YAHOO.util.Event.getPageX(e),YAHOO.util.Event.getPageY(e));};YAHOO.util.DD.prototype.toString=function(){return ("DD "+this.id);};YAHOO.util.DDProxy=function(id,_140,_141){if(id){this.init(id,_140,_141);this.initFrame();}};YAHOO.extend(YAHOO.util.DDProxy,YAHOO.util.DD);YAHOO.util.DDProxy.dragElId="ygddfdiv";YAHOO.util.DDProxy.prototype.resizeFrame=true;YAHOO.util.DDProxy.prototype.centerFrame=false;YAHOO.util.DDProxy.prototype.createFrame=function(){var self=this;var body=document.body;if(!body||!body.firstChild){setTimeout(function(){self.createFrame();},50);return;}var div=this.getDragEl();if(!div){div=document.createElement("div");div.id=this.dragElId;var s=div.style;s.position="absolute";s.visibility="hidden";s.cursor="move";s.border="2px solid #aaa";s.zIndex=999;body.insertBefore(div,body.firstChild);}};YAHOO.util.DDProxy.prototype.initFrame=function(){this.createFrame();};YAHOO.util.DDProxy.prototype.applyConfig=function(){YAHOO.util.DDProxy.superclass.applyConfig.call(this);this.resizeFrame=(this.config.resizeFrame!==false);this.centerFrame=(this.config.centerFrame);this.setDragElId(this.config.dragElId||YAHOO.util.DDProxy.dragElId);};YAHOO.util.DDProxy.prototype.showFrame=function(_145,_146){var el=this.getEl();var _147=this.getDragEl();var s=_147.style;this._resizeProxy();if(this.centerFrame){this.setDelta(Math.round(parseInt(s.width,10)/2),Math.round(parseInt(s.height,10)/2));}this.setDragElPos(_145,_146);YAHOO.util.Dom.setStyle(_147,"visibility","visible");};YAHOO.util.DDProxy.prototype._resizeProxy=function(){if(this.resizeFrame){var DOM=YAHOO.util.Dom;var el=this.getEl();var _149=this.getDragEl();var bt=parseInt(DOM.getStyle(_149,"borderTopWidth"),10);var br=parseInt(DOM.getStyle(_149,"borderRightWidth"),10);var bb=parseInt(DOM.getStyle(_149,"borderBottomWidth"),10);var bl=parseInt(DOM.getStyle(_149,"borderLeftWidth"),10);if(isNaN(bt)){bt=0;}if(isNaN(br)){br=0;}if(isNaN(bb)){bb=0;}if(isNaN(bl)){bl=0;}var _154=Math.max(0,el.offsetWidth-br-bl);var _155=Math.max(0,el.offsetHeight-bt-bb);DOM.setStyle(_149,"width",_154+"px");DOM.setStyle(_149,"height",_155+"px");}};YAHOO.util.DDProxy.prototype.b4MouseDown=function(e){var x=YAHOO.util.Event.getPageX(e);var y=YAHOO.util.Event.getPageY(e);this.autoOffset(x,y);this.setDragElPos(x,y);};YAHOO.util.DDProxy.prototype.b4StartDrag=function(x,y){this.showFrame(x,y);};YAHOO.util.DDProxy.prototype.b4EndDrag=function(e){YAHOO.util.Dom.setStyle(this.getDragEl(),"visibility","hidden");};YAHOO.util.DDProxy.prototype.endDrag=function(e){var DOM=YAHOO.util.Dom;var lel=this.getEl();var del=this.getDragEl();DOM.setStyle(del,"visibility","");DOM.setStyle(lel,"visibility","hidden");YAHOO.util.DDM.moveToEl(lel,del);DOM.setStyle(del,"visibility","hidden");DOM.setStyle(lel,"visibility","");};YAHOO.util.DDProxy.prototype.toString=function(){return ("DDProxy "+this.id);};YAHOO.util.DDTarget=function(id,_158,_159){if(id){this.initTarget(id,_158,_159);}};YAHOO.extend(YAHOO.util.DDTarget,YAHOO.util.DragDrop);YAHOO.util.DDTarget.prototype.toString=function(){return ("DDTarget "+this.id);};
