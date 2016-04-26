{include file='frontend/header.tpl'}

<body id="wbg_xd_detail">

<header class="mod_profile_header type_A">
	<h1>{$shop['csp_name']}</h1>
	<h2>{$productive_set['title_examinator']}：{$productive['m_username']}</h2>
</header>

<div class="body mod_common_list_style">
	<div class="shoulder">
		<h1>{$productive['_created_ymd']} 巡店记录</h1>
		<h2>{$shop['csp_address']}</h2>
		<div class="score">
			<div>
				<em>{$total_score}<i>{if 0 < $productive_set['score_rule_diy']}%{else}分{/if}</i></em>
				<p>{if 0 < $productive_set['score_rule_diy']}合格率{else}总得分{/if}</p>
			</div>
		</div>
	</div>
	<ul class="mod_common_list">
		{foreach $items['p2c'][0] as $_id}
		<li>
			<a class="m_link" href="/frontend/productive/viewscore/pt_id/{$productive['pt_id']}/pti_id/{$_id}">
				<label>{$items[$_id]['pti_name']}</label>{$item2score[$_id]}{if 0 < $productive_set['score_rule_diy']}%{else}分{/if}
			</a>
		</li>
		{/foreach}
	</ul>
</div>

<div class="foot numbtns single">
	<input id="btn_go_back" type="reset" value="返回" />
</div>

{include file='frontend/footer.tpl'}
