{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post" action="{$formActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label class="col-sm-2 control-label">名片创建者</label>
		<div class="col-sm-10">
			<p class="form-control-static"><strong>{$namecard['_username']|escape}</strong> &nbsp; 更新自 {$namecard['_updated']}</p>
		</div>
	</div>
	<div class="form-group">
		<label for="ncf_name" class="col-sm-2 control-label">所在群组</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="ncf_name" name="ncf_name" placeholder="名片夹所在的群组" value="{$namecard['_folder']|escape}" maxlength="30" disabled="disabled" />
		</div>
	</div>
	<div class="form-group">
		<label for="ncc_name" class="col-sm-2 control-label">公司</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="ncc_name" name="ncc_name" placeholder="所在公司名" value="{$namecard['_company']|escape}" maxlength="30" disabled="disabled" />
		</div>
	</div>
	<div class="form-group">
		<label for="ncj_name" class="col-sm-2 control-label">职务</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="ncj_name" name="ncj_name" placeholder="担任职务" value="{$namecard['_job']|escape}" maxlength="30" disabled="disabled" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_realname" class="col-sm-2 control-label">真实姓名</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="nc_realname" name="nc_realname" placeholder="真实姓名" value="{$namecard['nc_realname']|escape}" maxlength="30" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_mobilephone" class="col-sm-2 control-label">手机号码</label>
		<div class="col-sm-10">
			<input type="tel" class="form-control" id="nc_mobilephone" name="nc_mobilephone" placeholder="手机号码" value="{$namecard['nc_mobilephone']|escape}" maxlength="12" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_wxuser" class="col-sm-2 control-label">微信号</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="nc_wxuser" name="nc_wxuser" placeholder="微信号" value="{$namecard['nc_wxuser']|escape}" maxlength="40" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_gender_{$namecard['nc_gender']}" class="col-sm-2 control-label">性别</label>
		<div class="col-sm-10">
{foreach $base->_namecard_gender as $_id => $_name}
			<label class="radio-inline"><input type="radio" id="nc_gender_{$_id}" name="nc_gender" value="{$_id}"{if $namecard['nc_gender']==$_id} checked="checked"{/if} /> {$_name}</label>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<label for="nc_active_{$namecard['nc_active']}" class="col-sm-2 control-label">在职状态</label>
		<div class="col-sm-10">
{foreach $base->_namecard_active as $_id => $_name}
			<label class="radio-inline"><input type="radio" id="nc_active_{$_id}" name="nc_active" value="{$_id}"{if $namecard['nc_active']==$_id} checked="checked"{/if} /> {$_name}</label>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<label for="nc_telephone" class="col-sm-2 control-label">电话号码</label>
		<div class="col-sm-10">
			<input type="tel" class="form-control" id="nc_telephone" name="nc_telephone" placeholder="电话号码" value="{$namecard['nc_telephone']|escape}" maxlength="12" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_email" class="col-sm-2 control-label">邮箱</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="nc_email" name="nc_email" placeholder="email" value="{$namecard['nc_email']|escape}" maxlength="40" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_qq" class="col-sm-2 control-label">QQ</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="nc_qq" name="nc_qq" placeholder="qq" value="{$namecard['nc_qq']|escape}" maxlength="12" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_birthday" class="col-sm-2 control-label">生日</label>
		<div class="col-sm-10">
			<input type="date" class="form-control" id="nc_birthday" name="nc_birthday" value="{$namecard['nc_birthday']|escape}" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_address" class="col-sm-2 control-label">地址</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="nc_address" name="nc_address" value="{$namecard['nc_address']|escape}" maxlength="250" />
		</div>
	</div>
	<div class="form-group">
		<label for="nc_remark" class="col-sm-2 control-label">备注</label>
		<div class="col-sm-10">
			<textarea class="form-control" id="nc_remark" name="nc_remark" rows="3">{$namecard['nc_remark']|escape}</textarea>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">保存</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}