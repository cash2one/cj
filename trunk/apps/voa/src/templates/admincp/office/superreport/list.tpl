{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索报表</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<script>
						init.push(function () {	
							var options2 = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options2);
							$('#bs-datepicker-range2').datepicker(options2);
						});
					</script>	
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">日期：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_begin_time" name="created_begintime"   placeholder="开始日期" value="{$search_conds['created_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_end_time" name="created_endtime" placeholder="结束日期" value="{$search_conds['created_endtime']|escape}" />
						</div>
					</div>
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_ao_subject">区域：</label>
					<div style="min-width: 390px;display: inline-table;vertical-align:middle;">
{include 
	file="$tpl_dir_base/common_selector_placeregion.tpl"
	selector_name='placeregionid'
	placeregionid=$search_conds['placeregionid']
	placetypeid=1
}
				</div>
					
				</div>
			</div>
			<div class="form-row  m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_username">提交人：</label>
					<div class="input-daterange input-group " style="min-width: 290px;display: inline-table;vertical-align:middle;" id="contact_container">
{include 
	file="$tpl_dir_base/common_selector_member.tpl"
	input_type='checkbox'
	input_name='contacts[]'
	selector_box_id='contact_container'
	default_data={$existed_rights}
	allow_member=true
	allow_department=false	
}					
					</div>
					
					<label class="vcy-label-none" for="id_ao_begintime">门店名称：</label>
					<input type="text" class="form-control form-small" id="id_name" name="name"  value="{$search_conds['name']|escape}" maxlength="54" />
					
					<span class="space"></span>
							
					
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a class="btn btn-default form-small form-small-btn" role="button" href="{$listAllUrl}">全部记录</a>
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
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$form_delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th class="text-left">区域</th>
			<th>门店名称</th>
			<th>汇报人</th>
			<th>上报日期</th>
			<th>营业额</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2" class="text-left">{if $form_delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_data['dr_id']}]" class="px" value="{$_data['dr_id']}"{if !$form_delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td class="text-left">{$_data['area']|escape}</td>
			<td>{$placenames[$_data['csp_id']]|escape}</td>			
			<td>{$usernames[$_data['m_uid']]|escape}</td>
			<td>{$_data['created']}</td>
			<td>{$_data['volume']}</td>
			<td>{$base->linkShow($view_url, $_data['dr_id'], '详情', 'fa-eye', '')}</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">{if $issearch}未搜索到指定条件的报表{else}暂无任何报表{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>

<script type="text/javascript">
window._app = "contacts_pc";
window._root = '{$FM_JSFRAMEWORK}';
</script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}lib/requirejs/require.js"></script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}config.js"></script>
<script type="text/javascript">
requirejs(["jquery", "views/contacts"], function( $, contacts) {
	$(function () {
		var view = new contacts();
		view.input_type = 'checkbox';
		view.render({
			"container": "#contact_container",
			"contacts_default_data": {$existed_rights},
			deps_enable: false,
			contacts_enable: true
		});
	});
});
</script>

{include file="$tpl_dir_base/footer.tpl"}
