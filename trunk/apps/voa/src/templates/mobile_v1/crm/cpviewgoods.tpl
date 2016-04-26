{include file='mobile_v1/header_fz.tpl'}

<div class="ui-goods-detaile">
	<a href="javascript:history.go(-1);" class="ui-back-a"><i class="ui-back"></i></a>
	<div class="ui-slider">
		<ul class="ui-slider-content" style="width:300%;" id="slider"></ul>
	</div>
	<div class="ui-form ui-margin-0 ui-margin-bottom-10" id="goodsbase"></div>
	<div class="ui-btn-wrap">
		<button class="ui-btn-lg ui-btn-primary">分享给客户</button>
	</div>
	<div class="ui-form ui-margin-0 ui-goods-conten ui-margin-bottom-10" id="goodsdetail"></div>
</div>

{literal}
<script id="slider_tpl" type="text/template">
<%if (!_.isEmpty(slide)) {%>
<%_.each(slide, function(item) {%>
<li>
	<span style="background-image:url(<%=item.url%>)"></span>
</li>
<%});%>
<%} else {%>
<li></li>
<%}%>
</script>

<script id="goodsdetail_tpl" type="text/template">
<div class="ui-form-item ui-form-item-order ui-border-b ui-goods-item">产品详情</div>
<div class="ui-main-content"><%=message%></div>
</script>

<script id="goodsbase_tpl" type="text/template">
<div class="ui-form-item ui-form-item-show ui-border-t ui-conten-more ui-goods-title">
	<p class="ui-padding-bottom-0"><%=subject%></p>
</div>
<div class="ui-form-item ui-form-item-show ui-conten-more ui-goods-standard">
	<label>&#165; <%=price%></label>
	<p>货号:<%=goodsnum%>  &nbsp;累计售出:<%=saledcount%></p>
</div>
</script>
{/literal}

<script type="text/javascript">
var dataid = '{$goodsid}';
var goodsdetail = {};

require(["jweixin", "qyjsapi"], function(wx, jsapi) {
	// 初始化微信接口加载未完成
	var wx_loaded = false;
	{cyoa_jsapi list=['hideMenuItems', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareTimeline'] debug=0}
	wx.ready(function () {
		// 微信接口验证完毕
		wx_loaded = true;
		// 隐藏菜单项
		wx.hideMenuItems({
		    menuList: ['menuItem:share:appMessage', 'menuItem:share:timeline', 'menuItem:share:qq', 'menuItem:copyUrl']
		});
	});

	var api = new jsapi(wx);
	// 循环检查微信接口是否加载
    var it = setInterval(function () {
    	// 如果已加载微信接口
    	if (wx_loaded) {
    		api.appmsg({
    			title: goodsdetail['subject'], // 分享标题
    			desc: goodsdetail['message'], // 分享描述
    			link: '/frontend/travel/viewgoods/goodsid/' + goodsdetail['dataid'], // 分享链接
    			imgUrl: goodsdetail['cover'], // 分享图标
    			type: 'link', // 分享类型,music、video或link，不填默认为link
    			dataUrl: '' // 如果type是music或video，则要提供数据链接，默认为空
    		});

    		api.timeline({
    			title: goodsdetail['subject'],
    			link: '/frontend/travel/viewgoods/goodsid/' + goodsdetail['dataid'],
    			imgUrl: goodsdetail['cover']
    		});

    		api.shareqq({
    			title: goodsdetail['subject'], // 分享标题
    			desc: goodsdetail['message'], // 分享描述
    			link: '/frontend/travel/viewgoods/goodsid/' + goodsdetail['dataid'], // 分享链接
    			imgUrl: goodsdetail['cover']
    		});
    	}
    }, 500);

});

{literal}
require(["zepto", "underscore", "showlist", "frozen"], function($, _, showlist, fz) {
	var el = $.loading({
		content: '加载中...'
	});
	// 请求详情
	$.ajax({
		'type': 'GET',
		'url': '/api/travel/get/goodsdetail/?dataid=' + dataid,
		'success': function(data, status, xhr) {
			goodsdetail = data["result"];

			$("#slider").html(_.template($("#slider_tpl").html(), goodsdetail));
			$("#goodsdetail").html(_.template($("#goodsdetail_tpl").html(), goodsdetail));
			$("#goodsbase").html(_.template($("#goodsbase_tpl").html(), goodsdetail));
			var slider = new window.fz.Scroll('.ui-slider', {
				role: 'slider',
				indicator: true,
				autoplay: true,
				interval: 3000
			});
			el.loading('hide');
		}
	});
});
{/literal}
</script>

{include file='mobile_v1/footer_fz.tpl'}