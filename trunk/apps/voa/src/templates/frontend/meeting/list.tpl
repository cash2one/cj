{include file='frontend/header.tpl'}

<body id="wbg_hyt_list">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<nav class="mod_list_nav">
	<a href="/meeting/list/join"{if 'join' == $ac} class="current"{/if}>待参加</a>
	<a href="/meeting/list/fin"{if 'fin' == $ac} class="current"{/if}>已结束</a>
</nav>

{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>没有您需要查看的会议列表</span></em>
{else}
<ul id="meeting_list">
	{include file='frontend/meeting/list_li.tpl'}
</ul>
{/if}

{include file='frontend/footer_nav.tpl'}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多...</a>
{/if}

<script>
{literal}
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'meeting_list', {'updated':'_meeting_updated'});
	});
})
{/literal}
</script>


{include file='frontend/footer.tpl'}
