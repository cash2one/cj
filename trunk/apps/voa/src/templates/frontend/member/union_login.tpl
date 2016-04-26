{include file='frontend/header.tpl'}
<body>

<div class="container wxs-wrapper wxs-login"><div class="row"><div class="col-md-offset-4 col-md-4">
	<form id="loginform" action="{$from_action}" method="post">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<input type="hidden" name="bind_data" value="{$bind_data}" />
		<div class="form-group input-group">
			<span class="input-group-addon">用户名</span>
			<input type="text" name="account" id="account" class="form-control input-lg" placeholder="用户名" maxlength="16" />
		</div>
		<div class="form-group input-group">
			<span class="input-group-addon">密码</span>
			<input type="password" name="password" id="password" class="form-control input-lg" placeholder="密码" />
		</div>
		<div class="text-center"><button type="submit" class="btn btn-lg btn-info"><strong>登录</strong></button></div>
	</form>
</div></div></div>

<script type="text/javascript" src="{$wbs_javascript_path}/md5.js"></script>
<script type="text/javascript" src="{$wbs_javascript_path}/jquery.js"></script>
{literal}
<script type="text/javascript">
$(document).ready(function() {
	/** submit 事件 */
	$('#loginform').submit(function() {
		var account = $.trim($('#account').val());
		var passwd = $.trim($('#password').val());

		if (0 >= account.length) {
			alert('请输入登录帐号');
			return false;
		}

		if (0 > passwd.length) {
			alert('请输入密码');
			return false;
		}

		var pwd = hex_md5(passwd);
		$('#password').val(pwd);
		return true;
	});
});
</script>
{/literal}
</body>
{include file='frontend/footer.tpl'}