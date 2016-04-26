{include file='frontend/header.tpl'}

<body id="wbg_msq_ways">

<header class="mod_profile_header type_A">
	<div class="center">
		<figure><img src="{$cinstance->avatar($wbs_uid)}" /></figure>
		<h1>{$wbs_username}<span>{$jobs[$wbs_user.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－工作台</h2>
	</div>
</header>

<img height="300" src="{$qrcode_url}" />

{include file='frontend/footer.tpl'}