{include file='frontend/header.tpl'}

<body id="wbg_wpx_profile">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<h2>{if 'unclosed' == $status}进行中{else}已结束{/if}的评选</h2>
<ul class="mod_common_list">
	{if $list}
	{include file='frontend/vote/list_li.tpl'}
	{else}
	<em class="mod_empty_notice"><span>没有您需要查看的评选列表</span></em>
	{/if}
</ul>

{include file='frontend/footer_nav.tpl'}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'vote_ul', {'updated':'_vote_updated'});
	});
});
{/literal}
</script>


{include file='frontend/footer.tpl'}
