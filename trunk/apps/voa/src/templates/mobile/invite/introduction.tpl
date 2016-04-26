{include file='mobile/header.tpl' css_file='app_invite_introduction.css'}

<div class="banner">
	<img src="../../../include/../../../misc/images/invite_qybj.png" width="100%" height="100%" style="">
	<div class="banner-logo-area" style="text-align: center;">
		<img src="{if $logo}{$logo}{else}../../../include/../../../misc/images/invite_logo.jpg{/if}" width="80" height="80" style="margin-top: 20px; border-radius: 50%" />
	</div>
	<div class="banner-text-area">
		<div class="banner-text">
			{$sitedata['sitename']}
		</div>
	</div>
</div>

<div class="introduction-area">
	<div class="introduction">简介</div>
	<div class="introduction-text">{$introduction}</div>
</div>

<div class="join-us-area">
	<div class="join-us">
		<a href="/frontend/invite/join?m_uid={$m_uid}">
			<div class="join-us-text">
				马上加入
			</div>
		</a>
	</div>
</div>

{include file='mobile/footer.tpl'}