(function(d){d.fn.tabby=function(f){var g=d.extend({},d.fn.tabby.defaults,f);var h=d.fn.tabby.pressed;return this.each(function(){$this=d(this);var i=d.meta?d.extend({},g,$this.data()):g;$this.bind("keydown",function(k){var j=d.fn.tabby.catch_kc(k);if(16==j){h.shft=true}if(17==j){h.ctrl=true;setTimeout("$.fn.tabby.pressed.ctrl = false;",1000)}if(18==j){h.alt=true;setTimeout("$.fn.tabby.pressed.alt = false;",1000)}if(9==j&&!h.ctrl&&!h.alt){k.preventDefault;h.last=j;setTimeout("$.fn.tabby.pressed.last = null;",0);e(d(k.target).get(0),h.shft,i);return false}}).bind("keyup",function(j){if(16==d.fn.tabby.catch_kc(j)){h.shft=false}}).bind("blur",function(j){if(9==h.last){d(j.target).one("focus",function(k){h.last=null}).get(0).focus()}})})};d.fn.tabby.catch_kc=function(f){return f.keyCode?f.keyCode:f.charCode?f.charCode:f.which};d.fn.tabby.pressed={shft:false,ctrl:false,alt:false,last:null};function b(f){if(window.console&&window.console.log){window.console.log("textarea count: "+f.size())}}function e(i,h,g){var f=i.scrollTop;if(i.setSelectionRange){a(i,h,g)}else{if(document.selection){c(i,h,g)}}i.scrollTop=f}d.fn.tabby.defaults={tabString:String.fromCharCode(9)};function a(j,f,u){var t=j.selectionStart;var r=j.selectionEnd;if(t==r){if(f){if("\t"==j.value.substring(t-u.tabString.length,t)){j.value=j.value.substring(0,t-u.tabString.length)+j.value.substring(t);j.focus();j.setSelectionRange(t-u.tabString.length,t-u.tabString.length)}else{if("\t"==j.value.substring(t,t+u.tabString.length)){j.value=j.value.substring(0,t)+j.value.substring(t+u.tabString.length);j.focus();j.setSelectionRange(t,t)}}}else{j.value=j.value.substring(0,t)+u.tabString+j.value.substring(t);j.focus();j.setSelectionRange(t+u.tabString.length,t+u.tabString.length)}}else{var v=j.value.split("\n");var s=new Array();var l=0;var h=0;var g=false;for(var n in v){h=l+v[n].length;s.push({start:l,end:h,selected:(l<=t&&h>t)||(h>=r&&l<r)||(l>t&&h<r)});l=h+1}var k=0;for(var n in s){if(s[n].selected){var q=s[n].start+k;if(f&&u.tabString==j.value.substring(q,q+u.tabString.length)){j.value=j.value.substring(0,q)+j.value.substring(q+u.tabString.length);k-=u.tabString.length}else{if(!f){j.value=j.value.substring(0,q)+u.tabString+j.value.substring(q);k+=u.tabString.length}}}}j.focus();var p=t+((k>0)?u.tabString.length:(k<0)?-u.tabString.length:0);var m=r+k;j.setSelectionRange(p,m)}}function c(q,w,f){var p=document.selection.createRange();if(q==p.parentElement()){if(""==p.text){if(w){var l=p.getBookmark();p.moveStart("character",-f.tabString.length);if(f.tabString==p.text){p.text=""}else{p.moveToBookmark(l);p.moveEnd("character",f.tabString.length);if(f.tabString==p.text){p.text=""}}p.collapse(true);p.select()}else{p.text=f.tabString;p.collapse(false);p.select()}}else{var k=p.text;var n=k.length;var u=k.split("\r\n");var z=document.body.createTextRange();z.moveToElementText(q);z.setEndPoint("EndToStart",p);var m=z.text;var x=m.split("\r\n");var r=m.length;var y=document.body.createTextRange();y.moveToElementText(q);y.setEndPoint("StartToEnd",p);var v=y.text;var g=document.body.createTextRange();g.moveToElementText(q);g.setEndPoint("StartToEnd",z);var s=g.text;var h=d(q).html();d("#r3").text(r+" + "+n+" + "+v.length+" = "+h.length);if((r+s.length)<h.length){x.push("");r+=2;if(w&&f.tabString==u[0].substring(0,f.tabString.length)){u[0]=u[0].substring(f.tabString.length)}else{if(!w){u[0]=f.tabString+u[0]}}}else{if(w&&f.tabString==x[x.length-1].substring(0,f.tabString.length)){x[x.length-1]=x[x.length-1].substring(f.tabString.length)}else{if(!w){x[x.length-1]=f.tabString+x[x.length-1]}}}for(var t=1;t<u.length;t++){if(w&&f.tabString==u[t].substring(0,f.tabString.length)){u[t]=u[t].substring(f.tabString.length)}else{if(!w){u[t]=f.tabString+u[t]}}}if(1==x.length&&0==r){if(w&&f.tabString==u[0].substring(0,f.tabString.length)){u[0]=u[0].substring(f.tabString.length)}else{if(!w){u[0]=f.tabString+u[0]}}}if((r+n+v.length)<h.length){u.push("");n+=2}z.text=x.join("\r\n");p.text=u.join("\r\n");var j=document.body.createTextRange();j.moveToElementText(q);if(0<r){j.setEndPoint("StartToEnd",z)}else{j.setEndPoint("StartToStart",z)}j.setEndPoint("EndToEnd",p);j.select()}}}})(jQuery);