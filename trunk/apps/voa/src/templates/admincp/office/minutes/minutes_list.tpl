{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_mi_username">记录人：</label>
					<input type="text" class="form-control form-small" id="id_mi_username" name="mi_username" placeholder="输入姓名" value="{$search_by['mi_username']|escape}" maxlength="54" />
					<span class="space"></span>
			
					<label class="vcy-label-none" for="id_begintime">记录时间范围：</label>
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
						<input type="text" class="input-sm form-control" id="id_begintime" name="begintime"  placeholder="开始日期" value="{$search_by['begintime']|escape}" />
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_endtime" name="endtime" placeholder="结束日期" value="{$search_by['endtime']|escape}" />
					</div>
					<!-- <input type="date" class="form-control form-small" id="id_begintime" name="begintime" value="{$search_by['begintime']|escape}" />
					<label class="vcy-label-none" for="id_endtime"> 至 </label>
					<input type="date" class="form-control form-small" id="id_endtime" name="endtime" value="{$search_by['endtime']|escape}" /> -->
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_mim_username">参会人：</label>
					<input type="text" class="form-control form-small" id="id_mim_username" name="mim_username" placeholder="输入姓名" value="{$search_by['mim_username']|escape}" maxlength="54" />
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_mi_subject">会议主题关键词：</label>
					<input type="text" class="form-control form-small" id="id_mi_subject" name="mi_subject" placeholder="标题关键词" value="{$search_by['mi_subject']|escape}" maxlength="30" />
					<span class="space"></span>
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
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
<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12 table-bordered">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>记录人</th>
			<th>所在部门/职务</th>
			<th >主题</th>
			<th>记录时间</th>
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
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} class="px" /><span class="lbl"> </span></label></td>
			<td>{$_data['_realname']|escape}</td>
			<td>{$_data['_department']|escape}<br />{$_data['_job']}</td>
			<td class="text-left">{$_data['mi_subject']|escape}</td>
			<td>{$_data['_created']}</td>
			<td>
				{$base->linkShow($delete_url_base, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($view_url_base, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}