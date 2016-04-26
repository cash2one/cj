{include file='cyadmin/header.tpl'}

<form class="form-horizontal font12" role="form" method="post" action="{$form_action_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel panel-warning">
	<div class="panel-heading"><strong>{$operation_list[$module][$operation]['name']}</strong></div>
	<div class="panel-body">
		{$form_rows}
{if isset($module_plugin['cp_identifier'])}
	{if $module_plugin['cp_identifier'] == 'askoff'}
		{include file='admincp/setting/form_askoff.tpl'}
	{elseif $module_plugin['cp_identifier'] == 'reimburse'}
		{include file='admincp/setting/form_reimburse.tpl'}
	{elseif $module_plugin['cp_identifier'] == 'footprint'}
		{include file='admincp/setting/form_footprint.tpl'}
	{/if}
{/if}
		<div class="form-group">
			<div class="col-sm-offset-{$form_left_cols} col-sm-{$form_right_cols}">
				<button type="submit" class="btn btn-primary">保存</button>
				&nbsp;&nbsp;
				<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
			</div>
		</div>
	</div>
</div>
</form>

{include file='cyadmin/footer.tpl'}