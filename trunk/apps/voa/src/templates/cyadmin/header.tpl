{if !$ajax}<!DOCTYPE html>
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
	<title>{$nav_title|default:'首页'} - {$setting['sitename']}</title>
	<link rel="stylesheet" href="{$static_url}css/bootstrap.css" />
	<link rel="stylesheet" href="{$static_url}css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="{$static_url}font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="{$static_url}css/bootstrap-select.min.css" />
	<link rel="stylesheet" href="{$static_url}css/style.css" />
	<link rel="stylesheet" href="{$static_url}css/datepicker.css" />
	<link rel="stylesheet" href="{$static_url}css/style_content_train.css"/>
	<link rel="stylesheet" href="{$static_url}css/jquery.iviewer.css" />
    <link rel="stylesheet" href="{$static_url}css/dialog/twitter.css" />
	<link rel="shortcut icon" type="image/x-icon" href="{$static_url}images/favicon.ico" />
	<link rel="apple-touch-icon" href="{$static_url}images/favicon.ico" />
	<!--bs_validator-->
	<link rel="stylesheet" type="text/css" href="{$static_url}js/bootstr_valodator/css/bootstrapValidator.min.css">
	<style type="text/css">
		#menu1 li span{
			display: block;
		}
		#menu1 li{
			padding: 3px 0;
		}
	</style>
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="{$static_url}css/style-ie8.css" />
	<script type="text/javascript" src="{$static_url}js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$static_url}js/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="{$static_url}js/bootstrap.js"></script>
	<script type="text/javascript" src="{$static_url}js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="{$static_url}js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="{$static_url}js/jquery.iphone-switch.js"></script>
	<script type="text/javascript" src="{$static_url}js/jquery-migrate-1.2.1.js"></script>
	<script type="text/javascript" src="{$static_url}js/pixel-admin.min.js"></script>
	<script type="text/javascript" src="{$static_url}js/jquery.editinplace.js"></script>
	<script type="text/javascript" src="{$static_url}js/jquery.fileupload.js"></script>
    <script type="text/javascript" src="{$static_url}js/jquery.artDialog.js"></script>
	<script type="text/javascript" src="{$static_url}js/common.js"></script>
    <script type="text/javascript" src="{$static_url}js/train.js"></script>

    <script type="text/javascript" src="{$static_url}js/bootstr_valodator/js/bootstrapValidator.min.js"></script>
	
{if !empty($css_extend_files)}
	{foreach $css_extend_files as $_css_file}
	<link rel="stylesheet" href="{$static_url}css/{$_css_file}" />
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
	<div class="navbar-header">
		<a href="javascript:;" class="navbar-brand">畅移云工作</a>
	</div>
	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
		<ul class="nav navbar-nav">
				<li{if $module == 'home'} class="active"{/if}><a href="{$base->cpurl('', '', '', '')}"><i class="fa fa-home"></i> 首页</a></li>
			{foreach $module_list as $k => $mod}
				<li{if $module == $k} class="active"{/if}><a href="{$base->cpurl($mod['module'], $mod['operation'], $mod['subop'], $mod['cp_pluginid'])}">{if $mod['icon']}<i class="fa {$mod['icon']}"></i> {/if}{$mod['name']}</a></li>
			{/foreach}
<!--
			<li class="dropdown">
				<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">帮助中心<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="javascript:;">常见问题</a></li>
					<li><a href="javascript:;">新手入门</a></li>
					<li class="divider"></li>
					<li><a href="javascript:;">服务升级</a></li>
				</ul>
			</li>
-->
		</ul>
		
		<ul class="nav navbar-nav navbar-right vcy-nav-string">
		    <!-- <li><span  class="notification  notification-bill" ><span>待处理票据</span>
		                   <span class="num">{$notification_bill_total}</span>
		                   </span>
		            
		                </li>
		    <li><span  class="notification notification-card" ><span>待处理名片</span>
		                   <span class="num">{$notification_card_total}</span>
		                   </span>
		            
		                </li> -->
		    <li><span  class="dropdown-toggle notification notification-app" data-toggle="dropdown"><span>待处理应用</span>
           <span class="num">{$notification_total}</span>
           </span>
          <ul class="dropdown-menu" role="menu">
            <li><div class="dropdown-content">
            <span class="notiloading">正在加载中。。。。</span>
            <span class="notinodata">没有数据</span>
            <table class="table font12 table-hover">
           
            </table>
            </div></li>
            <!--
            <li class="divider"></li>
            <li><a href="#">更多</a></li>-->
          </ul>
        </li>
		
		<!--add by ppker-->
		<li>
			<a href="/enterprise/overdue/">
				<span  class="notification notification-overdue" ><span>应用过期提醒</span>
	        	<span class="num" style="background-color:#F30404">{$notification_overdue_total}</span>
	        	
	        	</span>
	        </a>
        </li>
		
		{if 2 == $ext_job}	
	        <li>
	        	<a href="/company/operationrecord" style="position:relative;left:-32px;">
	        		<span class="notification notification-overdue"><span style=""> 企业负责人变更</span>
	        		</span>
	        	</a>

	        	<a style="position:absolute;top:0;left:65px;" id="drop4" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
	          		<span class="caret"></span>
	        	</a>
				<ul id="menu1" class="dropdown-menu" aria-labelledby="drop4">
					{foreach $sell_man as $k => $val}
						<li><span class="label label-info">{$val}</span></li>
					{/foreach}
		        </ul>
	        </li>
	    {/if}
		<!--end by ppker-->

			<li><span>{$user['ca_username']}（{$usergroup['cag_title']}）</span></li>
			<li><a href="{$base->cpUrl('logout')}{$formhash}"><i class="fa fa-sign-out"></i>退出</a></li>
		</ul>
	</div>
</nav>
<div id="page-container" class="container content">
	<div class="vcy-menu-wrapper">
		<div class="vcy-menu">
			<dl>
			{if isset($module_list[$module]) && $module_list[$module]['display']}
				<dt>&nbsp;</dt>
				{foreach $operation_list[$module] as $k => $op}
					{if $op['display']}
						<dd{if $module == $op['module'] && $operation == $k} class="active"{/if}>{if $op['icon']}<i class="fa {$op['icon']}"></i> {/if}<a href="{$base->cpUrl($op['module'], $op['operation'], $op['subop'])}">{$op['name']}</a></dd>
					{/if}
				{/foreach}
			{else}
				<dt>&nbsp;</dt>
						<dd class="active"><a href="{$base->cpUrl('', '', '', '')}">首页</a></dd>
				{foreach $module_list as $k => $_module}
						<dd><a href="{$base->cpUrl($_module['module'], '' , '', '')}">{$_module['name']}</a></dd>
				{/foreach}
			{/if}
			</dl>
		</div>
	</div>
	<div class="vcy-body-wrapper">
		<div class="vcy-body">
			<div class="vcy-body-box">
			{if $subop && !empty($navmenu['title'])}
			<div class="row">
				<div class="vcy-ao-titlegroup">
					<div class="col-sm-10 vcy-ao-title">
						<ul class="nav nav-pills">
				{if !empty($navmenu['title'])}
							<li class="disabled">{$navmenu['list']}</li>
				{else}
							<li class="disabled">
								{$operation_list[$module][$operation]['name']}
								<i class="fa fa-angle-double-right"></i>
								{$subop_list[$module][$operation][$subop]['name']}
							</li>
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
					{foreach $subop_list[$module][$operation] as $k => $m}
						{if !preg_match('/delete|view|edit/', $k)}
							<li{if $k == $subop} class="active"{/if}>
								<a href="{$base->cpUrl($module, $operation, $k)}">
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
