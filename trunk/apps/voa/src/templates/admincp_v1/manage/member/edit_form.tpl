{include file='admincp/header.tpl'}

<form id="form-member-edit" role="form" method="post" action="{$form_action_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" id="m_password" name="m_password" value="" />
	<table class="table font12">
		<colgroup>
			<col class="t-col-9" />
			<col class="t-col-16" />
			<col class="t-col-9" />
			<col class="t-col-16" />
			<col class="t-col-9"/>
			<col class="t-col-16" />
			<col class="t-col-9" />
			<col class="t-col-16" />
		</colgroup>
		<tfoot>
			<tr>
				<td colspan="8" class="text-center">
					<button type="submit" class="btn btn-primary">{if $m_uid}保存{else}添加{/if}</button>
					&nbsp;&nbsp;
					<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<th><label class="text-right vcy-label-middle text-danger" for="m_username">真实姓名 *</label></th>
				<td colspan="2"><input type="text" class="form-control" id="m_username" name="m_username" placeholder="请输入真实姓名，一旦提交将不可更改！" value="{$member['m_username']|escape}" maxlength="50"  required="required"{if $m_uid} disabled="disabled"{/if} /></td>
				<td colspan="2"><span class="help-block vcy-label-middle"><span class="text-warning">注：真实姓名一旦提交不可更改！</span></span></td>
				<td colspan="3">
{if $member['m_admincp']}
					<span class="help-block vcy-label-middle text-info">
						<strong class="text-danger">已允许使用后台管理</strong>
						<a href="#" role="button" class="btn btn-danger btn-xs" target="_blank">取消管理权限</a>
					</span>
{else}
					<span class="help-block vcy-label-middle" style="display:none !important;">
						<span class="text-info">不允许使用后台管理</span>
						<a href="#" role="button" class="btn btn-info btn-xs" target="_blank">设为管理员</a>
					</span>
{/if}
				</td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle text-danger" for="m_mobilephone">手机号码 *</label></th>
				<td><input type="tel" class="form-control" id="m_mobilephone" name="m_mobilephone" placeholder="手机号码" value="{$member['m_mobilephone']|escape}" maxlength="11"  required="required" /></td>
				<th><label class="text-right vcy-label-middle text-danger" for="m_email">邮箱 *</label></th>
				<td colspan="2"><input type="email" class="form-control" id="m_email" name="m_email" placeholder="Email" value="{$member['m_email']|escape}" maxlength="40"  required="required" /></td>
				<th><label class="text-right vcy-label-middle text-danger" for="input_password" style="display:none !important;">登录密码</label></th>
				<td colspan="2"><input type="text" class="form-control" id="input_password" placeholder="不修改，请留空" value="" style="display:none !important;" /></td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle text-danger" for="cd_id">所在部门 *</label></th>
				<td colspan="3">{$department_select}</td>
				<th><label class="text-right vcy-label-middle" style="display:none !important;">关联其他部门</label></th>
				<td colspan="3"></td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle">担任职务</label></th>
				<td>
					<select id="cj_id" name="cj_id" size="1" class="form-control">
						<option value="0">请选择……</option>
{foreach $job_list as $_id => $_v}
						<option value="{$_id}"{if $_id == $member['cj_id']} selected="selected"{/if}>{$_v['cj_name']}</option>
{/foreach}
					</select>
				</td>
				<th><label class="text-right vcy-label-middle" for="m_number">工号</label></th>
				<td><input type="number" class="form-control" id="m_number" name="m_number" value="{$member['m_number']|escape}" max="9999999999" min="0" /></td>
				<th><label class="text-right vcy-label-middle" for="m_active">在职状态</label></th>
				<td>
					<select id="m_active" name="m_active" size="1" class="form-control">
{foreach $active_list as $_id => $_name}
						<option value="{$_id}"{if $member['m_active'] == $_id} selected="selected"{/if}>{$_name}</option>
{/foreach}
					</select>
				</td>
				<th><label class="text-right vcy-label-middle" for="m_gender">性别</label></th>
				<td>
					<select id="m_gender" name="m_gender" size="1" class="form-control">
{foreach $gender_list as $_id => $_name}
						<option value="{$_id}"{if $member['m_gender'] == $_id} selected="selected"{/if}>{$_name}</option>
{/foreach}
					</select>
				</td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle" for="mf_qq">QQ</label></th>
				<td><input type="text" class="form-control" id="mf_qq" name="mf_qq" value="{$member['mf_qq']|escape}" maxlength="12" /></td>
				<th><label class="text-right vcy-label-middle" for="mf_weixinid">微信号</label></th>
				<td><input type="text" class="form-control" id="mf_weixinid" name="mf_weixinid" value="{$member['mf_weixinid']|escape}" maxlength="64" /></td>
				<th><label class="text-right vcy-label-middle" for="mf_telephone">电话号码</label></th>
				<td><input type="tel" class="form-control" id="mf_telephone" name="mf_telephone" value="{$member['mf_telephone']|escape}" maxlength="64" /></td>
				<th><label class="text-right vcy-label-middle" for="mf_birthday">生日</label></th>
				<td><input type="date" class="form-control" id="mf_birthday" name="mf_birthday" value="{$member['mf_birthday']|escape}" /></td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle" for="mf_address">住址</label></th>
				<td colspan="3"><input type="text" class="form-control" id="mf_address" name="mf_address" value="{$member['mf_address']|escape}" maxlength="255" /></td>
				<th><label class="text-right vcy-label-middle" for="mf_idcard">身份证号</label></th>
				<td colspan="3"><input type="text" class="form-control" id="mf_idcard" name="mf_idcard" value="{$member['mf_idcard']|escape}" maxlength="20" /></td>
			</tr>
			<tr>
				<th><label class="text-right vcy-label-middle" for="mf_remark">备注</label></th>
				<td colspan="7">
					<textarea class="form-control" rows="3" id="mf_remark" name="mf_remark">{$member['mf_remark']|escape}</textarea>
				</td>
			</tr>
		</tbody>
	</table>
</form>
<script type="text/javascript" src="{$staticUrl}/js/md5.js"></script>
<script type="text/javascript">
jQuery(function(){
	jQuery('#form-member-edit').submit(function(){
		var	password	=	jQuery('#newpassword').val();
		if ( password != '' ) {
			var	pwd	=	hex_md5(password);
			var	hex_md5_string	=	hex_md5(pwd.substr(16) + password);
			jQuery('#m_password').val(hex_md5_string);
		}
		return true;
	});
});
</script>
{include file='admincp/footer.tpl'}