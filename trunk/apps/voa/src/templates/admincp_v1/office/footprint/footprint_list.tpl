{include file='admincp/header.tpl'}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜素{$module_plugin['cp_name']|escape}</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_m_username">轨迹提交人：</label>
					<input type="text" class="form-control form-small" id="id_m_username" name="m_username" placeholder="输入姓名" value="{$search_by['m_username']|escape}" maxlength="54" />
					<span class="space"></span>

					<label class="vcy-label-none" for="id_fp_type">轨迹类型：</label>
					<select id="id_fp_type" name="fp_type" class="selectpicker bla bla bli bootstrap-select-small" data-width="auto">
						<option value="">全部类型</option>
{foreach $footprint_type_list as $_type => $_name}
						<option value="{$_type}"{if $search_by['fp_type'] == $_type} selected="selected"{/if}>{$_name|escape}</option>
{/foreach}
					</select>
				</div>
			</div>
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_fp_subject">　客户名称：</label>
					<input type="text" class="form-control form-small" id="id_fp_subject" name="fp_subject" placeholder="输入关键词" value="{$search_by['fp_subject']|escape}" maxlength="30" />
					<span class="space"></span>
					
					<label class="vcy-label-none" for="id_fp_visittime_after">拜访时间范围：</label>
					<input type="date" class="form-control form-small" id="id_fp_visittime_after" name="fp_visittime_after" value="{$search_by['fp_visittime_after']|escape}" />
					<label class="vcy-label-none" for="id_fp_visittime_before"> 至 </label>
					<input type="date" class="form-control form-small" id="id_fp_visittime_before" name="fp_visittime_before" value="{$search_by['fp_visittime_before']|escape}" />
					<span class="space"></span>
					
					<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
					<span class="space"></span>
					<a href="{$list_url}" role="button" class="btn btn-default form-small form-small-btn">全部记录</a>
				</div>
			</div>
		</form>
	</div>
</div>

<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-12" />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} />删除</label></th>
			<th>轨迹提交人</th>
			<th>所在部门/职务</th>
			<th>类型/客户名称</th>
			<th>拜访时间</th>
			<th>地理位置</th>
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
			<td class="text-left"><input type="checkbox" name="delete[{$_id}]" value="{$_id}"{if !$delete_url_base} disabled="disabled"{/if} /></td>
			<td>{$_data['_realname']|escape}</td>
			<td>{$_data['_department']|escape}<br />{$_data['_job']}</td>
			<td>[{$_data['_type']}]{$_data['fp_subject']|escape}</td>
			<td>{$_data['_visittime']}</td>
			<td>{$_data['fp_address']|escape}</td>
			<td>
				{$base->linkShow($delete_url_base, $_id, '删除', 'fa-times', 'class="_delete"')} | 
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

{include file='admincp/footer.tpl'}