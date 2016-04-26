var app = window._app;
var root = window._root;
// 模块路径
var paths = {
	//jquery: window._jsdir+'/jquery-1.11.1.min',
	jqueryui: root + '/lib/jqueryui/jquery-ui.min',
	"jquery.ui.widget": root + '/lib/jqueryui/jquery.ui.widget',
	"jquery.fileupload": root + '/3rdparty/jquery-upload/jquery.fileupload',
	"jquery.fileupload-validate": root + '/3rdparty/jquery-upload/jquery.fileupload-validate',
	"jquery.fileupload-process": root + '/3rdparty/jquery-upload/jquery.fileupload-process',
	"jquery.iframe-transport": root + '/lib/jquery/jquery.iframe-transport',
	//ueditor_lang: '/misc/ueditor/lang/zh-cn/zh-cn',
	underscore: root + '/lib/underscore/underscore',
	text: root + "/lib/requirejs-text/text",
	css: root + "/lib/require-css/css.min",
	require_router: root + "/lib/requirejs-router/router",
	datetimepicker: root + '/lib/jquery-datetimepicker/jquery.datetimepicker',
	"jquery-mobile": root + '/lib/jquery-mobile/jquery.mobile-1.4.5.min',
	//"jquery-mobile-router": "lib/jquery-mobile/jquery.mobile.router.min",
	//bootstrap: window._jsdir+'bootstrap.min',
	"jquery-carousel": root + '/3rdparty/carousel/jquery-carousel',
	"jquery-bootgrid": root + '/3rdparty/jquery-bootgrid/jquery.bootgrid',
	"jquery-lazyload": root + '/lib/lazyloadxt/jquery.lazyloadxt',
	"jquery-lazyload-autoload": root + '/lib/lazyloadxt/jquery.lazyloadxt.autoload.min',
	//"jquery-photoswipe": root + '/3rdparty/jquery-mobile-photoswipe/code-photoswipe-jQuery-1.0.11',
	//"simple-inheritance": root + '/3rdparty/jquery-mobile-photoswipe/simple-inheritance.min',
	"jtree": root + '/3rdparty/jtree/jstree',
	"swipebox": root + '/3rdparty/swipebox/js/jquery.swipebox.min',
	"iscrollview": root + '/3rdparty/iscroll/jquery.mobile.iscrollview',
	"iscroll": root + '/3rdparty/iscroll/iscroll',

	"mobile-datepicker": root + '/3rdparty/jquery-mobile-datepicker/js/jqm-datebox.core',
	"mobile-datepicker-css": root + '/3rdparty/jquery-mobile-datepicker/css/jqm-datebox',
	"mobile-datepicker-flipbox": root + '/3rdparty/jquery-mobile-datepicker/js/jqm-datebox.mode.flipbox',
	"mobile-datepicker-datebox": root + '/3rdparty/jquery-mobile-datepicker/js/jqm-datebox.mode.datebox',
	'mobile-datepicker-lang': root + '/3rdparty/jquery-mobile-datepicker/js/jqm-datebox.lang.utf8',

	"utils": root + '/utils',
	"widgets": root + '/widgets',
	"data": root + '/data'
};
// 如果没有加载jquery 刚定义
if (!window.jQuery) {
    paths.jquery = root + '/lib/jquery/jquery.min';
    paths.bootstrap = root + '/lib/bootstrap/js/bootstrap.min';
} else {
    //REGISTER THE CURRENT JQUERY
    define('jquery', [], function() {
		return window.jQuery;
	});

    // 如果bootstrap已经载入则定义空的 bootstrap，避免重复载入二次dropdown不工作
    if ($.support.transition) {
    	define('bootstrap', [], function () {
			return '';
		});
    } else { // 如果没载入刚定义bootstrap的模块路径
    	paths.bootstrap = root + '/lib/bootstrap/js/bootstrap.min';
    }
}
// requirejs 配置注册
// Sets the require.js configuration for your application.

requirejs.config({
	baseUrl: root + '/app/' + app,
	// enforceDefine: true,
	// urlArgs: "bust=" + (new Date()).getTime(),
	urlArgs: "version=" + window.version,
	waitSeconds: 200,
	// 3rd party script alias names
	paths: paths,
	// Sets the configuration for your third party scripts that are not AMD compatible
	shim: {
		jquery: {exports : '$'},
		underscore: {exports : '_'},
		"jquery-carousel": {deps: ['css!' + root + '/3rdparty/carousel/carousel.css']},
		"jquery.fileupload-process": {exports : 'jquery.fileupload-process'},
		"jquery.fileupload": {exports : 'jquery.fileupload'},
		"jquery.fileupload-validate": {exports : 'jquery.fileupload-validate', deps:  ["jquery.ui.widget", "jquery.iframe-transport"]},
		bootstrap: {deps : ['css!' + root + '/lib/bootstrap/css/bootstrap.min.css']},
		jqueryui: {deps : ['css!' + root + '/lib/jqueryui/css/jquery-ui.min.css']},
		//"jquery.upload": {deps : ["jquery.upload-validate", "jquery.upload-process", "jquery.ui.widget", "jquery.iframe-transport"]},
		//"jquery.upload": {deps : ["jquery.upload-validate", "jquery.ui.widget", "jquery.iframe-transport"]},
		"jquery-mobile": {deps : ['css!' + root + '/lib/jquery-mobile/jquery.mobile-1.4.5.min.css']},
		datetimepicker: {deps: ['css!' + root + '/lib/jquery-datetimepicker/jquery.datetimepicker.css']},
		"jquery-bootgrid": {deps: ['css!' + root + '/3rdparty/jquery-bootgrid/jquery.bootgrid.css']},
		//"jquery-photoswipe": {deps: ["simple-inheritance", 'css!' + root + '/3rdparty/jquery-mobile-photoswipe/photoswipe.css']},
		"swipebox": {deps: ['css!' + root + '/3rdparty/swipebox/css/swipebox.min.css']},
		"jtree": {deps: ['css!' + root + '/3rdparty/jtree/themes/default/style.min.css']},
		"jquery-lazyload-autoload": {deps: ["jquery-lazyload"]},
		"iscrollview": {deps: ['iscroll', 'css!' + root + '/3rdparty/iscroll/jquery.mobile.iscrollview.css', 'css!' + root + '/3rdparty/iscroll/jquery.mobile.iscrollview-pull.css']},
		"mobile-datepicker-datebox": {deps: ["css!mobile-datepicker-css", "mobile-datepicker", "mobile-datepicker-flipbox", "mobile-datepicker-lang"]}
	}
});
