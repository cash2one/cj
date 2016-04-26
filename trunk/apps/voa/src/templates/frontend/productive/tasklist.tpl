{include file='frontend/header.tpl'}

<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>
<body id="wbg_xd_plan">

<h1>我的反馈计划</h1>
{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>您还没有反馈计划</span></em>
{else}
<ul class="mod_common_list" id="plan_list">
	{include file='frontend/productive/tasklist_li.tpl'}
</ul>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{literal}
<script>
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'plan_list', {'page':'_page'});
	});
})
</script>
{/literal}

{include file='frontend/footer.tpl'}
