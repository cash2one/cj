{include file='mobile/header.tpl' }

<section class="ui-notice ui-notice-success kdzs-ui-success"> <i></i>
    <h2>快递领取成功！</h2>
</section>
<div class="ui-form kdzs-ui-margin-top kdzs-ui-margin-bottom">
    <div class="ui-form-item">
        <label for="#">收件人</label>
        <span class="ui-form-item-unit">{$express['username']}</span>
    </div>
    <div class="ui-form-item ui-border-t">
        <label for="#">手机号</label>
        <span class="ui-form-item-unit">{$express['phone']}</span>
    </div>
</div>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button id="scan_ok" class="ui-btn-lg ui-btn-primary" data-eid="{$express['eid']}">确定</button>
</div>

<script>

require(["zepto","jweixin", "frozen"], function($,wx,fz) {

	   //微信接口是否加载完毕
		var wx_loaded = false;
		{cyoa_jsapi list=['closeWindow'] debug=0}
		// 微信接口加载完毕
		wx.ready(function () {
			wx_loaded = true;
		});
		
		//扫描确认
		$('#scan_ok').click(function(e) {
			var url = "/frontend/express/scan?act=scan_ok&eid="+$(this).data('eid');
		    var __loading = $.loading({
					"content": "请稍候……"
		    });
		    $.ajax({
		        type: 'post',
		        url: url,
		        success: function(data){
					// 循环检查微信接口是否加载
					var it = setInterval(function () {
						if (wx_loaded) {
							__loading.loading("hide");
							wx.closeWindow();
							clearInterval(it);
							return false;
						}
					}, 500);
		        },
		        error: function(xhr, type){
		        }
		     }); 

		});

});

</script>

{include file='mobile/footer.tpl'}