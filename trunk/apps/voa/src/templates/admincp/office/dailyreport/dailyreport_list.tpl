{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜日报</strong></div>
	<div class="panel-body">
		<form id="id-form-search" class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_sr_type">日报类型：</label>
					<select id="id_sr_type" name="dr_type" class="form-control font12" data-width="auto">
						<option value="0">全部</option>
						{foreach $dailyType as $_k => $_n}
						{if $_n[1] == 1}<option value="{$_k}"{if $searchBy['dr_type']==$_k} selected="selected"{/if}>{$_n[0]}</option>{/if}
						{/foreach}
					</select>
					<span class="space"></span>
					<label class="vcy-label-none" for="id_cab_realname_author">提交人：</label>
					<input type="text" class="form-control form-small" id="id_cab_realname_author" name="cab_realname_author" placeholder="输入姓名" value="{$searchBy['cab_realname_author']|escape}" maxlength="54" />		
<!--
					<label class="vcy-label-none" for="id_dr_subject">标题关键词：</label>
					<input type="text" class="form-control form-small" id="id_dr_subject" name="dr_subject" placeholder="关键词" value="{$searchBy['dr_subject']|escape}" maxlength="30" />
					<span class="space"></span>
-->                 <span class="space"></span>
					<label class="vcy-label-none" for="id_begintime">提交日期范围：</label>
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
						<input type="text" class="input-sm form-control" id="id_begintime" name="begintime"  placeholder="开始日期" value="{$searchBy['begintime']|escape}" />
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_endtime" name="endtime" placeholder="结束日期" value="{$searchBy['endtime']|escape}" />
					</div>
					<!-- <input type="date" class="form-control form-small" id="id_begintime" name="begintime" value="{$searchBy['begintime']|escape}" />
					<label class="vcy-label-none" for="id_endtime"> 至 </label>
					<input type="date" class="form-control form-small" id="id_endtime" name="endtime" value="{$searchBy['endtime']|escape}" /> -->
					<span class="space"></span>
					<label class="vcy-label-none" for="id_cab_realname_receive">接收人：</label>
					<input type="text" class="form-control form-small" id="id_cab_realname_receive" name="cab_realname_receive" placeholder="输入姓名" value="{$searchBy['cab_realname_receive']|escape}" maxlength="30" />
				 </div>
			 </div>	
			 <div class="form-row">
				<div class="form-group">
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				    <span class="space"></span>
					<button type="button" id="id-download" class="btn btn-warning form-small form-small-btn margin-left-12"><i class="fa fa-cloud-download"></i> 导出</button>
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
<form  class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
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
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>类型</th>
			<th>日报提交人</th>
			<th>所在部门/职务</th>
			<th>标题</th>
			<th>提交时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$dailyType[$_data['dr_type']][0]|escape}</td>
			<td>{$_data['_realname']|escape}</td>
			<td>{$_data['_department']|escape}<br />{$_data['_job']}</td>
			<td>{if empty($_data['dr_subject'])} {$_data['_reporttime']} {else}{$_data['dr_subject']} {/if}</td>
			<td>{$_data['_created']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的日报信息{else}暂无任何日报信息{/if}</td>
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