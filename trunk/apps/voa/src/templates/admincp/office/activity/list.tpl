{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">

		<form id="id-form-search" class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">

				<div class="form-group" style="margin-bottom:20px">				
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
						<input type="text" class="input-sm form-control" id="id_ac_time_after" name="ac_time_after" placeholder="开始日期" value="{$search_by['ac_time_after']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_ac_time_before" name="ac_time_before" placeholder="结束日期" value="{$search_by['ac_time_before']|escape}">
					</div>

				</div>
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_m_username">发起人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="输入姓名" value="{$search_by['m_username']|escape}" maxlength="54" />
				</div>
				
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_ac_subject">活动标题：</label>
					<input type="text" class="form-control form-small" id="id_rb_subject" name="ac_subject" placeholder="标题关键词" value="{$search_by['ac_subject']|escape}" maxlength="30" />
				</div>
				<div class="form-group" style="margin-bottom:20px">
						<label class="vcy-label-none" for="id_ac_type">状态：</label>
						<select id="id_ac_type" name="ac_type" class="form-control form-small" data-width="auto">
							<option value="999" {if $search_by['ac_type'] == 999} selected="selected"{/if}>全部类型</option>
							<option value="1"{if $search_by['ac_type'] == 1} selected="selected"{/if}>已开始的</option>
							<option value="2"{if $search_by['ac_type'] == 2} selected="selected"{/if}>未开始的</option>
							<option value="3"{if $search_by['ac_type'] == 3} selected="selected"{/if}>已结束的</option>
						</select>
						<span class="space"></span>					
						<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>

			</div>
		</form>
	</div>
</div>
<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
	<table class="table table-bordered table-hover font12">
		<colgroup>
			<col class="t-col-5" />
			<col class="t-col-25" />
			<col class="t-col-15" />
			<col class="t-col-10" />
			<col class="t-col-20" />
			<col class="t-col-15" />
		</colgroup>
		<thead>
			<tr>
				<th class="text-left">
					<label class="checkbox">
					<input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span>
					</label>
				</th>
				<th>活动标题</th>
				<th>发起人</th>
				<th>状态</th>
				<th>发起时间</th>
				<th>操作</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2">{if $delete_url_base}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
				<td colspan="4" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $list as $_id => $_data}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
				<td><a href="{$view_url_base}{$_id}">{$_data['title']|escape}</a></td>
				<td>{$_data['uname']|escape}</td>
				<td>{$_data['_type']|escape}</td>
				<td>{$_data['_created']|escape}</td>
				<td>
					{$base->linkShow($delete_url_base, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} |
					{$base->linkShow($edit_url_base, $_id, '编辑', 'fa-edit', '')}
				</td>
			</tr>
	{foreachelse}
			<tr>
				<td colspan="6" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}