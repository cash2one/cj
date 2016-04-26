{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索客户</strong></div>
	<div class="panel-body">

		<form id="id-form-search" class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">

				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_ac_time_after">创建时间：</label>
					<script>
						init.push(function () {
							var options1 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options1);
						});
					</script>
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<input type="text" class="input-sm form-control" id="id_created_time_after" name="created_time_after" placeholder="开始日期" value="{$search_by['created_time_after']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_created_time_before" name="created_time_before" placeholder="结束日期" value="{$search_by['created_time_before']|escape}">
					</div>

				</div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_ac_time_after">更新时间：</label>
					<script>
						init.push(function () {
							var options2 = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range2').datepicker(options2);
						});
					</script>
					<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range2">
						<input type="text" class="input-sm form-control" id="id_updated_time_after" name="updated_time_after" placeholder="开始日期" value="{$search_by['updated_time_after']|escape}">
						<span class="input-group-addon">至</span>
						<input type="text" class="input-sm form-control" id="id_updated_time_before" name="updated_time_before" placeholder="结束日期" value="{$search_by['updated_time_before']|escape}">
					</div>

				</div>
				<span class="space"></span>
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_m_username">客户简称：</label>
					<input type="text" class="form-control form-small" id="id_short_name" name="short_name" placeholder="输入名称" value="{$search_by['short_name']|escape}" maxlength="54" />
				</div>
				<span class="space"></span>
				<div class="form-group" style="margin-bottom:20px">
					<label class="vcy-label-none" for="id_m_username">跟进人：</label>
					<input type="text" class="form-control form-small" id="id_slae_name" name="slae_name" placeholder="输入名字" value="{$search_by['slae_name']|escape}" maxlength="54" />
				</div>
				<span class="space"></span>
				<div class="form-group" style="margin-bottom:20px">
						<label class="vcy-label-none" for="id_type">销售状态：</label>
						<select id="id_type" name="type" class="form-control form-small" data-width="auto">
							<option value="999" {if $search_by['type'] == 999} selected="selected"{/if}>全部状态</option>
							{if $type_num > 0}
								{foreach $type as $_key_type => $_value_type}
									<option value="{$_key_type}"{if $search_by['type'] == $_key_type} selected="selected"{/if}>{$_value_type['name']}</option>
								{/foreach}
							{/if}
						</select>
				</div>
				<span class="space"></span>
				<div class="form-group" style="margin-bottom:20px">
						<label class="vcy-label-none" for="id_source">客户来源：</label>
						<select id="id_source" name="source" class="form-control form-small" data-width="auto">
							<option value="999" {if $search_by['source'] == 999} selected="selected"{/if}>全部来源</option>
							{if $souce_num > 0}
								{foreach $source as $_key_source => $_value_source}
									<option value="{$_key_source}"{if $search_by['source'] == $_key_source} selected="selected"{/if}>{$_value_source['name']}</option>
								{/foreach}
							{/if}
						</select>
				</div>
				<span class="space"></span>
				<div class="form-group" style="margin-bottom:20px">
					<span class="space"></span>
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
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
			<col class="t-col-25" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
			<col class="t-col-10" />
		</colgroup>
		<thead>
			<tr>
				<th class="text-left">
					<label class="checkbox">
					<input type="checkbox" class="px" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />
					<span class="lbl">全选</span>
					</label>
				</th>
				<th>公司全称</th>
				<th>公司简称</th>
				<th>跟进人</th>
				<th>销售阶段</th>
				<th>客户来源</th>
				<th>创建时间</th>
				<th>最后更新时间</th>
				<th>操作</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="2" class="text-left">{if $delete_url_base}<button type="submit" class="btn btn-danger select_delete">批量删除</button>{/if}</td>
				<td colspan="7" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $list as $_id => $_data}
			<tr>
				<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
				<td>{$_data['company']|escape}</td>
				<td>{$_data['companyshortname']|escape}</td>
				<td>{$_data['sale_name']|escape}</td>
				<td>{$_data['type']|escape}</td>
				<td>{$_data['source']|escape}</td>
				<td>{$_data['_created']|escape}</td>
				<td>{$_data['_updated']|escape}</td>
				<td>
					{$base->linkShow($delete_url_base, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} |
					{$base->linkShow($view_url_base, $_id, '详情', 'fa-eye', '')}
				</td>
			</tr>
	{foreachelse}
			<tr>
				<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	</form>
</div>

<div id="modal-delete-department" class="modal modal-alert modal-danger fade in" aria-hidden="false" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<i class="fa fa-times-circle"></i>
			</div>
			<div class="modal-title"></div>
			<div class="modal-body">
				<span class=" text-info">客户资料删除后无法恢复，确定要删除吗？</span>
			</div>
			<div class="modal-footer">
				<button type="button" id="delete_false" class="btn" data-dismiss="modal">取消</button>
				<button type="button" id="delete_true" name="btn_delete" class="btn btn-danger" data-dismiss="modal" data-id="105">确定</button>
			</div>
		</div>
	</div>
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

<script type="text/javascript">
	$(function(){

		if ($('form[role=form] table a._delete').get(0) != undefined &&
				$("form[role=form] table button.btn-danger").get(0) != undefined) {

			// 保存当前点击的起始对象
			var onclick = null;
			// 保存点击A链接的href地址
			var href_url = null;
			// 批量删除时按钮的上级表单
			var jq_submit = null;

			// 当点击A链接删除按钮时
			$('form[role=form] table a._delete').on('click', function () {
				onclick = $(this);
				href_url = onclick.attr('href');
				$('#modal-delete-department').modal('show');
				return false;
			});

			// 当点击批量删除时
			$("form[role=form] button.btn-danger").on('click', function (e, confirm_submit) {
				// 先判断是不是一个都没有选择
				var checkbox = $("input[name^='delete[']:checked");
				if (checkbox.length > 0) {
				} else {
					alert('请选择一个删除项');
					return false;
				}
				if (!confirm_submit || confirm_submit == undefined) {
					jq_submit = $(this);
					onclick = jq_submit;
					$('#modal-delete-department').modal('show');
					return false;
				}
			});

			// 模拟框中的确定按钮
			$('#delete_true').on('click', function () {
				if (onclick.attr('type') != 'submit') {
					// 当前点击来源是A链接删除
					window.location.href = href_url;
				} else {
					// 当前点击来源是批量删除
					jq_submit.trigger('click', ['y']);
				}
				return false;
			});
		}
	});
</script>

{include file="$tpl_dir_base/footer.tpl"}