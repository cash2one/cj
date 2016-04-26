var app = window._app;
var root = window._root;
// 模块路径
paths = {
	    //jquery: window._jsdir+'/jquery-1.11.1.min',
	    jqueryui: root+'/lib/jqueryui/jquery-ui.min',
	    "jquery.ui.widget": root+'/lib/jqueryui/jquery.ui.widget',
	    "jquery.upload": root+'/lib/jquery/jquery.fileupload',
	    "jquery.iframe-transport": root+'/lib/jquery/jquery.iframe-transport',
	    //ueditor_lang: '/misc/ueditor/lang/zh-cn/zh-cn',
	    underscore: root+'/lib/underscore/underscore',
	    text: root+"/lib/requirejs-text/text", 
	    css: root+"/lib/require-css/css.min", 
	    require_router: root+"/lib/requirejs-router/router", 
	    datetimepicker: root+'/lib/jquery-datetimepicker/jquery.datetimepicker',
	    "jquery-mobile": root+'/lib/jquery-mobile/jquery.mobile-1.4.5.min',
	    //"jquery-mobile-router": "lib/jquery-mobile/jquery.mobile.router.min",
	    //bootstrap: window._jsdir+'bootstrap.min',
	    "jquery-bootgrid": root+'/3rdparty/jquery-bootgrid/jquery.bootgrid',
	    //"jquery-photoswipe": root+'/3rdparty/jquery-mobile-photoswipe/code-photoswipe-jQuery-1.0.11',
	    //"simple-inheritance": root+'/3rdparty/jquery-mobile-photoswipe/simple-inheritance.min',
	    "jtree": root+'/3rdparty/jtree/jstree',
	    "swipebox": root+'/3rdparty/swipebox/js/jquery.swipebox.min',
	    "iscrollview": root+'/3rdparty/iscroll/jquery.mobile.iscrollview',
	    "iscroll": root+'/3rdparty/iscroll/iscroll',
	    "utils": root+'/utils',
	    "widgets": root+'/widgets',
	    "data": root+'/data',
	  };
// 如果没有加载jquery 刚定义
if (!window.jQuery) {
    paths.jquery = root+'/lib/jquery/jquery.min';
    paths.bootstrap = root+'/lib/bootstrap/bootstrap.min';
} else {
    //REGISTER THE CURRENT JQUERY
    define('jquery', [], function () { return window.jQuery; });
    
    // 如果bootstrap已经载入则定义空的 bootstrap，避免重复载入二次dropdown不工作
    if ($.support.transition) {
    	define('bootstrap', [], function () { return '' });
    } else {// 如果没载入刚定义bootstrap的模块路径
    	paths.bootstrap = root+'/lib/bootstrap/bootstrap.min';
    }
}
// requirejs 配置注册
// Sets the require.js configuration for your application.

requirejs.config({
  baseUrl: root+'/app/'+app,
  //enforceDefine: true,
  //urlArgs: "bust=" + (new Date()).getTime(),
  waitSeconds: 200,
  // 3rd party script alias names
  paths: paths,
  // Sets the configuration for your third party scripts that are not AMD compatible
  shim: {
  	jquery: { exports : '$'},
  	underscore: { exports : '_'},
  	jqueryui: { deps : ['css!'+root+'/lib/jqueryui/css/jquery-ui.min.css']},
  	"jquery-mobile": { deps : ['css!'+root+'/lib/jquery-mobile/jquery.mobile-1.4.5.min.css']},
  	datetimepicker: { deps: ['css!'+root+'/lib/jquery-datetimepicker/jquery.datetimepicker.css']},
  	"jquery-bootgrid": {deps: ['css!'+root+'/3rdparty/jquery-bootgrid/jquery.bootgrid.css']},
  	//"jquery-photoswipe": {deps: ["simple-inheritance", 'css!'+root+'/3rdparty/jquery-mobile-photoswipe/photoswipe.css']},
  	"swipebox": {deps: ['css!'+root+'/3rdparty/swipebox/css/swipebox.min.css']},
  	"jtree": {deps: ['css!'+root+'/3rdparty/jtree/themes/default/style.min.css']},
  	"iscrollview": {deps: ['iscroll', 'css!'+root+'/3rdparty/iscroll/jquery.mobile.iscrollview.css', 'css!'+root+'/3rdparty/iscroll/jquery.mobile.iscrollview-pull.css']},

  },
});

// Includes File Dependencies

requirejs(['router', "jquery"], function(router, $) {
	if (window.mobile) {		
		$( document ).on( "mobileinit",
			// Set up the "mobileinit" handler before requiring jQuery Mobile's module
			function () {
				// Prevents all anchor click handling including the addition of active button state and alternate link bluring.
				$.mobile.linkBindingEnabled = false;
	
				// Disabling this will prevent jQuery Mobile from handling hash changes
				$.mobile.hashListeningEnabled = false;
	
				//$.mobile.autoInitializePage = false;
				//$.mobile.ajaxEnabled = false;
				//$.mobile.pushStateEnabled = false;
			}
		)
		$(function() {
			require( [ "jquery-mobile" ], function () {
				// Initialize jqm page
				$.mobile.initializePage();
				// Instantiates a  Router
				// 路由初始化
	            router.init();
			});
			
		});
	} else {
		router.init();
	}
});
