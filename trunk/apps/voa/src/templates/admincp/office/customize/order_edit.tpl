{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title font12"><strong>订单信息</strong></h3>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-sm-2 text-right">买家姓名:</div>
			<div class="col-sm-4 text-danger">{$order['customer_name']}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">联系电话:</div>
			<div class="col-sm-4"></i> {$order['mobile']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">收货地址:</div>
			<div class="col-sm-4"></i> {$order['address']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">订单状态:</div>
			<div class="col-sm-4"></i> <span id="order_status">{$order['_status']|escape}</span><span class="space"></span><button id="change">　修 改　</button></div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">订单编号:</div>
			<div class="col-sm-4"></i> {$order['ordersn']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">下单时间:</div>
			<div class="col-sm-4"></i> {$order['_created']|escape}</div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">快递公司:</div>
			<div class="col-sm-4"></i><span id ="express">{$order['express']}</span></div>
		</div>
		<div class="row">
			<div class="col-sm-2 text-right">快递单号:</div>
			<div class="col-sm-4"></i><span id="expressn">{$order['expressn']}</span><span class="space"></span><button id="change_express">　修 改　</button></div>
		</div>
		{if $order['order_status'] > 1 && $order['pay_time']}
		<div class="row">
			<div class="col-sm-2 text-right">付款时间:</div>
			<div class="col-sm-4"></i> {$order['_pay_time']|escape}</div>
		</div>
		{/if}
		{if $order['sale_name']}
		<div class="row">
			<div class="col-sm-2 text-right">销售姓名:</div>
			<div class="col-sm-4"></i> {$order['sale_name']|escape}</div>
		</div>
		{/if}
		{if $order['sale_phone']}
		<div class="row">
			<div class="col-sm-2 text-right">销售电话:</div>
			<div class="col-sm-4"></i> {$order['sale_phone']|escape}</div>
		</div>
		{/if}
		{if $order['customer_memo']}
		<div class="row">
			<div class="col-sm-2 text-right">客户备注:</div>
			<div class="col-sm-4"></i> {$order['customer_memo']|escape}</div>
		</div>
		{/if}
		{if $order['sale_memo']}
		<div class="row">
			<div class="col-sm-2 text-right">客户备注:</div>
			<div class="col-sm-4"></i> {$order['sale_memo']|escape}</div>
		</div>
		{/if}
	</div>
</div>


<div class="tab-content">
	<div class="tab-pane active">
		<table class="table table-striped table-hover table-bordered font12 table-light">
			<thead>
				<tr>
					<th>商品名称</th>
					<th>商品规格</th>
					<th>员工姓名</th>
					<th>单价</th>
					<th>数量</th>
					<th>总价</th>
				</tr>
			</thead>
			<tbody>
			{foreach $goods_list as $r}
				<tr>
					<td>{$r['goods_name']|escape}</td>
					<td>{$r['style_name']}</td>
					<td>{$users[$r['saleuid']]['m_username']}</td>
					<td>{$r['_price']}元</td>
					<td>{$r['num']}</td>
					<td>{$r['_amount']}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>


<ul class="nav nav-tabs font12">
	<li><a href="#result" data-toggle="tab"><i class="fa fa-file-o"></i> 操作记录</a></li>
</ul>
<div class="tab-content">
	<div class="tab-pane active" id="operator-log">
		<table class="table table-striped table-hover table-bordered font12 table-light">
			<colgroup>
				<col class="t-col-13" />
				<col class="t-col-18" />
				<col class="t-col-30" />
				<col />
			</colgroup>
			<thead>
				<tr>
					<th>操作人</th>
					<th>时间</th>
					<th>旧状态</th>
					<th>新状态</th>
					<th>备注</th>
				</tr>
			</thead>
			<tbody>
				<tr class="warning">
					<td colspan="5">暂无操作记录</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div id="changeDiv" style="display:none;">
	更改状态
	<select class="order_status">
		{foreach $status as $sid => $v}
		<option value="{$sid}" {if $searchBy['order_status'] == $sid}selected{/if}>{$v}</option>
		{/foreach}
	</select><br/><br/>
	修改原因:<br/>
	<textarea class="memo" style="width:260px;height:100px;resize:none"></textarea>
</div>

<div id="chExpressDiv" style="display:none;">
	快递编号:
	<input type="text" class="form-control expressn" style="width: 200px;"  value="{$order['expressn']}" maxlength="54" />
    <br/>
         快递公司:
    <input type="text" class="form-control express" style="width: 200px;"   value="{$order['express']}" maxlength="20" />
    <br/>
	修改原因:<br/>
	<textarea class="express_memo" style="width:260px;height:100px;resize:none"></textarea>
</div>
<script>
var status = "{$order['order_status']}";	//当前状态
var orderid = "{$order['orderid']}";		//订单id
$(function (){
	var ul = $('ul.nav-pills').html('');
	ul.append('<li><a href="/admincp/office/travel/sale/pluginid/{$pluginid}/"><i class="fa fa-cloud"></i> 服务号与企业号打通</a></li>');
	ul.append('<li class="active"><a href="{$prev}"><i class="fa fa-cloud"></i> 订单列表</a></li>');
	ul.append('<li><a href="/admincp/office/travel/main/pluginid/{$pluginid}/?act=customer"><i class="fa fa-list"></i> 客户列表</a></li>');
	ul.append('<li class="goods-list" ><a href="/admincp/office/travel/main/pluginid/{$pluginid}/?act=main"><i class="fa fa-cloud"></i> 产品列表</a></li>');
	
	//弹出修改状态容器
	$('#change').click(function (){
		$('#changeDiv option').show();
		$('#changeDiv option[value=' + status + ']').hide();
		if(status == 1) {
			$('#changeDiv option:eq(1)').attr('selected', true);
		}else{
			$('#changeDiv option:first').attr('selected', true);
		}
		bootbox.confirm({
			message: $('#changeDiv').html(),
			callback: function(result) {
				if(result) {
					var new_status = $('.bootbox-body .order_status').val();
					var memo = $('.bootbox-body .memo').val();
					if(!memo) {
						bootbox.alert("请输入原因!");
						return false;
					}
					//修改状态,并保存操作日志
					var data = {
						orderid: orderid,
						new_status: new_status,
						memo: memo
					}
					$.post('?act=log', data, function (json){
						if(json.state) {
							status = new_status;
							var _status = $('#changeDiv option[value='+status+']').text();
							$('#order_status').text(_status);
							loadlog();
							bootbox.alert("修改状态成功");
						}else{
							bootbox.alert("修改状态错误:" + json.msg);
						}
					}, 'json');
				}
			},
			className: "bootbox-sm"
		});
	});
	
	//弹出修改快递编号、快递公司
	$('#change_express').click(function (){
		$('#chExpressDiv option').show();
		bootbox.confirm({
			message: $('#chExpressDiv').html(),
			callback: function(result) {
				if(result) {
					var express = $('.bootbox-body .express').val();
					var expressn = $('.bootbox-body .expressn').val();
					var memo = $('.bootbox-body .express_memo').val();
					
					if(!express) {
					    bootbox.alert("请输入快递公司!");
						return false;
					}
					
					if(!expressn) {
					    bootbox.alert("请输入快递单号!");
						return false;
					}
					
					if(!memo) {
						bootbox.alert("请输入原因!");
						return false;
					}
				
					//修改状态,并保存操作日志
					var data = {
						orderid: orderid,
						express:express,
						expressn:expressn,
						memo: memo
					}
					$.post('?act=log_express', data, function (json){
						if(json.state) {
							loadlog();
							$('#express').text(express);
							$('#expressn').text(expressn);
							bootbox.alert("修改状态成功");
						}else{
							bootbox.alert("修改状态错误:" + json.msg);
						}
					}, 'json');
				}
			},
			className: "bootbox-sm"
		});
	});
	
	loadlog();
});
//加载操作日志
function loadlog()
{
	$.getJSON('?act=loadlog&orderid='+orderid, function (json){
		if(json.state) {
			var list = json.msg;
			var tbody = $('#operator-log tbody');
			if(list.length > 0) tbody.html('');
			for(k in list)
			{
				var r = list[k];
				var tr = '<tr><td>'+r.oper_name+'</td><td>'+r._created+'</td><td>'+r._old_status+'</td><td>'+r._new_status+'</td><td  style="word-wrap:break-word; word-break:normal; word-break:break-all;text-align:left;">'+r.memo+'</td></tr>';
				tbody.append(tr);
			}
		}else{
			bootbox.alert("加载操作日志失败:" + json.msg);
		}
	});
}
</script>
{include file="$tpl_dir_base/footer.tpl"}