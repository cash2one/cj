{include file='mobile_v1/header_fz.tpl'}

<div class="ui-goods-select">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="javascript:history.go(-1);" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix" id="classname"></h3>
			</div>
		</div>
	</div>

	<ul class="ui-list">
		<li id="goodsdetail"></li>
		<li class="ui-border-t ui-padding-bottom-0" id="goodsstyle_label">规格</li>
		<li class="ui-grid-select" id="goodsstyle"></li>
		<li class="ui-border-t ui-padding-bottom-0">数量</li>
		<li class="">
			<div class="shp-cart-opt">
				<div class="quantity-wrapper">
					<input type="hidden" id="styleid" name="styleid" value="" />
					<a class="quantity-decrease disabled" id="decrease" href="javascript:;">-</a>
					<input type="text" size="4" value="1" name="num" id="num" class="quantity" />
					<a class="quantity-increase" id="increase" href="javascript:;">+</a>
				</div>
			</div>
			<!--<div class="ui-list-action" id="amount">库存：0</div>-->
		</li>
	</ul>
	<div class="ui-btn-group-tiled ui-btn-wrap">
		<button id="addtocart" class="ui-btn-lg">加入购物车</button>
		<button id="addtoorder" class="ui-btn-lg ui-btn-primary">确认购买</button>
	</div>
</div>

{literal}
<script id="goodsdetail_tpl" type="text/template">
<div class="ui-list-thumb">
	<span style="background-image:url(<%=cover%>)"></span>
</div>
<div class="ui-list-info">
	<p class="title"><%=subject%></p>
	<h4 class="ui-text-color-primary">&#165; <%=price%></h4>
</div>
</script>

<script id="goodsstyle_tpl" type="text/template">
<ul class="ui-grid-halve">
<%_.each(styles, function(item) {%>
	<li><div class="ui-border style_div<%if (defstyleid == item.styleid) {%> ui-select<%}%>" data-styleid="<%=item.styleid%>"><%=item.stylename%></div></li>
<%});%>
</ul>
</script>
{/literal}

<script type="text/javascript">
var dataid = '{$goodsid}';
var goodsclass = {$jsongoodsclass};
var styles = {};
{literal}
require(["zepto", "underscore", "frozen"], function($, _, fz) {

	var el = $.loading({
		content: '加载中...'
	});
	// 请求详情
	$.ajax({
		'type': 'GET',
		'url': '/api/travel/get/goodsdetail/?dataid=' + dataid,
		'success': function(data, status, xhr) {
			// 获取指定规格
			var styleid = 0;
			var leftstyleid = 0;
			var defstyleid = 0;
			var price = data["result"]["price"];
			var style_count = 0;
			styles = data["result"]["styles"];
			_.each(styles, function(sty) {

				style_count ++;
				if (0 == styleid && price == sty["price"]) {
					styleid = sty["styleid"];
					return true;
				}

				if (0 == defstyleid) {
					defstyleid = sty["styleid"];
				}

				if (0 == leftstyleid && 0 < sty["amount"]) {
					leftstyleid = sty["styleid"];
					return true;
				}
			});

			// 分类名称
			if (0 < data["result"]["classid"] && _.has(goodsclass, data["result"]["classid"])) {
				$("#classname").text(goodsclass[data["result"]["classid"]]["classname"]);
			} else {
				$("#classname").text("选择购买数量");
			}

			// 详情
			styleid = 0 == styleid ? defstyleid : styleid;
			var style = styles[styleid];
			if (0 < style["amount"]) {
				// do nothing.
			} else if (0 < leftstyleid && 0 < styles[leftstyleid]["amount"]) {
				style = styles[leftstyleid];
			} else if(0 < defstyleid) {
				style = styles[defstyleid];
			}

			data["result"]["price"] = style["price"];
			data["result"]["defstyleid"] = style["styleid"];
			$("#styleid").val(style["styleid"]);
			$("#amount").html("库存：" + style["amount"]);
			$("#goodsdetail").html(_.template($("#goodsdetail_tpl").html(), data["result"]));
			if (1 < style_count) {
				$("#goodsstyle").html(_.template($("#goodsstyle_tpl").html(), data["result"]));
			} else {
				$("#goodsstyle").hide();
				$("#goodsstyle_label").hide();
			}

			el.loading('hide');
		}
	});

	$("#goodsstyle").on("tap", ".style_div", function(e) {
		var styleid = $(this).data("styleid");
		$("#goodsstyle").find(".ui-select").removeClass('ui-select');
		$(this).addClass('ui-select');
		$("#styleid").val(styleid);
		$("#amount").html("库存：" + styles[styleid]["amount"]);
	});

	$("#decrease").on("tap", function(e) {
		var num = $("#num").val();
		if (1 == num) {
			return;
		}

		-- num;
		$("#num").val(num);
	});

	$("#increase").on("tap", function(e) {
		var num = $("#num").val();
		var styleid = $("#styleid").val();
		if (num >= parseInt(styles[styleid]["amount"])) {
			$.tips({
				content: '商品库存不足',
				stayTime: 2000,
				type: "warn"
			});
			return;
		}

		++ num;
		$("#num").val(num);
	});

	// 加入购物车
	$("#addtocart").on("tap", function(e) {
		var el = $.loading({
			content:'正在加入购物车...'
		});
		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/cartadd',
			'data': {'goods_id': dataid, "num": $("#num").val(), "styleid": $("#styleid").val()},
			'success': function(data, status, xhr) {
				var dlg = $.dialog({
					title:'',
					content:'加入购物车成功',
					button:["确认"]
				});
				dlg.on("dialog:action", function(e) {
					history.go(-1);
				});
				el.loading("hide");
			}
		});

		return false;
	});

	// 下单
	$("#addtoorder").on("tap", function(e) {

		var el = $.loading({
			content:'正在下单...'
		});
		$.ajax({
			'type': 'POST',
			'url': '/api/order/post/cartadd',
			'data': {"goods_id": dataid, "num": $("#num").val(), "styleid": $("#styleid").val()},
			'success': function(data, status, xhr) {
				el.loading("hide");
				window.location.href = "/frontend/travel/pay?cartids=" + data["result"];
			}
		});

		return false;
	});
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}