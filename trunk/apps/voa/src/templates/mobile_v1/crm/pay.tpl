{include file='mobile_v1/header_fz.tpl'}

<div class="ui-payment">
	<div class="ui-selector ui-selector-line ui-margin-0 clearfix">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="javascript:history.go(-1);" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">产品支付</h3>
			</div>
		</div>
	</div>
	<ul class="ui-list ui-list-link ui-border-tb ui-margin-0 ui-margin-bottom-10 ui-top-content">
		<li class="ui-border-t" id="addr"></li>
	</ul>
	<ul class="ui-list ui-list-text" id="cartlist"></ul>
	<div class="ui-btn-wrap ui-padding-bottom-0">
		<button id="paybtn" class="ui-btn-lg ui-btn-primary" data-ac="pay">点击进行微信支付</button>
	</div>
	<div class="ui-btn-wrap" style="display:none;">
		<button class="ui-btn-lg ui-btn-info">邀请微信好友代付</button>
	</div>
</div>

{literal}
<script id="addr_tpl" type="text/template">
<%if (_.isEmpty(address)) {%>
<div class="ui-list-thumb"> <i class="ui-icon ui-icon-location-white"></i></div>
<div class="ui-list-info">
	<h4 class="ui-nowrap">! 请填写收货地址</h4>
</div>
<%} else {%>
<div class="ui-list-thumb"> <i class="ui-icon ui-icon-location-white"></i></div>
<div class="ui-list-info">
	<h4 class="ui-nowrap">收货人:<%=address.name%><span class="ui-list-action"><%=address.phone%></span></h4>
	<p><%=address.adr%></p>
</div>
<%}%>
</script>

<script id="cartlist_tpl" type="text/template">
<%_.each(goods, function(item) {%>
<li class="">
	<div class="ui-list-thumb">
		<span style="background-image:url(<%=item.cover%>)"></span>
	</div>
	<div class="ui-list-info">
		<p class="ui-goods-title-12"><%=item.subject%></p>
		<% if (item.style_name) { %><p>规格: <%=item.style_name%></p><% } %>
	</div>
	<div class="ui-list-action">
		<h4 class="ui-nowrap ui-text-color-primary" id="goods_price">&#165; <%=item.price%></h4>
		<p class="ui-nowrap ui-text-color-default" id="goods_num">x<%=item.num%></p>
	</div>
</li>
<%});%>

<li class="ui-border-t ui-form-item-link ui-list-delivery">
    <div class="ui-list-info">
        <h4 class="ui-nowrap">配送方式</h4>
        <div class="ui-list-action" id="type_p"><%=goods.p_sets[0].exptype%> <%=goods.p_sets[0].expcost%>元</div> 
        <select id="express_type" name="express_type" onchange="javascript:change_type(this);">
		<%_.each(goods.p_sets, function(data) {%>
		     <option value="<%=data.expid%>"><%=data.exptype%> <%=data.expcost%></option>
		<%});%>
		</select>                     
    </div>           
</li>

<li class="ui-border-t ui-text-right">
	<div class="ui-text-right ui-order-count">
		<small>共 <%=goods.length%> 件商品<span class="ui-text-total">合计:</span></small>
		<span class="ui-text-color-primary" id="price">&#165; <%=price%></span>
	</div>
</li>

</script>
{/literal}

<script type="text/javascript">
var cartids = {$cartids};
var abs_params = null;
var address = {};
var pay_params = null;
var pay_btn = null;
{literal}
require(["zepto", "underscore", "qyjsapi", "frozen"], function($, _, jsapi, fz) {

	var g_el = $.loading({
		content: '加载中...'
	});
	var jsapi = new jsapi();
	// 请求地址详情
	$.ajax({
		'type': 'GET',
		'url': '/api/order/get/address',
		'success': function(data, status, xhr) {
			$("#addr").html(_.template($("#addr_tpl").html(), data["result"]));
			address = data["result"]["address"];
			abs_params = data["result"]["ads_params"];
		}
	});

	// 获取订单详情
	$.ajax({
		'type': 'GET',
		'url': '/api/order/get/cartlist?pay=pay&cartids=' + cartids.join(","),
		'success': function(data, status, xhr) {
			var price = 0;
			var e_price = data["result"]["p_sets"][0]["expcost"];
			var list = [];
			list["p_sets"] = data["result"]["p_sets"];
			$.each(data["result"], function (k, item) {
				if (k != "p_sets") {
					price += (item.price * item.num / 100);
					item.goods["num"] = item.num;
					item.goods["style_name"] = item.style_name;
					list.push(item.goods);
				}
			});
			var sum = parseFloat(price)+parseFloat(e_price)
			$("#cartlist").html(_.template($("#cartlist_tpl").html(), {"goods": list, "price": sum}));
			g_el.loading('hide');
		}
	});

	// 切换收货地址
	$("#addr").on("tap", function(e) {
		// 编辑地址
		jsapi.edit_addr(abs_params, function(addr) {
			address = addr;
			$("#addr").html(_.template($("#addr_tpl").html(), {"address": addr}));
		});
	});

	// 支付操作
	function pay() {
		jsapi.pay(pay_params, function(res, st) {
			if (st == 'ok') {
				setTimeout(function() {
					location.href = "/frontend/travel/orderlist";
				}, 1000);
				return true;
			} else if (st == 'cancel') {
				//location.href = "/frontend/travel/orderlist";
			}

			pay_btn.text('重新支付');
			pay_btn.data("ac", 'repay');
			g_el.loading('hide');
		});
	}

	// 支付请求
	$("#paybtn").on("tap", function(e) {
	
	    var expid = $('#express_type').val();//快递分类id
	    
		// 判断是否有地址
		if (_.isEmpty(address) || _.isEmpty(address.name) || _.isEmpty(address.phone) || _.isEmpty(address.adr)) {
			var dia = $.dialog({
				title:'',
				content:'请先选择收货地址',
				button:["确认"]
			});
			return true;
		}

		g_el = $.loading({
			content: '加载中...'
		});
		pay_btn = $(this);
		$(this).text("请求支付中...");

		// 判断是否重新支付
		if ("repay" == pay_btn.data("ac")) {
			pay();
			return true;
		}

		// 生成订单
		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/cartpay',
			'data': {
				'cartids': cartids,
				'name': address.name,
				'phone': address.phone,
				'expid':expid,
				'adr': address.adr
			},
			'success': function(data, status, xhr) {
				if(_.has(data, "result") && _.isEmpty(data.result)) {
					pay_btn.text('重新支付');
					var dia = $.dialog({
						title: '',
						content: _.isEmpty(data["errmsg"]) ? '支付操作错误, 请重新尝试' : data["errmsg"],
						button: ["确认"]
					});
					g_el.loading('hide');
					return false;
				}

				pay_btn.text('开始支付...');
				pay_params = data.result.pay_params;
				pay();
			}
		});
	});
});

/**
 * 变更快递费用
 */
function change_type(obj){
    $(obj).find('option').each(function () {
		if ($(this).prop('selected')) {
			$('#type_p').text($(this).text()+'元'); 
			var express_price =$(this).text().toString().split(" ")[1];//快递费用
			express_price = parseFloat(express_price);
			
			var goods_price = $('#goods_price').text();//商品价格
			goods_price = goods_price.replace(/[^0-9\.]/g, '');
			goods_price = parseFloat(goods_price);
			
			var goods_num = $('#goods_num').text();//商品数量
			goods_num = goods_num.replace(/[^0-9]/g, '');
			goods_num = parseInt(goods_num);
			
			var goods_sum = goods_price*goods_num+express_price;
			$('#price').text(goods_sum);
		}
	});
}
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}