{include file='frontend/header.tpl'}

<body id="wbg_xsgj_profile">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<header class="admin">
	<h1>活跃人群</h1>
	<div class="infobox members"><div class="mod_members slider"><div class="sinner">
		<ul class="box">
			<!--li元素的id对应于php结构中的uid-->
			<li id="{$wbs_uid}"><a href="/footprint/mine"><img src="{$cinstance->avatar($wbs_uid)}" />{$wbs_username}</a></li>
			{foreach $team_users as $u}
			<li id="{$u['m_uid']}"><a href="/footprint/list/{$u['m_uid']}"><img src="{$cinstance->avatar($u['m_uid'])}" />{$u['m_username']}</a></li>
			{/foreach}
		</ul>
	</div></div></div>
	<h2>最新轨迹</h2>
</header>

{include file='frontend/footprint/footprint.tpl'}

{include file='frontend/footer.tpl'}
