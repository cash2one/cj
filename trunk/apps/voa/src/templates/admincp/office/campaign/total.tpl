{include file="$tpl_dir_base/header.tpl"}


<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			分享排行
		</div>
	</div>
<form class="form-horizontal" role="form" method="post" action="javascript:;">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-10" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
		<col class="t-col-5" />
	</colgroup>
	<thead>
		<tr>
			<th>姓名</th>
			<th>部门</th>
			<th>职位</th>
			<th>分享数</th>
			<th>被阅读数</th>
			<th>活动报名人数</th>
			<th>签到人数</th>
		</tr>
	</thead>
	<tfoot>
	<tr>
		<td colspan="7" class="text-right vcy-page">{$multi}</td>
	</tr>
	</tfoot>
	<tbody>
{foreach $list as $_id => $_data}
		<tr>
			<td>{$users[$_data['saleid']]['m_username']}</td>
			<td>{if $users[$_data['saleid']]['cd_id']}{$departments[$users[$_data['saleid']]['cd_id']]['cd_name']}{/if}</td>
			<td>{if $users[$_data['saleid']]['cj_id']}{$jobs[$users[$_data['saleid']]['cj_id']]['cj_name']}{/if}</td>
			<td>{$_data['share']}</td>
			<td>{$_data['hits']}</td>
			<td>{$_data['regs']}</td>
			<td>{$_data['signs']}</td>
		</tr>
{foreachelse}
	<tr><td colspan="7">记录不存在</td></tr>
{/foreach}
	</tbody>
</table>
</form>
</div>

<style>
#growls{
	right:30px;
	top:100px;
}
</style>
<script>
var delUrl = '{$deleteUrlBase}';
{literal}
$(function (){
	//单个删除
	$('a.delete').click(function (){
		var thistr = $(this).closest('tr');
		if (!confirm('确定删除“'+thistr.find('td:eq(1)').text()+'”？')) {
			return false;
		}
		$.getJSON(this.rel, function (json){
			if (json.state) {
				alert('活动已删除');
				thistr.remove();
			} else {
				alert(json.info);
			}
		});
	});
	//批量删除
	$('#batch_delete').click(function (){
		if($('input.delete:checked').length == 0) {
			return alert('请选择要删除的活动');
		}
		if (!confirm('确定删除所选活动?')) {
			return false;
		}
		var data = $('form:last').serialize();

		$.post(delUrl, data, function (json){
			if (json.state) {
				$('input.delete:checked').each(function (i, e){
					$(e).closest('tr').remove();
				});
			} else {
				alert(json.info);
			}
		}, 'json');
	});
});
</script>
{/literal}
{include file="$tpl_dir_base/footer.tpl"}
