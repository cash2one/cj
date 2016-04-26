{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索订单</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row m-b-20">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_username" >订单编号：</label>
					<input type="text" class="form-control" style="width: 200px;" id="ordersn" name="ordersn"  value="{$searchBy['ordersn']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ao_begintime">客户姓名：</label>
					<input type="text" class="form-control"  style="width:120px;" id="customer_name" name="customer_name"  value="{$searchBy['customer_name']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ao_begintime">员工姓名：</label>
					<input type="text" class="form-control" style="width:125px;" id="sale_name" name="sale_name"  value="{$searchBy['sale_name']|escape}" maxlength="54" />
				</div>
			</div>
			
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_ao_begintime" >客户电话：</label>
					<input type="text" class="form-control" style="width: 200px;" id="mobile" name="mobile"  value="{$searchBy['mobile']|escape}" maxlength="54" />
					<span class="space"></span>
					<label class="vcy-label-none" for="id_ao_begintime">订单状态：</label>
					<select name="order_status" class="form-control" style="width:120px;">
						<option value="" {if $searchBy['order_status'] == ''}selected{/if}>订单状态</option>
						{foreach $status as $sid => $v}
						<option value="{$sid}" {if $searchBy['order_status'] == $sid}selected{/if}>{$v}</option>
						{/foreach}
					</select>
					<span class="space"></span>
					<script>
						init.push(function () {	
							var options2 = {							
								todayBtn: "linked",
								orientation: $('body').hasClass('right-to-left') ? "auto right" : 'auto auto'
							}
							$('#bs-datepicker-range1').datepicker(options2);
							$('#bs-datepicker-range2').datepicker(options2);
						});
					</script>	
					<div class="input-daterange input-group" style="width: 290px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range1">
						<label class="vcy-label-none" for="id_created">创建时间：</label>
						<div class="input-daterange input-group" style="width: 220px;display: inline-table;vertical-align:middle;" id="bs-datepicker-range">
							<input type="text" class="input-sm form-control" id="id_created_begintime" name="created_begintime"   placeholder="开始日期" value="{$searchBy['created_begintime']|escape}" />
							<span class="input-group-addon">至</span>
							<input type="text" class="input-sm form-control" id="id_created_endtime" name="created_endtime" placeholder="结束日期" value="{$searchBy['created_endtime']|escape}" />
						</div>
					</div>
					
				</div>
			</div>
			<div class="form-row m-b-20">
				<span class="space"></span>
				<button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				<span class="space"></span>
				<a class="btn btn-default form-small form-small-btn" role="button" href="{$prev}">全部订单</a>
				<span class="space"></span>
				<a href="{$prev}?act=putout" class="btn btn-default ">&nbsp;导出</a>
				<span class="space"></span>
				<a class="btn btn-default form-small form-small-btn" role="button" href="?act=imporder">快递单导入</a>
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
<form class="form-horizontal" role="form" method="post" action="{$formDeleteUrl}">
<input type="hidden" name="formhash" value="{$formhash}" />
<table class="table table-striped table-bordered table-hover font12">
	<thead>
		<tr>
			<th>订单编号</th>
			<th>下单时间</th>
			<th>金额</th>
			<th>客户姓名</th>
			<th>客户电话</th>
			<th>订单状态</th>
			<th>快递公司</th>
			<th>快递单号</th>
			<th>操作</th>
		</tr>
	</thead>
	<tbody>
{foreach $list as $_data}
		<tr>
			<td>{$_data['ordersn']|escape}</td>
			<td>{$_data['_created']}</td>
			<td>{$_data['_amount']|escape}</td>
			<td>{$_data['customer_name']}</td>
			<td>{$_data['mobile']}</td>
			<td>{$_data['_status']}</td>
			<td>{$_data['express']}</td>
			<td>{$_data['expressn']}</td>
			<td>
				<!--<a class="delete" rel="{$_data['orderid']}" href="javascript:;">删除</a> | -->
				<a href="{$prev}?act=detail&id={$_data['orderid']}">查看详情</a>
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="99" class="warning">{if $issearch}未搜索到指定条件的订单{else}暂无任何订单{/if}</td>
		</tr>
{/foreach}
	</tbody>
	{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="99" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
	{/if}
</table>
</form>
</div>
<script>
$(function (){
	/**var ul = $('ul.nav-pills').html('');
	ul.append('<li><a href="/admincp/office/travel/sale/pluginid/{$pluginid}/"><i class="fa fa-cloud"></i> 服务号与企业号打通</a></li>');
	ul.append('<li class="active"><a href="javascript:;"><i class="fa fa-cloud"></i> 订单列表</a></li>');
	ul.append('<li><a href="/admincp/office/travel/main/pluginid/{$pluginid}/?act=customer"><i class="fa fa-list"></i> 客户列表</a></li>');
	ul.append('<li class="goods-list" ><a href="/admincp/office/travel/main/pluginid/{$pluginid}/?act=main"><i class="fa fa-cloud"></i> 产品列表</a></li>');
	*/
	$('a.delete').on('click', function () {
		var id = this.rel;
		if(!id) return;
		var tr = $(this).closest('tr');
		var name = tr.find('td.name').text();
		bootbox.confirm({
			message: '确定删除订单 ['+id+':'+name+'] 吗?',
			callback: function(result) {
				if(result) {
					$.getJSON('{$prev}?act=delete&id='+id, function (json){
						if(json.state == 1) {
							tr.remove();
						}else{
							alert(json.msg);
						}
					});
				}
			},
			className: "bootbox-sm"
		});
	});
});
</script>
{include file="$tpl_dir_base/footer.tpl"}
<style>
.form-small {
	width: 100px!important;
}
.col-sm-4 {
	width: 30%!important;
}
.col-sm-8 {
	width: 70%!important;
}
#id_created_begintime, #id_created_endtime {
	width: 90px;
}
</style>