{include file='frontend/header.tpl'}

<body id="wbg_spl_index">

<header class="mod_profile_header type_A">
	<div class="center">
		<figure><a href="/addressbook/show/{$wbs_uid}"><img src="{$cinstance->avatar($wbs_user.m_uid)}" /></a></figure>
		<h1>{$wbs_user.m_username}<span>{$jobs[$wbs_user.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－审批</h2>
	</div>
	<ul>
		<li><b>{$askfor_ct_my}</b>我的审批</li>
		<li><b>{$askfor_ct_deal}</b>待我审批</li>
		<li><a href="/askfor/new"><i class="icon build"></i>发起审批</a></li>
	</ul>
</header>

<ul class="mod_common_list list">
	<li class="withicon waiting">
		<a href="/askfor/list/deal" class="m_link">
			<span class="m_icon"></span>
			<label>待我审批</label>
		</a>
	</li>
	<li class="withicon launched">
		<a href="/askfor/list/my">
			<span class="m_icon"></span>
			<label class="unread">我发起的审批</label>
		</a>
	</li>
	<li class="withicon finished">
		<a href="/askfor/list/done">
			<span class="m_icon"></span>
			<label>已完成的审批</label>
		</a>
	</li>
</ul>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}