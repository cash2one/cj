<html>
<head>
	<link rel="stylesheet" href="//cdn.bootcss.com/weui/0.4.0/style/weui.min.css">
	<script src="http://cdn.bootcss.com/jquery/3.0.0-beta1/jquery.min.js"></script>

	<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1"/>
	<title>微信支付</title>
	<script type="text/javascript">
		//调用微信JS api 支付
		function jsApiCall() {
			WeixinJSBridge.invoke(
					'getBrandWCPayRequest',
					{
						"appId": "{$appId}", //公众号名称，由商户传入
						"timeStamp": "{$timeStamp}", //时间戳，自1970年以来的秒数
						"nonceStr": "{$nonceStr}", //随机串
						"package": "{$package}",
						"signType": "{$signType}", //微信签名方式：
						"paySign": "{$paySign}" //微信签名
					},
					function (res) {
						if (res.err_msg == "get_brand_wcpay_request:cancel") {
							return false;
						}
						// 显示支付完成页面
						if (res.err_msg == "get_brand_wcpay_request:ok") {
							if ("{$success_url}" != "") {
								window.location.href = "{$success_url}";
							}
						}
						// 显示错误页面
						if (res.err_msg == "get_brand_wcpay_request:fail") {
							if ("{$error_url}" != "") {
								url = "{$error_url}" + '?error_msg=' + res.err_desc;
								window.location.href = url;
							}
						}
					}
			);
		}
		function callpay() {
			if (typeof WeixinJSBridge == "undefined") {
				if (document.addEventListener) {
					document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
				} else if (document.attachEvent) {
					document.attachEvent('WeixinJSBridgeReady', jsApiCall);
					document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
				}
			} else {
				jsApiCall();
			}
		}

	</script>
</head>

<body>

<!--支付页面-->
<div id="pay_msg">

	<div class="weui_msg">
		<div class="weui_icon_area"><i class="weui_icon_waiting weui_icon_msg"></i></div>
	</div>

	<div class="weui_cells">
		<div class="weui_cell">
			<div class="weui_cell_bd weui_cell_primary">
				<p>支付金额</p>
			</div>
			<div class="weui_cell_ft">
				¥ {$total_fee}
			</div>
		</div>
		<div class="weui_cell">
			<div class="weui_cell_bd weui_cell_primary">
				<p>支付内容</p>
			</div>
			<div class="weui_cell_ft">
				{$body}
			</div>
		</div>
	</div>

	<div class="weui_opr_area">
		<p class="weui_btn_area">
			<a class="weui_btn weui_btn_primary" onclick="callpay();">立即支付</a>
		</p>
	</div>
</div>

</body>
</html>