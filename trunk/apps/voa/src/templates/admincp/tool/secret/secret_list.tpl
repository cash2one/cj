{include file="$tpl_dir_base/header.tpl"}

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-15" />
		<col class="t-col-8" />
		<col />
{if !empty($module_plugin_set['is_secret'])}
		<col class="t-col-15" />
{/if}
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none checkbox"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />删除</label></th>
			<th>发布时间</th>
			<th>回复数</th>
			<th>标题</th>
{if !empty($module_plugin_set['is_secret'])}
			<th>发布者</th>
{/if}
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $delete_url_base}<button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-trash-o"></i> 批量删除所选</button>{/if}</td>
	{if !empty($module_plugin_set['is_secret'])}
			<td colspan="4" class="text-right vcy-page">{$multi}</td>
	{else}
			<td colspan="3" class="text-right vcy-page">{$multi}</td>
	{/if}
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /></td>
			<td>{$_data['_created']}</td>
			<td>{$_data['_count']}</td>
			<td>{$_data['st_subject']|escape}</td>
	{if !empty($module_plugin_set['is_secret'])}
			<td>{$_data['m_username']|escape}</td>
	{/if}
			<td>
				{$base->linkShow($delete_url_base, $_id, '删除', 'fa-trash-o', 'class="_delete"')} | 
				{$base->linkShow($view_url_base, $_id, '浏览', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="{if !empty($module_plugin_set['is_secret'])}6{else}5{/if}" class="warning">暂无秘密主题列表数据</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file="$tpl_dir_base/footer.tpl"}