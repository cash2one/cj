{include file='frontend/header.tpl'}

<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>
<body id="wbg_xd_list">

<h1>我{if 'mine' == $ac}发出{else}收到{/if}的</h1>
{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>没有您需要查看的巡店记录</span></em>
{else}
<ul class="mod_common_list" id="inspect_list">
	{include file='frontend/inspect/list_li.tpl'}
</ul>
{/if}

{if !empty($list) && $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{literal}
<script>
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'inspect_list', {'page':'_page'});
	});
})
</script>
{/literal}

{include file='frontend/footer.tpl'}
