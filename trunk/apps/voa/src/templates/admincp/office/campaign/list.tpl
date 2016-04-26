{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<script>
						init.push(function () {	
							var options = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range').datepicker(options);
						});
					</script>
					<div class="input-daterange input-group" style="width: 290px;	display: inline-table;vertical-align:middle;">
						<label class="vcy-label-none" for="overtime_begin">截止时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="overtime_begin" name="overtime_begin"   placeholder="开始日期" value="{$searchBy['overtime_begin']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="overtime_end" name="overtime_end" placeholder="结束日期" value="{$searchBy['overtime_end']|escape}" />
						</div>
					</div>
					<span class="space"></span>
					<label class="vcy-label-none" for="subject">活动主题：</label>
					<input type="text" class="form-control form-small" id="title" name="subject"  value="{$searchBy['subject']|escape}" maxlength="54"  style="width:170px;"/>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<div class="input-daterange input-group" style="width: 290px;	display: inline-table;vertical-align:middle;">
						<label class="vcy-label-none" for="typeid">　　分类：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							{if count($cats)>0}
							<select id="typeid" name="typeid" class="form-control form-small">
								<option value="-1">请选择</option>
									{foreach $cats as $_key => $_name}
										<option value="{$_key}"{if $searchBy['typeid'] == $_key} selected="selected"{/if}>{$_name|escape}</option>
									{/foreach}
							</select>
							{/if}
						</div>
					</div>
					<span class="space"></span>
					<label class="vcy-label-none" for="status">　　状态：</label>
					<select id="is_push" name="is_push" class="form-control form-small" style="width:170px;">
						<option value="-1">请选择</option>
						<option value="1"{if $searchBy['is_push'] == 1} selected="selected"{/if}>已发布</option>
						<option value="0"{if $searchBy['is_push'] == 0} selected="selected"{/if}>草稿</option>
					</select>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<button type="button" onclick="location.href='{$listAllUrl}'" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 所有活动</button>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
<form class="form-horizontal" role="form" method="post" action="javascript:;">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-10" />
		<col class="t-col-5" />
		<col class="t-col-10" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th class="text-left">活动主题</th>
			<th>活动分类</th>
			<th>截止日期</th>
			<th>状态</th>
			<th>分享人数</th>
			<th>分享次数</th>
			<th>报名人数</th>
			<th>签到人数</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2" class="text-left">{if $deleteUrlBase}<button id="batch_delete" type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="8" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" class="delete" name="delete[]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td class="text-left"><a href="{$viewUrl}?id={$_data['id']}">{$_data['subject']}</a></td>
			<td>{$_data['_type']}</td>
			<td>{$_data['_overtime']}</td>
			<td>{$_data['_is_push']}</td>
			<td>{$_data['share']}</td>
			<td>{$_data['hits']}</td>
			<td>{$_data['regs']}</td>
			<td>{$_data['signs']}</td>
			<td>
				<a rel="{$deleteUrlBase}?id={$_data['id']}" href="javascript:;" class="text-danger delete"><i class="fa fa-times"></i> </a> | 
				<a href="{$editUrlBase}?id={$_data['id']}"><i class="fa fa-edit"></i> </a>
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="99" class="warning">{if $issearch}未搜索到指定条件的活动{else}暂无任何活动{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>

<style>
#growls{
	right:30px;
	top:100px;
}					
</style>
<script>
var delUrl = '{$deleteUrlBase}';
{literal}
$(function (){
	//单个删除
	$('a.delete').click(function (){
		var thistr = $(this).closest('tr');
		if (!confirm('确定删除“'+thistr.find('td:eq(1)').text()+'”？')) {
			return false;
		}
		$.getJSON(this.rel, function (json){
			if (json.state) {
				alert('活动已删除');
				thistr.remove();
			} else {
				alert(json.info);
			}
		});
	});
	//批量删除
	$('#batch_delete').click(function (){
		if($('input.delete:checked').length == 0) {
			return alert('请选择要删除的活动');
		}
		if (!confirm('确定删除所选活动?')) {
			return false;
		}
		var data = $('form:last').serialize();
		
		$.post(delUrl, data, function (json){
			if (json.state) {
				$('input.delete:checked').each(function (i, e){
					$(e).closest('tr').remove();
				});
			} else {
				alert(json.info);
			}
		}, 'json');
	});
});
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}