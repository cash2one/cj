{$expand_css[]="pages.min.css"}
{$expadn_css[]="expand_login.css"}
{$expand_js[]="expand_md5.js"}
{include file="$tpl_dir_base/adminer/header.tpl"}

<div class="theme-clean page-signin main-navbar-fixed">
	<div id="page-signin-bg">
		<div class="overlay"></div>
	</div>
	<div class="signin-container">
		<div class="signin-info">
			<a href="{$base->cpUrl()}" class="logo">畅移云工作平台</a>
			<div class="slogan">基于微信的云工作平台</div>
			<ul>
				<li><i class="fa fa-sitemap signin-icon"></i> 产品愿景 - 连接人与工作</li>
				<li><i class="fa fa-file-text-o signin-icon"></i> 设计理念 - 让工作充满乐趣</li>
				<li><i class="fa fa-heart signin-icon"></i> 基于微信 - 简单易用</li>
			</ul>
		</div>
		<div class="signin-form">
			<form id="loginform" class="form-horizontal" role="form" method="POST" action="{$action_url}">
			{if $referer}
			<input type="hidden" name="referer" value="{$referer|escape}" />
			{/if}
			<input type="hidden" name="formhash" value="{$formhash}" />
				<div class="signin-text">
					<span>登录畅移云工作 后台</span>
				</div>
				<div class="form-group w-icon">
					<input type="text" id="account" value="{$adminer_username}" name="account" class="form-control input-lg" placeholder="请输入您的手机号码或邮箱">
					<span class="fa fa-user signin-form-icon"></span>
				</div>
				<div class="form-group w-icon">
					<input type="password" id="password" name="password" class="form-control input-lg" placeholder="请输入您的登录密码">
					<span class="fa fa-lock signin-form-icon"></span>
				</div>
				<div class="form-group">
					<div class="text-danger text-center">{$err_msg}</div>
				</div>
				<div class="form-actions">
					<button type="submit" class="signin-btn bg-primary">立即登录</button>
					<span class="forgot-password">
						<span class="checkbox">
							<label for="remember">
								<input type="checkbox" id="remember" class="px" name="adminer_remember" value="1"{if !empty($adminer_remember)} checked="checked"{/if} />
								<span class="lbl">保持登录状态 </span>
							</label>
						</span>
					</span>
				</div>
			</form>
			<div class="signin-with">
				<a href="{$base->cpUrl('pwd')}" class="signin-with-btn login-btn">忘记密码？点击找回</a>
			</div>
		</div>
	</div>
	<div class="not-a-member">
		Copyright &copy;2014<a href="http://www.vchangyi.com/" target="_blank"> 畅移（上海）信息科技有限公司</a>
	</div>
</div>
{literal}
<script type="text/javascript">
$(document).ready(function() {
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
		return true;
	});
});
</script>
{/literal}
{include file="$tpl_dir_base/adminer/footer.tpl"}