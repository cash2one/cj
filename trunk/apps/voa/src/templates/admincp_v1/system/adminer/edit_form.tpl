{include file='admincp/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$actionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="ca_username" class="col-sm-2 control-label">手机号</label>
		<div class="col-sm-9">
			<input type="tel" class="form-control" id="ca_mobilephone" name="ca_mobilephone" placeholder="手机号" value="{$adminer['ca_mobilephone']|escape}" maxlength="11" />
			<span class="help-block"><span class="text-danger">不填写真实手机号码将无法使用忘记密码找回功能</span></span>
		</div>
	</div>
	<div class="form-group">
		<label for="ca_email" class="col-sm-2 control-label">Email</label>
		<div class="col-sm-9">
			<input type="email" class="form-control" id="ca_email" name="ca_email" placeholder="邮箱地址" value="{$adminer['ca_email']|escape}" maxlength="45" />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_username" class="col-sm-2 control-label">姓名</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="ca_username" name="ca_username" placeholder="显示名称" value="{$adminer['ca_username']|escape}" maxlength="15" required="required" />
		</div>
	</div>
{if $ca_id}
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
{/if}
	<div class="form-group">
		<label for="ca_password" class="col-sm-2 control-label">登录密码</label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="ca_password" name="ca_password" placeholder="登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value=""{if !$ca_id} required="required"{/if} />
		</div>
	</div>
	<div class="form-group">
		<label for="ca_password2" class="col-sm-2 control-label">再次输入密码</label>
		<div class="col-sm-9">
			<input type="password" class="form-control" id="ca_password2" placeholder="再次输入登录密码{if $ca_id}，如果不修改密码，请留空{/if}" value="" maxlength="32"{if !$ca_id} required="required"{/if} />
		</div>
	</div>
	<div class="form-group">
		<label for="cagid" class="col-sm-2 control-label">所属管理组</label>
		<div class="col-sm-9">
			<select id="cagid" name="cag_id" size="1" class="selectpicker bla bla bli" data-width="auto"{if $adminer['ca_locked'] == $systemadminer} disabled="disabled"{/if}{if !$ca_id} required="required"{/if}>
				<option value="0">请选择……</option>
{foreach $groupList as $_cag_id => $_cag}
	{if $_cag_id > 0}
				<option value="{$_cag_id}"{if $_cag_id == $adminer['cag_id']} selected="selected"{/if}>{$_cag['cag_title']|escape}</option>
	{/if}
{/foreach}
			</select>
			{if !$ca_id}{$addAdminerGroupLink}{/if}
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">允许登录</label>
		<div class="col-sm-9">
{foreach $userLockedStatus as $_id => $_n}
			<div class="radio-inline">
				<label class="vcy-label-none vcy-text-normal">
					<input type="radio" id="cg_locked_{$_id}" name="ca_locked" value="{$_id}"{if $_id == $adminer['ca_locked']} checked="checked"{/if}{if $adminer['ca_locked']==$systemadminer || ($adminer['ca_locked'] != $systemadminer && $_id == $systemadminer)} disabled="disabled"{/if} />
					{$_n}
				</label>
			</div>
{/foreach}
		</div>
	</div>
<!--
{if $ca_id}
	<div class="form-group">
		<label for="cd_id" class="col-sm-2 control-label">所在部门</label>
		<div class="col-sm-9">
			<select id="cd_id" name="cd_id" size="1" class="selectpicker bla bla bli" data-width="auto" disabled>
				<option value="0"{if $adminer['cd_id']<=0} selected="selected"{/if}>请选择……</option>
	{foreach $departmenuList as $cd_id=>$cd}
				<option value="{$cd_id}"{if $cd_id == $adminer['cd_id']} selected="selected"{/if}>{$cd['cd_name']|escape}</option>
	{/foreach}
			</select>
		</div>
	</div>
{/if}
-->
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-9">
			<button type="submit" class="btn btn-primary">{if $ca_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>
<script type="text/javascript" src="{$staticUrl}/js/md5.js"></script>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-adminer-edit').submit(function(){
		/**if ( $.trim(jQuery('#ca_username').val()) == '' ) {
			alert('请输入管理员登录名');
			return false;
		}*/
		var	password	=	$.trim(jQuery('#ca_password').val());
		var	password2	=	jQuery('#ca_password2').val();
		var	ca_id		=	0;
		var	match		=	(jQuery('#form-adminer-edit').attr('action')).match(/ca_id=(\d+)/);
		if ( match !== null && match[1] > 0 ) {
			ca_id	=	match[1];
		}
		/** 新增管理员则密码必须填写 */
		if ( !ca_id && password.length <= 0 ) {
			alert('请输入登录密码');
			return false;
		}
		if ( password.length > 0 ) {
			if ( password != password2 ) {
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