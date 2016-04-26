{include file="$tpl_dir_base/header.tpl"}
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
{if $displayorder_edit_url}
<form class="form-horizontal" role="form" method="post" action="{$displayorder_edit_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
{/if}

<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col width="90" />
		<col />
		<col width="150" />
		<col width="90" />
		<col width="120" />
	</colgroup>
	<thead>
		<tr>
			<th>显示顺序</th>
			<th>部门名称</th>
			<th>ID 信息</th>
			<th>员工数</th>
			<th>操作</th>
		</tr>
	</thead>
{if $department_list}
	<tfoot>
		<tr>
			<td colspan="5" class="text-right"><button type="submit" class="btn btn-primary"{if !$displayorder_edit_url} readonly="readonly" disabled="disabled"{/if}>更新排序</button></td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $department_list as $_cd_id => $_cd}
		<tr>
			<td><label class="px-single"><input type="text" class="form-control" class="px" name="displayorder[{$_cd_id}]" placeholder="0到99整数" value="{$_cd['cd_displayorder']}" maxlength="2"{if !$displayorder_edit_url} readonly="readonly"{/if} /><span class="lbl"> </span></label></td>
			<td><p class="form-control-static font14">{$_cd['cd_name']|escape}</p></td>
			<td>{$_cd['cd_qywxid']}</td>
			<td>{$base->linkShow($addressbook_url_base, $_cd_id, $_cd['cd_usernum'], 'fa-database', ' title="员工数"')}</td>
			<td>
			{$base->linkShow($delete_url_base, $_cd_id, '删除', 'fa-trash-o', 'class="_delete"')} | {$base->linkShow($edit_url_base, $_cd_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="5" class="warning">暂无部门信息，请添加新部门。</td>
		</tr>
{/foreach}
	</tbody>
</table>
{if $displayorder_edit_url}
</form>
{/if}
</div>
{include file="$tpl_dir_base/footer.tpl"}