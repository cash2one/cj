{include file='mobile/header.tpl' css_file='app_invite_introduction.css'}



<div class="success-head-area">
	<img src="../../../include/../../../misc/images/mdlg_icon_succ.png" width="60px" height="60px" class="success-head-img">
	<div class="success-head-text">信息提交成功！</div>
</div>

<div class="success-text-area">
	<div class="success-text">温馨提示：<br /></div>
	<div class="success-text1">您的信息已经提交并通知管理员，请耐心等候管理员审批吧！</div>
</div>

<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
	<button id="w-close" class="ui-btn-lg ui-btn-primary" style="margin-top: 176px;">确定</button>
</div>


<script type="text/javascript">
	require(["zepto", "underscore", "frozen", "jweixin"], function ($, _, fz, wx) {
		// 初始化微信接口加载未完成
		var wx_loaded = false;
		{cyoa_jsapi list=['closeWindow'] debug=0}
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
			}, 501);
		});
	});
</script>



{include file='mobile/footer.tpl'}