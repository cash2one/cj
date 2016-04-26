{include file='cyadmin/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$form_action_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="ca_username" class="col-sm-2 control-label">登录名</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="ca_username" name="ca_username" placeholder="用于后台登录的登录名" value="{$adminer['ca_username']|escape}" maxlength="15" />
		</div>
	</div>
{if $ca_id}
	<div class="form-group">
		<label class="col-sm-2 control-label">最后登录时间</label>
		<div class="col-sm-10">
			<p class="form-control-static">{$adminer['_lastlogin']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">最后登录 IP</label>
		<div class="col-sm-10">
			<p class="form-control-static">{$adminer['ca_lastloginip']}</p>
		</div>
	</div>
{/if}
	<div class="form-group">
		<label for="ca_password" class="col-sm-2 control-label">登录密码</label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="ca_password" name="ca_password" placeholder="登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value="" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_password2" class="col-sm-2 control-label">再次输入密码</label>
		<div class="col-sm-10">
			<input type="password" class="form-control" id="ca_password2" placeholder="再次输入登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value="" maxlength="32" />
		</div>
	</div>
	<div class="form-group">
		<label for="cagid" class="col-sm-2 control-label">所属管理组</label>
		<div class="col-sm-10">
			<select id="cagid" name="cag_id" size="1" class="selectpicker bla bla bli" data-width="auto"{if $adminer['ca_locked'] == $system_adminer} disabled="disabled"{/if}>
				<option value="0">请选择……</option>
{foreach $adminergroup_list as $_cag_id => $_cag}
	{if $_cag_id > 0}
				<option value="{$_cag_id}"{if $_cag_id == $adminer['cag_id']} selected="selected"{/if}>{$_cag['cag_title']|escape}</option>
	{/if}
{/foreach}
			</select>
			{$add_adminergroup_url}
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">允许登录</label>
		<div class="col-sm-10">
{foreach $adminer_locked_map as $_id => $_n}
			<div class="radio-inline">
				<label class="vcy-label-none vcy-text-normal">
					<input type="radio" id="cg_locked_{$_id}" name="ca_locked" value="{$_id}"{if $_id == $adminer['ca_locked']} checked="checked"{/if}{if $adminer['ca_locked']==$system_adminer || ($adminer['ca_locked'] != $system_adminer && $_id == $system_adminer)} disabled="disabled"{/if} />
					{$_n}
				</label>
			</div>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<label for="ca_realname" class="col-sm-2 control-label">真实姓名</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="ca_realname" name="ca_realname" placeholder="输入真实姓名，可以为空" value="{$adminer['ca_realname']|escape}" maxlength="54" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_mobilephone" class="col-sm-2 control-label">手机号码</label>
		<div class="col-sm-10">
			<input type="tel" class="form-control" id="ca_mobilephone" name="ca_mobilephone" placeholder="输入手机号码，可以为空" value="{$adminer['ca_mobilephone']|escape}" maxlength="11" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_mobilephone" class="col-sm-2 control-label">电子邮箱</label>
		<div class="col-sm-10">
			<input type="email" class="form-control" name="ca_email" placeholder="输入电子邮箱" value="{$adminer['ca_email']|escape}" maxlength="20" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_mobilephone" class="col-sm-2 control-label">职位</label>
		<div class="col-sm-10">
			<label><input type="radio" name="ca_job" {if $adminer['ca_job'] == 1}checked{/if} value="1"/>主管</label>
			<label><input type="radio" name="ca_job" {if $adminer['ca_job'] == 2}checked{/if} value="2"/>销售人员</label>
		</div>
	</div>
	<div class="form-group">
		<label for="ca_mobilephone" class="col-sm-2 control-label">上级主管</label>
		<div class="col-sm-10">
			<select class="selectpicker bla bla bli" name="upid">
				<option>请选择...</option>
				{foreach $leader_list as $key=>$val}
				<option value="{$key}" {if $leader_id == $key}selected{/if}>{$val}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">{if $ca_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
<script type="text/javascript" src="{$static_url}/js/md5.js"></script>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-adminer-edit').submit(function(){
		if ($.trim(jQuery('#ca_username').val()) == '') {
			alert('请输入管理员登录名');
			return false;
		}
		var	password = $.trim(jQuery('#ca_password').val());
		var	password2 = jQuery('#ca_password2').val();
		var	ca_id = 0;
		var	match = (jQuery('#form-adminer-edit').attr('action')).match(/ca_id=(\d+)/);
		if (match !== null && match[1] > 0) {
			ca_id = match[1];
		}
		/** 新增管理员则密码必须填写 */
		if (!ca_id && password.length <= 0) {
			alert('请输入登录密码');
			return false;
		}
		if (password.length > 0) {
			if (password != password2) {
				alert('两次输入的密码不一致，请确认输入是否正确');
				return false;
			}
			
			var	pwd = hex_md5(password);
			var	hex_md5_string = hex_md5(pwd.substr(16) + password);
			jQuery('#ca_password').val(hex_md5_string);
			jQuery('#ca_password2').val(hex_md5_string);
		}
		return true;
	});
});
</script>

{include file='cyadmin/footer.tpl'}