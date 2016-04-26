{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">

		<form id="id-form-search" class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="">
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_m_username">申请人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="输入姓名" value="{$search_by['m_username']|escape}" maxlength="54" />
				</div>
				<div class="form-group" style="margin-bottom:20px">	
					<span class="space"></span>			
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
						<input type="text" class="input-sm form-control" id="id_rb_time_after" name="rb_time_after" placeholder="开始日期" value="{$search_by['rb_time_after']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_rb_time_before" name="rb_time_before" placeholder="结束日期" value="{$search_by['rb_time_before']|escape}">
					</div>
						<!-- <input type="date" class="form-control form-small" id="id_rb_time_after" name="rb_time_after" value="{$search_by['rb_time_after']|escape}" />
						<label class="vcy-label-none" for="id_rb_time_before"><span class="space"></span>至<span class="space"></span></label>
						<input type="date" class="form-control form-small" id="id_rb_time_before" name="rb_time_before" value="{$search_by['rb_time_before']|escape}" /> -->
				</div>
				<div class="form-group" style="margin-bottom:20px">
<!--
					<span class="space"></span>
					<label class="vcy-label-none" for="id_rb_type">类型：</label>
					<select id="id_rb_type" name="rb_type" class="form-control form-small" data-width="auto">
						<option value="">全部类型</option>
{foreach $reimburse_type_list as $_type => $_name}
						<option value="{$_type}"{if $search_by['rb_type'] == $_type} selected="selected"{/if}>{$_name|escape}</option>
{/foreach}
					</select>
-->
				</div>
				</div>	
				<div class="form-group">
					<label class="vcy-label-none" for="id_rbpc_username">审批人：</label>
					<input type="text" class="form-control form-small" id="id_rbpc_username" name="rbpc_username" placeholder="输入姓名" value="{$search_by['rbpc_username']|escape}" maxlength="54" />
				</div>
				<div class="form-group">
					<span class="space"></span>
					<label class="vcy-label-none" for="id_rb_subject">主题关键文字：</label>
					<input type="text" class="form-control form-small" id="id_rb_subject" name="rb_subject" placeholder="标题关键词" value="{$search_by['rb_subject']|escape}" maxlength="30" />
					<span class="space"></span>					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
					<span class="space"></span>
					<button type="button" id="id-download" class="btn btn-warning form-small form-small-btn margin-left-12"><i class="fa fa-cloud-download"></i> 导出</button>
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
			<col class="t-col-12" />
			<col class="t-col-9" />
			<col />
			<col class="t-col-16" />
			<col class="t-col-13" />
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
				<th>申请人</th>
				<th>金额</th>
				<th>事由</th>
				<th>申请时间</th>
				<th>状态</th>
				<th>操作</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2">{if $delete_url_base}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
				<td colspan="5" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $list as $_id => $_data}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
				<td>{$_data['_realname']|escape}</td>
				<td>￥{$_data['_expend']}</td>
				<td class="text-left">{$_data['rb_subject']|escape}</td>
				<td>{$_data['_time']}</td>
				<td>{$_data['_status']|escape}</td>
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

<script type="text/javascript">
jQuery(function(){
	jQuery('#id-download').click(function(){
		if (jQuery('#__dump__').length == 0) {
			jQuery('body').append('<iframe id="__dump__" name="__dump__" src="about:blank" style="width:0;height:0;padding:0;margin:0;border:none;"></iframe>');
		}
		jQuery('#id-form-search').append('<input type="hidden" id="id-dump-input" name="is_dump" value="1" />').attr('target', '__dump__').submit();
		jQuery('#id-form-search').removeAttr('target');
		jQuery('#id-dump-input').remove();
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}