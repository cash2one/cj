<!DOCTYPE HTML>
<html class="no-js ui-mobile-rendering" lang="zh-cn">
	<head>
		<meta charset="utf-8" />
		<title>{$navtitle}</title>
		<meta name="description" content="{$navtitle}" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" />
		<!-- 删除默认的苹果工具栏和菜单栏  -->
	    <meta name="apple-mobile-web-app-capable" content="yes" />
		<!-- 控制状态栏显示样式 -->
	    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<!-- 禁止了把数字转化为拨号链接 -->
	    <meta name="format-detection" content="telephone=no" />
		<script type="text/javascript">
		window.IMGDIR = '{$IMGDIR}';
		window.JSDIR = '{$JSDIR}';
		window.STATICDIR = '{$STATICDIR}';
		window.default_view = '{$__view}';
		window.default_arguments = {$__params};
		window.mobile = true;
		window.userinfo = {if $userinfo}{$userinfo}{else}[]{/if};
		window.companyinfo = {if $companyinfo}{$companyinfo}{else}[]{/if};
		window._app = "{$plugin_identifier}";
		window._root = '{$JSFRAMEWORK}';
		window.version = '{$static_version}';
		{if $saleuid}window.saleuid = {$saleuid};{/if}
		var loc = window.location.pathname;
		var dir = loc.substring(0, loc.lastIndexOf('/'));
		</script>
		<script type="text/javascript" src="{$JSFRAMEWORK}lib/requirejs/require.js?ver={$static_version}"></script>
        <script type="text/javascript" src="{$JSFRAMEWORK}config.js?ver={$static_version}"></script>
        <script src="{$JSFRAMEWORK}main.js?ver={$static_version}"></script>
	</head>
	<body>
