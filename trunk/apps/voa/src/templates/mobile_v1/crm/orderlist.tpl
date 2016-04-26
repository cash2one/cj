{include file='mobile_v1/header_fz.tpl'}

<div class="ui-myorder" >
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="/frontend/travel/index" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">我的订单</h3>
			</div>
		</div>
	</div>
	<div id="orderlist"></div>
</div>


{literal}
<script id="orderlist_tpl" type="text/template">
<%if (!_.isEmpty(list)) {%>
<%_.each(list, function(order) {%>
<ul class="ui-list">
	<li class="ui-padding-bottom-0 ui-order-code">订单编号: <%=order.ordersn%></li>
	<%var goods_ids = [];%>
	<%var goods_nums = [];%>
	<%var goods_styleids = [];%>
	<%_.each(order["goods_list"], function(goods) {%>
	<li class="ui-padding-top-0 ui-padding-bottom-0 to_order_detail" data-href="/frontend/travel/orderdetail?orderid=<%=order.orderid%>">
		<div class="ui-list-thumb">
			<span style="background-image:url(<%=goods.cover%>)"></span>
		</div>
		<div class="ui-list-info">
			<p class="title"><%=goods.goods_name%></p>
			<p class="ui-text-right">数量： <%=goods.num%></p>
		</div>
	</li>
	<%goods_ids.push(goods.goods_id);%>
	<%goods_nums.push(goods.num);%>
	<%goods_styleids.push(goods.style_id);%>
	<%});%>
	<li class="ui-border-t ui-text-right">
		<h2 class="ui-text-color-primary">总计: &#165; <%=(order.amount / 100)%></h2>
		<div class="ui-order-payment">
			<%if (3 > order.order_status && 1 == order.repay) {%>
			<a class="ui-btn ui-btn-primary repay" data-orderid="<%=order.orderid%>">立即付款</a>
			<%} else {%>
			<a class="ui-btn rebuy" data-dataid="<%=goods_ids.join(',')%>" data-num="<%=goods_nums.join(',')%>" data-styleid="<%=goods_styleids.join(',')%>">重新购买</a>
			<%}%>
		</div>
	</li>
</ul>
<%});%>
<%} else {%>
<section class="ui-notice ui-notice-norecord"> <i></i>
	<p>暂无数据</p>
</section>
<%}%>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "frozen", "showlist"], function($, _, fz, showlist) {
	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': '/api/order/get/list/', 'data': {'type': 'client'}}, {
		"dist": $('#orderlist'),
		"cb": function(dom) {
			//alert(dom.html());
		}
	});

	// 监听查看点击
	$("#orderlist").on("tap", ".to_order_detail", function(e) {
		window.location.href = $(this).data("href");
	});

	// 监听重新付款按钮
	$("#orderlist").on("tap", ".repay", function(e) {
		var el = $.loading({
			content: '加载中...'
		});
		var abtn = $(this);
		// 请求详情
		$.ajax({
			'type': 'GET',
			'url': '/api/order/get/pay2?orderid=' + $(this).data("orderid"),
			'success': function(data, status, xhr) {
				WeixinJSBridge.invoke(
					'getBrandWCPayRequest',
					data["result"],
					function(res) {
						if (res.err_msg == 'get_brand_wcpay_request:ok') {
							abtn.remove();
							return true;
						} else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
							el.loading('hide');
							return false;
						} else {
							el.loading('hide');
							return false;
						}
					}
				);
			}
		});
	});

	// 监听重新购买按钮
	var dataids = [];
	var nums = [];
	var styleids = [];
	var cartids = [];
	$("#orderlist").on("tap", ".rebuy", function(e) {
		var el = $.loading({
			content: '加载中...'
		});
		dataids = $(this).data("dataid").toString().split(",");
		nums = $(this).data("num").toString().split(",");
		styleids = $(this).data("styleid").toString().split(",");
		addtocart();
	});

	// 把产品推入购物车
	function addtocart() {
		if (0 == dataids.length) {
			// 如果购物车里无产品
			if (0 == cartids.length) {
				var dia = $.dialog({
					title:'',
					content:'产品重新购买操作失败',
					button:["确认"]
				});
				return true;
			}

			var cartidstr = '';
			_.each(cartids, function(id) {
				cartidstr += "cartids[]=" + id;
			});
			window.location.href = "/frontend/travel/pay?" + cartidstr;
			return true;
		}

		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/cartadd',
			'data': {"goods_id": dataids.pop(), "num": nums.pop(), "styleid": styleids.pop()},
			'success': function(data, status, xhr) {

				if (_.has(data, "result") && 0 < parseInt(data["result"])) {
					cartids.push(data["result"]);
				}

				addtocart();
			}
		});
	}
});
</script>
{/literal}

{include file='mobile_v1/footer_fz.tpl'}