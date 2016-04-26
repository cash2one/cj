{include file='frontend/header.tpl'}

<body id="wbg_bx_list">
<script src="{$wbs_javascript_path}/MOA.listmore.js"></script>

<h1>{if 'dealing' == $ac}待我审批{elseif 'dealed' == $ac}我已审批{else}我发起{/if}的报销</h1>
{if empty($list)}
<em class="mod_empty_notice mod_common_list_style"><span>没有您需要查看的报销列表</span></em>
{else}
<ul class="mod_common_list" id="reimburse_list">
	{include file='frontend/reimburse/search_li.tpl'}
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
		_more.init('show_more', 'reimburse_list', {'updated':'_reimburse_updated'});
	});
});
</script>
{/literal}

{include file='frontend/footer.tpl'}
