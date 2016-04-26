{include file="$tpl_dir_base/header.tpl"}

<div class="table-light">
	<div class="table-header">
		<a href="{$addtm_url}" role="button" class="btn btn-info form-small form-small-btn" style="float: right; ">+添加题目</a>
		<div class="table-caption font12">
			{$tiku.name}-题目列表
		</div>

	</div>

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}?delete" onsubmit="return listSumbit();">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-26 "/>
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-14" />
		<col class="t-col-5" />
		<col class="t-col-19" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>名称</th>
			<th>类型</th>
			<th>分数</th>
			<th>答案</th>
			<th>排序</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">
				{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}			
			</td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['title']|escape}</td>
			<td>{$types[$_data['type']]}</td>
			<td>{$_data['score']}</td>
			<td>{$_data['answer']}</td>
			<td>{$_data['orderby']}</td>
			<td>
				{$base->linkShow($deletetm_url, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($updatetm_url, $_id, '编辑', 'fa-edit', '')}
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="10" class="warning">{if $issearch}未搜索到指定条件的题目数据{else}暂无任何题目数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>

<script type="text/javascript">
$('._delete').bind('click', function () {
	if (!confirm("您确认要删除此题目？")) {
        return false;
    }else{
    	return true;
    }
});
function listSumbit(){
	if (!confirm('您确认要删除吗？')){ 
		return false; 
	}else{ 
		return true; 
	} 
}
</script>

{include file="$tpl_dir_base/footer.tpl"}
