{include file='mobile/header.tpl' css_file='app_activity.css'}

{if $is == 'true'}
	<div class="ui-tips ui-tips-success"><i></i>&nbsp;&nbsp;
		已签到
	</div>
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
			<label for="#">姓名</label>

			<p>{$user['name']}</p>
		</div>
		<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
			<label for="#">手机号</label>

			<p>{$user['phone']}</p>
		</div>
		{if $user['outsider'] == 1}
			<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
				<label for="#">备注</label>

				<p>{$user['remark']}</p>
			</div>
		{else}
			<div class="ui-form-item ui-form-item-show ui-conten-more ui-border-t">
				<label for="#">邮箱</label>

				<p>{$user['email']}</p>
			</div>
		{/if}
	</div>
	<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
		<button class="ui-btn-lg ui-btn-primary" id="w-close">确定</button>
	</div>
{else}
	<section class="ui-notice ui-notice-fail kdzs-ui-margin-bottom"><i></i>

		<h2>签到失败</h2>
	</section>
	<div class="ui-form">
		<div>
			{if $is == 1}
				<p class="hd-ui-wrap">请确认报名人是否成功报名</p>
			{elseif $is == 2}
				<p class="hd-ui-wrap">这个活动不存在</p>
			{elseif $is == 3}
				<p class="hd-ui-wrap">你没有权限扫描</p>
			{elseif $is == 4}
				<p class="hd-ui-wrap">已经签到过</p>
			{/if}
		</div>
	</div>
	<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
		<button id="w-close" class="ui-btn-lg ui-btn-primary">确定</button>
	</div>
{/if}

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