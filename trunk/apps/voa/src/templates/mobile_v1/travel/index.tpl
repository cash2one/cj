{include file='mobile_v1/header.tpl'}

<script type="text/javascript">
require(["http://res.wx.qq.com/open/js/jweixin-1.0.0.js"], function (wx) {
	
	// 载入接口
	{cyoa_jsapi list=['onMenuShareAppMessage', 'onMenuShareTimeline', 'onMenuShareQQ'] debug=0}
	
});
</script>

{include file='mobile_v1/footer.tpl'}