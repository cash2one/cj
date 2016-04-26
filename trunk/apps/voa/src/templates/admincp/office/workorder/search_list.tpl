<table class="table table-striped table-hover font12 table-bordered">
	<colgroup>
		<col class="t-col-12" />
		<col class="t-col-11" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-12" />
		<col class="t-col-12" />
	</colgroup>
	<thead>
		<tr>
			<th>派单人</th>
			<th>工单编号</th>
			<th>派单时间</th>
			<th>联系地址</th>
			<th>工单状态</th>
			<th>详情</th>
		</tr>
	</thead>
{if $count > 0}
	<tfoot>
		<tr>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $var_id => $var_data}
		<tr>
			<td>{$var_data['sender']|escape}</td>
			<td>{$var_data['woid']}</td>
			<td>{$var_data['ordertime']}</td>
			<td class="text-left">{$var_data['address']|escape}</td>
			<td>{$var_data['wostate_name']}</td>
			<td>
				{$base->linkShow($view_url_base, $var_data['woid'], '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">{if $var_is_search}未搜索到指定条件的工单数据{else}暂无任何派单数据{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
