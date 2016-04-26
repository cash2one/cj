{include file='mobile/header.tpl' }

<section class="ui-notice ui-notice-fail kdzs-ui-margin-bottom"> <i></i>
    <h2>快递领取失败！</h2>
    <p>{$error}</p>
</section>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button id="cancle" class="ui-btn-lg ui-btn-primary">确定</button>
</div>

<script>
require(["zepto", "jweixin","frozen"], function($,wx,fz) {

	   //微信接口是否加载完毕
		var wx_loaded = false;
		{cyoa_jsapi list=['closeWindow'] debug=0}
		// 微信接口加载完毕
		wx.ready(function () {
			wx_loaded = true;
		});
		
		//取消
		$('#cancle').click(function(e) {
			var __loading = $.loading({
				"content": "请稍候……"
			});
			// 循环检查微信接口是否加载
			var it = setInterval(function () {
				if (wx_loaded) {
					__loading.loading("hide");
					wx.closeWindow();
					clearInterval(it);
					return false;
				}
			}, 500);
		});
	
	
});
</script>
{include file='mobile/footer.tpl'}

