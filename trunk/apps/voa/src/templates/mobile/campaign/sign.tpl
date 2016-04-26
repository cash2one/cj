{include file='mobile/header.tpl' navtitle="活动签到"}
<style>
.ui-tips i:before {
	background-image: url(/static/images/icon.png?_bid=306);
}
</style>
	<div id="oa-hy-signok">
		<div class="ui-tips ui-tips-success"> <i></i>&nbsp;&nbsp;&nbsp;
			已签到
		</div>
	</div>
	<div class="ui-form">
		<div class="ui-form-item ui-form-item-show ui-conten-more">
			<label>姓&nbsp;&nbsp;&nbsp;名</label>
			<p>{$reg.name}</p>
		</div>

		<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more">
			<label>手机号</label>
			<p>{$reg.mobile}</p>
		</div>
	</div>
	<div class="ui-btn-wrap">
		<button id="return" class="ui-btn-lg ui-btn-primary">确定</button>
	</div>

{include file='mobile/footer.tpl'}
<script>
require(["zepto"], function($) {
	//关闭页面
	$('#return').click(function (){
		if(window.WeixinJSBridge) wx_close_window();
	});
});
</script>