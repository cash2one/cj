{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索审批</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" id="list_form" role="form" action="{$formActionUrl}">
			<div class="form-row">
				<div class="form-group">

					<label class="vcy-label-none" for="id_cd_id">流程类型：</label>
					<select id="id_aft_type" name="type" class="form-control form-small" data-width="auto">
						<option value="-1">不限</option>
						<option value="0">自由流程</option>
						<option value="1">固定流程</option>
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_cd_id">流程名称：</label>
					<select id="id_aft_id" name="aft_id" class="form-control form-small" data-width="auto">
						<option value="0">不限</option>
						{foreach $templates as $_aft_id => $_template}
							<option value="{$_aft_id}"{if $searchBy['aft_id'] == $_aft_id} selected="selected"{/if}>{$_template['name']|escape}</option>
						{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_cd_id">所在部门：</label>
					<select id="id_cd_id" name="cd_id" class="form-control form-small" data-width="auto">
						<option value="0">所有部门</option>
						{foreach $departmentList as $_cd_id => $_cd}
							<option value="{$_cd_id}"{if $searchBy['cd_id'] == $_cd_id} selected="selected"{/if}>{$_cd['cd_name']|escape}</option>
						{/foreach}
					</select>
					<span class="space"></span>

					<label class="vcy-label-none" for="id_af_status">审批状态：</label>
					<select id="id_af_status" name="af_status" class="form-control form-small" data-width="auto">
						<option value="-1">不限</option>
						{foreach $askforStatusDescriptions as $_k=>$_n}
							<option value="{$_k}"{if $searchBy['af_status']==$_k} selected="selected"{/if}>{$_n}</option>
						{/foreach}
					</select>
					<span class="space"></span>

				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">申请人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username"
					       placeholder="申请人用户名" value="{$searchBy['m_username']|escape}" maxlength="54"/>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_af_subject">主题：</label>
					<input type="text" class="form-control form-small" id="id_af_subject" name="af_subject"
					       placeholder="申请主题" value="{$searchBy['af_subject']|escape}" maxlength="255"/>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ac_time_after">发起时间范围：</label>
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
						<input type="text" class="input-sm form-control" id="id_ac_time_after" name="begin" placeholder="开始日期" value="{$search_by['begin']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_ac_time_before" name="end" placeholder="结束日期" value="{$search_by['end']|escape}">
					</div>
					<span class="space"></span>
					<button id="issearch" class="btn btn-info form-small form-small-btn margin-left-12"><i
							class="fa fa-search"></i> 搜索
					</button>
					<span class="space"></span>
					<button id="isdownload" class="btn btn-warning form-small form-small-btn margin-left-12"><i
							class="fa fa-cloud-download"></i> 导出
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	$(function () {
		// 搜索操作
		$('#issearch').on('click', function () {
			$('#isdownload_post').remove();
			$('#list_form').append('<input type="hidden" id="issearch_post" name="issearch" value="1" />');
			return true;
		});
		// 导出操作
		$('#isdownload').on('click', function () {
			$('#list_form').append('<input type="hidden" id="isdownload_post" name="isdownload" value="1" />');
			return true;
		});
	});
</script>
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
		<input type="hidden" name="formhash" value="{$formhash}"/>
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col class="t-col-12"/>
				<col class="t-col-16"/>
				<col class="t-col-12"/>
				<col/>
				<col class="t-col-15"/>
				<col class="t-col-15"/>
			</colgroup>
			<thead>
			<tr>
				<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px"
				                                                     onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span
								class="lbl">全选</span></label></th>
				<th>申请人</th>
				<th>申请时间</th>
				<th>部门</th>
				<th>审批标题</th>
				<th>审批状态</th>
				<th>操作</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="2" class="text-left">{if $deleteUrlBase}
						<button type="submit" class="btn btn-danger">批量删除</button>
					{/if}</td>
				<td colspan="6" class="text-right">{if $multi}{$multi}{/if}</td>
			</tr>
			</tfoot>
			<tbody>
			{foreach $askforList as $_af_id => $_af}
				<tr>
					<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_af_id}]"
					                                                      class="px"
					                                                      value="{$_af_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span
									class="lbl"> </span></label></td>
					<td><a href="{$searchByUidBaseUrl}{$_af['m_uid']}">{$_af['m_username']|escape}</a></td>
					<td>{$_af['_created']}</td>
					<td><a href="{$searchByDepartmentBaseUrl}{$_af['cd_id']}">{$_af['_department']|escape}</a></td>
					<td class="text-left">{$_af['af_subject']|escape}</td>
					<td class="text-{$_af['_status_class_tag']}">{$_af['_status']}</td>
					<td>
						{$base->linkShow($deleteUrlBase, $_af_id, '删除', 'fa-times', 'class="text-danger _delete"')} |
						{$base->linkShow($viewBaseUrl, $_af_id, '详情', 'fa-eye', '')}
					</td>
				</tr>
				{foreachelse}
				<tr class="warning">
					<td colspan="7">{if $issearch}未找到符合条件的审批事件{else}暂无审批事件数据{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}