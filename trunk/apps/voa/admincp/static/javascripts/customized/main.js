// 模块路径
paths = {
	    //jquery: window._jsdir+'/jquery-1.11.1.min',
	    jqueryui: window._jsdir+'/customized/lib/jqueryui/jquery-ui.min',
	    //"jquery.ui.widget": 'lib/jqueryui/jquery-ui.min',
	    ueditor: '/misc/ueditor/ueditor.all.min',
	    ueditor_config: '/misc/ueditor/ueditor.config',
	    //ueditor_lang: '/misc/ueditor/lang/zh-cn/zh-cn',
	    underscore: window._jsdir+'/customized/lib/underscore/underscore',
	    text: window._jsdir+'/customized/lib/requirejs-text/text',
		css: window._jsdir + "/customized/lib/require-css/css.min",
	    require_router: window._jsdir+'/customized/lib/requirejs-router/router', 
	    datetimepicker: window._jsdir+'/customized/lib/jquery-datetimepicker/jquery.datetimepicker',
	    upload: window._jsdir+'/jquery.fileupload',
	    //bootstrap: window._jsdir+'bootstrap.min',
	    "jquery.ui.widget": window._jsdir+'/jquery.ui.widget',
	    "jquery.iframe-transport": window._jsdir+'/jquery.iframe-transport',
		"jquery.fileupload": window._jsdir+'/customized/3rdparty/jquery-upload/jquery.fileupload',
		"jquery.fileupload-validate": window._jsdir+'/customized/3rdparty/jquery-upload/jquery.fileupload-validate',
		"jquery.fileupload-process": window._jsdir+'/customized/3rdparty/jquery-upload/jquery.fileupload-process',

	    //"jquery-resizableColumns": "3rdparty/jquery-resizableColumns/jquery.resizableColumns",
	    "jquery-bootgrid": window._jsdir+'/customized/3rdparty/jquery-bootgrid/jquery.bootgrid',

		"jquery-lazyload": window._jsdir + '/customized/lib/lazyloadxt/jquery.lazyloadxt',
		"jquery-lazyload-autoload": window._jsdir + '/customized/lib/lazyloadxt/jquery.lazyloadxt.autoload.min',

	    col_resizable: window._jsdir+'/customized/3rdparty/colResizable-1.3.source',
	    scrollto: window._jsdir+'/customized/3rdparty/jquery-scrollto',
	    utils: window._jsdir+'/customized/utils',
	    "widgets": window._jsdir+'/customized/widgets',
		"data": window._jsdir+'/customized/data'
	  };
// 如果没有加载jquery 刚定义
if (!window.jQuery) {
    paths.jquery = window._jsdir+'/jquery-1.11.1.min';
    paths.bootstrap = window._jsdir+'bootstrap.min';
} else {
    //REGISTER THE CURRENT JQUERY
    define('jquery', [], function () { return window.jQuery; });
    
    // 如果bootstrap已经载入则定议空的 bootstrap，避免重复载入二次dropdown不工作
    if ($.support.transition) {
    	define('bootstrap', [], function () { return '' });
    } else {// 如果没载入刚定义bootstrap的模块路径
    	paths.bootstrap = window._jsdir+'bootstrap.min';
    }
}
// requirejs 配置注册
requirejs.config({
	baseUrl: window._jsdir+'customized/app/'+window._appname,
	//enforceDefine: true,
	//urlArgs: "bust=" + (new Date()).getTime(),
	waitSeconds: 200,
	paths: paths,
	shim: {
		jquery: { exports : '$'},
		underscore: { exports : '_'},
		ueditor: {deps: ["ueditor_config"]},
		"jquery-lazyload-autoload": {deps: ["jquery-lazyload"]},
	  	"jquery.fileupload-process": {exports : 'jquery.fileupload-process'},
	  	"jquery.fileupload": {exports : 'jquery.fileupload'},
	  	"jquery.fileupload-validate": {exports : 'jquery.fileupload-validate',
			deps:  ["jquery.ui.widget", "jquery.iframe-transport"]}
	}
});

requirejs(['router', "jquery"], function(router, $) {
	$(function() {
		router.init();
	})
});
