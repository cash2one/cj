{include file='cyadmin/header.tpl'}
{include file='cyadmin/data/template/menu.tpl'}
<div class="panel panel-default">

<div class="panel-heading">模板列表
<button type="button" class="close"><span
	class="glyphicon glyphicon glyphicon-chevron-down"></span></button>
</div>

<div class="panel-body">

<form action="{$form_url}" method="post">
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-2" />
		<col class="t-col-30" />
		<col class="t-col-15" />
		<col class="t-col-15" />


	</colgroup>
	<thead>
		<tr>
			<th><input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span></th>
			<th>模板标题</th>
			<th>创建时间</th>
			<th>操作</th>

		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan = '1' class= "text-right"><button name="submit" value="1" type="submit"
	class="btn btn-primary  input-sm">批量删除</button></td>
			<td colspan="8" class="text-right">{$multi}</td>
		</tr>
	</tfoot>
	<tbody>
		{foreach $data as $val}
		<tr>
			<td><input type="checkbox" class="px" name="delete[{$val['ne_id']}]" value="{$val['ne_id']}" /></td>

			<td>{$val['title']}</td>
			<td>{$val['_created']}</td>

			<td>
				{$base->show_link($view_url_base, $val['ne_id'], '详情', 'fa-eye')} |
				{$base->show_link($add_url_base, $val['ne_id'], '编辑', 'fa-edit')} |
				{$base->show_link($delete_url_base, $val['ne_id'], '删除', 'fa-times')}
			</td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
		{/foreach}
	</tbody>
</table>
<div class="control-label col-sm-1">

	</form>
</div>
</div>
</div>
{include file='cyadmin/footer.tpl'}
