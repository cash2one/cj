{include file='mobile/header.tpl' navtitle='畅移云工作'}

<section class="ui-notice ui-notice-success kdzs-ui-success"> <i></i>
	<h2>欢迎使用畅移云工作平台！</h2>
</section>
<div style="padding-top:50px;text-align:center">
	<br />
	<br />
	<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
		<button id="w-close" class="ui-btn-lg ui-btn-primary">关闭</button>
<!--
		<br />
		<button id="w-share" class="ui-btn-lg ui-btn-primary">分享朋友圈</button>
		<br />
		<button id="w-friend" class="ui-btn-lg ui-btn-primary">分享朋友</button>
-->
	</div>
</div>

<script type="text/javascript">
require(["zepto", "underscore", "frozen", "jweixin"], function ($, _, fz, wx) {
	// 初始化微信接口加载未完成
	var wx_loaded = false;
	{cyoa_jsapi list=['closeWindow', 'onMenuShareTimeline', 'onMenuShareAppMessage'] debug=0}
	// 微信接口加载完毕
	wx.ready(function () {
		// 微信接口验证完毕
		wx_loaded = true;
	});
	// 绑定点击关闭按钮动作
	$('#w-close').click(function () {
		// loading
		var __loading = $.loading({
			"content": "请稍候……"
		});
		// 循环检查微信接口是否加载
		var it = setInterval(function () {
			// 如果已加载微信接口
			if (wx_loaded) {
				// 隐藏loading
				__loading.loading("hide");
				// 关闭窗口
				wx.closeWindow();
				// 停止定时器
				clearInterval(it);
				return false;
			}
		}, 500);
	});

	// 绑定分享
	$('#w-share').click(function () {
		// loading
		var __loading = $.loading({
			"content": "请稍候……"
		});
		// 循环检查微信接口是否加载
		var it = setInterval(function () {
			// 如果已加载微信接口
			if (wx_loaded) {
				// 隐藏loading
				__loading.loading("hide");
				// 分享
				wx.onMenuShareTimeline({
					"title": '畅移云工作', // 分享标题
					"link": 'http://www.vchangyi.com/', // 分享链接
					"imgUrl": 'http://1251064102.cdn.myqcloud.com/1251064102/changyi_main/15010602/images/v141010/logo_black.png', // 分享图标
					"success": function (res) {
						alert('success: ' + JSON.stringify(res));
					},
					"cancel": function () {
						alert('cancel: ' + JSON.stringify(res));
					},
					"fail": function (res) {
						alert('fail: ' + JSON.stringify(res));
					},
					"complete": function (res) {
						alert('complete: ' + JSON.stringify(res));
					},
					"trigger": function (res) {
						alert('trigger: ' + JSON.stringify(res));
					}
				});
				// 停止定时器
				clearInterval(it);
				return false;
			}
		}, 500);
	});

	// 绑定分享
	$('#w-friend').click(function () {
		// loading
		var __loading = $.loading({
			"content": "请稍候……"
		});
		// 循环检查微信接口是否加载
		var it = setInterval(function () {
			// 如果已加载微信接口
			if (wx_loaded) {
				// 隐藏loading
				__loading.loading("hide");
				// 分享
				wx.onMenuShareAppMessage({
					"title": '畅移云工作', // 分享标题
					"desc": '分享描述', // 分享描述
					"link": 'http://demo.vchangyi.com/', // 分享链接
					"imgUrl": 'http://1251064102.cdn.myqcloud.com/1251064102/changyi_main/15010602/images/v141010/logo_black.png', // 分享图标
					"type": 'link', // 分享类型,music、video或link，不填默认为link
					"dataUrl": '', // 如果type是music或video，则要提供数据链接，默认为空
					"success": function (res) {
						alert('success: ' + JSON.stringify(res));
					},
					"cancel": function () {
						alert('cancel: ' + JSON.stringify(res));
					},
					"fail": function (res) {
						alert('fail: ' + JSON.stringify(res));
					},
					"complete": function (res) {
						alert('complete: ' + JSON.stringify(res));
					},
					"trigger": function (res) {
						alert('trigger: ' + JSON.stringify(res));
					}
				});
				// 停止定时器
				clearInterval(it);
				return false;
			}
		}, 500);
	});
});
</script>

{include file='mobile/footer.tpl'}