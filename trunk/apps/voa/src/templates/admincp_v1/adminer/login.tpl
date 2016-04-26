{include file='admincp/adminer/header.tpl'}

<script type="text/javascript" src="{$staticUrl}/js/md5.js"></script>
<form id="loginform" class="form-horizontal" role="form" method="POST" action="{$action_url}">
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
				<div class="col-sm-6">
					<input type="text" class="form-control" id="account" name="account" placeholder="手机号码或邮箱" value="{$adminer_username}" maxlength="54" />
				</div>
				<div class="col-sm-4">
					<div class="help-block font12">
						<label class="checkbox" title="一周内不再输入密码"><input type="checkbox" name="adminer_remember" value="1"{if !empty($adminer_remember)} checked="checked"{/if} /> 保持登录</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<label for="adminer_password" class="col-sm-2 control-label">密码</label>
				<div class="col-sm-6">
					<input type="password" class="form-control" id="password" name="password" placeholder="请输入您的登录密码" value="" />
				</div>
				<div class="col-sm-4 font12">
					<div class="help-block"><a href="/admincp/pwd/">忘记密码？</a></div>
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
$(document).ready(function() {
	/** submit 事件 */
	$('#loginform').submit(function() {
		var account = $.trim($('#account').val());
		var password = $.trim($('#password').val());
		if (0 >= account.length) {
			alert('请输入用户名');
			return false;
		}
		if (0 > password.length) {
			alert('请输入密码');
			return false;
		}
		var pwd = hex_md5(password);
		$('#password').val(pwd);
		//$('#password').val(hex_md5(pwd.substr(16) + password));
		return true;
	});
});

</script>
{/literal}

{include file='admincp/adminer/footer.tpl'}