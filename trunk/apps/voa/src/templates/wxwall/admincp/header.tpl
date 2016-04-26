<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
	<!--[if IE]>
	<meta content="IE=Edge" http-equiv="X-UA-Compatible" />
	<![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="description" content="bdFramework" />
	<meta name="author" content="deepseath@gmail.com" />
	<title>{if $navTitle}{$navTitle} - {/if}畅移微信墙</title>
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap.css" />
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="{$staticUrl}font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap-select.min.css" />
	<link rel="stylesheet" href="{$staticUrl}css/style.css" />
	<!--[if lt IE 9]>
	<script type="text/javascript" src="{$staticUrl}js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$staticUrl}js/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/bootstrap.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/common.js"></script>
	<script type="text/javascript">
	jQuery(function(){
		jQuery('.selectpicker').selectpicker();
	});
	</script>
</head>
<body>
<!--[if lt IE 9]><div class="alert alert-warning alert-dismissable ie-tip-msg text-center">
	<button type="button" class="close ie-close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<strong>提醒：</strong> 
	为获得更好的浏览操作体验，建议您更换更高版本的 IE浏览器 或者其他内核浏览器（如
	<a href="http://www.google.cn/intl/zh-CN/chrome/browser/" target="_blank">谷歌浏览器</a>、
	<a href="https://www.mozilla.org/en-US/firefox/all/?q=Chinese%20(Simplified),%20%E4%B8%AD%E6%96%87%20(%E7%AE%80%E4%BD%93)" target="_blank">Firefox浏览器</a>
	）
</div><![endif]-->
<div class="wxs-page-width">
	<nav class="navbar navbar-default wxs-navbar" role="navigation">
		<div class="logo">
			<h1><a href="{$cinstance->wxwall_admincp_url('')}"><strong>畅移微信墙</strong><small>基于畅移云工作的微信墙</small></a></h1>
		</div>
		<div class="navigations">
			<ul class="list-inline wxs-vcy-links">
				<li><a class="label label-info font-12 weight-normal" href="http://www.vchangyi.com/" target="_blank">畅移首页</a></li>
				<li><a class="label label-info font-12 weight-normal" href="javascript:;">功能介绍</a></li>
				<li><a class="label label-info font-12 weight-normal" href="javascript:;">联系我们</a></li>
			</ul>
			<div class="clearfix"></div>
			<ul class="nav nav-pills wxs-links">
{if !empty($ww_id)}
	{foreach $navLinks as $_act_id => $_act}
				<li{if $_act['module'] == $module} class="active"{/if}><a href="{$_act['url']}">{$_act['name']}</a></li>
	{/foreach}
				<li><a href="{$wxwallUrl}" target="_blank">微信墙展示</a></li>
				<li><a href="{$logoutLink}{$formhash}">退出登录</a></li>
{else}
				<li class="active"><a href="{$cinstance->wxwall_admincp_url('')}">登录</a></li>
{/if}
			</ul>
		</div>
	</nav>
