{include file="$tpl_dir_base/header.tpl"}


<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索任务</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">发起人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="创建者" value="{$searchBy['m_username']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_p_subject">标题：</label>
					<input type="text" class="form-control form-small" id="id_p_subject" name="p_subject" placeholder="标题" value="{$searchBy['p_subject']|escape}" maxlength="30" />
					</div><span class="space"></span>
					<div class="form-group" >				
						<label class="vcy-label-none" for="id_rb_time_after">申请时间范围：</label>				
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
							<input type="text" class="input-sm form-control" id="id_rb_time_after" name="p_begintime" placeholder="开始日期" value="{$searchBy['p_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_p_endtime" name="rb_time_before" placeholder="结束日期" name="p_endtime" value="{$searchBy['p_endtime']|escape}" />
						</div>
					</div>

					<!-- <label class="vcy-label-none" for="id_p_begintime">日期：</label>
					<input type="date" class="form-control form-small" id="id_p_begintime" name="p_begintime" value="{$searchBy['p_begintime']|escape}" />
					<label class="vcy-label-none" for="id_p_endtime"> 至 </label>
					<input type="date" class="form-control form-small" id="id_p_endtime" name="p_endtime" value="{$searchBy['p_endtime']|escape}" /> -->

					<span class="space"></span><button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				
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
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-8" />
		<col />
		<col class="t-col-6" />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col class="t-col-8" />
		<col class="t-col-8" />
		<col class="t-col-12" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><!-- <label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} />删除</label> -->
			
				<label class="checkbox">
				<input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} />
				<span class="lbl">全选</span>
				</label>
		
			</th>
			<th>发起人</th>
			<th>名称</th>
			<th>状态</th>
			<th>起始时间</th>
			<th>进度</th>
			<th>总时间</th>
			<th>剩余时间</th>
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
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} class="px" /><span class="lbl"> </span></label></td>
			<td>{$_data['m_username']|escape}<!-- <br />{$base->linkShow($advancedUrlBase, $_id, '推进', 'fa-bullhorn', '')} --></td>
			<td class="text-left">{$_data['p_subject']|escape}</td>
			<td>{$_data['_status']}</td>
			<td>{$_data['_begintime']}<br />{$_data['_endtime']}</td>
			<td title="已完成 {$_data['p_progress']}%">
				<div class="progress font10">
					<div class="progress-bar progress-bar-success t-col-{$_data['p_progress']} progress_black" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100">
						<span>{$_data['p_progress']}% Complete</span>
					</div>
				</div>
			</td>
			<td>{$_data['_totaltime'][0]}天{$_data['_totaltime'][1]}小时</td>
			<td>{if $_data['_remaintime'][0] >= 0}{$_data['_remaintime'][0]}天{$_data['_remaintime'][1]}小时{else}已过期{/if}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				<!-- {$base->linkShow($editUrlBase, $_id, '编辑', 'fa-edit', '')} | -->
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
		{foreachelse}
		<tr>
			<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的任务信息{else}暂无任务信息{/if}</td>
		</tr>
		{/foreach}
	</tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}