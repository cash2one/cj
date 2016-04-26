{*
	载入微信jsapi接口模块
	
	$jsapi array 微信 js api 调用参数，必须提供
	$jsapi_list string 需要加载的微信jsapi列表，如：['chooseImage','previewImage']
	默认：会加载全部接口模块。但不推荐这么做，最好按需加载。
	$jsapi_debug 是否启用调试模式
	默认：0，不启用。
*}
{if empty($__IMAGE_UPLOADER_MODULE__)}
	{assign var=__IMAGE_UPLOADER_MODULE__ value="1" scope="root"}
	
	{if !empty($jsapi_debug)}
		{$jsapi_debug = 1}
	{else}
		{$jsapi_debug = 0}
	{/if}

	<script type="text/javascript">
	if (typeof(jQuery) == 'undefined' || !window.jQuery) {
		document.write("<script type=\"text/javascript\" src=\"{$wbs_javascript_path}/jquery-1.9.1.min.js\"><\/script>");
	}
	</script>
	<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
	<script type="text/javascript">
	jQuery(function(){
		wx.config({
			"appId": '{$jsapi['corpid']}',
			"timestamp": '{$jsapi['timestamp']}',
			"nonceStr": '{$jsapi['nonce_str']}',
			"signature": '{$jsapi['signature']}',
			"debug": {if $jsapi_debug}true{else}false{/if},
			"jsApiList": {if $jsapi_list}{$jsapi_list}{else}[
				'checkJsApi',
				'onMenuShareTimeline',
				'onMenuShareAppMessage',
				'onMenuShareQQ',
				'onMenuShareWeibo',
				'hideMenuItems',
				'showMenuItems',
				'hideAllNonBaseMenuItem',
				'showAllNonBaseMenuItem',
				'translateVoice',
				'startRecord',
				'stopRecord',
				'onVoiceRecordEnd',
				'playVoice',
				'pauseVoice',
				'stopVoice',
				'uploadVoice',
				'downloadVoice',
				'chooseImage',
				'previewImage',
				'uploadImage',
				'downloadImage',
				'getNetworkType',
				'openLocation',
				'getLocation',
				'hideOptionMenu',
				'showOptionMenu',
				'closeWindow',
				'scanQRCode',
				'chooseWXPay',
				'openProductSpecificView',
				'addCard',
				'chooseCard',
				'openCard'
			]{/if}
		});
	});
	</script>
{/if}
