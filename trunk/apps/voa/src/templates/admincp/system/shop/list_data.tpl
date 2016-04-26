<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-15" />
		<col />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$delete_url_base || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th class="text-left">门店名称</th>
			<th>门店区域</th>
			<th>门店地址</th>
			<th>门店负责人</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2" class="text-left">{if $delete_url_base}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="4" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_data['placeid']}]" class="px" value="{$_data['placeid']}"{if !$delete_url_base} disabled="disabled"{/if} /><span class="lbl"></span></label></td>
			<td class="text-left">{$_data['name']|escape}</td>
			<td class="text-left">{implode(' - ', $_data['_placeregion'])|escape}</td>			
			<td class="text-left">{$_data['address']|escape}</td>
			<td class="text-left">{implode(', ', $_data['_placemember_master'])|escape}</td>
			<td class="text-center">
				{$base->linkShow($delete_url_base, $_data['placeid'], '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($edit_url_base, $_data['placeid'], '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="6" class="warning">{if $is_search}未搜索到指定条件的门店{else}暂无门店数据{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>