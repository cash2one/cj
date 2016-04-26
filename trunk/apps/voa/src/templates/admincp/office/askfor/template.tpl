{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">				
			<ul class="nav nav-pills text-sm">
				<li><a href="javascript:void(0);" onclick="is_use()">启用</a></li>
				<li><a href="javascript:void(0);"  onclick="unuse()">禁用</a></li>
				<li><a href="javascript:void(0);"  onclick="delete_templates()">删除</a></li>
				<li><a href="{$addBaseUrl}"><i class="fa fa-plus"></i>&nbsp;添加流程</a></li>
			</ul>										
		</div>
	</div>
<form class="form-horizontal" role="form" method="post" action="{$deleteBaseUrl}" id="action_form">
<input type="hidden" name="formhash" value="{$formhash}" />
<input type="hidden" name="action" value="" id="action"/>
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');" /><span class="lbl">全选</span></label></th>
			<th>流程名称</th>
			<th>状态</th>
			<th>创建人</th>
			<th>创建时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}" /><span class="lbl"> </span></label></td>
			<td>{$_data['name']|escape}</td>
			<td>{$_data['is_use']}</td>			
			<td>{$_data['creator']|escape}</td>
			<td>{$_data['created']}</td>
			<td>
				{$base->linkShow($deleteOneUrl, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($editBaseUrl, $_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">暂无任何审批流程</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>
<script type="text/javascript">
	function is_use() {
		$('#action').val('is_use');
		$('#action_form').submit();
	}

	function unuse() {
		$('#action').val('unuse');
		$('#action_form').submit();
	}

	function delete_templates() {
		$('#action').val('delete');
		$('#action_form').submit();
	}
</script>
{include file="$tpl_dir_base/footer.tpl"}
