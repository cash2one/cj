{include file='mobile_v1/header_fz.tpl'}

<div class="ui-myorder">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="/frontend/travel/cpindex" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">销售订单</h3>
			</div>
		</div>
	</div>
	<div  id="orderlist"><div>
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
	<li class="ui-padding-top-0 ui-padding-bottom-0 to_order_detail"  data-href="/frontend/travel/cporderdetail?orderid=<%=order.orderid%>">
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
		<div class="ui-order-payment">&nbsp;</div>
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
	sl.show_ajax({'url': '/api/order/get/list/'}, {
		"dist": $('#orderlist'),
		"cb": function(dom) {
			//alert(dom.html());
		}
	});

	// 监听查看点击
	$("#orderlist").on("tap", ".to_order_detail", function(e) {
		window.location.href = $(this).data("href");
	});

});
</script>
{/literal}

{include file='mobile_v1/footer_fz.tpl'}