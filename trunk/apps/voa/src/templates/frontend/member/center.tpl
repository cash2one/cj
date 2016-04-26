{include file='frontend/header.tpl'}

<body id="wbg_grzx_index">

<header class="mod_profile_header type_B">
	<div class="center">
		<figure><img src="{$cinstance->avatar($wbs_user.m_uid)}" /></figure>
		<h1>{$wbs_user.m_username}<span>{$jobs[$wbs_user.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－个人中心</h2>
	</div>
	<ul>
		<li><a href="/sign/list"><i class="icon attendance"></i>我的考勤</a></li>
		<li><a href="/addressbook/edit"><i class="icon namecard"></i>我的资料</a></li>
	</ul>
</header>

<ul class="mod_common_list list">
	<li class="withicon approve">
		<a href="/askfor" class="m_link">
			<span class="m_icon"></span>
			<label>待我审批({$askfor_ct})</label>
		</a>
	</li>
	<li class="withicon meeting">
		<a href="/meeting" class="m_link">
			<span class="m_icon"></span>
			<label class="unread">待参加的会议({$meeting_ct})</label>
		</a>
	</li>
	<li class="withicon jobs">
		<a href="/project" class="m_link">
			<span class="m_icon"></span>
			<label>进行中的工作({$project_ct})</label>
		</a>
	</li>
</ul>

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}