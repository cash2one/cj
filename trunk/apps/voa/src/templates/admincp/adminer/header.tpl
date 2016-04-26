<!DOCTYPE html>
<!--[if IE 8]><html class="ie8"><![endif]-->
<!--[if IE 9]><html class="ie9 gt-ie8"><![endif]-->
<!--[if gt IE 9]><!--><html class="gt-ie8 gt-ie9 not-ie"><!--<![endif]-->
<head>
	<meta charset="utf-8" />
	<title>畅移云工作后台</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<link href="{$CSSDIR}bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}pixel-admin.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}widgets.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}rtl.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}themes.min.css" rel="stylesheet" type="text/css" />
	<link href="{$CSSDIR}expand_style.css" rel="stylesheet" type="text/css" />
	<link href="{$IMGDIR}images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link href="{$CSSDIR}expand_login.css" rel="stylesheet" type="text/css" />
<!--[if lt IE 9]>
	<link href="{$CSSDIR}expand_ie8.css" rel="stylesheet" />
	<script type="text/javascript" src="{$JSDIR}ie.min.js"></script>
<![endif]-->
	<script type="text/javascript" src="{$JSDIR}jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}bootstrap.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}pixel-admin.min.js"></script>
	<script type="text/javascript" src="{$JSDIR}expand_common.js"></script>
	<script type="text/javascript">var init = [];</script>
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
<body class="theme-default main-menu-animated">
