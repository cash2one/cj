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
	<title>{$setting['sitename']}</title>
	<link rel="stylesheet" href="{$static_url}css/bootstrap.css" />
	<link rel="stylesheet" href="{$static_url}css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="{$static_url}font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="{$static_url}css/bootstrap-select.min.css" />
	<link rel="stylesheet" href="{$static_url}css/style.css" />
	<link rel="shortcut icon" type="image/x-icon" href="{$static_url}images/favicon.ico" />
	<link rel="apple-touch-icon" href="{$static_url}images/favicon.ico" />
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="{$static_url}css/style-ie8.css" />
	<script type="text/javascript" src="{$static_url}js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="{$static_url}js/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="{$static_url}js/bootstrap.js"></script>
	<script type="text/javascript" src="{$static_url}js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="{$static_url}js/common.js"></script>
	<script type="text/javascript">
	jQuery(function() {
		jQuery('.selectpicker').selectpicker();
	});
	</script>
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
{/if}

<script type="text/javascript" src="{$static_url}/js/md5.js"></script>
<form id="loginform" class="form-horizontal" role="form" method="POST" action="{$form_action_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="login-panel">
	<h1 class="text-center"><strong>登录后台系统</strong></h1>
	<div class="panel panel-default">
		<div class="panel-body">
		{if !empty($err_msg)}
			<div class="form-group">
				<div class="text-danger text-center">{$err_msg}</div>
			</div>
		{/if}
			<div class="form-group">
				<label for="adminer_account" class="col-sm-2 control-label">帐号</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="username" name="username" placeholder="登录帐号" value="{$adminer_username}" maxlength="54" />
				</div>
			</div>
			<div class="form-group">
				<label for="adminer_password" class="col-sm-2 control-label">密码</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="password" name="password" placeholder="请输入您的登录密码" value="" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="adminer_remember" value="1"{if !empty($adminer_remember)} checked="checked"{/if} />
							一周内自动登录
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group">
		<div class="col-xs-12">
			<button type="submit" class="btn btn-warning btn-lg"><strong>立即登录</strong></button>
		</div>
	</div>
</div>
</form>
{literal}
<script type="text/javascript">
jQuery(function() {
	//submit 事件
	jQuery('#loginform').submit(function() {
		var username = $.trim(jQuery('#username').val());
		var passwd = $.trim(jQuery('#password').val());
		if (0 >= username.length) {
			WG.popup({'content':'请输入用户名'});
			return false;
		}
		if (0 >= passwd.length) {
			WG.popup({'content':'请输入密码'});
			return false;
		}
		var pwd = hex_md5(passwd);
		jQuery('#password').val(hex_md5(pwd.substr(16) + passwd));
		return true;
	});
});
</script>
{/literal}
{if !$ajax}
</body>
</html>
{/if}