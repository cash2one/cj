{include file='uc/header.tpl'}

<div class="row">
	<div class="col-md-4 col-md-offset-4">
		<form id="form-login" class="form-horizontal" role="form" method="POST" action="/register/">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<div class="form-group">
				<label for="uc_mobilephone" class="col-sm-4 control-label">手机号码</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" id="uc_mobilephone" name="mobilephone" placeholder="输入您的手机号码" />
				</div>
			</div>
			<div class="form-group">
				<label for="uc_smscode" class="col-sm-4 control-label">手机短信验证码</label>
				<div class="col-sm-8">
					<div class="col-sm-7" style="margin-left:0;padding-left:0;"><input type="text" class="form-control" id="uc_smscode" name="smscode" placeholder="输入手机接收到的短信验证码" /></div>
					<div class="col-sm-5" style="margin-right:0;padding-right:0;"><button type="button" class="btn btn-default">获取短信验证码</button></div>
				</div>
			</div>
			<div class="form-group">
				<label for="uc_email" class="col-sm-4 control-label">邮箱</label>
				<div class="col-sm-8">
					<input type="password" class="form-control" id="uc_email" name="email" placeholder="输入您的邮箱地址" />
				</div>
			</div>
			<div class="form-group">
				<label for="uc_realname" class="col-sm-4 control-label">真实姓名</label>
				<div class="col-sm-8">
					<input type="password" class="form-control" id="uc_realname" name="realname" placeholder="输入真实姓名" />
				</div>
			</div>
			<div class="form-group">
				<label for="uc_password" class="col-sm-4 control-label">密码</label>
				<div class="col-sm-8">
					<input type="password" class="form-control" id="uc_password" name="password" placeholder="设置登录密码" />
				</div>
			</div>
			<div class="form-group">
				<label for="uc_password2" class="col-sm-4 control-label">再次输入密码</label>
				<div class="col-sm-8">
					<input type="password" class="form-control" id="uc_password2" placeholder="再次输入登录密码" />
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-offset-4 col-sm-8">
					<button type="submit" class="btn btn-primary">注册</button>
				</div>
			</div>
		</form>
	</div>
</div>

{include file='uc/footer.tpl'}