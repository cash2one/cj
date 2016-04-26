{include file="$tpl_dir_base/header.tpl"}
<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			记录列表
		</div>
	</div>
<form class="form-horizontal" role="form" method="post" action="{$formActionUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-8" />
		<col />
		<col class="t-col-8" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th><label class="checkbox vcy-label-none" style="padding-top:3px;"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');" /> 全选</label></th>
			<th>会议室名称</th>
			<th>二维码</th>
			<th>会议室地点</th>
			<th>容纳人数</th>
			<th>可用设备</th>
			<th>开放时间</th>
			<th>操作</th>
		</tr>
	</thead>
{if $meetingRoomList}
	<tfoot>
		<tr>
			<td colspan="4" style="text-align:left;border-right:none">
				<button type="submit" class="btn btn-danger">删除所选会议室</button>　　
				<button type="submit" name="download" class="btn btn-danger" id="download">下载所选的二维码</button>
			</td>
			<td colspan="5" class="text-right vcy-page" style="border-left:none">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $meetingRoomList as $mr_id=>$mr}
		<tr>
			<td><input type="checkbox" name="delete[{$mr_id}]" value="{$mr_id}" /></td>
			<td>{$mr['mr_name']|escape}</td>
			<td><a href="javascript:;" url="/admincp/office/meeting/mrlist/pluginid/15/?act=qrcode&id={$mr_id}" class="qrcode">查看</a></td>
			<td>{$mr['mr_address']|escape}</td>
			<td>{$mr['mr_galleryful']|escape}</td>
			<td>{$mr['mr_device']|escape}</td>
			<td>{$mr['_timestart']} - {$mr['_timeend']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $mr_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($editUrlBase, $mr_id, '编辑', 'fa-edit')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">暂无会议室信息，请添加会议室</td>
		</tr>
{/foreach}
	</tbody>
</table>

</form>
</div>
<script>
$(function (){
	//显示二维码
	$('a.qrcode').on('click', function () {
		var url = $(this).attr('url');
		bootbox.alert('<img src="' + url + '"/><div><a href="'+url+'&download=1"><button class="btn">保存到本地</button></a></div>');
		$('.modal-dialog').width(790);
		$('.modal-footer').hide();
	});
	//下载二维码
	$('#download').click(function (){
		if($('td input:checkbox:checked').length == 0) {
			bootbox.alert('请选择会议室');
			return false;
		}
		
	});
});
</script>
{include file="$tpl_dir_base/footer.tpl"}
<style>
.bootbox-body img{
	border: 1px solid #ccc;
	margin-top: 12px;
}
.bootbox-body div {
	text-align: center;
}
.bootbox-body button {
	margin: 12px auto 0 auto;
}
</style>