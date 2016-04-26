{include file='frontend/header.tpl'}

<body id="wbg_wxq_profile">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<header class="mod_profile_header type_C">
	<div class="center">
		<figure><img src="{$cinstance->avatar($wbs_uid)}" /></figure>
		<h1>{$wbs_username}<span>{$jobs[$wbs_user.cj_id].cj_name}</span></h1>
		<h2>畅移云工作－微信墙</h2>
	</div>
	<ul>
		<li><b>{$ct_fin}</b>已结束</li>
		<li><b>{$ct_running}</b>运行中</li>
		<li><a href="/wxwall/new"><i class="icon build"></i>申请微信墙</a></li>
	</ul>
</header>

<h2>审核中</h2>
<ul class="mod_common_list">
	{foreach $mine_list as $v}
	<li><a href="/wxwall/view/{$v['ww_id']}" class="m_link"><h1>{$v['ww_subject']}</h1><time>{$v['_begintime']} 开始</time></a></li>
	{/foreach}
</ul>

<h2>正运行</h2>
<ul class="mod_common_list">
	{foreach $run_list as $v}
	<li><a href="/wxwall/view/{$v['ww_id']}" class="m_link"><h1>{$v['ww_subject']}</h1><time>{$v['_begintime']} 开始</time></a></li>
	{/foreach}
</ul>

<h2>已结束</h2>
<ul class="mod_common_list" id="wxwall_ul">
	{include file='frontend/wxwall/list_li.tpl'}
</ul>

{include file='frontend/footer_nav.tpl'}

{if $perpage <= count($fin_list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'wxwall_ul', {'updated':'_wxwall_updated'});
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
