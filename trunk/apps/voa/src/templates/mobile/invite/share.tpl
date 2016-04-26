{include file='mobile/header.tpl' css_file='app_invite.css'}
<body style="height: 95%;">

<div style="display: none">
	{* 默认的分享图标 *}
	<img src="{$share_data['imgUrl']}" alt="" style="width: 300px; height: 300px" />
</div>

<div class="head">
	<div class="head-text">
		点击右上角将二维码分享出去<br />
		可快速邀请人员加入您的企业号
	</div>
</div>

<div class="central">
	<div class="central-circle-back">
		<img src="{if $logo}{$logo}{else}../../../include/../../../misc/images/invite_logo.jpg{/if}" width="80" height="80" style="border-radius: 50%" />
	</div>
	<div class="central-backing">
		<div class="central-backing-head">
			<p class="central-backing-head-text">{$sitename}</p>
		</div>
		<div class="central-qrcode">
			<img src="{$url}qrcode?timestamp={$qrcode_timestamp}&m_uid={$m_uid}" width="170px" height="170px">
		</div>

		<div class="share-area">
			<div class="share-text" onclick="wxshare()">分享给好友</div>
		</div>
		<div class="ui-dialog ui-dialog-bg" id="wxshare" onclick="wxshare()"></div>
	</div>
</div>

<div class="bottom">
	<div class="bottom-text">二维码将在{$overdue}日失效</div>
</div>

</body>

<script type="text/javascript">

	//  显示分享给好友
	function wxshare(){
		$("#wxshare").toggleClass("show");
	}

	require(["zepto", "underscore", "submit", "wxshare"], function($, _, submit, WXShare) {
//		 调用分享接口
		{$_cyoa_jsapi_[] = 'onMenuShareTimeline'}
		{$_cyoa_jsapi_[] = 'onMenuShareAppMessage'}
		{$_cyoa_jsapi_[] = 'onMenuShareQQ'}
		{$_cyoa_jsapi_[] = 'onMenuShareWeibo'}
		var wxshare = new WXShare();
		wxshare.load({rjson_encode($share_data)});
	});



</script>



{include file='mobile/footer.tpl'}