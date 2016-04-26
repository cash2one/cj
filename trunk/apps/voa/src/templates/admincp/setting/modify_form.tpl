{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal font12" role="form" method="post"
	action="{$modifyFormActionUrl}">
	<input type="hidden" name="formhash" value="{$formhash}" /> 
	{if $flag}
	<ul class="nav nav-tabs font12">
		<li class="active"><a href="#list_proc" data-toggle="tab">{$operation_list[$module][$operation][$module_plugin_id]['name']}{$proc}&nbsp;
		</a></li>
		<li><a href="#list_comment" data-toggle="tab">{$operation_list[$module][$operation][$module_plugin_id]['name']}{$comment}&nbsp;
		</a></li>
	</ul>
	{/if}
	<div class="panel panel-warning">
		{if !$flag}
		<div class="panel-heading">
			<strong>{$operation_list[$module][$operation][$module_plugin_id]['name']}</strong>
		</div>
		{/if}
		<div class="panel-body">
			{if $flag}
			<div class="tab-content">
				<div class="tab-pane active" id="list_proc">{$formRows}</div>
				<div class="tab-pane" id="list_comment">
			{else} 
			    {$formRows}
			{/if} 
			
			{if isset($module_plugin['cp_identifier'])} 
				{if $module_plugin['cp_identifier'] == 'askoff'} 
					{include file="$tpl_dir_base/setting/form_askoff.tpl"} 
				{elseif $module_plugin['cp_identifier'] == 'reimburse'} 
					{include file="$tpl_dir_base/setting/form_reimburse.tpl"} 
				{elseif $module_plugin['cp_identifier'] == 'footprint'} 
					{include file="$tpl_dir_base/setting/form_footprint.tpl"} 
				{elseif $module_plugin['cp_identifier'] == 'sign'} 
					{include file="$tpl_dir_base/setting/form_sign.tpl"} 
				{elseif $module_plugin['cp_identifier'] == 'dailyreport'} 
					{include file="$tpl_dir_base/setting/form_dailyreport.tpl"} 
			    {/if} 
			{/if} 
			
			{if $flag}
			    </div>
			{/if}
				<div class="form-group">
					<div class="col-sm-offset-{$formLeftCols} col-sm-{$formRightCols}">
						<button type="submit" class="btn btn-primary">保存</button>
						&nbsp;&nbsp; <a href="javascript:history.go(-1);" role="button"
							class="btn btn-default">返回</a>
					</div>
				</div>
			{if $flag}
			</div>
			{/if}
		</div>
	</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}
