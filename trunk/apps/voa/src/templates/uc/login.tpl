{include file='uc/header.tpl'}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<form id="form-login" class="form-horizontal" role="form" method="POST" action="/login/">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<div class="form-group">
				<label for="uc_account" class="col-sm-2 control-label">登录帐号</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="uc_account" name="account" placeholder="手机号或邮箱地址" />
				</div>
			</div>
			<div class="form-group">
				<label for="uc_password" class="col-sm-2 control-label">登录密码</label>
				<div class="col-sm-10">
					<input type="password" class="form-control" id="uc_password" name="password" placeholder="输入登录密码" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<div class="checkbox">
						<label><input type="checkbox" name="remember" /> 保持登录</label>
					</div>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<button type="submit" class="btn btn-primary">登录</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-login').submit(function(){
		var account = jQuery.trim(jQuery('#uc_account').val());
		var password = jQuery.trim(jQuery('#uc_password').val());
		if (account == '') {
			alert('请填写登录帐号');
			return false;
		}
		if (password == '') {
			alert('请填写登录密码');
			return false;
		}
		
		return true;
	});
});
</script>

{include file='uc/footer.tpl'}