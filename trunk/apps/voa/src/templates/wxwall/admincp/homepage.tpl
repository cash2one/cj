{include file='wxwall/admincp/header.tpl'}

<div class="container wxs-wrapper wxs-login">
	<div class="row">
		<div class="col-md-offset-4 col-md-4">
			<form id="cpLogin" action="{$cinstance->wxwall_admincp_url('login')}" method="post">
				<input type="hidden" name="formhash" value="{$formhash}" />
				<div class="form-group input-group">
					<span class="input-group-addon"><i class="fa fa-user"></i></span>
					<input type="text" name="wxscreen_account" class="form-control input-lg" placeholder="微信墙管理帐号" maxlength="16" />
				</div>
				<div class="form-group input-group">
					<span class="input-group-addon"><i class="fa fa-lock"></i></span>
					<input type="password" name="wxscreen_password" class="form-control input-lg" placeholder="微信墙管理密码" />
				</div>
				<div class="text-center"><button type="submit" class="btn btn-lg btn-info"><strong>登录微信墙管理系统</strong></button></div>
			</form>
		</div>
	</div>
</div>

{include file='wxwall/admincp/footer.tpl'}