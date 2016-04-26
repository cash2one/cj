{include file='frontend/header.tpl'}
<body id="wbg_rc_all">

<br/>

{if empty($list)}
<em class="mod_empty_notice"><span>没有您需要查看的分享列表</span></em>
{else}
<ul class="mod_common_list">
{foreach $list as $plan}
	<li class="withicon">
		<a href="/plan/share/detail/{$plan['pl_id']}">
			<img src="/misc/images/rc_type{$plan['pl_type'] + 1}.png" />
			<time>{$plan['_begin_at_m']}<i>月</i>{$plan['_begin_at_d']}<i>日</i><em>{$plan['_begin_at_t']}-{$plan['_finish_at_t']}</em></time>
			<div><h1>{$plan['pl_subject']}</h1></div>
			<div>{$plan['username']}</div>
		</a>
	</li>
{/foreach}
</ul>
{/if}

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}
