{include file='frontend/header.tpl'}

<body id="wbg_spl_list">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<h1>审批列表</h1>
{if empty($list)}
<em class="mod_empty_notice"><span>没有您需要查看的审批列表</span></em>
{else}
<ul class="mod_common_list" id="askfor_list">
	{include file='frontend/askfor/list_li.tpl'}
</ul>
{/if}

{include file='frontend/footer_nav.tpl'}

{if $perpage <= count($list)}
<a id="show_more" href="javascript:void(0)" class="mod_ajax_more">加载更多&gt;&gt;</a>
{/if}

{literal}
<script>
require(['dialog', 'business'], function() {
	$onload(function() {
		/** 加载更多 */
		var _more = new c_list_more();
		_more.init('show_more', 'askfor_list', {'updated':'_askfor_updated'});
	});
});
</script>
{/literal}


{include file='frontend/footer.tpl'}
