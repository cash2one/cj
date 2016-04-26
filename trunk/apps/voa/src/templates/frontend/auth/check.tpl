{include file='mobile/header.tpl' navtitle="登陆PC端"}
<style>
	body {
		background-color: #f6f7f7;
	}
	.ui-btn-login {
		background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.5, #40bff4), to(#40bff4));
		width: 300px;
		height: 40px;
		margin: 0 auto;
		border-radius: 4px;
		border: 0;
	}

	.ui-btn-login:not(.disabled):not(:disabled):active, .ui-btn-login:active {
		background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0.5, #40AFB8), to(#40AFB8));
	}

	.center {
		text-align: center;
	}

	.cancel-login-a {
		margin: 10px auto;
		color: #777;
		font-size: 15px;
		font-family: "Hiragino Sans GB", "Microsoft YaHei", "黑体", Verdana, Geneva, sans-serif;
	}

	.margin {
		margin: 0 auto;
	}

	.ui-btn-text {
		font-family: "Hiragino Sans GB", "Microsoft YaHei", "黑体", Verdana, Geneva, sans-serif;
		font-size: 18px;
	}
	.text-message {
		font-size: 18px;
		color: #333;
		font-family: "Hiragino Sans GB", "Microsoft YaHei", "黑体", Verdana, Geneva, sans-serif;
	}
	.img-computer {
		margin-top: 100px;
	}
	.text-message-div {
		margin-top: 20px;
	}
	.btn-login-margin-top {
		margin-top: 70px;
	}
	.ui-btn-login-gai {
		color: #fff;
	}
</style>
<body>

<div class="center">
	<img class="margin img-computer" src="{$m_info['m_face']}" width="150px" height="123px"/>
</div>


<div class="center">
	<div class="cancel-login-a" id="w_close">姓名：{$m_info['m_username']}</div>
</div>
<div class="center">
	<div class="cancel-login-a" id="w_close">部门：{$dep_name}</div>
</div>
<div class="ui-btn-wrap btn-login-margin-top center">
	<button class="ui-btn-login-gai ui-btn-login margin" id="send_login">
		<span class="ui-btn-text">确认是本人,马上登录</span>
	</button>
</div>


</body>
<script type="text/javascript">

	require(["zepto", "underscore", "frozen", "jweixin"], function ($, _, fz, wx) {
		/**
		 * 页面微信关闭接口调用
		 */
		// 初始化微信接口加载未完成
		var wx_loaded = false;
		{cyoa_jsapi list=['closeWindow'] debug=0}
		// 微信接口加载完毕
		wx.ready(function () {
			// 微信接口验证完毕
			wx_loaded = true;
		});
		// 绑定点击关闭按钮动作
		$('#w_close').click(function () {
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


		/**
		 * 登录，通讯
		 */
		var authcode = "{$authcode}";
		var timestamp = "{$auth_timestamp}";
		var singture = "{$singture}";
		$('#send_login').on('click', function () {
			$(this).prop("disabled", true);
			$.ajax({
				"type": "post",
				"dataType": "json",
				"url": "/api/auth/post/authlogin",
		{literal}
				"data": {"authcode": authcode, "timestamp": timestamp, "singture": singture},
				"success": function (data) {
					if (data.errcode == 0) {
						// 调用微信关闭接口关闭页面
						var __loading = $.loading({
							"content": "登录成功！"
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
					} else {
						$.tips({content:data.errmsg});
					}
					$(this).prop("disabled", false);
				},
				"error": function () {
					$.tips({content:'网络发生错误！'});
				}
			});
		});
		{/literal}

	});
</script>

{include file='mobile/footer.tpl'}
