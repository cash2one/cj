{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post" action="{$form_action_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="cd_name" class="col-sm-2 control-label text-danger">部门名称 *</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="cd_name" name="cd_name" placeholder="部门名称，不能超过{$name_rule[2]}个字符" value="{$department['cd_name']|escape}" maxlength="{$name_rule[2]}" required="required" />
			<span class="help-block">为保证效果推荐不超过 5个字符</span>
		</div>
	</div>
	{if 0 == $cd_id}
	<div class="form-group">
		<label for="cd_name" class="col-sm-2 control-label text-danger">所在部门</label>
		<div class="col-sm-10">
			{$department_select}
			<span class="help-block">如果是子部门, 请选择其上级部门</span>
		</div>
	</div>
	{else}
	<input type="hidden" name="cd_upid" value="{$department['cd_upid']}" />
	{/if}
	<div class="form-group">
		<label for="cd_displayorder" class="col-sm-2 control-label">显示顺序</label>
		<div class="col-sm-10">
			<input type="number" class="form-control" id="cd_displayorder" name="cd_displayorder" placeholder="设置 {$displayorder_rule[0]}到{$displayorder_rule[1]}中间的数值" value="{$department['cd_displayorder']}" min="{$displayorder_rule[0]}" max="{$displayorder_rule[1]}" />
			<span class="help-block">显示顺序，设置 {$displayorder_rule[0]}到{$displayorder_rule[1]} 中间的数值，数值越小显示越靠前</span>
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-primary">{if $cd_id}编辑{else}添加{/if}</button>
			<span class="space"></span>
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}