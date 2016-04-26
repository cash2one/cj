{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索请假记录</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_username">申请人：</label>
					<input type="text" class="form-control form-small" id="id_ao_username" name="ao_username" placeholder="输入姓名" value="{$searchBy['ao_username']|escape}" maxlength="54" />
					<span class="space"></span>

{if $askoff_types}
					<select id="id_ao_type" name="ao_type" class="form-control form-small" data-width="auto">
						<option value="-1">请假类型</option>
	{foreach $askoff_types as $_key => $_name}
						<option value="{$_key}"{if $searchBy['ao_type'] == $_key} selected="selected"{/if}>{$_name|escape}</option>
	{/foreach}
					</select>
{/if}
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_ao_begintime">请假日期范围：</label>
				<!-- 	<input type="date" class="form-control form-small" id="id_ao_begintime" name="ao_begintime" value="{$searchBy['ao_begintime']|escape}" />
				<label class="vcy-label-none" for="id_ao_endtime"> 至 </label>
				<input type="date" class="form-control form-small" id="id_ao_endtime" name="ao_endtime" value="{$searchBy['ao_endtime']|escape}" /> -->
					<script>
						init.push(function () {	
							var options2 = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range').datepicker(options2);
						});
					</script>						
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
						<input type="text" class="input-sm form-control" id="id_ao_begintime" name="ao_begintime"   placeholder="开始日期" value="{$searchBy['ao_begintime']|escape}" />
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_ao_endtime" name="ao_endtime" placeholder="结束日期" value="{$searchBy['ao_endtime']|escape}" />
					</div>

				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_aopc_username">审批人：</label>
					<input type="text" class="form-control form-small" id="id_aopc_username" name="aopc_username" placeholder="输入姓名" value="{$searchBy['aopc_username']|escape}" maxlength="54" />
					<span class="space"></span>
					
{if $askoff_status}
					<select id="id_ao_status" name="ao_status" class="form-control form-small" data-width="auto">
						<option value="-1">审批状态</option>
	{foreach $askoff_status as $_key => $_name}
						<option value="{$_key}"{if $searchBy['ao_status'] == $_key} selected="selected"{/if}>{$_name|escape}</option>
	{/foreach}
					</select>
{/if}
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_ao_subject">请假申请关键词：</label>
					<input type="text" class="form-control form-small" id="id_ao_subject" name="ao_subject" placeholder="标题关键词" value="{$searchBy['ao_subject']|escape}" maxlength="30" />
					<span class="space"></span>
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
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
<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-10" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-10" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>申请人</th>
			<th>所在部门/职务</th>
			<th>类型/主题</th>
			<th>请假时长</th>
			<th>假期周期</th>
			<th>提交时间</th>
			<th>状态</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="7" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['_realname']|escape}</td>
			<td>{$_data['_department']|escape}<br />{$_data['_job']}</td>
			<td>[{$_data['_type']}]<span class="space"></span>{$_data['ao_subject']|escape}</td>
			<td>{$_data['_timespace']}</td>
			<td>{$_data['_begintime_ymdhi']}<br />{$_data['_endtime_ymdhi']}</td>
			<td>{$_data['_created']}</td>
			<td>{$_data['_status']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的请假申请信息{else}暂无任何请假申请信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}