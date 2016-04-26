{if 'test.vchangyi.com' == $domain}
	{$static_version = "?_t={$timestamp}"}
{else}
	{$static_version = ''}
{/if}<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
<meta name="format-detection" content="telephone=no" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<title>{$navtitle}</title>
<link rel="stylesheet" href="{$wbs_css_path}/global.css{$static_version}" />
<link rel="stylesheet" href="{$wbs_css_path}/common.css{$static_version}" />
{if !empty($css_file)}
	{if is_array($css_file)}
		{foreach $css_file as $_css_file}
<link rel="stylesheet" href="{$wbs_css_path}/{$_css_file}{$static_version}" />
		{/foreach}
	{else}
<link rel="stylesheet" href="{$wbs_css_path}/{$css_file}{$static_version}" />
	{/if}
{/if}
{literal}
<style>
html { height:100%; }
body { height:100%; overflow-x:hidden; }
#section { height:100%; }
.page-container { position:relative; width:100%; min-height:100%; overflow-x:hidden; background-color:#F8F8F8; }
@-webkit-keyframes slider_right_out { from{ -webkit-transform:translateX(0px); opacity:1; } to { -webkit-transform:translateX(50%); opacity:0; } }
@-webkit-keyframes slider_left_in { from{ -webkit-transform:translateX(-50%); opacity:0; } to { -webkit-transform:translateX(0px); opacity:1; } }
@-webkit-keyframes slider_left_out { from{ -webkit-transform:translateX(0px); opacity:1; } to { -webkit-transform:translateX(-50%); opacity:0; } }
@-webkit-keyframes slider_right_in { from{ -webkit-transform:translateX(50%); opacity:0; } to { -webkit-transform:translateX(0px); opacity:1; } }

.slider_left_out { -webkit-animation:slider_left_out 350ms ease-in-out; }
.slider_left_in { -webkit-animation:slider_left_in 350ms ease-in-out; }
.slider_right_out { -webkit-animation:slider_right_out 350ms ease-in-out; }
.slider_right_in { -webkit-animation:slider_right_in 350ms ease-in-out; }
.ani_start { position:absolute; top:0; left:0; z-index:999; width:100%; height:100%; overflow-x:hidden; }
.ani_start .section_container { overflow-x:hidden; -webkit-transform:translate3d(0, 0, 0); -webkit-backface-visibility:hidden; background-color:#F5F5F5; }
</style>
{/literal}

<script type="text/javascript">
// 指定js基础路径，会在require动态加载其他js时引用
window._js_path = '{$wbs_javascript_path}';
window._append_parent = 'append_parent';
{if 'test.vchangyi.com' == $domain || 'local.vchangyi.net' == $domain}window._debug = true;{/if}
</script>
<script type="text/javascript" src="{$wbs_javascript_path}/require.cfg.js{if 'test.vchangyi.com' == $domain}?ts={$timestamp}{/if}"></script>
<script type="text/javascript" src="{$wbs_javascript_path}/require.js"></script>
<script type="text/javascript">
//初始化
require(["zepto", "frozen"], function($, fz) {
	$(document).ready(function() {
		// ajax div
		$('body').append('<div id="' + window._append_parent + '" hidden></div>');
		// 隐藏底部导航栏
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

		// 关闭浏览器窗口
		if ($('#btn_go_back')) {
			$('#btn_go_back').on('click', function(e) {
				wx_history_go(-1);
			});
		}
	});
});
</script>
</head>
<body{if !empty($body_id)} id="{$body_id}"{/if}{if !empty($body_class)} class="{$body_class}"{/if}>
<section class="section_container">
<div id="cyoa-body">

