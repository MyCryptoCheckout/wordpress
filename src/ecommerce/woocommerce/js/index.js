(()=>{var e={838:function(e){e.exports=function(){"use strict";const{entries:e,setPrototypeOf:t,isFrozen:n,getPrototypeOf:o,getOwnPropertyDescriptor:r}=Object;let{freeze:a,seal:i,create:l}=Object,{apply:c,construct:s}="undefined"!=typeof Reflect&&Reflect;a||(a=function(e){return e}),i||(i=function(e){return e}),c||(c=function(e,t,n){return e.apply(t,n)}),s||(s=function(e,t){return new e(...t)});const u=b(Array.prototype.forEach),m=b(Array.prototype.pop),p=b(Array.prototype.push),f=b(String.prototype.toLowerCase),d=b(String.prototype.toString),h=b(String.prototype.match),g=b(String.prototype.replace),y=b(String.prototype.indexOf),T=b(String.prototype.trim),E=b(Object.prototype.hasOwnProperty),_=b(RegExp.prototype.test),A=(N=TypeError,function(){for(var e=arguments.length,t=new Array(e),n=0;n<e;n++)t[n]=arguments[n];return s(N,t)});var N;function b(e){return function(t){for(var n=arguments.length,o=new Array(n>1?n-1:0),r=1;r<n;r++)o[r-1]=arguments[r];return c(e,t,o)}}function S(e,o){let r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:f;t&&t(e,null);let a=o.length;for(;a--;){let t=o[a];if("string"==typeof t){const e=r(t);e!==t&&(n(o)||(o[a]=e),t=e)}e[t]=!0}return e}function w(e){for(let t=0;t<e.length;t++)E(e,t)||(e[t]=null);return e}function R(t){const n=l(null);for(const[o,r]of e(t))E(t,o)&&(Array.isArray(r)?n[o]=w(r):r&&"object"==typeof r&&r.constructor===Object?n[o]=R(r):n[o]=r);return n}function v(e,t){for(;null!==e;){const n=r(e,t);if(n){if(n.get)return b(n.get);if("function"==typeof n.value)return b(n.value)}e=o(e)}return function(){return null}}const L=a(["a","abbr","acronym","address","area","article","aside","audio","b","bdi","bdo","big","blink","blockquote","body","br","button","canvas","caption","center","cite","code","col","colgroup","content","data","datalist","dd","decorator","del","details","dfn","dialog","dir","div","dl","dt","element","em","fieldset","figcaption","figure","font","footer","form","h1","h2","h3","h4","h5","h6","head","header","hgroup","hr","html","i","img","input","ins","kbd","label","legend","li","main","map","mark","marquee","menu","menuitem","meter","nav","nobr","ol","optgroup","option","output","p","picture","pre","progress","q","rp","rt","ruby","s","samp","section","select","shadow","small","source","spacer","span","strike","strong","style","sub","summary","sup","table","tbody","td","template","textarea","tfoot","th","thead","time","tr","track","tt","u","ul","var","video","wbr"]),x=a(["svg","a","altglyph","altglyphdef","altglyphitem","animatecolor","animatemotion","animatetransform","circle","clippath","defs","desc","ellipse","filter","font","g","glyph","glyphref","hkern","image","line","lineargradient","marker","mask","metadata","mpath","path","pattern","polygon","polyline","radialgradient","rect","stop","style","switch","symbol","text","textpath","title","tref","tspan","view","vkern"]),C=a(["feBlend","feColorMatrix","feComponentTransfer","feComposite","feConvolveMatrix","feDiffuseLighting","feDisplacementMap","feDistantLight","feDropShadow","feFlood","feFuncA","feFuncB","feFuncG","feFuncR","feGaussianBlur","feImage","feMerge","feMergeNode","feMorphology","feOffset","fePointLight","feSpecularLighting","feSpotLight","feTile","feTurbulence"]),D=a(["animate","color-profile","cursor","discard","font-face","font-face-format","font-face-name","font-face-src","font-face-uri","foreignobject","hatch","hatchpath","mesh","meshgradient","meshpatch","meshrow","missing-glyph","script","set","solidcolor","unknown","use"]),O=a(["math","menclose","merror","mfenced","mfrac","mglyph","mi","mlabeledtr","mmultiscripts","mn","mo","mover","mpadded","mphantom","mroot","mrow","ms","mspace","msqrt","mstyle","msub","msup","msubsup","mtable","mtd","mtext","mtr","munder","munderover","mprescripts"]),k=a(["maction","maligngroup","malignmark","mlongdiv","mscarries","mscarry","msgroup","mstack","msline","msrow","semantics","annotation","annotation-xml","mprescripts","none"]),M=a(["#text"]),I=a(["accept","action","align","alt","autocapitalize","autocomplete","autopictureinpicture","autoplay","background","bgcolor","border","capture","cellpadding","cellspacing","checked","cite","class","clear","color","cols","colspan","controls","controlslist","coords","crossorigin","datetime","decoding","default","dir","disabled","disablepictureinpicture","disableremoteplayback","download","draggable","enctype","enterkeyhint","face","for","headers","height","hidden","high","href","hreflang","id","inputmode","integrity","ismap","kind","label","lang","list","loading","loop","low","max","maxlength","media","method","min","minlength","multiple","muted","name","nonce","noshade","novalidate","nowrap","open","optimum","pattern","placeholder","playsinline","poster","preload","pubdate","radiogroup","readonly","rel","required","rev","reversed","role","rows","rowspan","spellcheck","scope","selected","shape","size","sizes","span","srclang","start","src","srcset","step","style","summary","tabindex","title","translate","type","usemap","valign","value","width","wrap","xmlns","slot"]),P=a(["accent-height","accumulate","additive","alignment-baseline","ascent","attributename","attributetype","azimuth","basefrequency","baseline-shift","begin","bias","by","class","clip","clippathunits","clip-path","clip-rule","color","color-interpolation","color-interpolation-filters","color-profile","color-rendering","cx","cy","d","dx","dy","diffuseconstant","direction","display","divisor","dur","edgemode","elevation","end","fill","fill-opacity","fill-rule","filter","filterunits","flood-color","flood-opacity","font-family","font-size","font-size-adjust","font-stretch","font-style","font-variant","font-weight","fx","fy","g1","g2","glyph-name","glyphref","gradientunits","gradienttransform","height","href","id","image-rendering","in","in2","k","k1","k2","k3","k4","kerning","keypoints","keysplines","keytimes","lang","lengthadjust","letter-spacing","kernelmatrix","kernelunitlength","lighting-color","local","marker-end","marker-mid","marker-start","markerheight","markerunits","markerwidth","maskcontentunits","maskunits","max","mask","media","method","mode","min","name","numoctaves","offset","operator","opacity","order","orient","orientation","origin","overflow","paint-order","path","pathlength","patterncontentunits","patterntransform","patternunits","points","preservealpha","preserveaspectratio","primitiveunits","r","rx","ry","radius","refx","refy","repeatcount","repeatdur","restart","result","rotate","scale","seed","shape-rendering","specularconstant","specularexponent","spreadmethod","startoffset","stddeviation","stitchtiles","stop-color","stop-opacity","stroke-dasharray","stroke-dashoffset","stroke-linecap","stroke-linejoin","stroke-miterlimit","stroke-opacity","stroke","stroke-width","style","surfacescale","systemlanguage","tabindex","targetx","targety","transform","transform-origin","text-anchor","text-decoration","text-rendering","textlength","type","u1","u2","unicode","values","viewbox","visibility","version","vert-adv-y","vert-origin-x","vert-origin-y","width","word-spacing","wrap","writing-mode","xchannelselector","ychannelselector","x","x1","x2","xmlns","y","y1","y2","z","zoomandpan"]),U=a(["accent","accentunder","align","bevelled","close","columnsalign","columnlines","columnspan","denomalign","depth","dir","display","displaystyle","encoding","fence","frame","height","href","id","largeop","length","linethickness","lspace","lquote","mathbackground","mathcolor","mathsize","mathvariant","maxsize","minsize","movablelimits","notation","numalign","open","rowalign","rowlines","rowspacing","rowspan","rspace","rquote","scriptlevel","scriptminsize","scriptsizemultiplier","selection","separator","separators","stretchy","subscriptshift","supscriptshift","symmetric","voffset","width","xmlns"]),H=a(["xlink:href","xml:id","xlink:title","xml:space","xmlns:xlink"]),F=i(/\{\{[\w\W]*|[\w\W]*\}\}/gm),z=i(/<%[\w\W]*|[\w\W]*%>/gm),B=i(/\${[\w\W]*}/gm),W=i(/^data-[\-\w.\u00B7-\uFFFF]/),G=i(/^aria-[\-\w]+$/),Y=i(/^(?:(?:(?:f|ht)tps?|mailto|tel|callto|sms|cid|xmpp):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i),j=i(/^(?:\w+script|data):/i),q=i(/[\u0000-\u0020\u00A0\u1680\u180E\u2000-\u2029\u205F\u3000]/g),X=i(/^html$/i),$=i(/^[a-z][.\w]*(-[.\w]+)+$/i);var K=Object.freeze({__proto__:null,MUSTACHE_EXPR:F,ERB_EXPR:z,TMPLIT_EXPR:B,DATA_ATTR:W,ARIA_ATTR:G,IS_ALLOWED_URI:Y,IS_SCRIPT_OR_DATA:j,ATTR_WHITESPACE:q,DOCTYPE_NAME:X,CUSTOM_ELEMENT:$});const V=function(){return"undefined"==typeof window?null:window};return function t(){let n=arguments.length>0&&void 0!==arguments[0]?arguments[0]:V();const o=e=>t(e);if(o.version="3.1.0",o.removed=[],!n||!n.document||9!==n.document.nodeType)return o.isSupported=!1,o;let{document:r}=n;const i=r,c=i.currentScript,{DocumentFragment:s,HTMLTemplateElement:N,Node:b,Element:w,NodeFilter:F,NamedNodeMap:z=n.NamedNodeMap||n.MozNamedAttrMap,HTMLFormElement:B,DOMParser:W,trustedTypes:G}=n,j=w.prototype,q=v(j,"cloneNode"),$=v(j,"nextSibling"),Z=v(j,"childNodes"),J=v(j,"parentNode");if("function"==typeof N){const e=r.createElement("template");e.content&&e.content.ownerDocument&&(r=e.content.ownerDocument)}let Q,ee="";const{implementation:te,createNodeIterator:ne,createDocumentFragment:oe,getElementsByTagName:re}=r,{importNode:ae}=i;let ie={};o.isSupported="function"==typeof e&&"function"==typeof J&&te&&void 0!==te.createHTMLDocument;const{MUSTACHE_EXPR:le,ERB_EXPR:ce,TMPLIT_EXPR:se,DATA_ATTR:ue,ARIA_ATTR:me,IS_SCRIPT_OR_DATA:pe,ATTR_WHITESPACE:fe,CUSTOM_ELEMENT:de}=K;let{IS_ALLOWED_URI:he}=K,ge=null;const ye=S({},[...L,...x,...C,...O,...M]);let Te=null;const Ee=S({},[...I,...P,...U,...H]);let _e=Object.seal(l(null,{tagNameCheck:{writable:!0,configurable:!1,enumerable:!0,value:null},attributeNameCheck:{writable:!0,configurable:!1,enumerable:!0,value:null},allowCustomizedBuiltInElements:{writable:!0,configurable:!1,enumerable:!0,value:!1}})),Ae=null,Ne=null,be=!0,Se=!0,we=!1,Re=!0,ve=!1,Le=!0,xe=!1,Ce=!1,De=!1,Oe=!1,ke=!1,Me=!1,Ie=!0,Pe=!1,Ue=!0,He=!1,Fe={},ze=null;const Be=S({},["annotation-xml","audio","colgroup","desc","foreignobject","head","iframe","math","mi","mn","mo","ms","mtext","noembed","noframes","noscript","plaintext","script","style","svg","template","thead","title","video","xmp"]);let We=null;const Ge=S({},["audio","video","img","source","image","track"]);let Ye=null;const je=S({},["alt","class","for","id","label","name","pattern","placeholder","role","summary","title","value","style","xmlns"]),qe="http://www.w3.org/1998/Math/MathML",Xe="http://www.w3.org/2000/svg",$e="http://www.w3.org/1999/xhtml";let Ke=$e,Ve=!1,Ze=null;const Je=S({},[qe,Xe,$e],d);let Qe=null;const et=["application/xhtml+xml","text/html"];let tt=null,nt=null;const ot=r.createElement("form"),rt=function(e){return e instanceof RegExp||e instanceof Function},at=function(){let e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};if(!nt||nt!==e){if(e&&"object"==typeof e||(e={}),e=R(e),Qe=-1===et.indexOf(e.PARSER_MEDIA_TYPE)?"text/html":e.PARSER_MEDIA_TYPE,tt="application/xhtml+xml"===Qe?d:f,ge=E(e,"ALLOWED_TAGS")?S({},e.ALLOWED_TAGS,tt):ye,Te=E(e,"ALLOWED_ATTR")?S({},e.ALLOWED_ATTR,tt):Ee,Ze=E(e,"ALLOWED_NAMESPACES")?S({},e.ALLOWED_NAMESPACES,d):Je,Ye=E(e,"ADD_URI_SAFE_ATTR")?S(R(je),e.ADD_URI_SAFE_ATTR,tt):je,We=E(e,"ADD_DATA_URI_TAGS")?S(R(Ge),e.ADD_DATA_URI_TAGS,tt):Ge,ze=E(e,"FORBID_CONTENTS")?S({},e.FORBID_CONTENTS,tt):Be,Ae=E(e,"FORBID_TAGS")?S({},e.FORBID_TAGS,tt):{},Ne=E(e,"FORBID_ATTR")?S({},e.FORBID_ATTR,tt):{},Fe=!!E(e,"USE_PROFILES")&&e.USE_PROFILES,be=!1!==e.ALLOW_ARIA_ATTR,Se=!1!==e.ALLOW_DATA_ATTR,we=e.ALLOW_UNKNOWN_PROTOCOLS||!1,Re=!1!==e.ALLOW_SELF_CLOSE_IN_ATTR,ve=e.SAFE_FOR_TEMPLATES||!1,Le=!1!==e.SAFE_FOR_XML,xe=e.WHOLE_DOCUMENT||!1,Oe=e.RETURN_DOM||!1,ke=e.RETURN_DOM_FRAGMENT||!1,Me=e.RETURN_TRUSTED_TYPE||!1,De=e.FORCE_BODY||!1,Ie=!1!==e.SANITIZE_DOM,Pe=e.SANITIZE_NAMED_PROPS||!1,Ue=!1!==e.KEEP_CONTENT,He=e.IN_PLACE||!1,he=e.ALLOWED_URI_REGEXP||Y,Ke=e.NAMESPACE||$e,_e=e.CUSTOM_ELEMENT_HANDLING||{},e.CUSTOM_ELEMENT_HANDLING&&rt(e.CUSTOM_ELEMENT_HANDLING.tagNameCheck)&&(_e.tagNameCheck=e.CUSTOM_ELEMENT_HANDLING.tagNameCheck),e.CUSTOM_ELEMENT_HANDLING&&rt(e.CUSTOM_ELEMENT_HANDLING.attributeNameCheck)&&(_e.attributeNameCheck=e.CUSTOM_ELEMENT_HANDLING.attributeNameCheck),e.CUSTOM_ELEMENT_HANDLING&&"boolean"==typeof e.CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements&&(_e.allowCustomizedBuiltInElements=e.CUSTOM_ELEMENT_HANDLING.allowCustomizedBuiltInElements),ve&&(Se=!1),ke&&(Oe=!0),Fe&&(ge=S({},M),Te=[],!0===Fe.html&&(S(ge,L),S(Te,I)),!0===Fe.svg&&(S(ge,x),S(Te,P),S(Te,H)),!0===Fe.svgFilters&&(S(ge,C),S(Te,P),S(Te,H)),!0===Fe.mathMl&&(S(ge,O),S(Te,U),S(Te,H))),e.ADD_TAGS&&(ge===ye&&(ge=R(ge)),S(ge,e.ADD_TAGS,tt)),e.ADD_ATTR&&(Te===Ee&&(Te=R(Te)),S(Te,e.ADD_ATTR,tt)),e.ADD_URI_SAFE_ATTR&&S(Ye,e.ADD_URI_SAFE_ATTR,tt),e.FORBID_CONTENTS&&(ze===Be&&(ze=R(ze)),S(ze,e.FORBID_CONTENTS,tt)),Ue&&(ge["#text"]=!0),xe&&S(ge,["html","head","body"]),ge.table&&(S(ge,["tbody"]),delete Ae.tbody),e.TRUSTED_TYPES_POLICY){if("function"!=typeof e.TRUSTED_TYPES_POLICY.createHTML)throw A('TRUSTED_TYPES_POLICY configuration option must provide a "createHTML" hook.');if("function"!=typeof e.TRUSTED_TYPES_POLICY.createScriptURL)throw A('TRUSTED_TYPES_POLICY configuration option must provide a "createScriptURL" hook.');Q=e.TRUSTED_TYPES_POLICY,ee=Q.createHTML("")}else void 0===Q&&(Q=function(e,t){if("object"!=typeof e||"function"!=typeof e.createPolicy)return null;let n=null;const o="data-tt-policy-suffix";t&&t.hasAttribute(o)&&(n=t.getAttribute(o));const r="dompurify"+(n?"#"+n:"");try{return e.createPolicy(r,{createHTML:e=>e,createScriptURL:e=>e})}catch(e){return console.warn("TrustedTypes policy "+r+" could not be created."),null}}(G,c)),null!==Q&&"string"==typeof ee&&(ee=Q.createHTML(""));a&&a(e),nt=e}},it=S({},["mi","mo","mn","ms","mtext"]),lt=S({},["foreignobject","desc","title","annotation-xml"]),ct=S({},["title","style","font","a","script"]),st=S({},[...x,...C,...D]),ut=S({},[...O,...k]),mt=function(e){p(o.removed,{element:e});try{e.parentNode.removeChild(e)}catch(t){e.remove()}},pt=function(e,t){try{p(o.removed,{attribute:t.getAttributeNode(e),from:t})}catch(e){p(o.removed,{attribute:null,from:t})}if(t.removeAttribute(e),"is"===e&&!Te[e])if(Oe||ke)try{mt(t)}catch(e){}else try{t.setAttribute(e,"")}catch(e){}},ft=function(e){let t=null,n=null;if(De)e="<remove></remove>"+e;else{const t=h(e,/^[\r\n\t ]+/);n=t&&t[0]}"application/xhtml+xml"===Qe&&Ke===$e&&(e='<html xmlns="http://www.w3.org/1999/xhtml"><head></head><body>'+e+"</body></html>");const o=Q?Q.createHTML(e):e;if(Ke===$e)try{t=(new W).parseFromString(o,Qe)}catch(e){}if(!t||!t.documentElement){t=te.createDocument(Ke,"template",null);try{t.documentElement.innerHTML=Ve?ee:o}catch(e){}}const a=t.body||t.documentElement;return e&&n&&a.insertBefore(r.createTextNode(n),a.childNodes[0]||null),Ke===$e?re.call(t,xe?"html":"body")[0]:xe?t.documentElement:a},dt=function(e){return ne.call(e.ownerDocument||e,e,F.SHOW_ELEMENT|F.SHOW_COMMENT|F.SHOW_TEXT|F.SHOW_PROCESSING_INSTRUCTION|F.SHOW_CDATA_SECTION,null)},ht=function(e){return"function"==typeof b&&e instanceof b},gt=function(e,t,n){ie[e]&&u(ie[e],(e=>{e.call(o,t,n,nt)}))},yt=function(e){let t=null;if(gt("beforeSanitizeElements",e,null),(n=e)instanceof B&&("string"!=typeof n.nodeName||"string"!=typeof n.textContent||"function"!=typeof n.removeChild||!(n.attributes instanceof z)||"function"!=typeof n.removeAttribute||"function"!=typeof n.setAttribute||"string"!=typeof n.namespaceURI||"function"!=typeof n.insertBefore||"function"!=typeof n.hasChildNodes))return mt(e),!0;var n;const r=tt(e.nodeName);if(gt("uponSanitizeElement",e,{tagName:r,allowedTags:ge}),e.hasChildNodes()&&!ht(e.firstElementChild)&&_(/<[/\w]/g,e.innerHTML)&&_(/<[/\w]/g,e.textContent))return mt(e),!0;if(7===e.nodeType)return mt(e),!0;if(Le&&8===e.nodeType&&_(/<[/\w]/g,e.data))return mt(e),!0;if(!ge[r]||Ae[r]){if(!Ae[r]&&Et(r)){if(_e.tagNameCheck instanceof RegExp&&_(_e.tagNameCheck,r))return!1;if(_e.tagNameCheck instanceof Function&&_e.tagNameCheck(r))return!1}if(Ue&&!ze[r]){const t=J(e)||e.parentNode,n=Z(e)||e.childNodes;if(n&&t)for(let o=n.length-1;o>=0;--o)t.insertBefore(q(n[o],!0),$(e))}return mt(e),!0}return e instanceof w&&!function(e){let t=J(e);t&&t.tagName||(t={namespaceURI:Ke,tagName:"template"});const n=f(e.tagName),o=f(t.tagName);return!!Ze[e.namespaceURI]&&(e.namespaceURI===Xe?t.namespaceURI===$e?"svg"===n:t.namespaceURI===qe?"svg"===n&&("annotation-xml"===o||it[o]):Boolean(st[n]):e.namespaceURI===qe?t.namespaceURI===$e?"math"===n:t.namespaceURI===Xe?"math"===n&&lt[o]:Boolean(ut[n]):e.namespaceURI===$e?!(t.namespaceURI===Xe&&!lt[o])&&!(t.namespaceURI===qe&&!it[o])&&!ut[n]&&(ct[n]||!st[n]):!("application/xhtml+xml"!==Qe||!Ze[e.namespaceURI]))}(e)?(mt(e),!0):"noscript"!==r&&"noembed"!==r&&"noframes"!==r||!_(/<\/no(script|embed|frames)/i,e.innerHTML)?(ve&&3===e.nodeType&&(t=e.textContent,u([le,ce,se],(e=>{t=g(t,e," ")})),e.textContent!==t&&(p(o.removed,{element:e.cloneNode()}),e.textContent=t)),gt("afterSanitizeElements",e,null),!1):(mt(e),!0)},Tt=function(e,t,n){if(Ie&&("id"===t||"name"===t)&&(n in r||n in ot))return!1;if(Se&&!Ne[t]&&_(ue,t));else if(be&&_(me,t));else if(!Te[t]||Ne[t]){if(!(Et(e)&&(_e.tagNameCheck instanceof RegExp&&_(_e.tagNameCheck,e)||_e.tagNameCheck instanceof Function&&_e.tagNameCheck(e))&&(_e.attributeNameCheck instanceof RegExp&&_(_e.attributeNameCheck,t)||_e.attributeNameCheck instanceof Function&&_e.attributeNameCheck(t))||"is"===t&&_e.allowCustomizedBuiltInElements&&(_e.tagNameCheck instanceof RegExp&&_(_e.tagNameCheck,n)||_e.tagNameCheck instanceof Function&&_e.tagNameCheck(n))))return!1}else if(Ye[t]);else if(_(he,g(n,fe,"")));else if("src"!==t&&"xlink:href"!==t&&"href"!==t||"script"===e||0!==y(n,"data:")||!We[e])if(we&&!_(pe,g(n,fe,"")));else if(n)return!1;return!0},Et=function(e){return"annotation-xml"!==e&&h(e,de)},_t=function(e){gt("beforeSanitizeAttributes",e,null);const{attributes:t}=e;if(!t)return;const n={attrName:"",attrValue:"",keepAttr:!0,allowedAttributes:Te};let r=t.length;for(;r--;){const a=t[r],{name:i,namespaceURI:l,value:c}=a,s=tt(i);let p="value"===i?c:T(c);if(n.attrName=s,n.attrValue=p,n.keepAttr=!0,n.forceKeepAttr=void 0,gt("uponSanitizeAttribute",e,n),p=n.attrValue,n.forceKeepAttr)continue;if(pt(i,e),!n.keepAttr)continue;if(!Re&&_(/\/>/i,p)){pt(i,e);continue}ve&&u([le,ce,se],(e=>{p=g(p,e," ")}));const f=tt(e.nodeName);if(Tt(f,s,p)){if(!Pe||"id"!==s&&"name"!==s||(pt(i,e),p="user-content-"+p),Q&&"object"==typeof G&&"function"==typeof G.getAttributeType)if(l);else switch(G.getAttributeType(f,s)){case"TrustedHTML":p=Q.createHTML(p);break;case"TrustedScriptURL":p=Q.createScriptURL(p)}try{l?e.setAttributeNS(l,i,p):e.setAttribute(i,p),m(o.removed)}catch(e){}}}gt("afterSanitizeAttributes",e,null)},At=function e(t){let n=null;const o=dt(t);for(gt("beforeSanitizeShadowDOM",t,null);n=o.nextNode();)gt("uponSanitizeShadowNode",n,null),yt(n)||(n.content instanceof s&&e(n.content),_t(n));gt("afterSanitizeShadowDOM",t,null)};return o.sanitize=function(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=null,r=null,a=null,l=null;if(Ve=!e,Ve&&(e="\x3c!--\x3e"),"string"!=typeof e&&!ht(e)){if("function"!=typeof e.toString)throw A("toString is not a function");if("string"!=typeof(e=e.toString()))throw A("dirty is not a string, aborting")}if(!o.isSupported)return e;if(Ce||at(t),o.removed=[],"string"==typeof e&&(He=!1),He){if(e.nodeName){const t=tt(e.nodeName);if(!ge[t]||Ae[t])throw A("root node is forbidden and cannot be sanitized in-place")}}else if(e instanceof b)n=ft("\x3c!----\x3e"),r=n.ownerDocument.importNode(e,!0),1===r.nodeType&&"BODY"===r.nodeName||"HTML"===r.nodeName?n=r:n.appendChild(r);else{if(!Oe&&!ve&&!xe&&-1===e.indexOf("<"))return Q&&Me?Q.createHTML(e):e;if(n=ft(e),!n)return Oe?null:Me?ee:""}n&&De&&mt(n.firstChild);const c=dt(He?e:n);for(;a=c.nextNode();)yt(a)||(a.content instanceof s&&At(a.content),_t(a));if(He)return e;if(Oe){if(ke)for(l=oe.call(n.ownerDocument);n.firstChild;)l.appendChild(n.firstChild);else l=n;return(Te.shadowroot||Te.shadowrootmode)&&(l=ae.call(i,l,!0)),l}let m=xe?n.outerHTML:n.innerHTML;return xe&&ge["!doctype"]&&n.ownerDocument&&n.ownerDocument.doctype&&n.ownerDocument.doctype.name&&_(X,n.ownerDocument.doctype.name)&&(m="<!DOCTYPE "+n.ownerDocument.doctype.name+">\n"+m),ve&&u([le,ce,se],(e=>{m=g(m,e," ")})),Q&&Me?Q.createHTML(m):m},o.setConfig=function(){at(arguments.length>0&&void 0!==arguments[0]?arguments[0]:{}),Ce=!0},o.clearConfig=function(){nt=null,Ce=!1},o.isValidAttribute=function(e,t,n){nt||at({});const o=tt(e),r=tt(t);return Tt(o,r,n)},o.addHook=function(e,t){"function"==typeof t&&(ie[e]=ie[e]||[],p(ie[e],t))},o.removeHook=function(e){if(ie[e])return m(ie[e])},o.removeHooks=function(e){ie[e]&&(ie[e]=[])},o.removeAllHooks=function(){ie={}},o}()}()}},t={};function n(o){var r=t[o];if(void 0!==r)return r.exports;var a=t[o]={exports:{}};return e[o].call(a.exports,a,a.exports,n),a.exports}n.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return n.d(t,{a:t}),t},n.d=(e,t)=>{for(var o in t)n.o(t,o)&&!n.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},n.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{"use strict";const e=window.React,t=window.wp.htmlEntities;var o=n(838),r=n.n(o);const{registerPaymentMethod:a}=window.wc.wcBlocksRegistry,{getSetting:i}=window.wc.wcSettings,l=i("mycryptocheckout_data",{}),c=(0,t.decodeEntities)(l.title),s=n=>{const{eventRegistration:o}=n,{onPaymentSetup:a}=o,[i,c]=(0,e.useState)(""),s=(0,e.useRef)(null);return(0,e.useEffect)((()=>{const e=a((()=>i?{type:"success",meta:{paymentMethodData:{selectedCurrency:i}}}:{type:"error",message:"Please select a currency."}));return()=>e()}),[i,a]),(0,e.useEffect)((()=>{if(s.current){const e=s.current.querySelector("select#mcc_currency_id");e&&e.addEventListener("change",(e=>{c(e.target.value)}))}}),[]),(0,e.createElement)("div",{ref:s,dangerouslySetInnerHTML:{__html:r().sanitize((0,t.decodeEntities)(l.payment_fields))}})};a({name:"mycryptocheckout",label:(0,e.createElement)((t=>{const{PaymentMethodLabel:n}=t.components;return(0,e.createElement)(n,{text:c})}),null),content:(0,e.createElement)(s,null),edit:(0,e.createElement)(s,null),canMakePayment:()=>!0,ariaLabel:c,supports:{features:l.supports}})})()})();