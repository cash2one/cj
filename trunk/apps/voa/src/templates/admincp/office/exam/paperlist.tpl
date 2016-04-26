{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索试卷</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_nt_subject">　试卷名称：</label>
					<input type="text" class="form-control form-small" id="id_name" name="name" placeholder="输入关键词" value="{$search_conds['name']|escape}" maxlength="30" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_nt_subject">　试卷状态：</label>
					<select name="status" class="form-control form-small">
						<option value="-1"{if $search_conds['status'] === '-1'} selected{/if}>请选择</option>
						<option value="0"{if $search_conds['status'] === '0'} selected{/if}>草稿</option>
						<option value="1"{if $search_conds['status'] === '1'} selected{/if}>未开始</option>
						<option value="2"{if $search_conds['status'] === '2'} selected{/if}>已开始</option>
						<option value="3"{if $search_conds['status'] === '3'} selected{/if}>已结束</option>
						<option value="4"{if $search_conds['status'] === '4'} selected{/if}>已终止</option>
					</select>
					<span class="space"></span>
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">考试时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_begin_time" name="begin_time"   placeholder="开始日期" value="{$search_conds['begin_time']|escape}" autocomplete="off" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_end_time" name="end_time" placeholder="结束日期" value="{$search_conds['end_time']|escape}" autocomplete="off" />
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
					<a href="{$paperlist_url}" role="button" class="btn btn-default form-small form-small-btn">全部试卷</a>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			试卷列表
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
			<th>试卷名称</th>
			<th>考试时间</th>
			<th>考试时长</th>
			<th>试卷总分</th>
			<th>及格分数</th>
			<th>试卷状态</th>
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
			<td>{$base->linkShow($viewpaper_url, $_id, $_data['name'], '', '')}</td>
			<td>
				{if !empty($_data['begin_time'])}
					{rgmdate($_data['begin_time'], 'Y/m/d H:i')} -
					{rgmdate($_data['end_time'], 'Y/m/d H:i')}
				{else}
				-
				{/if}</td>
			<td>{if !empty($_data['paper_time'])}{$_data['paper_time']}{else}-{/if}</td>
			<td>{$_data['total_score']}</td>
			<td>{$_data['pass_score']}</td>
			<td>{$_data['status_show']}</td>
			<td>{$_data['username']}</td>
			<td>{rgmdate($_data['created'], 'Y/m/d H:i')}</td>
			<td>
				{$base->linkShow($addpaper_url, $_id, '编辑', 'fa-edit', '')} |
				{if $_data['status'] == 1&&time()>$_data['begin_time']&&time()<$_data['end_time']}
					<a href="javascript:;" onclick="stopPaper({$_id})"><i class="fa fa-ban"></i> 提前终止</a>
				{/if}
				{if $_data['status'] != 0&&time()>$_data['begin_time']}
					{$base->linkShow($tjdetail_url, $_id, '查看统计', 'fa-eye', '')} |
				{/if}
				{$base->linkShow($deletepaper_url, $_id, '删除', 'fa-times', 'class="text-danger _delete"')}
				
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="10" class="warning">{if $issearch}未搜索到指定条件的试卷数据{else}暂无任何试卷数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>


<div id="myModal" class="modal fade" tabindex="-1" role="dialog" style="display: none;">
	<form class="form-horizontal" role="form" method="get" action="{$stoppaper_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<input type="hidden" name="id" value="" id="stop_id"/>
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h4 class="modal-title" id="myModalLabel">提前终止</h4>
			</div>
			<div class="modal-body padding-sm">
				<textarea  class="form-control form-small" name="reason" placeholder="终止的原因" rows="4" ></textarea>
			</div>
			<!-- / .modal-body -->
			<div class="modal-footer text-right">
				<button type="submit" class="btn btn-default btn-sm btn-primary">确定</button>									
			</div>
		</div>
		<!-- / .modal-content -->
	</div>
	<!-- / .modal-dialog -->
	</form>
</div>
<!-- /.modal -->

<script type="text/javascript">
$('._delete').bind('click', function () {
	if (!confirm("您确认要删除此试卷？")) {
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

function stopPaper(id){
	$('#stop_id').val(id);
	$('#myModal').modal();
}
</script>
{include file="$tpl_dir_base/footer.tpl"}
