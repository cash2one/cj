{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>客户详情</strong></h3>
	</div>
	<div class="panel-body">
		<dl class="dl-horizontal font12 vcy-dl-list" style="margin-bottom:0">
			<dt>创建时间：</dt>
			<dd>{$sale['created']|escape}</dd>
			<dt>公司全称：</dt>
            <dd>{$sale['company']|escape}</dd>
			<dt>公司简称：</dt>
			<dd>{$sale['companyshortname']|escape}</dd>
			<dt>客户来源：</dt>
			<dd>{$sale['source']|escape}</dd>
			<dt>销售阶段：</dt>
			<dd>{$sale['type']|escape}</dd>
			<dt>跟进人：</dt>
			<dd><strong class="label label-primary font12">{$sale['sale_name']|escape}</strong></dd>
		</dl>
	</div>
</div>
<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
<input type="hidden" name="formhash" value="{$formhash}" />
<div class="table-light">
	<div class="table-header">
		<strong class="label label-info font12">回访</strong>
	</div>
	<table class="table table-bordered table-hover font12">
		<colgroup>
			<col class="t-col-15" />
			<col class="t-col-15" />
			<col class="t-col-30" />
			<col class="t-col-20" />
			<col class="t-col-20" />
		</colgroup>
		<thead>
			<tr>
				<th>销售人员</th>
				<th>客户状态</th>
				<th>备注</th>
				<th>地址</th>
				<th>更新时间</th>
			</tr>
		</thead>
	{if $total > 0}
		<tfoot>
			<tr>
				<td colspan="5" class="text-right vcy-page">{$multi}</td>
			</tr>
		</tfoot>
	{/if}
		<tbody>
	{foreach $list as $_id => $_data}
			<tr>
				<td>{$_data['m_uid']|escape}</td>
				<td>{$_data['type']|escape}</td>
				<td>{$_data['content']|escape}</td>
				<td>{$_data['present_address']|escape}</td>
				<td>{$_data['updated']|escape}</td>
			</tr>
	{foreachelse}
			<tr>
				<td colspan="5" class="warning">{if $issearch}未搜索到指定条件的{$module_plugin['cp_name']|escape}数据{else}暂无任何{$module_plugin['cp_name']|escape}数据{/if}</td>
			</tr>
	{/foreach}
		</tbody>
	</table>
	</form>
	 <a href="javascript:history.go(-1);" role="button"
                        class="btn btn-default">返回</a>
</div>


{include file="$tpl_dir_base/footer.tpl"}