{include file='cyadmin/header.tpl'}

<div class="panel panel-default">

<style type="text/css">
	.pagination{
		margin:0 0;
	}
</style>

<div class="panel-heading">销售变更提醒列表</div>

<div class="panel-body col-md-7">

<form>
<table class="table table-striped table-hover table-bordered font12" id="table">
	<colgroup>
		<col class="t-col-10" />
	</colgroup>
	
	<tfoot>
		<tr>
			<td colspan="1" class="text-right">{$multi}</td>
		</tr>
	</tfoot>
	<tbody>
		{foreach $data as $k=>$val}
		<tr>			
			<td class="px text-center">
				<span class="label label-danger">{$val['ep_name']}</span>&nbsp;&nbsp;
				由&nbsp;<span class="label label-success">{$val['front_man']}</span>&nbsp;
				变更为&nbsp;<span class="label label-primary">{$val['back_man']}</span>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-info">{$val['created']}</span>
			</td>
		</tr>
		{foreachelse}
			<tr>
				<td colspan="9" class="warning">暂无任何提醒数据</td>
			</tr>
		{/foreach}
	</tbody>
</table>


</form>
</div>
</div>

{include file='cyadmin/footer.tpl'}