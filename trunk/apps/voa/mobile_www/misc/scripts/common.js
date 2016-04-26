/**
 * By Deepseath@gmail.com
 */
var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser','rv':'rv'});
if(BROWSER.safari) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;
function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s|:)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		}else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function ddecimal(num,v){
	return num.toFixed(v);
}

function showSearchInput(){
	document.write('<input autocomplete="off" autocorrect="off" onwebkitspeechchange="this.form.submit();" x-webkit-grammar="builtin:search" x-webkit-speech="true" name="q" tabindex="1" value="" lang="zh-CN" />');
}

/**
 * 剔除 javascript 脚本
 * @param	s	字串；
 */
String.prototype.stripscript = function(s) {
	return s.replace(/<script.*?>.*?<\/script>/ig, '');
};

/**
 * 计算字串的长度
 * @return	integer
 */
String.prototype.rlength = function() {
	return (is_ie && this.indexOf('\n') != -1) ? this.replace(/\r?\n/g, '_').length : this.length;
};

/**
 * 去除左边空格
 * @return	string
 */
String.prototype.ltrim = function() {
	return this.replace(/^\s+/g, '');
};

/**
 * 去除右边空格
 * @return	string
 */
String.prototype.rtrim = function() {
	return this.replace(/(\s+)$/g, '');
};

/**
 * 去除两边的空格
 * @return	string
 */
String.prototype.trim = function() {
	return this.ltrim(this.rtrim());
};

/**
 * 加入收藏夹
 */
function addFavorite(url, title) {
	try {
		window.external.addFavorite(url, title);
	} catch (e){
		try {
			window.sidebar.addPanel(title, url, '');
        	} catch (e) {
			alert("请按 Ctrl+D 键添加到收藏夹");
		}
	}
}

/**
 * 设为首页
 */
function setHomepage(sURL) {
	if ( BROWSER.ie ) {
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(sURL);
	} else {
		alert("非 IE 浏览器请手动将本站设为首页");
	}
}

/**
 * 复制内容到剪切板
 */
function copyToClipboard(maintext,copyOverMsg,noSupportMsg){
	if ( window.clipboardData ){
		window.clipboardData.setData("Text", maintext);
	} else if ( window.netscape ) {
		try{
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		}catch(e){
			alert(typeof(noSupportMsg) == 'undefined' ? "非常抱歉，您的浏览器不支持复制功能，请按键 Ctrl + C 复制。" : noSupportMsg);
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip) return;
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans) return;
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext=maintext;
		str.data=copytext;
		trans.setTransferData("text/unicode",str,copytext.length*2);
		var clipid=Components.interfaces.nsIClipboard;
		if (!clip) return false;
		clip.setData(trans,null,clipid.kGlobalClipboard);
	}
	alert(typeof(copyOverMsg) == 'undefined' ? "以下内容已经复制完毕\n" + maintext : copyOverMsg);
}

function randomSuffixUrl(url){
	return url+(url.indexOf('?') >= 0 ? '&' : '?')+'__='+Math.random();
}

/**
 * Urlencode
 */
function UrlEncode(str){
	var ret="";
	var strSpecial="!\"#$%&'()*+,/:;<=>?[]^`{|}~%";
	for ( var i=0;i<str.length;i++ ) {
		var chr	=	str.charAt(i);
		var c	=	str2asc(chr);
		if ( parseInt("0x"+c) > 0x7f ) {
			ret	+=	"%"+c.slice(0,2)+"%"+c.slice(-2);
		} else {
			if ( chr == " ") {
				ret	+=	"+";
			} else if ( strSpecial.indexOf(chr) != -1 ) {
				ret	+=	"%"+c.toString(16);
			} else {
				ret	+=	chr;
			}
		}
	}
	return ret;
}

/**
 * 
 */
function str2asc(ch){
	var Strob=new String(ch);
	var sstr="";
	for ( var i=0;i<Strob.length;i++ ) {
		sstr	+=	new String(Strob.substr(i,1)).charCodeAt(0).toString(16);
	}
	return sstr;
}

/**
 * 固定对象在某个位置
 * @param fixBoxId 待固定位置的元素id
 * @param mainBoxId 主体内容区的元素id
 */
function fixBox(fixBoxId,mainBoxId){
	var	t	=	jQuery(fixBoxId).offset().top;
	var	mh	=	jQuery(mainBoxId).height();
	var	fh	=	jQuery(fixBoxId).height();
	var	width	=	jQuery(fixBoxId).parent().innerWidth();
	jQuery(window).scroll(function(e){
		var	s = jQuery(document).scrollTop();
		if ( s > t - 10 ){
			jQuery(fixBoxId).css({'position':'fixed','width':width+'px'});
			if(s + fh > mh){
				jQuery(fixBoxId).css({'top':mh-s-fh+'px','width':width+'px'});
			}
		} else {
			jQuery(fixBoxId).css('position','');
		}
	});
}

function pageUrl(mpurl,page){
	return mpurl.replace('__pagenum__',page);
}

function multi(num, perpage, curpage, mpurl, maxpages) {
	//alert('Num='+num+';perpage='+perpage+';curpage='+curpage+';mpurl='+mpurl+';maxpages'+maxpages);
	num		=	Number(num);
	perpage	=	Number(perpage);
	curpage	=	Number(curpage);
	maxpages=	Number(maxpages);
	var	page		=	10;
	var	dot			=	'...';
	var	multipage	=	'';
	var	realpages	=	1;
	var	pageNext	=	0;
	page	-=	Number( (curpage.toString()).length ) - 1;
	if ( page <= 0 ) {
		page	=	1;
	}
	var	offset,from,to;
	if ( num > perpage ) {
		offset	=	Math.floor(page * 0.5);
		realpages	=	Math.ceil(num / perpage);
		curpage		=	curpage > realpages ? realpages : curpage;
		//alert(num+'/'+perpage);
		pages		=	maxpages && maxpages < realpages ? maxpages : realpages;
		if ( page > pages ) {
			from	=	1;
			to		=	pages;
		} else {
			from	=	curpage - offset;
			to		=	from + page - 1;
			if ( from < 1 ) {
				to	=	curpage + 1 - from;
				from=	1;
				if ( to - from < page ) {
					to	=	page;
				}
			} else if( to > pages ) {
				from	=	pages - page + 1;
				to		=	pages;
			}
		}
		pageNext	=	to;
		multipage	=	(curpage - offset > 1 && pages > page ? '<li><a href="'+pageUrl(mpurl,1)+'" class="first">1 '+dot+'</a></li>' : '') + ( curpage > 1 ? '<li><a href="'+pageUrl(mpurl,curpage - 1)+'">&laquo;</a></li>' : '');
		for ( var i = from; i <= to; i++ ) {
			multipage	+=	i == curpage ? '<li class="active"><span>'+i+' <span class="sr-only">(current)</span></span></li>' : '<li><a href="'+pageUrl(mpurl,i)+'">'+i+'</a></li>';
		}
		multipage	+=	(to < pages ? '<li><a href="'+pageUrl(mpurl,pages)+'">'+dot+' '+realpages+'</a></li>' : '') + (curpage < pages ? '<li><a href="'+pageUrl(mpurl,curpage + 1)+'">&raquo;</a></li>' : '');
		multipage	=	multipage ? '<ul class="pagination">'+multipage+'</ul>' : '';
	}
	return multipage;
}