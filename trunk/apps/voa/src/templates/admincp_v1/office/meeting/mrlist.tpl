{include file='admincp/header.tpl'}

<form class="form-horizontal" role="form" method="post" action="{$formActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-hover font12">
	<colgroup>
		<col class="t-col-8" />
		<col />
		<col class="t-col-20" />
		<col class="t-col-15" />
		<col class="t-col-20" />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="vcy-label-none"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');" /> 删除</label></th>
			<th>会议室名称</th>
			<th>会议室地址</th>
			<th>容纳人数</th>
			<th>可用设备</th>
			<th>开放时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $meetingRoomList}
	<tfoot>
		<tr>
			<td>&nbsp;</td>
			<td><button type="submit" class="btn btn-danger">批量删除</button></td>
			<td colspan="5" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $meetingRoomList as $mr_id=>$mr}
		<tr>
			<td><input type="checkbox" name="delete[{$mr_id}]" value="{$mr_id}" /></td>
			<td>{$mr['mr_name']|escape}</td>
			<td>{$mr['mr_address']|escape}</td>
			<td>{if $mr['_volume']}（{$mr['_volume']}）{/if}{$mr['mr_galleryful']|escape}</td>
			<td>{$mr['mr_device']|escape}</td>
			<td>{$mr['_timestart']} - {$mr['_timeend']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $mr_id, '删除', 'fa-times', 'class="_delete"')} | 
				{$base->linkShow($editUrlBase, $mr_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="7" class="warning">暂无会议室信息，请添加会议室</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>

{include file='admincp/footer.tpl'}