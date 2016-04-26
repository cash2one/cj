{if !$ajax}<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
	<!--[if IE]>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<![endif]-->
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="description" content="bdFramework" />
	<meta name="author" content="deepseath@gmail.com" />
	<title>{$nav_title|default:'首页'} - 畅移云工作后台管理系统</title>
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap.css" />
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="{$staticUrl}font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap-select.min.css" />
	<link rel="stylesheet" href="{$staticUrl}css/style.css?t=20140924" />
	<link rel="stylesheet" href="{$staticUrl}css/datepicker.css" />
    	<link rel="stylesheet" href="{$staticUrl}css/multi-select.css" />
	<link rel="stylesheet" href="{$staticUrl}css/token-input-facebook.css" />
	<link rel="stylesheet" href="{$staticUrl}css/bootstrap-timepicker.css" />
	<link rel="shortcut icon" type="image/x-icon" href="{$staticUrl}images/favicon.ico" />
	<link rel="apple-touch-icon" href="{$staticUrl}images/favicon.ico" />
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="{$staticUrl}css/style-ie8.css" />
	<script type="text/javascript" src="{$staticUrl}js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$staticUrl}js/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/jquery-ui-1.10.2.custom.min.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/jquery.form.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/bootstrap.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/jquery.multi-select.js"></script>
	<!--[if lt IE 9]>
	<script src="{$staticUrl}js/html5shiv.min.js"></script>
	<script src="{$staticUrl}js/respond.min.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$staticUrl}js/common.js"></script>
    	<script type="text/javascript" src="{$staticUrl}js/jquery.editinplace.js"></script>
    	<script type="text/javascript" src="{$staticUrl}js/jquery.iframe-transport.js"></script>
	<script type="text/javascript" src="{$staticUrl}js/jquery.fileupload.js"></script>
    	<script type="text/javascript" src="{$staticUrl}js/jquery.tokeninput.js"></script>
    	<script type="text/javascript" src="{$staticUrl}js/bootstrap-timepicker.js"></script>
	
	<script type="text/javascript">
	$(function() {
		$('.selectpicker').selectpicker();
	});
	</script>
{if !empty($extCssFile)}
	{foreach $extCssFile AS $_cssfile}
	<link rel="stylesheet" href="{$staticUrl}css/{$_cssfile}" />
	{/foreach}
{/if}
</head>
<body>
<!--[if lt IE 9]><div id="brower-warning" class="alert alert-warning alert-dismissable ie-tip-msg text-center">
	<button type="button" class="close ie-close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<strong>提醒：</strong>
	为获得更好的浏览操作体验，建议您更换更高版本的 IE浏览器 或者其他内核浏览器（如
	<a href="http://www.google.cn/intl/zh-CN/chrome/browser/" target="_blank">谷歌浏览器</a>、
	<a href="https://www.mozilla.org/en-US/firefox/all/?q=Chinese%20(Simplified),%20%E4%B8%AD%E6%96%87%20(%E7%AE%80%E4%BD%93)" target="_blank">Firefox浏览器</a>
	）
</div><![endif]-->
<nav id="page-nav" class="navbar navbar-default vcy-nav" role="navigation">
	<div class="navbar-header vcy-nav-logo">
		<a href="javascript:;" class="navbar-brand">畅移云工作<small>Beta</small></a>
	</div>
	<div class="collapse navbar-collapse vcy-nav-links" id="bs-example-navbar-collapse-0">
		<ul class="nav navbar-nav navbar-left">
<!--
			<li{if $module == 'home'} class="active"{/if}><a href="{$base->cpurl('', '', '', '')}"><i class="fa fa-home"></i> 首页</a></li>
-->
			{foreach $module_list as $k=>$mod}
				<li{if $module == $k} class="active"{/if}><a href="{$base->cpurl($mod['module'], $mod['operation'], $mod['subop'], $mod['cp_pluginid'])}">{if $mod['icon']}<i class="fa {$mod['icon']}"></i> {/if}{$mod['name']}</a></li>
			{/foreach}

			<li class="dropdown">
				<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">帮助中心<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="/admincp/help/list/?category=faq">常见问题</a></li>
					<li><a href="/admincp/help/list/?category=guide">新手入门</a></li>
<!--
					<li class="divider"></li>
					<li><a href="javascript:;">服务升级</a></li>
 -->
				</ul>
			</li>

		</ul>
		<ul class="nav navbar-nav navbar-right vcy-nav-string">
			<li><span>{$setting['sitename']} —— {$user['ca_username']}（{$usergroup['cag_title']}）</span></li>
			<li><a href="{$base->cpUrl('system', 'profile', 'pwd')}"><i class="fa fa-lock"></i> 修改密码</a></li>
			<li><a href="{$base->cpUrl('logout')}{$formhash}"><i class="fa fa-sign-out"></i> 退出</a></li>
		</ul>
	</div>
	<div class="navbar-temp"></div>
</nav>
<div id="page-container" class="container content">
	<div class="vcy-menu-wrapper">
		<div class="vcy-menu">
			<dl>
			{if isset($module_list[$module]) && $module_list[$module]['display']}
				<dt>&nbsp;</dt>
				{foreach $operation_list[$module] as $k=>$op_list}
					{foreach $op_list as $_module_plugin_id => $op}
						{if $op['display']}
						<dd{if $module == $op['module'] && $operation == $k && $module_plugin_id == $_module_plugin_id} class="active"{/if}>{if $op['icon']}<i class="fa {$op['icon']}"></i> {/if}<a href="{$base->cpUrl($op['module'], $op['operation'], $op['subop'], $op['cp_pluginid'])}">{$op['name']}</a></dd>
						{/if}
					{/foreach}
				{/foreach}
			{else}
				{if $module == 'help'}
					<dt>&nbsp;</dt>
						<dd{if $category === 'faq'} class="active"{/if}><a href="/admincp/help/list/?category=faq">常见问题</a></dd>
						<dd{if $category === 'guide'} class="active"{/if}><a href="/admincp/help/list/?category=guide">新手入门</a></dd>
				{else}
				<dt>&nbsp;</dt>
						<dd class="active"><a href="{$base->cpUrl('', '', '', '')}">首页</a></dd>
					{foreach $module_list as $k=>$_module}
						<dd><a href="{$base->cpUrl($_module['module'], '' , '', '')}">{$_module['name']}</a></dd>
					{/foreach}
				{/if}
			{/if}
			</dl>
		</div>
	</div>
	<div class="vcy-body-wrapper">
		<div class="vcy-body">
			<div class="vcy-body-box">
			{if $subop && !empty($navmenu)}
			<div class="row">
				<div class="vcy-ao-titlegroup">
					<div class="col-sm-10 vcy-ao-title">
						<ul class="nav nav-pills">
			{if empty($navmenu['links'])}
				{if !empty($navmenu['title'])}
							<li class="disabled">{$navmenu['list']}</li>
				{else}
							<li class="disabled">
				<!--
								{$operation_list[$module][$operation][$module_plugin_id]['name']}
								<i class="fa fa-angle-double-right"></i>
				-->
								{$subop_list[$module][$operation][$module_plugin_id][$subop]['name']}
							</li>
				{/if}
			{/if}
				{if !empty($navmenu['links'])}
					{foreach $navmenu['links'] as $k => $m}
						{if strpos($k, 'delete') === false && strpos($k, 'edit') === false}
							<li{if $k == $subop} class="active"{/if}>
								<a href="{$m['url']}">
								{if $m['icon']}
									<i class="fa {$m['icon']}"></i>
								{else}
									<i class="fa fa-link"></i>
								{/if}
									 {$m['name']}
								</a>
							</li>
						{/if}
					{/foreach}
				{else}
					{foreach $subop_list[$module][$operation][$module_plugin_id] as $k => $m}
						{if !preg_match('/delete|view|edit|modify/', $k)}
							<li{if $k == $subop} class="active"{/if}>
								<a href="{$base->cpUrl($module, $operation, $k, $module_plugin_id)}">
								{if $m['icon']}
									<i class="fa {$m['icon']}"></i>
								{else}
									<i class="fa fa-link"></i>
								{/if}
									 {$m['name']}
								</a>
							</li>
						{/if}
					{/foreach}
				{/if}
						</ul>
					</div>
					<div class="col-sm-2 vcy-ao-link text-right">
				{if !empty($navmenu['right'])}
						<a href="{$navmenu['right']['url']}" class="btn btn-primary btn-sm" role="button">{if !empty($navmenu['right']['icon'])}<i class="fa {$navmenu['right']['icon']}"></i> {/if}{$navmenu['right']['title']}</a>
				{else}
						<a href="javascript:history.go(-1);" class="btn btn-default form-small form-small-btn" role="button"><i class="fa fa-arrow-left"></i> 返回</a>
				{/if}
					</div>
				</div>
			</div>
			{/if}
{/if}
