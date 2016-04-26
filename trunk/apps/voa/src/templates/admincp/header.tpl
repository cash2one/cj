<!DOCTYPE html>
<!--[if IE 8]><html class="ie8"><![endif]-->
<!--[if IE 9]><html class="ie9 gt-ie8"><![endif]-->
<!--[if gt IE 9]><!--><html class="gt-ie8 gt-ie9 not-ie"><!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>{$nav_title|default:''} - 畅移云工作后台</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link href="{$CSSDIR}bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}bootstrap-select.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}token-input-facebook.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}multi-select.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}pixel-admin.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}widgets.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}rtl.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}expand_style.css" rel="stylesheet" type="text/css" />
	<link href="{$IMGDIR}images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link href="{$CSSDIR}themes.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}dou.css" rel="stylesheet" type="text/css" />
	<link href="{$JSDIR}choose-person/ng.poler.plugins.pc.min.css" rel="stylesheet" type="text/css" />
	<style type="text/css">
		.navbar .dropdown-menu>li>a>span.uulh{
			line-height: 22px;
		}
	</style>
{if !empty($css_file)}
	{if is_array($css_file)}
		{foreach $css_file as $_css_file}
	<link rel="stylesheet" type="text/css" href="{$CSSDIR}{$_css_file}" />
		{/foreach}
	{else}
	<link rel="stylesheet" type="text/css" href="{$CSSDIR}{$css_file}" />
	{/if}
{/if}
<!--[if lt IE 9]>
	<link href="{$CSSDIR}expand_ie8.css" rel="stylesheet" />
	<script type="text/javascript" src="{$JSDIR}ie.min.js"></script>
<![endif]-->
	<script type="text/javascript" src="{$JSDIR}jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}bootstrap.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}pixel-admin.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}expand_common.js"></script>
	<script type="text/javascript" src="{$JSDIR}jquery.multi-select.js"></script>
	<script type="text/javascript" src="{$JSDIR}jquery.fileupload.js"></script>
	<script type="text/javascript" src="{$JSDIR}jquery.tokeninput.js"></script>
	<script type="text/javascript" src="{$JSDIR}bootstrap-select.min.js"></script>
    <!-- angular 选人组件 begin -->
	<script type="text/javascript" src="{$JSDIR}choose-person/angular.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}choose-person/ui-bootstrap-tpls.js"></script>
	<script type="text/javascript" src="{$JSDIR}choose-person/angular-route.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}choose-person/angular-ui-router.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}choose-person/ng.poler.min.js"></script>
    <script type="text/javascript" src="{$JSDIR}choose-person/ng.poler.plugins.pc.min.js"></script>
    <!-- angular 选人组件 end -->
	<!-- angular 选人组件 begin -->
	<link href="{$JSDIR}choose-person/chooseStyle.css" rel="stylesheet" type="text/css" />
	<!-- angular 选人组件 end -->
    <!-- artTemplate 模板 begin -->
    <script type="text/javascript" src="{$JSDIR}template-native.js"></script>
    <!-- artTemplate 模板 end -->
    <script type="text/javascript" src="{$JSDIR}jquery.validate.min.js"></script>
	<script type="text/javascript">
	var init = [];
	jQuery(function() {
        jQuery('.selectpicker').selectpicker();
    });
	</script>
	{$expand_head}
	{if $expand_js}
		{foreach $expand_js as $_js_path}
	<script type="text/javascript" src="{$JSDIR}{$_js_path}"></script>
		{/foreach}
	{/if}
	{if $expand_css}
		{foreach $expand_css as $_css_path}
	<link href="{$CSSDIR}{$_css_path}" rel="stylesheet" type="text/css" />
		{/foreach}
	{/if}
</head>
<body class="theme-clean main-menu-animated">
<!--[if lt IE 9]><div id="brower-warning" class="alert alert-warning alert-dismissable ie-tip-msg text-center" style="position:fixed;top:0;left:0;z-index:99999">
	<button type="button" class="close ie-close" data-dismiss="alert" aria-hidden="true">&times;</button>
	<strong>提醒：</strong>
	为获得更好的浏览操作体验，建议您更换更高版本的 IE浏览器 或者其他内核浏览器（如
	<a href="http://www.google.cn/intl/zh-CN/chrome/browser/" target="_blank">谷歌浏览器</a>、
	<a href="https://www.mozilla.org/en-US/firefox/all/?q=Chinese%20(Simplified),%20%E4%B8%AD%E6%96%87%20(%E7%AE%80%E4%BD%93)" target="_blank">Firefox浏览器</a>
	）
</div><![endif]-->
<div id="main-wrapper" style="overflow: visible!important;">
	<div id="main-navbar" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<button type="button" id="main-menu-toggle"><i class="navbar-icon fa fa-bars icon"></i><span class="hide-menu-text">隐藏侧边栏</span></button>
		<div class="navbar-inner">
			<div class="navbar-header">
				<a href="#" class="navbar-brand"><span><img alt="畅移云工作 Beta" src="{$IMGDIR}admincp_logo.png" /></span></a>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar-collapse"><i class="navbar-icon fa fa-bars"></i></button>
			</div>
			<div id="main-navbar-collapse" class="collapse navbar-collapse main-navbar-collapse">
				<div>
					<ul class="nav navbar-nav">
						{foreach $module_list as $k => $mod}
						<li{if $module == $k} class="active"{/if}><a href="{$base->cpurl($mod['module'], $mod['operation'], $mod['subop'], $mod['cp_pluginid'])}">{if $mod['icon']}<i class="fa {$mod['icon']}"></i> {/if}{$mod['name']}</a></li>
						{/foreach}
						<li class="dropdown">
							<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-question-circle"></i>&nbsp;&nbsp;帮助中心 <i class="fa fa-caret-down"></i></a>
							<ul class="dropdown-menu">
								<li><a href="http://www.vchangyi.com/faq/" target="_blank"><span class="label label-warning pull-right">New</span>帮助中心</a></li>
								<li class="divider"></li>
								<li><a href="http://www.vchangyi.com/bbs/" target="_blank">官方论坛&nbsp;&nbsp;<i class="fa fa-comments-o fa-2 text-info"></i></a></li>
							</ul>
						</li>
					</ul>
					<div class="right clearfix">

						<ul class="nav navbar-nav pull-right right-navbar-nav">
					
						<li><a href="{$msg_url}" title = "消息提醒"><span class="glyphicon glyphicon-bell dou" style="font-size:25px;top:8px;"></span>&nbsp;&nbsp;</span><span id="msg_count" class="label label-danger" style="top:0;">{$header_info['total']}</span></a></li>
							
						<li><a href="/admincp/system/status/account"><span class="str">{if mb_strlen($setting['sitename']) > 5}{mb_substr($setting['sitename'], 0, 5)}...{else}{$setting['sitename']}{/if}</span></a>
						</li>


						<li class="dropdown">
						<a href="#" class="dropdown-toggle user-menu" data-toggle="dropdown">
							<span class="label label-primary" style="margin-top:12px;height:25px;line-height:25px;">{if !empty($header_info['header_free_pay_status'])}&nbsp;免费用户&nbsp;{else}&nbsp;付费状态&nbsp;{/if}</span>
							<i class="fa fa-caret-down"></i>
						</a>
						{if empty($header_info['header_free_pay_status'])}
							{if !empty($header_info['status_pay'])}
								<ul class="dropdown-menu" id="dro_status">

									{foreach $header_info['status_pay'] as $k => $v_pay}
										<li>
											<a>
												<span class="label label-primary uulh">{$v_pay[0]}</span>&nbsp;<span class="label label-info uulh">{$v_pay[1]}</span>
											</a>
										</li>
									{/foreach}
								</ul>
							{else}
								<ul class="dropdown-menu" id="dro_status">
									<li>
										<a>
											<span class="label label-primary uulh" style="width: 100%;">无</span>
										</a>
									</li>
								</ul>
							{/if}
						{/if}

						</li>

							<!-- <li><a href="javascript:;"><span class="str">{$setting['sitename']}</span></a></li> -->
							<!--
							<li><a href="javascript:;"><span class="str" id="paystatus" style="background:#009DDA;padding:5px 10px;color:#FFFFFF;border-radius:10%;"></span></a></li>
							-->
							<li class="dropdown">
								<a href="#" class="dropdown-toggle user-menu" data-toggle="dropdown">
									<img src="{$IMGDIR}avatars.jpg" alt="" />
									<span>{$user['ca_username']}（{$usergroup['cag_title']}）</span><i class="fa fa-caret-down"></i>
								</a>
								<ul class="dropdown-menu">
									<li><a href="{$base->cpUrl('system', 'profile', 'pwd')}"><i class="fa fa-lock"></i>&nbsp;修改密码</a></li>
									<li class="divider"></li>
									<li><a href="{$base->cpUrl('logout')}{$formhash}"><i class="fa fa-power-off"></i>&nbsp;退出登录</a></li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="main-menu" role="navigation">
		<div class="slimScrollDiv">
		<div id="main-menu-inner">
			<ul class="navigation">
	{if isset($module_list[$module]) && $module_list[$module]['display']}
		{foreach $operation_list[$module] as $k=>$op_list}
			{foreach $op_list as $_module_plugin_id => $op}
				{if $op['display']}
				<li{if $module == $op['module'] && $operation == $k && $module_plugin_id == $_module_plugin_id} class="active"{else} class="mm-dropdown"{/if}>
					{if isset($default_list['subop'][$op['module']][$op['operation']])}
						{$_subop = $default_list['subop'][$op['module']][$op['operation']]['subop']}
						{$_pluginid = $default_list['subop'][$op['module']][$op['operation']]['cp_pluginid']}
					{else}
						{$_subop = ''}
						{$_pluginid = 0}
					{/if}
					<a href="{$base->cpUrl($op['module'], $op['operation'], $_subop, $_pluginid)}" title="{$op['name']|escape}" {if $op['operation'] =='vstore'}target="_blank"{/if}>
						{if $op['icon']}<i class="menu-icon fa {$op['icon']}"></i>{else}<i class="menu-icon fa fa-dot-circle-o"></i>{/if}
						<span class="mm-text">{$op['name']}</span>
					</a>
				</li>
				{/if}
			{/foreach}
		{/foreach}
	{else}
		{if $module == 'help'}
				<li><a href="/admincp/help/list/?category=faq"{if $category === 'faq'} class="current"{/if}>常见问题</a></li>
				<li><a href="/admincp/help/list/?category=guide"{if $category === 'guide'} class="current"{/if}>新手入门</a></li>
		{else}
				<li><a href="{$base->cpUrl('', '', '', '')}">首页</a></dd>
			{foreach $module_list as $k=>$_module}
				<li><a href="{$base->cpUrl($_module['module'], '' , '', '')}">{$_module['name']}</a></li>
			{/foreach}
		{/if}
	{/if}
			</ul>
		</div>
		</div>
	</div>
	{$sub_nav_menu = array()}
	{$sub_nav_menu_hide = array()}
	<!-- // 定义子菜单显示数量，超出则隐藏 // -->
	{$sub_nav_menu_max_show = 4}
	{$sub_num = 0}
	{foreach $subop_list[$module][$operation][$module_plugin_id] as $k => $m}
		{if !$m['display'] || !$m['subnavdisplay']}
			{continue}
		{/if}
		{$sub_num = $sub_num + 1}
		{if $sub_num <= $sub_nav_menu_max_show}
			{$sub_nav_menu.$k=$m}
		{else}
			{$sub_nav_menu_hide.$k=$m}
		{/if}
	{/foreach}
	<div id="content-wrapper">
		<!-- <ul class="breadcrumb breadcrumb-page">
			<div class="breadcrumb-label text-light-gray">当前位置: </div>
			<li><a href="#">首页</a></li>
			<li class="active"><a href="#">Dashboard</a></li>
		</ul> -->
		<div id="sub-navbar" class="page-header">

			<div class="row">
				<!-- Page header, center on small screens -->
				<h1 class="col-xs-12 col-sm-4 text-center text-left-sm">
{if isset($subop_list[$module][$operation][$module_plugin_id][$subop]['name'])}
	{if $subop_list[$module][$operation][$module_plugin_id][$subop]['icon']}
				<i class="fa {$subop_list[$module][$operation][$module_plugin_id][$subop]['icon']} page-header-icon"></i>
	{else}
				<i class="fa fa-dashboard page-header-icon"></i>
	{/if}
				&nbsp;&nbsp;{$subop_list[$module][$operation][$module_plugin_id][$subop]['name']}
{else}
				<i class="fa fa-dashboard page-header-icon"></i>&nbsp;&nbsp;云工作
{/if}
				</h1>
				<div class="col-xs-12 col-sm-8">
					<div class="row">
						<hr class="visible-xs no-grid-gutter-h">
						<!-- "Create project" button, width=auto on desktops -->
						<div class="pull-right col-xs-12 col-sm-auto">
{if count($sub_nav_menu) > 1}
							<div class="subnavgroup">
								<div class="subnav">
									<ul class="nav nav-pills text-sm">
	{if $sub_nav_menu_hide}
		<!--<li class="dropdown pull-right tabdrop">
			<a href="javascript:;" data-toggle="dropdown" class="dropdown-toggle">
				<i class="fa fa-bars"></i> <strong class="caret"></strong>
			</a>
				<ul class="dropdown-menu">
		{foreach $sub_nav_menu_hide as $k => $m}
					<li{if $k == $subop} class="active"{/if}>
						<a href="{$base->cpUrl($module, $operation, $k, $module_plugin_id)}">
			{if $m['icon']}
					{if strpos($m['icon'], '.') === false}
						<i class="fa {$m['icon']}"></i>&nbsp;
					{else}
						<img src="{$IMGDIR}icon/{$m['icon']}" alt="" />
					{/if}
			{else}
					<i class="fa fa-link"></i>&nbsp;
			{/if}
						{$m['name']}
						</a>
					</li>
		{/foreach}
				</ul>
		</li>-->
	{/if}
	{foreach $sub_nav_menu as $k => $m}
		<li{if $k == $subop} class="active"{/if}>
			<a href="{$base->cpUrl($module, $operation, $k, $module_plugin_id)}">
				{if $m['icon']}
					{if strpos($m['icon'], '.') === false}
						<i class="fa {$m['icon']}"></i>&nbsp;
					{else}
						<img src="{$IMGDIR}icon/{$m['icon']}" alt="" />
					{/if}
				{else}
					<i class="fa fa-link"></i>&nbsp;
				{/if}
				{$m['name']}
			</a>
		</li>
	{/foreach}
	{foreach $sub_nav_menu_hide as $k => $m}
		<li{if $k == $subop} class="active"{/if}>
			<a href="{$base->cpUrl($module, $operation, $k, $module_plugin_id)}">
				{if $m['icon']}
				{if strpos($m['icon'], '.') === false}
				<i class="fa {$m['icon']}"></i>&nbsp;
				{else}
				<img src="{$IMGDIR}icon/{$m['icon']}" alt="" />
				{/if}
				{else}
				<i class="fa fa-link"></i>&nbsp;
				{/if}
				{$m['name']}
			</a>
		</li>
	{/foreach}
									</ul>
								</div>
							</div>
{/if}


						</div>

						<!-- Margin -->
						<div class="visible-xs clearfix form-group-margin"></div>
					</div>
				</div>
			</div>
		</div>

<script>
	/*$(function(){
		
		var get_tip = function () {

			$.ajax({
				url:'{*{$CYADMIN_URL}*}cyadmin/api/message/list/',
				dataType: 'jsonp',
				jsonp: 'callback',
				data:'info = {*{$info}*}&num=1&ep_id={*{$setting["ep_id"]}*}',
				type:'get',
				success:function(result){
					var num = result.result.total;
					*//*if(num<=0) $('span.glyphicon-bell').removeClass('dou');
					else $('span.glyphicon-bell').addClass('dou');*//*
					$("#msg_count").text(num);
					var c_str = '';
					$.each(result.result.status_pay, function(i,n){
						c_str += '<li><a><span class="label label-primary uulh">' + n[0] + '</span><span class="label label-info uulh">' + n[1] + '</span></a></li>';
					})


					$('#dro_status').append($(c_str));

				}
			});
			// setTimeout(get_tip, '30000');
		}
		// get_tip();
	});*/
</script>