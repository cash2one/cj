{include file='frontend/header.tpl'}

<body>

{if empty($list)}
<em class="mod_empty_remind"><span>没有您需要查看的定时提醒列表</span></em>
{else}
<ul class="mod_common_list" id="remind_list">
	{include file='frontend/remind/list_li.tpl'}
</ul>
{/if}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{include file='frontend/footer_nav.tpl'}

{literal}
<script>
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'remind_list', {'updated':'_remind_updated'});
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
