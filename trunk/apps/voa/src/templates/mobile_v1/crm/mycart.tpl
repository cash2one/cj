{include file='mobile_v1/header_fz.tpl'}

<div class="ui-mycart">
	<div class="ui-selector ui-selector-line">
		<div class="ui-selector-content">
			<div class="ui-selector-item ui-border-b">
				<a href="javascript:history.go(-1);" class="ui-back-a"> <i class="ui-back"></i></a>
				<h3 class="clearfix">我的购物车</h3>
				<a href="javascript:;" id="cpmode" class="ui-back-a ui-text-a ui-pull-right ui-text-color-primary">编辑</a>
			</div>
		</div>
	</div>

	<form id="frm2pay" method="POST" action="/frontend/travel/pay">
	<div><ul class="ui-list ui-list-checkbox" id="list"></ul></div>
	<div class="ui-tab-nav ui-tab-nav-footer ui-border-t">
		<div class="ui-btn-group-tiled ui-btn-wrap">
			<label class="ui-checkbox">
				<input type="checkbox" id="chkall" />
				<span id="show_fee">全选</span>
			</label>
			<a class="ui-btn ui-btn-primary" id="cpbtn" data-mode="buy">购买</a>
		</div>
	</div>
	</form>
</div>

{literal}
<script id="list_tpl" type="text/template">
<%if (_.isEmpty(list)) {%>
<%$("#list").parent().addClass("ui-cart-empmt");$("#list").remove();%>
<div class="ui-notice">
	<div class="ui-center">
		<h2>购物车快饿瘪 TAT</h2>
		<p>快给我挑点儿宝贝儿</p>
	</div>
	<div class="ui-btn-wrap">
		<a href="/frontend/travel/list" class="ui-btn-lg ui-btn-primary">去逛逛</a>
	</div>
</div>
<%} else {%>
<%_.each(list, function(item) {%>
<li class="ui-padding-bottom-0" id="detail_<%=item.cartid%>">
	<label class="ui-checkbox">
		<input type="checkbox" class="cartid" data-num="<%=item.num%>" name="cartids[<%=item.cartid%>]" value="<%=item.cartid%>" />
	</label>
	<div class="ui-list-thumb">
		<span style="background-image:url(<%=item.goods.cover%>)"></span>
	</div>
	<div class="ui-list-info">
		<p class="title"><%=item.goods_name%></p>
		<p>规格：<%=item.style_name%></p>
	</div>
</li>
<li class="ui-border-b ui-padding-top-0" id="fn_<%=item.cartid%>">
	<h2 class="ui-text-color-primary" id="fee_<%=item.cartid%>" data-price="<%=item.price%>">&#165; <%=(item.price * item.num / 100)%></h2>
	<div class="ui-list-action">
		<div class="shp-cart-opt">
			<div class="quantity-wrapper">
				<a class="quantity-decrease disabled" data-cartid="<%=item.cartid%>" href="javascript:;">-</a>
				<input type="text" size="4" value="<%=item.num%>" name="num[<%=item.cartid%>]" id="num_<%=item.cartid%>" class="quantity" />
				<a class="quantity-increase" data-cartid="<%=item.cartid%>" data-amount="<%=item.style_amount%>" href="javascript:;">+</a>
			</div>
		</div>
	</div>
</li>
<%});%>
<%}%>
</script>

<script type="text/javascript">
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {

	// ajax 回调
	function aj_success(data, status, xhr) {
		data["result"]["list"] = [];
		if (_.has(data, "result")) {
			data["result"]["list"] = data["result"];
		}

		return data;
	}

	// 调用 ajax 并显示
	var sl = new showlist();
	sl.show_ajax({'url': '/api/order/get/cartlist', 'success': aj_success}, {
		"dist": $('#list'),
		"cb": function(dom) {
			// do nothing.
		}
	});

	// 购买
	function to_buy() {
		var cartids = [];
		var el = $.loading({
			content: '加载中...'
		});
		var update_cart = false;
		// 取已选中的购物车中得商品
		$(".cartid").each(function(index) {
			var chkipt = $(this);
			// 未选中
			if (!chkipt.prop("checked")) {
				return true;
			}

			// 如果数量未改动
			var numipt = $("#num_" + chkipt.val());
			if (chkipt.data('num') == numipt.val()) {
				cartids.push(chkipt.val());
				return true;
			}

			// 更新数量
			$.ajax({
				'type': 'GET',
				'url': '/api/order/get/cartupdate/?cartid=' + chkipt.val() + "&num=" + numipt.val(),
				'success': function(data, status, xhr) {
					el.loading('hide');
					chkipt.data('num', numipt.val());
					to_buy();
				}
			});
			update_cart = true;
			return false;
		});

		// 如果正在更新数量
		if (true == update_cart) {
			return true;
		}

		el.loading('hide');
		// 判断是否为空
		if (0 == cartids.length) {
			$.dialog({
				title: '',
				content: '请选择需要购买的商品',
				button: ["确认"]
			});
			return false;
		}

		$("#frm2pay").submit();
		return true;
	}

	// 全选操作
	$("#chkall").on("click", function(e) {
		var checked = $(this).prop("checked");
		$(".cartid").each(function(index) {
			$(this).prop("checked", checked);
		});

		total_cart();
		return true;
	});

	// 删除购物车的商品
	function to_del() {
		var cartids = [];
		$(".cartid").each(function(index, item) {
			if (true == $(item).prop('checked')) {
				cartids.push($(item).val());
			}
		});

		// 如果用户未选择商品
		if (0 == cartids.length) {
			$.dialog({
				title: '',
				content: '请选择需要删除的商品',
				button: ["确认"]
			});
			return false;
		}

		// 开始进行删除操作
		var el = $.loading({
			content: '加载中...'
		});
		$.ajax({
			'type': 'GET',
			'url': '/api/order/get/cartdelete',
			'data': {'cartid': cartids},
			'success': function(data, status, xhr) {
				// 删除成功
				_.each(cartids, function(id) {
					$("#detail_" + id).remove();
					$("#fn_" + id).remove();
					return true;
				});

				// 判断购物车中是否还有产品
				if (0 >= $("#list").find("li").size()) {
					history.go(-1);
					return true;
				}

				el.loading("hide");
				return true;
			}
		});

		return false;
	}

	// 点击事件
	$("#cpmode").on("tap", function(e) {
		var cpmode = $(this);
		var cpbtn = $("#cpbtn");
		// 如果是购买模式
		if ('buy' == cpbtn.data("mode")) {
			cpbtn.data("mode", 'edit');
			cpmode.text("完成");
			cpbtn.text("删除");
		} else {
			cpbtn.data("mode", 'buy');
			cpmode.text("编辑");
			cpbtn.text("结算");
		}

		return true;
	});

	// 操作事件
	$("#cpbtn").on("tap", function(e) {
		if ('buy' == $(this).data("mode")) {
			to_buy();
		} else {
			to_del();
		}

		total_cart();
		return true;
	});

	// -1
	$("#list").on("click", ".quantity-decrease", function(e) {
		var cartid = $(this).data("cartid");
		var num = parseInt($("#num_" + cartid).val());
		if (1 == num) {
			return true;
		}

		-- num;
		$("#num_" + cartid).val(num);
		var price = $("#fee_" + cartid).data("price");
		$("#fee_" + cartid).html("&#165; " + (num * price / 100));
		total_cart();
		return true;
	});

	// +1
	$("#list").on("click", ".quantity-increase", function(e) {
		var cartid = $(this).data("cartid");
		var amount = $(this).data("amount");
		var num = parseInt($("#num_" + cartid).val());
		if (num >= amount) {
			return true;
		}

		num ++;
		$("#num_" + cartid).val(num);
		var price = $("#fee_" + cartid).data("price");
		$("#fee_" + cartid).html("&#165; " + (num * price / 100));
		total_cart();
		return true;
	});

	// 点击事件
	$("#list").on("click", ".cartid", function(e) {
		total_cart();
		return true;
	});

	// 计算总价
	function total_cart() {

		if ('buy' != $("#cpbtn").data("mode")) {
			return true;
		}

		var fee = 0;
		$(".cartid").each(function(index, item) {
			if (true == $(item).prop('checked')) {
				var cartid = $(item).val();
				fee += ($("#fee_" + cartid).data('price') * $("#num_" + cartid).val() / 100);
			}
		});

		// 如果选择了购物车商品
		if (0 == fee) {
			$("#show_fee").text('全选');
		} else {
			$("#show_fee").text(fee + ' 元');
		}

		return true;
	}
});
</script>
{/literal}

{include file='mobile_v1/footer_fz.tpl'}