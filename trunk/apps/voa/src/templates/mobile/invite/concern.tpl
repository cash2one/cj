{include file='mobile/header.tpl' css_file='app_invite.css'}
<body style="height: 95%;">
<div class="head" style="height: 53px;">
	<div class="head-text" style="font-size: 25px;">
		<img src="../../../include/../../../misc/images/invite_concern.png" width="28px" height="28px" class="concern-img" />
		<span class="concern-success">信息提交成功</span>
	</div>
</div>

<div class="central">
	<div class="central-circle-back">
		<img src="{if $logo}{$logo}{else}../../../include/../../../misc/images/invite_logo.jpg{/if}" width="80" height="80" style="border-radius: 50%" />
	</div>
	<div class="central-backing" style="height: 389px;">
		<div class="central-backing-head">
			<p class="central-backing-head-text">{$sitename}</p>
		</div>
		<div class="central-qrcode">
			<img src="{$qrcode}" width="170px" height="170px">
		</div>
		<div class="concern-text-area">
			<div class="concern-text">
				请长按此二维码并进行关注<br />
				提示:关注后才能使用企业号功能
			</div>
		</div>
	</div>
</div>
</body>

{include file='mobile/footer.tpl'}