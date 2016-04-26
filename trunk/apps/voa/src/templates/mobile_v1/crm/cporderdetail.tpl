{include file='mobile_v1/header_fz.tpl'}

<div class="ui-order-detail">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="{$refer}" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">订单详情</h3>
			</div>
		</div>
		<div id="orderdetail"></div>
	</div>
</div>

{literal}
<script id="orderdetail_tpl" type="text/template">
<ul class="ui-list">
	<li class="ui-border-b ui-order-code">订单编号: <%=ordersn%></li>
	<%_.each(goods_list, function(goods) {%>
	<li class="ui-padding-bottom-0">
		<div class="ui-list-thumb">
			<span style="background-image:url(<%=goods.goods.cover%>)"></span>
		</div>
		<div class="ui-list-info ui-padding-right-0">
			<p class="ui-goods-title-12"><%=goods.goods_name%></p>
			<p class="">规格：<%=goods.style_name%></p>
		</div>
	</li>
	<li class="ui-padding-top-0">
		<h2 class="ui-text-color-primary">总计:<%=(goods.price * goods.num / 100)%></h2>
		<div class="ui-list-action">数量：<%=goods.num%></div>
	</li>
	
    <li class="ui-border-t ui-report">
        <h4>产品提成: <%=goods.scale%>%</h4>
        <div class="ui-list-action">实际收益:  <span class="ui-text-color-primary">  &#165;<%=(goods.profit/100)%></span></div>
    </li>
	<%});%>
</ul>
<div class="ui-form">
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>下单时间</label>
		<p><%=_created%></p>
	</div>
	<%if (!_.isEmpty(express)) {%>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>快递公司</label>
		<p><%=express%></p>
	</div>
	<%}%>
	<%if (!_.isEmpty(expressn)) {%>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>快递单号</label>
		<p><%=expressn%></p>
	</div>
	<%}%>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>订单状态</label>
		<p class="ui-text-color-primary ui-padding-bottom-0" id="order_status"><%=_order_status%></p>
	</div>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>购 买 人</label>
		<p><%=customer_name%></p>
	</div>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>联系电话</label>
		<p><%=mobile%></p>
	</div>
	<%if (!_.isEmpty(sale_phone)) {%>
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
			<label>卖家电话</label>
			<p><%=sale_phone%></p>
		</div>
	</div>
	<%}%>
	<%if (!_.isEmpty(expid)) {%>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>快递费用</label>
		<p class="ui-text-color-primary"><%=express_price%> (<%=express_name%>)</p>
	</div>
	<%}%>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>订单总额</label>
		<p class="ui-text-color-primary">&#165; <%=(amount / 100)%></p>
	</div>
	<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
		<label>总收益</label>
		<p class="ui-text-color-primary">&#165; <%=(profit/100)%></p>
	</div>

</div>


</script>
{/literal}

<script type="text/javascript">
var orderid = '{$orderid}';
{literal}
require(["zepto", "underscore", "frozen"], function($, _, fz) {

	var el = $.loading({
		content: '加载中...'
	});

	// 取订单信息
	$.ajax({
		'type': 'GET',
		'url': '/api/order/get/cpdetail?orderid=' + orderid,
		'success': function(data, status, xhr) {
		
		    if (data.errcode != 0) {
		        alert(data.errmsg);
		    }else {
				$("#orderdetail").append(_.template($("#orderdetail_tpl").html(), data["result"]));
				var ts = data["result"]["created"] - Math.floor(_.now() / 1000) + 3600;
				setInterval(function() {
					countdown(ts --);
				}, 1000);
				el.loading('hide');
		    }
		}
	});

	// 取消订单
	$("#orderdetail").on("tap", ".cancel_order", function(e) {
		var el = $.loading({
			content: '加载中...'
		});
		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/delete',
			'data': {'orderid': orderid},
			'success': function(data, status, xhr) {
				window.location.href = "/frontend/travel/orderlist";
				el.loading('hide');
			}
		});
	});

	// 监听重新付款按钮
	$("#orderdetail").on("tap", ".repay", function(e) {
		var el = $.loading({
			content: '加载中...'
		});
		var abtn = $(this);
		// 请求详情
		$.ajax({
			'type': 'GET',
			'url': '/api/order/get/pay2?orderid=' + orderid,
			'success': function(data, status, xhr) {
				WeixinJSBridge.invoke(
					'getBrandWCPayRequest',
					data["result"],
					function(res) {
						if (res.err_msg == 'get_brand_wcpay_request:ok') {
							abtn.parent().remove();
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

	// 倒计时
	function countdown(ts) {

		var h = Math.floor(ts / 3600);
		var left = ts % 3600, i = Math.floor(left / 60), s = left % 60;
		var show = "";
		if (0 < h) {
			show += h + "小时";
		}

		if (0 < i) {
			show += i + "分";
		}

		if (0 < s) {
			show += s + "秒";
		}

		// 如果需要显示的字串为空, 则剔除
		if ("" == show) {
			$(".countdown").parent().remove();
			$(".buycancel").remove();
			return true;
		}

		$(".countdown").html(show);

		return true;
	}
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}