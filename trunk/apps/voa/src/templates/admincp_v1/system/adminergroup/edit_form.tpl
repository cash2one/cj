{include file='admincp/header.tpl'}

<form class="form-horizontal font12" role="form" method="POST" action="{$editTargetUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="form-group">
		<label for="cag_title" class="col-sm-2 control-label">管理组名称</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="cag_title" name="cag_title" placeholder="管理组名称" value="{$groupDetail['cag_title']|escape}" maxlength="32" />
		</div>
	</div>
	<div class="form-group">
		<label for="cag_enable_{$groupDetail['cag_enable']}" class="col-sm-2 control-label">启用状态</label>
		<div class="col-sm-10">
{foreach $groupEnableStatus as $_id => $_n}
			<div class="radio-inline">
				<label class="vcy-label-none vcy-text-normal"><input type="radio" id="cag_enable_{$_id}" name="cag_enable" value="{$_id}"{if $_id == $groupDetail['cag_enable']} checked="checked"{/if}{if $groupDetail['cag_enable']==$systemgroup || ($groupDetail['cag_enable'] != $systemgroup && $_id == $systemgroup)} disabled="disabled"{/if} />{$_n}</label>
			</div>
{/foreach}
		</div>
	</div>
	<div class="form-group">
		<label for="cag_description" class="col-sm-2 control-label">组描述</label>
		<div class="col-sm-10">
			<input type="text" class="form-control" id="cag_description" name="cag_description" placeholder="管理组描述文字，可以为空" value="{$groupDetail['cag_description']|escape}" maxlength="100" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">权限设置</label>
		<div class="col-sm-10">
{if $groupDetail['cag_enable'] == $systemgroup}
			<p class="form-control-static text-warning">
				该管理组为系统最高权限组，禁止修改设置。
			</p>
{else}
	{foreach $module_list as $_module => $_module_arr}
			<div class="panel panel-default">
				<div class="panel-heading">
					<label class="vcy-label-none vcy-text-normal">
						<input type="checkbox" id="act_{$_module}" onclick="javascript:checkAll(this,'cag_role[{$_module}');" />
						{$_module_arr['name']}
					</label>
				</div>
				<div class="panel-body font12">
		{foreach $operation_list[$_module] as $_operation => $_operation_arr_main}
			{if $_operation_arr_main@index != 0}
					<hr />
			{/if}
			{foreach $_operation_arr_main as $_operation_arr_id => $_operation_arr}
				{if $_operation_arr@index != 0}
					<hr />
				{/if}
				<div class="row">
					<div class="col-sm-2 text-left">
						<span class="checkbox-inline">
							<label class="vcy-label-none text-info">
								<input type="checkbox" id="{$_module}_{$_operation}" onclick="javascript:checkAll(this, 'cag_role[{$_module}_{$_operation}', 0);" />
								{$_operation_arr['name']}
							</label>
						</span>
					</div>
				{if isset($subop_list[$_module][$_operation][$_operation_arr_id])}
					{if count($subop_list[$_module][$_operation][$_operation_arr_id]) > 5}
					<div class="col-sm-10 text-left">
						<div class="row">
					{/if}
					{foreach $subop_list[$_module][$_operation][$_operation_arr_id] as $_subop => $_subop_arr}
							<div class="col-sm-2">
								<span class="checkbox-inline">
									<label class="vcy-label-none vcy-text-normal">
										<input type="checkbox" name="cag_role[{$_module}_{$_operation}_{$_subop}]" value="{$_module}_{$_operation}_{$_subop}"{if $groupDetail['cag_role'] && in_array($_subop_arr['id'],$groupDetail['cag_role'])} checked="checked"{/if} onchange="javascript:menuDefaultCheck('cag_role[{$_module}_{$_operation}_','{$_module}_{$_operation}_{$default_list['subop'][$_module][$_operation]['subop']}', this);" />
										{if $default_list['subop'][$_module][$_operation] == $_subop}
											<abbr title="默认功能，不选择此功能该模块其他功能均不可用">{$_subop_arr['name']}</abbr>
										{else}
											{$_subop_arr['name']}
										{/if}
									</label>
								</span>
							</div>
					{foreachelse}
							<div class="col-sm-3">
								<span class="checkbox-inline">
									<label class="vcy-label-none vcy-text-normal">
										<input type="checkbox" name="cag_role[{$_module}_{$_operation}]" value="{$_module}_{$_operation}"{if $groupDetail['cag_role'] && in_array($_operation_arr['id'],$groupDetail['cag_role'])} checked="checked"{/if} />
										{$_operation_arr['name']}
									</label>
								</span>
							</div>
					{/foreach}
					{if count($subop_list[$_module][$_operation][$_operation_arr_id]) > 5}
						</div>
					</div>
					{/if}
				{/if}
				</div>
			{/foreach}
		{/foreach}
				</div>
			</div>
	{/foreach}
{/if}
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" class="btn btn-lg btn-primary">{if $groupDetail['cag_id']}保存{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" class="btn btn-lg btn-default" role="button">返回</a>
		</div>
	</div>
</form>
<script type="text/javascript">
function menuDefaultCheck(namePrefix, defaultValue, current) {
	if (current.value == defaultValue) {
		if (!(jQuery(current).prop('checked'))) {
			jQuery('input[name^='+namePrefix.replace('[', '\\[')+']').prop('checked', false);
		}
	} else {
		jQuery('input[value='+defaultValue+']').prop('checked', true);
	}
}
</script>
{include file='admincp/footer.tpl'}