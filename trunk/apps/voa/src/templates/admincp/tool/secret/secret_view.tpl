{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="panel panel-default font12">
	<div class="panel-heading">主题《{$secret['st_subject']|escape}》</div>
	<div class="panel-body">
		<p>
		{if !empty($module_plugin_set['is_secret'])}
			<span class="label label-primary font12">发言人：{$thread['m_username']|escape}</span>
			<span class="space"></span>
		{/if}
			<span class="label label-primary font12">发表时间：{$thread['_created']}</span>
			<span class="space"></span>
			<span class="label label-info font12">回复数：{$post_count}</span>
			<span class="space"></span>
			{$base->linkShow($delete_url_base, $st_id, '删除主题及回复', 'fa-trash-o', 'class="_delete"')}
		</p>
		<p>
			{$thread['_message']}
		</p>
	</div>
</div>
<div class="panel panel-default font12">
	<div class="panel-body">
{if $post_count}
	<table class="table table-striped table-hover">
		<colgroup>
			<col class="t-col-6" />
			<col />
		</colgroup>
		<thead>
			<tr>
				<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'stp_ids');"{if !$delete_url_base || !$post_count} disabled="disabled"{/if} /> 删除</label></th>
				<th>回复内容</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td>{if $delete_url_base}<button type="submit" class="btn btn-primary"><i class="fa fa-trash-o"></i> 批量删除</button>{/if}</td>
				<td class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
		<tbody>
	{foreach $post_list as $stp_id => $stp}
			<tr>
				<td>
					<label class="checkbox-inline">
						<input type="checkbox" name="stp_ids[{$stp_id}]" value="{$stp_id}"{if !$form_delete_url} disabled="disabled"{/if} />
					</label>
				</td>
				<td>
					{if $stp['stp_subject']}<span class="help-block"><strong>{$stp['stp_subject']|escape}</strong></span>{/if}
					<span class="help-block">
		{if !empty($module_plugin_set['is_secret'])}
						<span class="label label-default font12">回复人：{$stp['m_username']|escape}</span>
		{/if}
						<span class="label label-default font12">回复时间：{$stp['_created']}</span>
					</span>
					{$stp['_message']}
				</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
{else}
	<p class="alert alert-warning">当前话题暂无回复内容</p>
{/if}
</div>
</div>

{include file="$tpl_dir_base/footer.tpl"}