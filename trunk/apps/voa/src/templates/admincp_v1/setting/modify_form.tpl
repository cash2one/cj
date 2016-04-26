{include file='admincp/header.tpl'}

<form class="form-horizontal font12" role="form" method="post" action="{$modifyFormActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel panel-warning">
	<div class="panel-heading"><strong>{$operation_list[$module][$operation][$module_plugin_id]['name']}</strong></div>
	<div class="panel-body">
		{$formRows}
{if isset($module_plugin['cp_identifier'])}
	{if $module_plugin['cp_identifier'] == 'askoff'}
		{include file='admincp/setting/form_askoff.tpl'}
	{elseif $module_plugin['cp_identifier'] == 'reimburse'}
		{include file='admincp/setting/form_reimburse.tpl'}
	{elseif $module_plugin['cp_identifier'] == 'footprint'}
		{include file='admincp/setting/form_footprint.tpl'}
	{elseif $module_plugin['cp_identifier'] == 'sign'}
		{include file='admincp/setting/form_sign.tpl'}
	{/if}
{/if}
		<div class="form-group">
			<div class="col-sm-offset-{$formLeftCols} col-sm-{$formRightCols}">
				<button type="submit" class="btn btn-primary">保存</button>
				&nbsp;&nbsp;
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
		</div>
	</div>
</div>
</form>

{include file='admincp/footer.tpl'}