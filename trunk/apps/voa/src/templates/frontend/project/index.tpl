{include file='frontend/header.tpl'}

<body id="wbg_gzt_index">

<header class="mod_profile_header type_A">
	<div class="center">
		<figure><a href="/addressbook/show/{$wbs_uid}"><img src="{$cinstance->avatar($wbs_uid)}" /></a></figure>
		<h1>{$wbs_username}<span>{$jobs[$wbs.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－工作台</h2>
	</div>
	<ul>
		<li><b>{$ct_mine}</b>我参与的项目</li>
		<li><b>{$ct_mine}</b>进行的项目</li>
		<li><a href="/project/new"><i class="icon build"></i>新建项目</a></li>
	</ul>
</header>
{if 0 == $ct_mine + $ct_closed + $ct_done}
<em class="mod_empty_notice"><span>您还没有用过工作台<br/>快来试试吧</span></em>
{else}
<ul class="mod_common_list list">
	<li class="withicon mine">
		<a href="/project/list/my" class="m_link">
			<span class="m_icon"></span>
			<label>我参与的项目</label>
		</a>
	</li>
	<li class="withicon finished">
		<a href="/project/list/done" class="m_link">
			<span class="m_icon"></span>
			<label class="unread">已完成的项目</label>
		</a>
	</li>
	<li class="withicon closed">
		<a href="/project/list/closed" class="m_link">
			<span class="m_icon"></span>
			<label>已关闭的项目</label>
		</a>
	</li>
</ul>
{/if}

{include file='frontend/footer_nav.tpl'}


{include file='frontend/footer.tpl'}