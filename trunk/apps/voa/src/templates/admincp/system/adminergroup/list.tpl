{include file="$tpl_dir_base/header.tpl"}
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col width="11%" />
		<col width="15%" />
		<col />
		<col width="15%" />
		<col width="13%" />
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
	
	<tbody>
{foreach $groupList as $cag_id => $cag}
		{if $cag_id}
		<tr>
			<td>{$cag['_enable']}</td>
			<td>{$cag['cag_title']|escape}</td>
			<td class="text-left">{$cag['cag_description']|escape}</td>
			<td>{$cag['_update']}</td>
			<td>
			{if $deleteUrlBase && $cag['cag_enable'] != $systemgroup}
				{$base->linkShow($deleteUrlBase, $cag_id, '删除', 'fa-times', 'class="text-danger _delete"')}
			{else}
				{$base->linkShow(false, $cag_id, '删除', 'fa-times')}
			{/if} | 
			{$base->linkShow($editUrlBase, $cag_id, '编辑', 'fa-edit')}
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
</div>
{include file="$tpl_dir_base/footer.tpl"}