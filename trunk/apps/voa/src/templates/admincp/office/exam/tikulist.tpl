{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索题库</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_nt_subject">　题库名称：</label>
					<input type="text" class="form-control form-small" id="id_name" name="name" placeholder="输入关键词" value="{$search_conds['name']|escape}" maxlength="30" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_nt_subject">　创建人：</label>
					<input type="text" class="form-control form-small" id="id_username" name="username" placeholder="输入关键词" value="{$search_conds['username']|escape}" maxlength="30" />
					<span class="space"></span>
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">创建时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_begintime" name="begintime" placeholder="开始日期" value="{$search_conds['begintime']|escape}" autocomplete="off" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_endtime" name="endtime" placeholder="结束日期" value="{$search_conds['endtime']|escape}" autocomplete="off" />
						</div>
					</div>
					<script>
						init.push(function () {
							
							var options = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options);
							
							
						});
					</script>
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$tiku_url}" role="button" class="btn btn-default form-small form-small-btn">全部题库</a>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			题库列表
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
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-8" />
		<col class="t-col-14" />
		<col class="t-col-19" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>题库名称</th>
			<th>单选题数量</th>
			<th>多选题数量</th>
			<th>判断题数量</th>
			<th>填空题数量</th>
			<th>总数量</th>
			<th>创建人</th>
			<th>创建时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">
				{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}			
			</td>
			<td colspan="8" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['name']|escape}</td>
			<td>{$_data['dan_num']}</td>
			<td>{$_data['duo_num']}</td>
			<td>{$_data['pan_num']}</td>
			<td>{$_data['tian_num']}</td>
			<td>{$_data['num']}</td>
			<td>{$_data['username']}</td>
			<td>{rgmdate($_data['created'],'Y/m/d H:i')}</td>
			<td>
				{$base->linkShow($viewtm_url, $_id, '查看题目', 'fa-eye', '')} |
				{$base->linkShow($addtm_url, $_id, '添加题目', 'fa-edit', '')} |
				{$base->linkShow($addtiku_url, $_id, '设置题库名称', 'fa-edit', '')} |
				{$base->linkShow($deletetiku_url, $_id, '删除', 'fa-times', 'class="text-danger _delete"')}
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="10" class="warning">{if $issearch}未搜索到指定条件的题库数据{else}暂无任何题库数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>
<script type="text/javascript">
$('._delete').bind('click', function () {
	if (!confirm("您确认要删除此题库？")) {
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
