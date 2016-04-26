{include file='cyadmin/header.tpl'}

<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-11" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-13" />
	</colgroup>
	<thead>
		<tr>
			<th>状态</th>
			<th>管理组名称</th>
			<th>描述</th>
			<th>更新时间</th>
			<th>操作</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th colspan="5"></th>
		</tr>
	</tfoot>
	<tbody>
{foreach $adminergroup_list as $cag_id => $cag}
	{if $cag_id}
		<tr{if $cag['cag_enable'] == $system_group} class="text-danger"{/if}>
			<td>{$cag['_enable']}</td>
			<td>{$cag['cag_title']|escape}</td>
			<td>{$cag['cag_description']|escape}</td>
			<td>{$cag['_updated']}</td>
			<td>
			{if $delete_url_base && $cag['cag_enable'] != $system_group}
				{$base->show_link($delete_url_base, $cag_id, '删除', 'fa-times', 'class="text-danger _delete"')}
			{else}
				{$base->show_link(false, $cag_id, '删除', 'fa-times')}
			{/if} | 
			{$base->show_link($edit_url_base, $cag_id, '编辑', 'fa-edit')}
			</td>
		</tr>
	{/if}
{foreachelse}
		<tr>
			<td colspan="5" class="warning">暂无管理组设定，请添加新管理组。</td>
		</tr>
{/foreach}
	</tbody>
</table>

{include file='cyadmin/footer.tpl'}