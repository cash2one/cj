<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="description" content="bdFramework" />
	<title>登录</title>
	<link rel="stylesheet" href="/admincp/static/css/bootstrap.css" />
	<link rel="stylesheet" href="/admincp/static/css/bootstrap-theme.min.css" />
	<link rel="stylesheet" href="/admincp/static/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="/admincp/static/css/bootstrap-select.min.css" />
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" href="/favicon.ico" />
	<!--[if lt IE 9]>
	<link rel="stylesheet" href="/admincp/static/css/style-ie8.css" />
	<script type="text/javascript" src="/admincp/static/js/html5.js"></script>
	<![endif]-->
	<script type="text/javascript" src="/admincp/static/js/jquery-1.10.2.js"></script>
	<script type="text/javascript" src="/admincp/static/js/bootstrap.js"></script>
	<script type="text/javascript" src="/admincp/static/js/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="/admincp/static/js/common.js"></script>
	<script type="text/javascript" src="/admincp/static/js/md5.js"></script>
{literal}
	<style type="text/css">
	.container{width:420px;margin-top:60px;font-size:17px;}
	.btn{width:90%;margin:0 auto;display:block}
	.login-form, .login-form div{height:40px;line-height:40px;margin-top:0;margin-bottom:0}
	.form-label{height:40px;line-height:40px;margin-top:0;margin-bottom:0}
	</style>
{/literal}
</head>
<body>
<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h3 class="panel-title"><strong>登录</strong></h3>
		</div>
		<div class="panel-body">
			<form id="form-login" action="/login/" method="post">
			<div class="row login-form">
				<div class="col-md-4 text-right form-label"><label for="account">手机号/邮箱</label></div>
				<div class="col-md-8 text-left"><input type="text" id="account" value="" maxlength="45" class="form-control" placeholder="请输入手机号或邮箱" required="required" /></div>
			</div>
			<hr />
			<div class="row login-form">
				<div class="col-md-4 text-right form-label"><label for="password">密码</label></div>
				<div class="col-md-8 text-left"><input type="password" id="password" value="" maxlength="45" class="form-control" required="required" /></div>
			</div>
			<hr />
			<button type="submit" class="btn btn-primary btn-lg">登录</button>
			</form>
		</div>
	</div>
</div>
{literal}
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-login').submit(function(){
		var t = this;
		var action = jQuery(t).attr('action');
		var account = jQuery.trim(jQuery('#account').val());
		var password = jQuery('#password').val();
		jQuery.ajax({
			"url":action,
			"type":"POST",
			"data":{"account":account,"password":hex_md5(password)},
			"success":function(data){
				if (typeof(data.errcode) == 'undefined') {
					alert('网络错误，请重试');
					return false;
				}
				if (data.errcode > 0) {
					alert(data.errmsg);
					return false;
				}
				window.location.href = '/admincp';
				return false;
			}
		});
		return false;
	});
});
</script>
{/literal}
</body>
</html>