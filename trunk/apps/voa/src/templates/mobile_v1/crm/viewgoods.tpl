{include file='mobile_v1/header_fz.tpl'}

<div class="ui-goods-detaile">
	<a href="{if 0 < $page}/frontend/travel/list/page/{$page}{else}javascript:history.go(-1);{/if}" class="ui-back-a"><i class="ui-back"></i></a>
	<div class="ui-slider">
		<ul class="ui-slider-content" style="width:300%;" id="slider"></ul>
	</div>
	<div class="ui-form ui-margin-0 ui-margin-bottom-10" id="goodsbase"></div>
	<div class="ui-list ui-margin-0 ui-goods-conten ui-margin-bottom-10" id="goodsdetail"></div>
	<div class="ui-tab-nav ui-tab-nav-footer ui-border-t">
		<a href="/frontend/travel/selectstyle?goodsid={$goodsid}" class="ui-btn-shopping">立即购买</a>
		<a href="/frontend/travel/selectstyle?goodsid={$goodsid}" id="addtocart" class="ui-btn-cart ui-icon">加入购物车</a>
		<a href="/frontend/travel/list" class="ui-btn-collect ui-icon">猜你喜欢</a>
	</div>
	<a href="/frontend/travel/mycart" class="ui-cart"> <i class="ui-icon ui-icon-cart"><span id="cart_total">0</span></i></a>
</div>

{literal}
<script id="slider_tpl" type="text/template">
<%if (!_.isEmpty(slide)) {%>
<%_.each(slide, function(item) {%>
<li>
	<span style="background-image:url(<%=item.url%>)"></span>
</li>
<%});%>
<%} else {%>
<li></li>
<%}%>
</script>

<script id="goodsdetail_tpl" type="text/template">
<div class="ui-form-item ui-form-item-order ui-border-b ui-goods-item">产品详情</div>
<div class="ui-main-content"><%=message%></div>
</script>

<script id="goodsbase_tpl" type="text/template">
<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more ui-goods-title">
	<p class="ui-padding-bottom-0"><%=subject%></p>
</div>
<div class="ui-form-item ui-form-item-show ui-conten-more ui-goods-standard">
	<label>&#165; <%=price%></label>
	<p>货号:<%=goodsnum%>  &nbsp;<!--累计售出:<%=saledcount%>--></p>
</div>
</script>
{/literal}

<script type="text/javascript">
var dataid = '{$goodsid}';
var styleid = 0;

{literal}
require(["zepto", "underscore", "frozen", "jweixin"], function($, _, fz, wx) {

	var el = $.loading({
		content: '加载中...'
	});
	// 请求详情
	$.ajax({
		'type': 'GET',
		'url': '/api/travel/get/goodsdetail/?dataid=' + dataid,
		'success': function(data, status, xhr) {
			var result = data["result"];
			$("#slider").html(_.template($("#slider_tpl").html(), result));
			$("#goodsdetail").html(_.template($("#goodsdetail_tpl").html(), result));
			$("#goodsbase").html(_.template($("#goodsbase_tpl").html(), result));
			$("#cart_total").text(result["cart_total"]);
			var slider = new window.fz.Scroll('.ui-slider', {
				role: 'slider',
				indicator: true,
				autoplay: true,
				interval: 3000
			});

			// 获取规格
			for (var id in result["styles"]) {
				if (0 < result["styles"]["amount"]) {
					styleid = result["styles"]["styleid"];
					break;
				}
			}

			// 隐藏
			el.loading("hide");
		}
	});

	// 加入购物车
	/**$("#addtocart").on("tap", function(e) {
		var el = $.loading({
			content:'正在加入购物车...'
		});
		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/cartadd',
			'data': {'goods_id': dataid, "num": 1, "styleid": styleid},
			'success': function(data, status, xhr) {
				var dia = $.dialog({
					title:'',
					content:'加入购物车成功',
					button:["确认"]
				});
				el.loading("hide");
			}
		});
	});*/
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}