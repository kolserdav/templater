var ui=function(e){var t={};function n(o){if(t[o])return t[o].exports;var a=t[o]={i:o,l:!1,exports:{}};return e[o].call(a.exports,a,a.exports,n),a.l=!0,a.exports}return n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{configurable:!1,enumerable:!0,get:o})},n.r=function(e){Object.defineProperty(e,"__esModule",{value:!0})},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=4)}([function(e,t,n){"use strict";e.exports=function(){function e(){}e.prototype.name=null,e.prototype.firstName=null,e.prototype.lastName=null,e.prototype.fullName=null;let t=new e,n="QWERTYUIOPASDFGHJKLMNBVCXZ",o=/[EYUIOAeyuioa]/,a="eyuioa",i="qwrtpsdfghjklzxcvbnm",l=(12*Math.random()).toFixed(0);return l=l<3?6:l,function e(r=0){return r<l?null===t.name?(t.name=n[(Math.random()*(n.length-1)).toFixed(0)],e(r+1)):t.name[r-1].match(o)?(t.name+=i[(Math.random()*(i.length-1)).toFixed(0)],e(r+1)):(t.name+=a[(Math.random()*(a.length-1)).toFixed(0)],e(r+1)):null===t.firstName?(t.firstName=t.name,t.name=null,l=(l=(12*Math.random()).toFixed(0))<3?6:l,e()):(null===t.lastName&&null!==t.firstName&&(t.lastName=t.name,t.fullName=t.firstName+"_"+t.lastName),t.fullName)}(),t.fullName}},function(e,t,n){let o=n(0);e.exports=function(e="name",t="cookie",n=1){n*=864e5;let a,i=(e=""===e?"name":e)+"=",l=new Date((new Date).getTime()+n);document.cookie?(a=!1,t=document.cookie.match(/\w*=\w*/)[0].replace(e+"=",""),document.cookie=i+t+"; expires="+l.toUTCString()):(a=!0,t="cookie"===t||""===t?btoa(o()):t,document.cookie=i+t+"; expires="+l.toUTCString());let r=atob(t);return JSON.stringify({name:{cookie:e,nameCookie:{clear:r,encode:t},time:l,newCookie:a}})}},function(e,t,n){"use strict";e.exports=function(e,t=null,n=null,o="POST"){let a=new XMLHttpRequest;null!==n&&""!==n||(n=function(e){console.log(e)});null!==t&&""!==t||(t="test=ajaxS works");if("GET"===o||"get"===o){let n=t;t=null,e=e+"?"+n}function i(){if(4!==a.readyState||200!==a.status)return!1;{let e=a.responseText;n(e)}}!function(e,t){a.onreadystatechange=i,a.open(o,e,!0),function(e){"GET"===o||"get"===o||e.setRequestHeader("Content-Type","application/x-www-form-urlencoded")}(a),a.send(t)}(e,t)}},function(e,t,n){const o=n(2),a=n(1);let i=document.querySelector("#jsonFile");i&&(i=i.getAttribute("src"));let l=document.querySelector("body");if(navigator.onLine){let e=a("name"),t=/www./,n=window.location.hostname,l=document.querySelector("title").innerText,r=window.location,c="cookie="+e+"&host="+r+"&title="+l;r.toString().match(t)&&(n="www."+n),o(n+"/respond-require-data",c,function(e){return e}),o(i,"",function(e,t=0){window.localStorage.nameLineLastTitle=document.querySelector("title").innerHTML,window.localStorage.nameLineLast=window.location,window.localStorage.nameLine=e})}else{let e=window.localStorage.nameLine,t=document.createElement("div");t.className="title",t.innerHTML="<h3>Offline pages</h3>",l.appendChild(t),function e(t,n=0){let o=JSON.parse(t);if(n<o.pages.count){let a=o.pages["page-"+n];for(key in a)if(a.hasOwnProperty(key)){let o=document.createElement("div");o.className="sic",o.innerHTML="<a href="+key+">"+a[key]+"</a>",l.appendChild(o),e(t,n+1)}if(n===o.pages.count-1){let e=document.createElement("div");e.className="sic2",e.innerHTML="<a href="+window.localStorage.nameLineLast+">"+window.localStorage.nameLineLastTitle+"</a>",l.appendChild(e)}}}(e)}},function(e,t,n){e.exports=n(3)}]);