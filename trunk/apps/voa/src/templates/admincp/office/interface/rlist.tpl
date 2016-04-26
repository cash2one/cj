{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$form_search_action_url}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
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
			日志列表
		</div>
	</div>

<form class="form-horizontal" role="form" method="post" action="{$delete_url}?delete">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-8 "/>
		<col class="t-col-11" />
		<col class="t-col-23" />
		<col class="t-col-10" />
		<col class="t-col-12" />
		<col class="t-col-17" />
	</colgroup>
	<thead>
		<tr>
			<th>应用名称</th>
			<th>接口名称</th>
			<th>请求方式</th>
			<th>请求参数</th>
			<th>返回状态码</th>
			<th>创建时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="9" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{if $list}
	{foreach $list as $_id => $_data}
		<tr>			
			<td>{$_data['cp_name']|escape}</td>
			<td>{$_data['name']}</td>
			<td>{$_data['method']}</td>
			<td>{$_data['_params']}</td>
			<td>{$_data['_code']}</td>
			<td>{$_data['_created']|escape}</td>
			<td>
				{$base->linkShow($view_url, $_data['id'], '详情', 'fa-edit', '')}&nbsp;
			</td>
		</tr>
	{/foreach}
{else}
		<tr>
			<td colspan="9" class="warning">{if $issearch}未搜索到指定条件的邀请数据{else}暂无任何数据{/if}</td>
		</tr>
{/if}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}
