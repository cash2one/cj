{include file='admincp/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$actionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="col-sm-2 control-label">最后登录时间</label>
		<div class="col-sm-9">
			<p class="form-control-static">{$adminer['_lastlogin']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">最后登录 IP</label>
		<div class="col-sm-9">
			<p class="form-control-static">{$adminer['ca_lastloginip']}</p>
		</div>
	</div>
	<div class="form-group">
		<label for="ca_password" class="col-sm-2 control-label">登录密码</label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="ca_password" name="ca_password" placeholder="登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value="" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_password2" class="col-sm-2 control-label">再次输入密码</label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="ca_password2" placeholder="再次输入登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value="" maxlength="32" />
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-9">
			<button type="submit" class="btn btn-primary">修改</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
<script type="text/javascript" src="{$staticUrl}/js/md5.js"></script>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-adminer-edit').submit(function(){

		var	password	=	$.trim(jQuery('#ca_password').val());
		var	password2	=	jQuery('#ca_password2').val();

		if (password.length <= 0) {
			alert('请输入登录密码');
			return false;
		}
		if (password.length > 0) {
			if (password != password2) {
				alert('两次输入的密码不一致，请确认输入是否正确');
				return false;
			}
			
			var	pwd	=	hex_md5(password);
			jQuery('#ca_password').val(pwd);
			jQuery('#ca_password2').val(pwd);
		}
		return true;
	});
});
</script>

{include file='admincp/footer.tpl'}