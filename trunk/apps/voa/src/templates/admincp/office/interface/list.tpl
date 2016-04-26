{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<script>
						init.push(function () {
							var options = {
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options);

							$('.one-delete').on('click',function(e){
								e.preventDefault();
								if(confirm("你确定要删除么？")){
									window.location.href = e.currentTarget.href;
								};
							});
						});
					</script>
					<label class="vcy-label-none" for="id_name">接口名称：</label>
					<input type="text" class="form-control form-small"  id="id_name" name="name" placeholder="输入接口名称" value="{$search_conds['name']|escape}" maxlength="30" />

					<span class="space"></span>
					<label class="control-label form-small " for="id_label_tc_id">应用名称：</label>
					<div class="form-group">
						<select name="cp_pluginid" class="form-control form-small" data-width="auto">
							<option value="" selected="selected">请选择类型</option> 
							{foreach $plugins as $_key => $_val}
								<option value="{$_val['cp_pluginid']}" {if $search_conds['cp_pluginid'] == $_val['cp_pluginid']}selected{/if}>{$_val['cp_name']}</option>
							{/foreach}
						</select>
					</div>
					
					<span class="space"></span>
					<div class="form-group">
						<span class="space"></span>
						<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					</div>
				
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

<form class="form-horizontal" role="form" method="post" action="{$delete_url}?delete">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-8 "/>
		<col class="t-col-11" />
		<col class="t-col-8" />
		<col class="t-col-25" />
		<col class="t-col-12" />
		<col class="t-col-17" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$delete_url || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>应用名称</th>
			<th>接口名称</th>
			<th>请求方式</th>
			<th>请求地址</th>
			<th>创建时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">
				{if $delete_url}<button type="submit" class="btn btn-danger">批量删除</button>{/if}
			</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>			
			<td class="text-left"><label class="px-single"><input type="checkbox" class="px" name="delete[{$_id}]" value="{$_id}"{if !$delete_url} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{$_data['cp_name']|escape}</td>
			<td>{$_data['name']}</td>
			<td>{$_data['method']}</td>
			<td>{$_data['url']}</td>
			<td>{$_data['_created']|escape}</td>
			<td>
				{$base->linkShow($edit_url, $_id, '编辑', 'fa-edit', '')}&nbsp;
				{$base->linkShow($delete_url, $_id, '删除', 'fa-times', 'class="text-danger  one-delete"')} 
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的邀请数据{else}暂无任何数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}
