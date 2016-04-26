<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="format-detection" content="telephone=no" />
<title>登陆PC端</title>
<link rel="stylesheet" href="{$wbs_css_path}/MOA.common.css" />
<link rel="stylesheet" href="{$wbs_css_path}/MOA.dialog.css" />
<link rel="stylesheet" href="{$wbs_css_path}/MOA.timeslider.css" />
<script type="text/javascript">
/** 指定js基础路径，会在require动态加载其他js时引用 */
var _globalRequireBaseUrl = '{$wbs_javascript_path}';
</script>
<script src="{$wbs_javascript_path}/MOA.common.js"></script>
<script src="{$wbs_javascript_path}/MOA.components.js"></script>
{* 载入jsapi *}
{if !empty($jsapi)}
{include file='frontend/h5mod/jsapi.tpl' jsapi=$jsapi jsapi_list=$jsapi_list}
{/if}
<script src="{$wbs_javascript_path}/require-config.js"></script>
<script src="{$wbs_javascript_path}/require.js"></script>

<script src="{$wbs_javascript_path}/MOA.ajaxform.js"></script>
<script src="{$wbs_javascript_path}/MOA.storageform.js"></script>
{literal}
<script type="text/javascript">
var ajax_form_lock = false;

function onBridgeReady() {
	WeixinJSBridge.invoke('getNetworkType', {}, function(e) {
		WeixinJSBridge.log(e.err_msg);
	});
}

$onload(function() {
	/** ajax div */
	$append(document.body, '<div id="append_parent" hidden></div>');
	/** 隐藏底部导航栏 */
	document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
		WeixinJSBridge.call('hideToolbar');
	});

	if (typeof WeixinJSBridge == "undefined") {
		if( document.addEventListener ) {
			document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
		} else if (document.attachEvent) {
			document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
			document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
		}
	} else {
	    onBridgeReady();
	}
	
	/** 关闭浏览器窗口 */
	if ($one('#btn_go_back')) {
		$one('#btn_go_back').addEventListener('click', function(e) {
			wx_history_go(-1);
		});
	}
});

/** 回退 */
function wx_history_go(index) {
	/**if ('undefined' == typeof(document.referer)) {
		wx_close_window();
		return true;
	}*/
	
	window.history.go(index);
}

/** 关闭微信浏览器 */
function wx_close_window() {
	WeixinJSBridge.invoke('closeWindow',{});
}

/** ajax 提交 */
function aj_form_submit(formid) {
	if (true == ajax_form_lock) {
		return false;
	}

	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	MAjaxForm.submit(formid, function(result) {
		MLoading.hide();
	});
	
	return true;
}

/** 模拟 form 提交 */
function aj_form_analog(url, data) {
	if (true == ajax_form_lock) {
		return false;
	}

	ajax_form_lock = true;
	MLoading.show('稍等片刻...');
	MAjaxForm.analog(url, data, 'post', function(result) {
		MLoading.hide();
	});
	
	return true;
}
</script>
{/literal}
</head>
